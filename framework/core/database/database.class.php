<?php
/**
 * File:			database.class.php
 * Author:			Rogier Pennink
 * Creation Date:	01/09/2008
 * Last Revision:	01/09/2008
 * Description:		The database class is an abstract base class that defines an interface through which users may
 *					connect to and use databases of different types. Each 'different type' is a subclass of class
 *					Database and can be requested through Database's static factory methods.
 */
 
/**
 * Include the subclass files of class Database to allow the Database factory to create instances of these
 * classes.
 */
require_once dirname( __FILE__ ) . "/mysql.class.php";
require_once dirname( __FILE__ ) . "/mysqli.class.php";
require_once dirname(__FILE__) .'/../object.class.php';

/**
 * Class:		DBObject
 * Author:		Rogier Pennink
 * Description:	Declares an interface for deriving classes to implement, in order to allow uniform updating and inserting to the database.
 */
abstract class DBObject extends Object
{
	
	/* Table metadata. */
	protected $dbFields;
	protected $dbTable;
	
	public function __construct($dbTable = '', array &$dbFields = array()) {
		$this->dbTable = $dbTable;
		
		/* If the database fields have been specified in the call to the constructor,
		 * use those, otherwise inspect this class' public members.
		 */
		if ( !empty($dbFields) ) {
			$this->dbFields = $dbFields;
		}
		else {
			$this->dbFields = $this->getPublicVars();
		}
	}
	
	/**
	 * If a method hasn't been defined, the __callstatic method is automatically called
	 * by PHP. Inside this function we can then search for patterns in the function
	 * name and delegate the call to more specific handlers.
	 * @param $method		The name of the method that has been called
	 * @param $args			The arguments passed to the method
	 * @return mixed		null if no delegate was found, the output of the delegate function otherwise
	 */
	public function __call($method, $args) {
		if ( preg_match('/^getBy([a-zA-Z0-9_]+)*$/', $method, $match) ) {
			return $this->__callHandler_getBy($match[1], $args);
		}
		return null;
	}
	
	/**
	 * The __callHandler_getBy method is a delegate for the __call method in the case of
	 * a method call starting with getBy (which is assumed to be a call to the database).
	 * This method doesn't do any form of caching and so should be carefully used.
	 * @access private
	 * @param $fieldsString		The string with field names to search on [format: FieldnameAndFieldname2AndFieldname3]
	 * @param $args				The arguments to pass to the query; must be an array.
	 * @return mixed 			False in the case of an error, 
	 */
	private function __callHandler_getBy($fieldsString, array $args) {
		$db = Application::getInstance()->getDB();
		
		/* TODO: Database field names with 'and' in them will be a problem here. */
		/* Split fields and check if number of arguments equals number of fields. */
		$fields = explode('And', $fieldsString);
		if ( count($fields) != count($args) ) return false; 
		if ( $this->dbTable == '' ) return false;
				
		/* Set up the SQL; Merge SQL with args and then execute the query. */
		$sql = 'SELECT * FROM `'. $this->dbTable .'` WHERE '. implode(' AND ', array_map(function($el) {return '`'. Object::strFromCamelCase($el) .'` = ?';}, $fields));
		$args = array_merge(array($sql), $args);
		$result = call_user_func_array(array($db, 'query'), $args);
		if ( $result === false ) return false;
				
		/* Walk through the reuslts and compile an array of objects. */
		$objects = array();
		while ( $data = $result->fetchAssoc() ) {
			$object = new get_class($this);
			foreach ( $data as $key => $value ) {
				$object->{$key} = is_numeric($value) ? $value : stripslashes($value);
			}
			$objects[] = $object;
		}
		
		return $objects;
	}
	
	abstract public function insert();
	abstract public function update();
	abstract public function save();
}

/**
 * Class:		ResultSet
 * Author:		Rogier Pennink
 * Description:	Describes a result set that may be returned by a database query.
 */
abstract class ResultSet
{
	abstract public function release();
	abstract public function getNumRows();
	abstract public function fetchAssoc();
	abstract public function fetchObject();
	abstract public function insertedID();
	abstract public function seek($pos);
	abstract public function getFields();

	public function seekStart() {
		$this->seek(0);
	}
}

abstract class Database
{
	/**
	 * Database type constants. For use in the get method.
	 */
	const DB_DEFAULT	= "defaultdb";
	const DB_MYSQL		= "mysql";
	const DB_MYSQLI		= "mysqli";
	const DB_POSTGRESQL	= "postgresql";			//!IMPORTANT not yet supported
	
	/* The arrays of established database objects. If filled, one of them may be returned upon requesting a new database object. */
	protected static $mysqlArr;
	protected static $mysqliArr;
	protected static $postgreArr;
		
	/**
	 * Function:	get
	 * Return:		A database-derived object of requested type
	 * Arguments:	A constant as defined by the Database class
	 * Description:	Returns a new database object of the requested type that implements
	 *				the Database interface.
	 */
	public static function get( $dbType, $new = false )
	{
		if ( !is_array(self::$mysqlArr) ) self::$mysqlArr = array();
		if ( !is_array(self::$mysqliArr) ) self::$mysqliArr = array();
		if ( !is_array(self::$postgreArr) ) self::$postgreArr = array();
		
		switch ( $dbType )
		{
			case Database::DB_MYSQLI:
			{
				/* Create new database objcts if necessary. */
				if ( count( self::$mysqliArr ) == 0 || $new )
				{
					$db = new DBMysqli();
					self::$mysqliArr[] = $db;
				}				
			
				return self::$mysqliArr[count(self::$mysqliArr) - 1];
			}			
		}
		
		/* Create new database objcts if necessary. */
		if ( count( self::$mysqlArr ) == 0 || $new )
		{
			$db = new DBMysql();
			self::$mysqlArr[] = $db;
		}
		
		return self::$mysqlArr[count(self::$mysqlArr) - 1];
	}

	/*********************** DEFINE INTERFACE DEFINITIONS BELOW ****************************/
	protected $querycounter;
	
	/**
	 * Database connection and selection methods.
	 */
	abstract public function connect( $host, $user, $pass );
	abstract public function setDatabase( $database );
	abstract public function getCurrentDatabase();
	abstract public function isConnected();
	abstract public function disconnect();
	
	abstract public function transactionBegin();
	abstract public function transactionRollback();
	abstract public function transactionCommit();

	/**
	 * Function:	query
	 * Return:		A result set
	 * Arguments:	A query in the mysqli style (mysql variants emulate mysqli)
	 * Description:	Executes a query and returns a resultset object. Multiple arguments may be passed
	 *				though the $sql parameter is mandatory and contains the actual query with question
	 *				marks for to-be-substituted data.
	 */
	abstract public function query($sql);
	
	/**
	 * Function:	existsAndGet
	 * Return:		A single row
	 * Arguments:	A table name and an associative array
	 * Description:	Used to retrieve unique records. The user passes the name of the table from which to
	 *				get the unique record and an associative array in which the keys represent field names
	 *				and the values represent field values. The method will return the first record of the
	 *				result set that matches the arguments passed. Returns false if no records found.
	 */
	abstract public function existsAndGet($table, array $uniqid);
	
	abstract public function prep($arg);

	public function num($table, array $criteria = array()) {
		/* Construct the where clause. */
		$where = ""; $qStr = ""; $args = array();
		foreach ( $criteria as $key => $val ) {
			$args[] = $val;
			$where .= " `".$key."`=? AND";
		}
		$where = $where != "" ? substr($where, 0, strlen($where)-4) : "";
		$sql = array("SELECT COUNT(*) as num FROM `$table`". ($where != ""?" WHERE".$where:""));
		/* Execute a SELECT COUNT query to retrieve the number of rows given the criteria. */
		$result = call_user_func_array(array($this, "query"), array_merge($sql, $args));
		if ( $result === false ) return false;
		$data = $result->fetchObject();
		$result->release();
		return $data->num;
	}
}