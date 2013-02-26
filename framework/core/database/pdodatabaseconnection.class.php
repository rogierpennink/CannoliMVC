<?php
namespace Cannoli\Framework\Core\Database;

use Cannoli\Framework\Core\Exception,
	Cannoli\Framework\Core\Plugin\Contracts\Database;

/**
 * A default implementation of the IDatabaseConnection interface. Database
 * plugins may use this as a base class for database-specific implementations.
 *
 * @package Cannoli
 * @subpackage Framework\Core\Database
 * @author Rogier Pennink
 * @category Database
 */
class PDODatabaseConnection extends Database\IDatabaseConnection
{
	private $dsn;

	private $pdo;

	private $isConnected;

	private $host;

	private $user;

	private $pass;

	private $dbName;

	public function __construct() {
		$this->reset();
	}

	public function reset() {
		$this->dsn 			= "";
		$this->isConnected 	= false;
		$this->host 		= "";
		$this->user 		= "";
		$this->pass 		= "";
		$this->dbName 		= "";

		
		if ( !is_null($this->pdo) ) {
			unset($this->pdo);
			$this->pdo = null;
		}
	}

	/**
	 * @access public
	 * @param $host 		The hostname of the database server
	 * @param $user 		Username with which to connect
	 * @param $pass 		The password that belongs to the $username
	 * @param $dbName 		The name of the database to connect to
	 * @return void
	 * @throws DatabaseConnectionException
	 */
	public function connect($host, $user, $pass, $dbName) {
		if ( $this->isConnected() ) {
			$message = "Database Connection instance is already connected.";
			throw new Exception\Database\DatabaseConnectionException($message, $host, $user, $pass, $dbName);
		}

		if ( $this->dsn == "" ) {
			$message = "DSN was not set.";
			throw new Exception\Database\DatabaseConnectionException($message, $host, $user, $pass, $dbName);
		}

		try {
			$this->pdo = new PDO($this->dsn);
		}
		catch (\PDOException $e) {
			$message = "An internal error occurred during the connection attempt: ". $e->getMessage() ." (". $e->getCode() .")";
			throw new Exception\Database\DatabaseConnectionException($message, $host, $user, $pass, $dbName);
		}

		$this->isConnected = true;
	}

	/**
	 * Attempts to disconnect the current database connection. If the connection is
	 * not currently in connected state, this method will generate a DatabaseDisconnectException.
	 * 
	 * @access public
	 * @return void
	 * @throws DatabaseDisconnectException
	 */
	public function disconnect() {
		if ( !$this->isConnected() ) {
			$message = "Database Connection instance is not connected.";
			throw new Exception\Database\DatabaseDisconnectException($message);
		}

		unset($this->pdo);
		$this->pdo = null;

		$this->isConnected = false;
	}

	/**
	 * Returns whether or not the database connection is currently in a connected state.
	 *
	 * @access public
	 * @return bool 		true if connected, false otherwise
	 */
	public function isConnected() {
		return !is_null($this->pdo) && $this->isConnected;
	}

	/**
	 * @access public
	 * @return bool 		true if a transaction is currently active (when autocommit is not on)
	 * @throws DatabaseNotConnectedException
	 */
	public function isInTransaction() {
		$this->checkIfConnected();

		// Apparently returns integers, so transform to bool
		return $this->pdo->inTransaction() ? true : false;
	}

	/**
	 * @access public
	 * @return bool 		true if transaction successfully started, false otherwise
	 * @throws DatabaseNotConnectedException
	 */
	public function transactionStart() {
		$this->checkIfConnected();

		return $this->pdo->beginTransaction() ? true : false;
	}

	/**
	 * Rolls back the current transaction, discarding any changes. Note, not all storage engines
	 * support transactions and in those cases this method may return true even if the rollback
	 * didn't actually happen.
	 *
	 * @access public
	 * @return bool 		true if rollback was successful, false otherwise
	 * @throws DatabaseNotConnectedException
	 */
	public function transactionRollback() {
		$this->checkIfConnected();

		return $this->pdo->rollBack() ? true : false;
	}

	/**
	 * Commits the current transaction, finalizing any changes.
	 * 
	 * @access public
	 * @return bool 		true if commit was successful, false otherwise
	 * @throws DatabaseNotConnectedException
	 */
	public function transactionCommit() {
		$this->checkIfConnected();

		return $this->pdo->commit() ? true : false;
	}

	/**
	 * Returns the hostname of the database server that this connection is associated with.
	 *
	 * @access public
	 * @return string 		The hostname of the database server
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * Returns the username of the database server that this connection used to connect with.
	 *
	 * @access public
	 * @return string 		The username of the database server credentials
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Returns the password of the database server that this connection used to connect with.
	 *
	 * @access public
	 * @return string 		The password of the database server credentials
	 */
	public function getPass() {
		return $this->pass;
	}

	/**
	 * Returns the database name that the connection is currently associated with.
	 *
	 * @access public
	 * @return string 		The database name
	 */
	public function getDbName() {
		return $this->dbName;
	}

	/**
	 * Executes an arbitrary SQL query on the database server and returns the result if any.
	 * 
	 * @access public
	 * @param $sql 			The SQL string
	 * @param $args 		Arguments that need to be bound to the query
	 * @return IResultSet
	 * @throws DatabaseNotConnectedException
	 */
	public function query($sql, array $args = array()) {
		$this->checkIfConnected();

		// Default value
		$stmt = false;

		try {
			$stmt = $this->pdo->prepare($sql);
		}
		catch ( \PDOException $e ) {
			$message = "Could not prepare query: ". $e->getMessage() ." (". $e->getCode() .")";
			throw new Exception\Database\DatabaseQueryException($message, $sql, $args);
		}

		if ( $stmt === false ) {
			$errorInfo = $this->pdo->getErrorInfo();
			if ( is_null($errorInfo[1]) )
				$message = "Could not prepare query, but no error code was set.";
			else
				$message = "Could not prepare query: ". $errorInfo[2] ." (". $errorInfo[1] .")";
			throw new Exception\Database\DatabaseQueryException($message, $sql, $args);
		}

		return $this->createResultSetFromPDOStatement($stmt, $args);
	}

	/**
	 * Creates an IResultSet instance using the PDOStatement object created by the call to PDO::prepare.
	 * Child classes can override this method should they wish to implement a different IResultSet
	 * implementation without replacing the whole PDODatabaseConnection::query method.
	 *
	 * @access protected
	 * @param $stmt 		A valid PDOStatement instance
	 * @param $queryArgs 	The parameters that need to be bound to the statement
	 * @return IResultSet
	 */
	protected function createResultSetFromPDOStatement(\PDOStatement &$stmt, array $queryArgs) {

	}

	/**
	 * Sets the dsn string that will be used when the pdo object is constructed.
	 *
	 * @access protected
	 * @param $dsn 			The dsn string that is used for constructing this PDO object
	 * @return void
	 */
	protected function setDSN($dsn) {
		$this->dsn = $dsn;
	}

	/**
	 * Helper method that throws an exception if there is no connection with the database.
	 *
	 * @access private
	 * @return void
	 * @throws DatabaseNotConnectedException
	 */
	private function checkIfConnected() {
		if ( !$this->isConnected() ) {
			throw new Exception\Database\DatabaseNotConnectedException("Not connected to any database.");
		}
	}
}
?>