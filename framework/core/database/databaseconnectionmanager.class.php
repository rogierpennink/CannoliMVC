<?php
namespace Cannoli\Framework\Core\Database;

use Cannoli\Framework\Core\Plugin\Contracts\Database;

/**
 * Manages IDatabaseConnection instances.
 *
 * @package Cannoli
 * @subpackage Framework\Core\Database
 * @author Rogier Pennink
 * @category Database
 */
class DatabaseConnectionManager implements Database\IDatabaseConnectionManager
{
	private $connections = array();

	/**
	 * @access public
	 * @param $id 			The unique id through which the new connection must be identified
	 * @param $factory 		A database connection factory object
	 * @return IDatabaseConnection
	 * @throws InvalidArgumentException 	If the id already exists
	 */
	public function &createDatabaseConnection($id, Database\IDatabaseConnectionFactory &$factory) {
		/* First check if a connection with the given id doesn't already exist. */
		$this->checkIdAvailability($id);

		$this->connections[$id] =& $factory->getDatabaseConnection();
		return $this->connections[$id];
	}
	
	/**
	 * @access public
	 * @param $id 			The unique id through which the connection must be identified
	 * @param $connection 	IDatabaseConnection instance
	 * @return void
	 * @throws InvalidArgumentException 	If the id already exists
	 */
	public function addDatabaseConnection($id, Database\IDatabaseConnection &$connection) {
		/* First check if a connection with the given id doesn't already exist. */
		$this->checkIdAvailability($id);

		$this->connections[$id] = $connection;
	}

	/**
	 * @access public
	 * @param $id 			The id for which to find a corresponding IDatabaseConnection
	 * @return mixed 		IDatabaseConnection instance if one is found, false otherwise
	 */
	public function getDatabaseConnection($id) {
		if ( !$this->hasConnection($id) ) {
			return false;
		}

		return $this->connections[$id];
	}

	/**
	 * Returns the list of ids for which an IDatabaseConnection instance is currently registered
	 *
	 * @access public
	 * @return array 		The array of known database connection Ids.
	 */
	public function getDatabaseConnectionIds() {
		return array_keys($this->connections);
	}

	/**
	 * Returns whether or not an IDatabaseConnection with the specified id has been registered.
	 *
	 * @access public
	 * @param $id 			The id to check for
	 * @return bool 		true if a connection was found, false otherwise
	 */
	public function hasConnection($id) {
		return isset($this->connections[$id]);
	}

	/**
	 * Throws an InvalidArgumentException if a connection with the specified id has already
	 * been registered. This is a simple helper method for the connection registration methods.
	 *
	 * @access private
	 * @param $id 			The id to check for
	 * @return void
	 * @throws InvalidArgumentException 	If the id already exists
	 */
	private function checkIdAvailability($id) {
		if ( $this->hasConnection($id) ) {
			throw new \InvalidArgumentException("Database connection with id \"". $id ."\" already exists.");
		}
	}
}
?>