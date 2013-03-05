<?php
namespace Cannoli\Framework\Plugin\CannoliDCM;

use Cannoli\Framework\Contract,
	Cannoli\Framework\Core\Plugin;

class CannoliDCM extends Plugin\Plugin implements Contract\Database\IDatabaseConnectionManager
{
	function createDatabaseConnection($id, Contract\Database\IDatabaseConnectionFactory &$factory) {}

	function addDatabaseConnection($id, Contract\Database\IDatabaseConnection &$connection) {}

	function getDatabaseConnection($id) {}

	function hasConnection($id) {}

	function getDatabaseConnectionIds() {}
}
?>