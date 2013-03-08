<?php
namespace Cannoli\Framework\Plugin\CannoliDCM;

use Cannoli\Framework\Application,
	Cannoli\Framework\Contract,
	Cannoli\Framework\Core\Plugin;

class CannoliDCM extends Plugin\PluginContractDeclaration implements Contract\Database\IDatabaseConnectionManager
{
	private $activeId = null;

	function createDatabaseConnection($id, Contract\Database\IDatabaseConnectionFactory &$factory) {}

	function addDatabaseConnection($id, Contract\Database\IDatabaseConnection &$connection) {}

	function getDatabaseConnection($id) {}

	function hasConnection($id) {}

	function getDatabaseConnectionIds() {}

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
}
?>