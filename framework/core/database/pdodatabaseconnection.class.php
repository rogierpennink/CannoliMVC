<?php
namespace Cannoli\Framework\Core\Database;

use Cannoli\Framework\Core\Exception,
	Cannoli\Framework\Core\Plugin\Contracts\Database;

class PDODatabaseConnection extends Database\IDatabaseConnection
{
	private $dsn;

	private $pdo;

	private $isConnected;

	public function __construct() {
		$this->dsn 			= "";
		$this->pdo 			= null;
		$this->isConnected 	= false;

	}
	// Server connection related

	/**
	 * @access public
	 * @param $host 		The hostname of the database server
	 * @param $user 		Username with which to connect
	 * @param $pass 		The password that belongs to the $username
	 * @param $dbName 		The name of the database to connect to
	 * @return bool 		true if connection was established, false otherwise
	 * @throws DatabaseConnectionException
	 */
	public function connect($host, $user, $pass, $dbName) {
		if ( $this->isConnected() ) {
			$message = "Database Connection instance is already connected.";
			throw new Exception\Database\DatabaseConnectionException($message, $host, $user, $pass, $dbName);
		}


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
	}

	function isConnected();

	// Transactions
	function transactionStart();
	function transactionRollback();
	function transactionCommit();

	// Setters / getters
	function getHost();
	function getUser();
	function getPass();

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
}
?>