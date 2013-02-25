<?php
namespace Cannoli\Framework\Core\Database;

use Cannoli\Framework\Core\Exception,
	Cannoli\Framework\Core\Plugin\Contracts\Database;

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

	
	public function getHost() {
		return $this->host;
	}

	public function getUser() {
		return $this->user;
	}

	public function getPass() {
		return $this->pass;
	}

	public function getDbName() {
		return $this->dbName;
	}

	// 
	function query($sql, array $args = array());

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