<?php
/**
 * File:			mysql.class.php
 * Author:			Rogier Pennink
 * Creation Date:	03/09/2008
 * Last Revision:	04/09/2008
 * Description:		Implements the public interface that is defined by the Database class using
 *					the mysql interface.
 */
 
require_once dirname( __FILE__ ) . "/database.class.php";
require_once dirname( __FILE__ ) . "/../logging/logger.php";

class ResultSetMysql extends ResultSet
{
	/* Protected state variables. */
	protected $result;
	protected $link;
	protected $numrows;
	protected $fields;

	protected $isclosed;
	
	/**
	 * Function:	__construct (constructor)
	 * Return:		none
	 * Arguments:	A mysql_query result resource
	 * Description:	Initializes the result set.
	 */
	public function __construct( $result, $link )
	{
		$this->link = $link;
		$this->result = $result;
		$this->isclosed = false;
		$this->fields = false;
		
		/* In case of a 'real result set' */
		if ( $result !== true )
		{					
			$nr = mysql_num_rows( $this->result );
			$ar = mysql_affected_rows( $this->link );
			if ( $nr > $ar ) $this->numrows = $nr;
			else $this->numrows = $ar;
		}
		else
		{
			$this->numrows = mysql_affected_rows( $this->link );
		}
	}

	//public function __destruct()
	//{
	//	$this->release();
	//}
	
	/**
	 * Function:	fetchAssoc
	 * Return:		A single row from the result set.
	 * Arguments:	none
	 * Description:	Wraps around a call to mysql_fetch_assoc to return an associative array of one
	 *				record in the result set.
	 */
	public function fetchAssoc()
	{
		return $this->result !== true ? mysql_fetch_assoc( $this->result ) : false;
	}
	
	/**
	 * Function:	fetchObject
	 * Return:		A single row from the result set.
	 * Arguments:	none
	 * Description:	An alternative to fetchAssoc, returns an object of which the publicly accessible
	 *				attributes hold the values for each field name.
	 *				If a query returns no result set (INSERT/UPDATE etc.), this method returns false.
	 */
	public function fetchObject()
	{
		return $this->result !== true ? mysql_fetch_object( $this->result ) : false;
	}
	
	/**
	 * Function:	getNumRows
	 * Return:		Number of rows in the result set
	 * Arguments:	none
	 * Description:	Gets the number of affected, or the number of fetched rows, depending on query type.
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
		if ( !is_array($this->fields) && $this->result !== true ) {
			$this->fields = array();
			$numfields = mysql_num_fields($this->result);
			for ( $i = 0; $i < $numfields; $i++ ) {
				$this->fields[] = mysql_field_name($this->result, $i);
			}
		}
		return $this->fields;
	}
	
	/**
	 * Function:	insertedID
	 * Return:		The last inserted ID
	 * Arguments:	None
	 * Description:	Returns the last automatically generated identifier for the last insert query.
	 */
	public function insertedID()
	{
		return $this->result !== true ? 0 : mysql_insert_id( $this->link );
	}

	public function seek($pos)
	{
		@mysql_data_seek($this->result, $pos);
	}

	public function release()
	{
		if ( !$this->isclosed && $this->result !== true ) {
			mysql_free_result($this->result);
			$this->is_closed = true;
		}
	}
}
 
class DBMysql extends Database
{	
	/* Protected state variables. */
	protected $con;
	
	protected $host;
	protected $user;
	protected $pass;
	protected $db;
	protected $connected;
	protected $logger;			// Of type Logger
	
	protected $trans_in_progress;
	
	/**
	 * Function:	__construct (constructor)
	 * Return:		none
	 * Arguments:	Server host, user, pass, database name, all optional
	 * Description:	Forwards the received parameters (if valid) to the connect method.
	 */
	public function __construct($host = "", $user = "", $pass = "", $db = "")
	{
		$this->querycounter = 0;
		$this->logger = new TextLogger("mysql_errors.txt", "DBMySQL Error Log");
		if ( strlen( $host ) > 0 && strlen( $user ) > 0 )
			$this->connect( $host, $user, $pass, $db );
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
			mysql_close($this->con);
		}
	}
	
	/**
	 * Function:	connect
	 * Return:		Boolean [success/failure]
	 * Arguments:	The database host, username and password, plus the database name optionally
	 * Description:	Attempts to make a connection with the specified database server.
	 */
	public function connect( $host, $user, $pass )
	{
		/* If already connected, return false. */
		if ( $this->isConnected() ) return false;
		
		/* Set variables to new content. */
		$this->host = $host; $this->user = $user; $this->pass = $pass;
		$this->connected = false;
		
		/* Attempt to make connection. */
		$this->con = mysql_connect( $this->host, $this->user, $this->pass );
		
		/* Check if it succeeded. */
		if ( mysql_errno() )
		{
			$this->logger->addField( "Type", "Connection Error" );
			$this->logger->addField( "Error", "No. ". mysql_errno() ." - ". mysql_error() );
			$this->logger->log();
			return false;
		}
		
		/* Select database if it is provided. */
		if ( func_num_args() > 3 )
			$this->setDatabase( func_get_arg( 3 ) );
		
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
		if ( !mysql_select_db( $dbname, $this->con ) )
		{
			$this->logger->addField( "Type", "Database selection error" );
			$this->logger->addField( "Info", $dbname );
			$this->logger->addField( "Error", "No. ". mysql_errno() ." - ". mysql_error() );
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
		
		/* Close, updated connection state and return. */
		mysql_close( $this->con );
		$this->connected = false;
		
		/* Roll back any transaction. */
		$this->transactionRollback();
		
		return true;		
	}
	
	public function transactionBegin() {
		if ( !$this->isConnected() ) return false;
		if ( $this->trans_in_progress ) return false;
		$this->query("START TRANSACTION");
		$this->trans_in_progress = true;
	}
	
	public function transactionCommit() {
		if ( !$this->isConnected() ) return false;
		if ( !$this->trans_in_progress ) return false;
		$this->query("COMMIT");
		$this->trans_in_progress = false;
	}
	
	public function transactionRollback() {
		if ( !$this->isConnected() ) return false;
		if ( !$this->trans_in_progress ) return false;
		$this->query("ROLLBACK");
		$this->trans_in_progress = false;
	}

	
	/**
	 * Function:	query
	 * Return:		A result set object or false on failure
	 * Arguments:	An sql argument and multiple optional arguments that need be prepared and inserted in the query.
	 * Description:	Queries the database after having inserted the escaped variables into the SQL.
	 */
	public function query( $sql )
	{
		/* Raise the query counter. */
		$this->querycounter += 1;
		
		/* Import the profiler. */
		global $profiler;
		
		/* Function arguments information. */
		$numArgs = func_num_args();
		$args = func_get_args();
		
		/**
		 * Walk through the query, replacing question marks with arguments. Fail if number of
		 * question marks is greater than the number of arguments.
		 */
		$pos = 0; $i = 1;
		while ( ( $pos = strpos( $sql, "?", $pos ) ) !== false )
		{
			if ( $i > $numArgs )
			{
				$this->logger->addField( "Type", "Query execution error" );
				$this->logger->addField( "Query", $sql );
				$this->logger->addField( "Error", "Not enough arguments for query." );
				$this->logger->log();
				return false;
			}
			
			$sql = substr_replace( $sql, $this->prep( $args[$i++] ), $pos, 1 );
			$pos = $pos + strlen($this->prep($args[$i-1]));
		}		
		
		$profiler->startBlock("Query ". $this->querycounter .": ". $sql);
						
		/**
		 * Now, query and pass the result of the query to a new result set object. Log and return
		 * false on error.
		 */
		if ( ( $result = mysql_query( $sql, $this->con ) ) === false )
		{
			$this->logger->addField( "Type", "Query execution error" );
			$this->logger->addField( "Query", $sql );
			$this->logger->addField( "Error", "No. ". mysql_errno() ." - ". mysql_error() );
			$this->logger->log();
			return false;
		}
		
		$res = new ResultSetMysql($result, $this->con);		
		$profiler->endBlock("Query ". $this->querycounter .": ". $sql);
		
		return $res;
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
	public function existsAndGet( $table, array $uniqid )
	{
		if ( !$this->isConnected() ) return false;
		
		/* Just to be sure, we escape the strings to rule out sql injection through user input. */
		$table = mysql_real_escape_string( $table, $this->con );
		
		/**
		 * Construct the query. TODO: simply fill it with question marks and do call_user_func_array on the
		 * query method to pass in the arguments.
		 */
		$sql = "SELECT * FROM `$table` WHERE";
		foreach ( $uniqid as $field => $value )
		{
			$sql .= " `". mysql_real_escape_string($field, $this->con) ."`=". $this->prep($value);
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
			unset( $result );
			return $arr;
		}
	}

	
	/**
	 * Function:	prep
	 * Return:		A prepared argument
	 * Arguments:	The variable to be prepared.
	 * Description:	If necessary, escapes the argument, so as to prevent sql-injection. This is a protected helper method.
	 */
	public function prep( $arg )
	{
		$arg = is_string($arg) && !is_numeric($arg) ? addslashes($arg) : $arg;
		if ( get_magic_quotes_gpc() )		// Strip slashes if magic quotes is on
		{
			$arg = stripslashes($ar );
		}

		if ( !is_numeric( $arg ) )			// Add mysql's slashes if not numeric
		{
			$arg = "'" . mysql_real_escape_string($arg) . "'";
		}

		return $arg;
	}
}

?>