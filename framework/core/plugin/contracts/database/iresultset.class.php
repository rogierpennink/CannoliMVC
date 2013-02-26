<?php
namespace Cannoli\Framework\Core\Plugin\Contracts\Database;

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

	// Cursor management
	function seek($pos);
	function seekStart();

	function execute();
	function recycle();
	function close();
}
?>