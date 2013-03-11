<?php
namespace Cannoli\Framework\Plugin\CannoliDCM;

use Cannoli\Framework\Application,
	Cannoli\Framework\Contract,
	Cannoli\Framework\Core\Plugin;

/**
 * The CannoliDCM class implements the IDatabaseConnectionManager contract for the
 * default set of cannoli database classes.
 *
 * @package Cannoli
 * @subpackage Framework\Plugin\CannoliDCM
 * @author Rogier Pennink
 * @category Database
 */
class CannoliDCM extends Plugin\PluginContractDeclaration implements Contract\Database\IDatabaseConnectionManager
{
	private $activeId = null;

	private $connections = array();

	private $databaseConnectionFactory;

	private $isInitialized = false;

	public function injectDatabaseConnectionFactory(Contract\Database\IDatabaseConnectionFactory &$factory) {
		$this->databaseConnectionFactory = $factory;
	}

	public function getConfigurationDomains() {
		return array("Cannoli.Application.Database");
	}

	/**
	 * Attempts to create a new IDatabaseConnection instance using the supplied DatabaseConnectionFactory
	 * and adds it to the manager's list of connections. Throws an InvalidArgumentException if a database-
	 * connection with the supplied $id already exists or if something was wrong with the $factory parameter.
	 * 
	 * @access public
	 * @param $id 			The id to which to attach the newly created database connection
	 * @param $factory 		The IDatabaseConnectionFactory instance that must be used to create a new connection with
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function createDatabaseConnection($id, Contract\Database\IDatabaseConnectionFactory &$factory) {
		if ( $this->hasConnection($id) ) {
			throw new \InvalidArgumentException("Database connection could not be registered with id \"$id\", id already exists.");
		}

		// Create new instance
		$connection =& $factory->getDatabaseConnection();
		$this->addDatabaseConnection($id, $connection);
	}

	/**
	 * Adds a new IDatabaseConnection instance to the manager with the given id.
	 * If the id already has a connection attached to it, or if something was wrong with
	 * the $connection parameter, an InvalidArgumentException will be thrown.
	 *
	 * @access public
	 * @param $id 			The id with which to register the new connection
	 * @param $connection 	The connection that must be added to the manager
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function addDatabaseConnection($id, Contract\Database\IDatabaseConnection &$connection) {
		if ( $this->hasConnection($id ) ) {
			throw new \InvalidArgumentException("Database connection could not be registered with id \"$id\", id already exists.");
		}

		$this->connections[$id] = $connection;
	}

	/**
	 * @access public
	 * @param $id 			The id to return a connection for
	 * @return IDatabaseConnection
	 * @throws InvalidArgumentException
	 */
	public function &getDatabaseConnection($id) {
		if ( !$this->hasConnection($id) ) {
			throw new \InvalidArgumentException("Database connection with id \"$id\" was not found");
		}

		return $this->connections[$id];
	}

	/**
	 * @access public
	 * @param $id 			The id to check for connections for
	 * @return bool
	 */
	public function hasConnection($id) {
		$this->initConnections();

		return isset($this->connections[$id]);
	}

	/**
	 * Returns the list of all the ids that currently have a database connection
	 * registered with them.
	 *
	 * @access public
	 * @return array 		The array of known ids
	 */
	public function getDatabaseConnectionIds() {
		$this->initConnections();

		return array_keys($this->connections);
	}

	/**
	 * Returns the currently active IDatabaseConnection instance. Throws an
	 * InvalidOperationException if no database connection exists or if no
	 * connection has been marked active.
	 *
	 * @access public
	 * @return IDatabaseConnection
	 * @throws InvalidOperationException
	 */
	public function getActiveConnection() {
		$this->initConnections();

		if ( !is_null($this->activeId) ) {
			return $this->getDatabaseConnection($this->activeId);
		}

		return false;
	}

	/**
	 * Sets the current 'active' connection. This is the connection that will be used by
	 * the framework for all its database-related tasks.
	 *
	 * @access public
	 * @param $id 			The id of the connection to activate
	 * @return void
	 */
	public function setActive($id) {
		if ( !$this->hasConnection() ) {
			throw new \UnexpectedValueException("Cannot set active database connection to '$id', connection not found");
		}

		$this->activeId = $id;
	}

	/**
	 * When this method is called, configurations will have been registered so
	 * we can initialize the database connections that were configured in the
	 * configuration files
	 *
	 * @access protected
	 * @return void
	 */
	protected function initConnections() {
		if ( $this->isInitialized ) return;

		$this->isInitialized = true;

		$connections = $this->config("Cannoli.Application.Database", "connections", array());

		foreach ( $connections as $connection ) {
			$this->createDatabaseConnection($connection->id, $this->databaseConnectionFactory);
			$db =& $this->getDatabaseConnection($connection->id);
			$db->connect($connection->host, $connection->user, $connection->pass, $connection->database);
		}

		// If no active id is set, we'll make the first id active
		if ( is_null($this->activeId) && count($connections) > 0 ) {
			$connectionIds = array_keys($this->connections);
			$this->activeId = $connectionIds[0];
		}
	}
}
?>