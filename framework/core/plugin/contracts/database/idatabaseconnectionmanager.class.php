<?php
namespace Cannoli\Framework\Core\Plugin\Contracts\Database;

interface IDatabaseConnectionManager
{
	function createDatabaseConnection($id, IDatabaseConnectionFactory &$factory);

	function addDatabaseConnection($id, IDatabaseConnection &$connection);

	function getDatabaseConnection($id);

	function hasConnection($id);

	function getDatabaseConnectionIds();
}
?>