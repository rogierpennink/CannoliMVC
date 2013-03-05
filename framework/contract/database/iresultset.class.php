<?php
namespace Cannoli\Framework\Contract\Database;

/**
 * Represents a database query result set.
 *
 * @package Cannoli
 * @subpackage Framework\Core\Plugin\ContractsDatabase
 * @author Rogier Pennink
 * @category Database
 */
interface IResultSet
{
	// Metadata
	function getRowCount();
	function insertedID();

	// Data fetch
	function fetchObject();
	function fetchAssoc();
	function fetchAllAsObject();
	function fetchAllAsAssoc();

	// Cursor management
	function seek($pos);
	function seekStart();

	function execute();
	function recycle();
	function close();
}
?>