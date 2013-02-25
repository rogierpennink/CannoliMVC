<?php
interface IResultSet {
	// Metadata
	function getRowCount();
	function insertedID();

	// Data fetch
	function fetchObject();
	function fetchAssoc();

	function seek($pos);
	function seekStart();
}
?>