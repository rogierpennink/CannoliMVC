<?php
namespace Cannoli\Framework\Plugin\CannoliMySQLPDO;

use Cannoli\Framework\Contract,
	Cannoli\Framework\Core\Exception,
	Cannoli\Framework\Core\Plugin;
	

/**
 * Implements the IResultSet interface using the PDO
 * database connection wrapper.
 *
 * @package Cannoli
 * @subpackage Framework\Core\Database
 * @author Rogier Pennink
 * @category Database
 */
class PDOResultSet implements Contract\Database\IResultSet
{
	private $repeatCount;

	private $pdo;

	private $sql;

	private $queryArgs;

	private $stmt;

	private $hasExecuted;

	private $cursorLocation;

	public function construct(\PDO &$pdo, $sql, array $queryArgs) {
		$this->pdo 				= $pdo;
		$this->sql 				= $sql;
		$this->queryArgs		= $queryArgs;
		$this->repeatCount		= 0;
		$this->cursorLocation 	= 0;
		$this->stmt 			= null;
		$this->hasExecuted 		= false;
	}

	// Metadata
	public function getRowCount() {}
	public function insertedID() {}

	// Data fetch
	public function fetchObject() {
		if ( !$this->hasExecuted ) $this->execute();

		return $this->stmt->fetch(\PDO::FETCH_OBJ, \PDO::FETCH_ORI_ABS, $this->cursorLocation++);
	}
	
	/**
	 *
	 */
	public function fetchAssoc() {
		if ( !$this->hasExecuted ) $this->execute();

		return $this->stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, $this->cursorLocation++);
	}

	/**
	 *
	 */
	public function fetchAllAsObject() {
		if ( !$this->hasExecuted ) $this->execute();

		return $this->stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	/**
	 *
	 */
	public function fetchAllAsAssoc() {
		if ( !$this->hasExecuted ) $this->execute();

		return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Attempts to set the cursor to the specified location in the result set.
	 *
	 * @access public
	 * @param $pos 			The position to which to attempt to set the cursor to
	 * @return bool
	 */
	public function seek($pos) {
		if ( $pos < 0 || $pos > $this->getRowCount() ) {
			return false;
		}

		$this->cursorLocation = $pos;
	}

	/**
	 * Attemps to set the cursor to the start of the result set.
	 *
	 * @access public
	 * @return bool
	 */
	public function seekStart() {
		$this->seek(0);
	}

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
	 *
	 * @access public
	 * @return void
	 * @throws DatabaseResultSetException
	 */
	public function recycle() {
		if ( is_null($this->stmt) ) {
			$message = "Cannot recycle result set because it is not currently opened.";
			throw new Exception\Database\DatabaseResultSetException($message);
		}

		$this->stmt->closeCursor();
	}

	/**
	 * Executes the sql query with the query arguments that were passed in the
	 * constructor. If the statement has already been executed previously, it
	 * will be recycled.
	 *
	 * @access public
	 * @return void
	 * @throws DatabaseQueryExecutionException
	 */
	public function execute() {
		if ( is_null($this->stmt) ) {
			$this->createStatement();
		}
		else {
			$this->recycle();	
		}
		
		$this->bindParams();

		try {
			$this->stmt->execute();	
		}
		catch ( \PDOException $e ) {
			$message = "Could not execute query: ". $e->getMessage() ." (". $e->getCode() .")";
			throw new Exception\Database\DatabaseQueryExecutionException($message);
		}

		$this->hasExecuted = true;
	}

	/**
	 * Binds the parameters specified in the queryArgs to the to-be-executed statement.
	 *
	 * @access protected
	 * @return void
	 * @throws DatabaseResultSetException
	 */
	protected function bindParams() {
		$this->checkStatement();

		if ( $this->hasExecuted ) {
			$message = "Parameters must be bound in a result set before query is executed.";
			throw new Exception\Database\DatabaseResultSetException($message);
		}
		
		$numCount = 1;
		foreach ( $this->queryArgs as $key => $value ) {
			$dataType = \PDO::PARAM_STR;
			if ( is_int($value) ) $dataType = \PDO::PARAM_INT;
			if ( is_bool($value) ) $dataType = \PDO::PARAM_BOOL;
			if ( is_null($value) ) $dataType = \PDO::PARAM_NULL;

			if ( is_numeric($key) )
				$this->stmt->bindValue($numCount++, $value, $dataType);
			else
				$this->stmt->bindValue($key, $value, $dataType);
		}
	}

	/**
	 * @access protected
	 */
	protected function createStatement() {
		if ( $this->repeatCount > 0 || !empty($this->queryArgs) )
			$this->stmt = $this->pdo->prepare($this->sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
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