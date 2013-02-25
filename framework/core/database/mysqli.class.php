<?php
/**
 * File:			mysqli.class.php
 * Author:			Rogier Pennink
 * Creation Date:	01/09/2008
 * Last Revision:	03/09/2008
 * Description:		Implements the public interface that is defined by the Database class using
 *					the mysqli interface.
 */

/**
 * Include the base class file.
 */
require_once dirname( __FILE__ ) . "/database.class.php";
require_once dirname( __FILE__ ) . "/../logging/logger.php";
 
class ResultSetMysqli extends ResultSet
{
	/* The prepared statement and various state and information variables. */
	protected $stmt;
	protected $isclosed;
	protected $numrows;
	protected $metadata;
	protected $results;
	protected $bindVarArray;
	protected $hasSet;
	protected $fields;
	
	protected $logger;
	
	/**
	 * Function:	__construct (constructor)
	 * Return:		none
	 * Arguments:	A valid mysqli_stmt object on which 'execute' has already been called.
	 * Description:	Constructs a new MySQLi Result Set.
	 */
	public function __construct($stmt)
	{
		$this->logger = new TextLogger("mysqli_errors.txt", "DBMySQLi Error Log");
		$this->isclosed = false;
		$this->results = array();
		$this->bindVarArray = array();
		$this->fields = false;
		
		/* Now we call 'parse' . */
		$this->parse($stmt);		
	}
	
	/**
	 * Function:	__destruct (destructor)
	 * Return:		none
	 * Arguments:	none
	 * Description:	Destroys the result set, closing the prepared statement as well.
	 */
	public function __destruct()
	{
		$this->release();
	}
	
	/**
	 * Function:	parse
	 * Return:		none
	 * Arguments:	The statement object to get data from
	 * Description: 'Parses' (or simply: gets) metadata from the prepared statement.
	 */
	protected function parse($stmt) {
		/* Set the stmt variable. */
		$this->stmt  = $stmt;
				
		/* Check if there is metadata (whether it was a select query, in other words). */
		if ( ( $this->metadata = $this->stmt->result_metadata() ) == NULL )
		{
			$this->hasSet = false;
			$this->numrows = $this->stmt->affected_rows;
			return;
		}
				
		/* We only need to bind the variables once, so check if they're bound already. */
		$this->fields = array();
		$fields = $this->metadata->fetch_fields();
		foreach ( $fields as $field ) {
			$this->fields[] = $field->name;
			$this->bindVarArray[] = &$this->results[$field->name];
		}

		if ( !call_user_func_array(array($this->stmt, "bind_result"), $this->bindVarArray) ) {
			$this->logger->addField("Type", "Statement Binding Error");
			$this->logger->addField("Error", "No. ". mysqli_errno() ." - ". mysqli_error());
			$this->logger->log();
		}
		
		/* The result is a set, thus update the $hasSet variable. */
		$this->hasSet = true;
		$this->numrows = $this->stmt->num_rows;
	}
	
	/**
	 * Function:	release
	 * Return:		none
	 * Arguments:	none
	 * Description:	Closes the current prepared statement, releasing its resources.
	 */
	public function release()
	{
		if ( !$this->isclosed ) {
			$this->stmt->free_result();
			$this->stmt->close();
		}
			
		$this->isclosed = true;
	}
	
	/**
	 * Function:	getNumRows
	 * Return:		An integer
	 * Arguments:	none
	 * Description:	Based on the type of query, this will either return the number of
	 *				affected rows (INSERT, UPDATE, DELETE) or the number of rows in the
	 *				result set (SELECT).
	 */
	public function getNumRows()
	{
		return $this->numrows;
	}
	
	/**
	 * Function:	getFields
	 * Return:		The field names of the resultset.
	 * Arguments:	None
	 * Description:	Gets the field names for the current result set.
	 */
	public function getFields() {
		return $this->fields;
	}
	
	/**
	 * Function:	fetchAssoc
	 * Return:		One row as an associative array.
	 * Arguments:	none
	 * Description:	Fetches one row of data from the buffered mysqli_stmt. Use this method as if you
	 *				were using mysql_fetch_assoc.
	 */
	public function fetchAssoc() {	
		/* If the result is a set, return false. */
		if ( !$this->hasSet ) return false;
		
		$result = $this->stmt->fetch();
		if ( $result != NULL && $result !== false ) {
			$output = array();
			foreach ( $this->results as $key => $value ) $output[$key] = $value;
			return $output;
		}
		elseif ( $result === false ) {
			$this->logger->addField( "Type", "Statement Binding Error" );
			$this->logger->addField( "Error", "No. ". mysqli_errno() ." - ". mysqli_error() );
			$this->logger->log();
			return false;
		}
		return NULL;
	}		
	
	/**
	 * Function:	fetchObject
	 * Return:		A row as an object.
	 * Arguments:	none
	 * Description:	Uses fetchAssoc to construct an object representation of the fetched row.
	 */
	public function fetchObject()
	{
		$var = $this->fetchAssoc();
		return ( $var == NULL ? false : (object)$var );
	}
	
	/**
	 * Function:	insertedID
	 * Return:		The last inserted ID
	 * Arguments:	None
	 * Description:	Returns the last automatically generated identifier for the last insert query.
	 */
	public function insertedID()
	{
		return !$this->hasSet ? $this->stmt->insert_id : 0;
	}

	public function seek($pos)
	{
		@$this->stmt->data_seek($pos);
	}
}

class DBMysqli extends Database
{
	/**
	 * Protected variables that describe the current object state.
	 */
	protected $connected;
	protected $host;
	protected $user;
	protected $pass;
	protected $db;
	
	protected $mysqli;
	protected $logger;
	
	protected $trans_in_progress;
	
	/**
	 * Function:	__construct (constructor)
	 * Return:		none
	 * Arguments:	Server host, user, pass, database name, all optional
	 * Description:	Forwards the received parameters (if valid) to the connect method.
	 */
	public function __construct( $host = "", $user = "", $pass = "", $db = "" )
	{
		$this->logger = new TextLogger( "mysqli_errors.txt", "DBMySQLi Error Log" );
		if ( strlen( $host ) > 0 && strlen( $user ) > 0 )
			$this->connect($host, $user, $pass, $db);
		$this->trans_in_progress = false;
	}
	
	/**
	 * Function:	__destruct (destructor)
	 * Return:		none
	 * Arguments:	none
	 * Description:	Rolls back the current transaction if it is in progress and closes the mysqli object
	 */
	public function __destruct() {
		if ( $this->isConnected() ) {
			if ( $this->trans_in_progress ) $this->transactionRollback();
			$this->mysqli->close();
		}
	}
	
	/**
	 * Function:	connect
	 * Return:		Boolean [success/failure]
	 * Arguments:	The database host, username and password, plus the database name optionally
	 * Description:	Attempts to make a connection with the specified database server.
	 */
	public function connect( $host, $user, $pass ) {
		/* If already connected, return false. */
		if ( $this->isConnected() ) return false;
		
		/* Set variables to new content. */
		$this->host = $host; $this->user = $user; $this->pass = $pass;
		$this->connected = false;
		
		/* Attempt to make connection. */
		if ( func_num_args() > 3 )
		{
			$this->db = func_get_arg( 3 );
			$this->mysqli = new mysqli( $this->host, $this->user, $this->pass, $this->db );
		}
		else
			$this->mysqli = new mysqli( $this->host, $this->user, $this->pass );
		
		/* Check if it succeeded. */
		if ( $this->mysqli->connect_errno )
		{
			$this->logger->addField( "Type", "Connection Error" );
			$this->logger->addField( "Info", $this->user."@".$this->host );
			$this->logger->addField( "Error", "No. ". $this->mysqli->connect_errno ." - ". $this->mysqli->connect_error );
			$this->logger->log();

			return false;
		}
		
		$this->trans_in_progress = false;
			
		/* No errors happened. */
		$this->connected = true;
		return $this->connected;
	}
	
	/**
	 * Function:	setDatabase
	 * Return:		Boolean [success/failure]
	 * Arguments:	The new database name
	 * Description:	Attempts to change (or simply set) the new database.
	 */
	public function setDatabase( $dbname )
	{
		if ( !$this->mysqli->select_db( $dbname ) )
		{
			$this->logger->addField( "Type", "Database Selection Error" );
			$this->logger->addField( "Info", $dbname );
			$this->logger->addField( "Error", "No. ". $this->mysqli->errno ." - ". $this->mysqli->error );
			$this->logger->log();
			return false;
		}
		
		/* Update the database name value and return true. */
		$this->db = $dbname;
		return true;
	}
	
	/**
	 * Function:	getCurrentDatabase
	 * Return:		The name of the current database, or an empty string if not connected
	 * Arguments:	None
	 * Description:	Returns the name of the currently selected database, or an empty string if not connected.
	 */
	public function getCurrentDatabase()
	{
		return ( $this->isConnected() ? $this->db : "" );
	}
	
	/**
	 * Function:	isConnected
	 * Return:		The current connection state.
	 * Arguments:	none
	 * Description:	Getter method for the $connected variable.
	 */
	public function isConnected()
	{
		return $this->connected;
	}
	
	/**
	 * Function:	disconnect
	 * Return:		Boolean [success/failure]
	 * Arguments:	none
	 * Description:	This method fails only if the connection was already closed.
	 */
	public function disconnect()
	{
		/* Early out condition. */
		if ( !$this->isConnected() ) return false;
		
		/* Roll back the transaction. */
		$this->transactionRollback();
		
		/* Close, updated connection state and return. */
		$this->mysqli->close();
		$this->connected = false;
		
		return true;
	}
	
	public function transactionBegin() {
		if ( !$this->isConnected() ) return false;
		if ( $this->trans_in_progress ) return false;
		$this->mysqli->autocommit(false);
		$this->trans_in_progress = true;
	}
	
	public function transactionCommit() {
		if ( !$this->isConnected() ) return false;
		if ( !$this->trans_in_progress ) return false;
		$this->mysqli->commit();
		$this->mysqli->autocommit(true);
		$this->trans_in_progress = false;
	}
	
	public function transactionRollback() {
		if ( !$this->isConnected() ) return false;
		if ( !$this->trans_in_progress ) return false;
		$this->mysqli->rollback();
		$this->mysqli->autocommit(true);
		$this->trans_in_progress = false;
	}
	
	/**
	 * Function:	query
	 * Return:		A ResultSet object or false on failure
	 * Arguments:	An sql query and multiple optional arguments for the prepared statement
	 * Description:	Executes a prepared query and returns the resulting ResultSet, which is really
	 *				a ResultSetMysqli object.
	 */
	public function query($sql)	{
		/* Attempt to create a prepared statement. */
		if ( !($stmt = $this->mysqli->prepare($sql)) ) {
			if ( $this->mysqli->errno == 1295 ) {
				/* Basically this is an 'early out' case. We don't need to do any
				 * parameter binding so we just feed the result of the query into
				 * a new ResultSetMysqli object and return it. */
				$result = $this->mysqli->query($sql);
				$rset = new ResultSetMysqli($result);
				return $rset;
			}
			else {
				$this->logger->addField( "Type", "Query Preparation Error" );
				$this->logger->addField( "Query", $sql );
				$this->logger->addField( "Error", "No. ". $this->mysqli->errno ." - ". $this->mysqli->error );
				$this->logger->log();
				return false;
			}
		}
		
		/* We must now bind the arguments, so construct a format string and an array of arguments. */
		$args = array(); $formatstring = ""; $references = array();
		for ( $i = 1; $i < func_num_args(); $i++ ) {	
			/* This codeblock constructs the format string. */
			$arg = func_get_arg($i);
			if ( is_int($arg) ) $formatstring .= "i";
			else if ( is_double($arg) ) $formatstring .= "d";
			else if ( is_string($arg) ) $formatstring .= "s";
			else $formatstring .= "b";
			
			/* Add the current argument to the $args array. */
			$references[$i - 1] = $arg;
			$args[] = &$references[$i - 1];		
		}
						
		/**
		 * Now, since no method in the stmt object exists that takes an array of parameters, we must
		 * call the function with call_user_func_array and supply the arguments as an array. Note: we
		 * only do this if there are arguments to be passed.
		 */
		if ( count($args) > 0 )	{
			array_unshift( $args, $formatstring );
			if ( call_user_func_array( array( $stmt, "bind_param" ), $args ) === false ) {
				$this->logger->addField( "Type", "Parameter Binding Error" );
				$this->logger->addField( "Query", $sql );
				$this->logger->addField( "Error", "No. ". $stmt->errno ." - ". $stmt->error );
				$this->logger->log();
				return false;
			}
		}
		
		/* Execute the query. */
		if ( $stmt->execute() === false ) {
			$this->logger->addField( "Type", "Query Execution Error" );
			$this->logger->addField( "Query", $sql );
			$this->logger->addField( "Error", "No. ". $stmt->errno ." - ". $stmt->error );
			$this->logger->log();		
			return false;
		}
		
		/* Store the result. (Return false if this fails) */
		if ( $stmt->store_result() === false ) {
			$this->logger->addField( "Type", "Result Storage Error" );
			$this->logger->addField( "Query", $sql );
			$this->logger->addField( "Error", "No. ". $stmt->errno ." - ". $stmt->error );
			$this->logger->log();		
			return false;
		}
		
		/* Create a new result set and then close the statement. */
		$rset = new ResultSetMysqli($stmt);
		
		/* Return the result set. */
		return $rset;
	}
	
	/**
	 * Function:	existsAndGet
	 * Return:		A single row
	 * Arguments:	A table name and an associative array
	 * Description:	Used to retrieve unique records. The user passes the name of the table from which to
	 *				get the unique record and an associative array in which the keys represent field names
	 *				and the values represent field values. The method will return the first record of the
	 *				result set that matches the arguments passed. Returns false if no records found.
	 */
	public function existsAndGet($table, array $uniqid)
	{
		/* Just to be sure, we escape the strings to rule out sql injection through user input. */
		$table = $this->mysqli->escape_string($table);
		
		/**
		 * Construct the query.
		 */
		$sql = "SELECT * FROM `$table` WHERE";
		foreach ( $uniqid as $field => $value )
		{
			$value = $this->mysqli->escape_string( $value );
			if ( !is_numeric( $value ) ) $value = "'". $value ."'";
			$sql .= " `". $this->mysqli->escape_string( $field ) ."`=". $value;
			$sql .= " AND";
		}
		$sql = substr_replace( $sql, "", -4 );
		
		/* Execute the query. */
		$result = $this->query($sql);
		
		/* If no rows, return false, otherwise return the result set. */
		if ( $result->getNumRows() == 0 )
			return false;
		else
		{
			$arr = $result->fetchAssoc();
			$result->release();
			return $arr;
		}
	}
	
	public function prep($arg)
	{
		return $arg;
	}
}

?>