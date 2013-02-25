<?php
namespace Cannoli\Framework\Core\Plugin\Contracts\Database;

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
}
?>