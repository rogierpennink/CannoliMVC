<?php
namespace Cannoli\Framework\Core\Plugin\Contracts\Database;

interface IDatabaseConnectionFactory
{
	function &getDatabaseConnection();
}
?>