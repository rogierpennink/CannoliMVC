<?php
require_once dirname(__FILE__) ."/database.class.php";

class ResultSetPDO implements IResultSet
{
	protected $stmt;
	
	protected $numrows;
	
	protected $fields;
	
	protected $isclosed;
	
	public function __construct($stmt) {
		$this->numrows = 0;
		$this->isclosed = true;
		$this->fields = array();
		
		$this->parse($stmt);
	}
	
	public function __destruct() {
		$this->release();
	}
	
	protected function parse($stmt) {
		$this->stmt = $stmt;
		
		$this->numrows = $stmt->rowCount;
		
		/* Fetch columns. */
		$num_columns = $stmt->columnCount;
		$this->fields = array();
		for ( $i = 0; $i < $num_columns; $i++ ) {
			// TODO: According to php documentation, fetchColumn
			$metadata = $stmt->getColumnMeta($i);
			$this->fields[$i] = $metadata["name"];
		}
		
		$this->isclosed = false;
	}
	
	public function release() {
		if ( $this->stmt instanceof PDOStatement ) {
			$this->stmt->closeCursor();
			unset($this->stmt);
		}
		$this->isclosed = true;
	}
	
	public function getNumRows() {
		return $this->numrows;
	}
	
	public function getFields() {
		return $this->fields;
	}
	
	public function fetchAssoc() {
		$result = $this->stmt->fetch(PDO::FETCH_ASSOC);
		if ( $result != NULL && $result !== false ) return $result;
		return NULL;
	}
	
	public function fetchObject() {
		$result = $this->stmt->fetch(PDO::FETCH_OBJ);
		if ( $result != NULL && $result !== false ) return $result;
		return NULL;
	}
}
?>