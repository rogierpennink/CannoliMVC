<?php
namespace Cannoli\Framework\Contract\Database;

interface IDatabaseConnectionFactory
{
	function &getDatabaseConnection();
}
?>