<?php
namespace Cannoli\Framework\Core\Plugin\Contracts\Database;

interface IDatabaseConnection
{
	// Server connection related
	function connect($host, $user, $pass, $dbName);
	function disconnect();
	function isConnected();

	// Transactions
	function isInTransaction();
	function transactionStart();
	function transactionRollback();
	function transactionCommit();

	// Setters / getters
	function getHost();
	function getUser();
	function getPass();
	function getDbName();

	// 
	function query($sql, array $args = array());
}
?>