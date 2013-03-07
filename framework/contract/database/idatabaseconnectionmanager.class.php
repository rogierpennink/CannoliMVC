<?php
namespace Cannoli\Framework\Contract\Database;

interface IDatabaseConnectionManager
{
	function createDatabaseConnection($id, IDatabaseConnectionFactory &$factory);

	function addDatabaseConnection($id, IDatabaseConnection &$connection);

	function getDatabaseConnection($id);

	function hasConnection($id);

	function getDatabaseConnectionIds();

	function getActiveConnection();

	function setActive($id);
}
?>