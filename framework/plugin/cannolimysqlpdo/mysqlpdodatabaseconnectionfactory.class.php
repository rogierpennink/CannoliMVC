<?php
namespace Cannoli\Framework\Plugin\CannoliMySQLPDO;

use Cannoli\Framework\Contract,
	Cannoli\Framework\Core\Plugin;

class MySQLPDODatabaseConnectionFactory extends Plugin\PluginContractDeclaration implements Contract\Database\IDatabaseConnectionFactory
{
	public function &getDatabaseConnection() {
		$connection = new MySQLPDODatabaseConnection();
		return $connection;
	}
}
?>