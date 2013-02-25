<?php
require_once dirname(__FILE__) ."/../../utility/singleton.class.php";

abstract class DaoBase extends Singleton
{
	/**
	 * Using late static binding, the getInstance method is able to instantiate
	 * inheriting classes correctly.
	 * TODO: Need to truly make it a singleton and keep only one instance
	 */
	public static function getInstance() {
		$clsName = get_called_class();
		
		if ( $clsName == __CLASS__ ) return null;
		
		try {
			$instance = new $clsName;
			return $instance;
		}
		catch ( Exception $e ) {
			return null;
		}
	}
	
	protected function __construct() {}
	
	protected function __clone() {}
}
