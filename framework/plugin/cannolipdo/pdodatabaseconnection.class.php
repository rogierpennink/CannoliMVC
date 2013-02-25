<?php
namespace Cannoli\Framework\Plugin\CannoliPDO;

use Cannoli\Framework\Core\Plugin\Contracts\Database;

class PDODatabaseConnection extends Database\IDatabaseConnection
{
	// Server connection related
	function connect($host, $user, $pass, $dbName);
	function disconnect();
	function isConnected();

	// Transactions
	function transactionStart();
	function transactionRollback();
	function transactionCommit();

	// Setters / getters
	function getHost();
	function getUser();
	function getPass();

	// 
	function query($sql, array $args = array());
}
?>