<?php
namespace Cannoli\Framework\Core\Database;

interface IDatabaseConnection
{
	// Server connection related
	function connect();
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