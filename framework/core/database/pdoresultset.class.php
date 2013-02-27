<?php
namespace Cannoli\Framework\Core\Database;

use Cannoli\Framework\Core\Exception;
	Cannoli\Framework\Core\Plugin\Contracts\Database;

/**
 * Implements the IResultSet interface using the PDO
 * database connection wrapper.
 *
 * @package Cannoli
 * @subpackage Framework\Core\Database
 * @author Rogier Pennink
 * @category Database
 */
class PDOResultSet implements Database\IResultSet
{
	private $repeatCount;

	private $pdo;

	private $sql;

	private $queryArgs;

	private $stmt;

	private $hasExecuted;

	public function __construct(\PDO &$pdo, $sql, array $queryArgs) {
		$this->pdo 			= $pdo;
		$this->sql 			= $sql;
		$this->queryArgs	= $queryArgs;
		$this->repeatCount	= 0;
		$this->stmt 		= null;
		$this->hasExecuted 	= false;
	}

	// Metadata
	function getRowCount();
	function insertedID();

	// Data fetch
	function fetchObject();
	function fetchAssoc();

	// Cursor management
	function seek($pos);
	function seekStart();

	/**
	 * Close the result set and the connection with the database. If resultset is already closed
	 * this method returns false.
	 * 
	 * @access public
	 * @return bool 		True if result set successfully closed, false otherwise
	 */
	public function close() {
		if ( is_null($this->stmt) ) return false;

		$this->stmt->closeCursor();
		unset($this->stmt);
		$this->stmt = null;

		$this->repeatCount = 0;
	}

	/**
	 * Recycles the result set, leaving it ready to be executed again.
	 */
	public function recycle() {
		if ( is_null($this->stmt) ) {
			$message = "Cannot recycle result set because it is not currently opened.";
			throw new Exception\Database\DatabaseResultSetException($message);
		}

		$this->stmt->closeCursor();
	}

	/**
	 * @access public
	 */
	public function execute() {
		if ( is_null($this->stmt) ) {
			$this->createStatement();
		}
		else {
			$this->recycle();	
		}

		// Bind params
		$this->bindParams();
	}

	/**
	 * @access protected
	 */
	protected function bindParams() {
		$this->checkStatement();

		if ( $this->hasExecuted ) {
			$message = "Parameters must be bound in a result set before query is executed.";
			throw new Exception\Database\DatabaseResultSetException($message);
		}
		
		foreach ( $this->queryArgs as $key => $value ) {
			$this->stmt->bindValue($key);
		}
	}

	/**
	 * @access protected
	 */
	protected function createStatement() {
		if ( $this->repeatCount > 0 || !empty($this->queryArgs) )
			$this->stmt = $this->pdo->prepare($this->sql);
		else
			$this->stmt = $this->pdo->query($this->sql);

		$this->repeatCount++;
	}

	/**
	 * @access private
	 */
	private function checkStatement() {
		if ( is_null($this->stmt) ) {
			$message = "No active database query was found.";
			throw new Exception\Database\DatabaseQueryException($message, $this->sql, $this->queryArgs);
		}
	}
}
?>