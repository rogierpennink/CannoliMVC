<?php
namespace Cannoli\Framework\Plugin\CannoliMySQLPDO;

/**
 * A thin wrapper around the framework's PDODatabaseConnection class. This
 * class only makes sure that PDO uses the correct DSN for MySQL.
 *
 * @package Cannoli
 * @subpackage Framework\Plugin\CannoliMySQL
 * @author Rogier Pennink
 * @category Database
 */
class MySQLPDODatabaseConnection extends PDODatabaseConnection
{
	/**
	 * Initiates a connection with the target database server, using the specified
	 * hostname, user credentials, and database name. This method overwrites the
	 * PDODatabaseConnection::connect method so it can add the correct DSN string
	 * just before proceeding with creating a connection.
	 *
	 * @access public
	 * @param $host 		The hostname of the database server
	 * @param $user 		Username with which to connect
	 * @param $pass 		The password that belongs to the $username
	 * @param $dbName 		The name of the database to connect to
	 * @return bool 		true if connection was established, false otherwise
	 * @throws DatabaseConnectionException
	 */
	public function connect($host, $user, $pass, $dbName) {
		$this->setDSN("mysql:host=".$host.";dbname=".$dbName);
		return parent::connect($host, $user, $pass, $dbName);
	}

	/**
	 * Overrides the creation of a new IResultSet instance so that we can make use
	 * of MySQLPDOResultSet which will throw exceptions for certain unsupported
	 * method calls.
	 *
	 * @access protected
	 * @param $sql  		A valid mysql query
	 * @param $queryArgs 	The parameters that need to be bound to the statement
	 * @return IResultSet
	 */
	protected function createResultSetFromQuery($sql, array $queryArgs) {
		$resultSet = new MySQLPDOResultSet();
		$resultSet->construct($this->pdo, $sql, $queryArgs);

		return $resultSet;
	}
}
?>