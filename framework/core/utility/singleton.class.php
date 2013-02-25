<?php
namespace Cannoli\Framework\Core\Utility;

abstract class Singleton
{
	private static $instances = array();
	
	/**
	 * Using late static binding, the getInstance method is able to instantiate
	 * inheriting classes correctly. 
	 */
	public static function &getInstance() {
		$clsName = get_called_class();
		
		if ( $clsName == __CLASS__ ) return null;
		
		try {
			if ( empty(self::$instances[$clsName]) ) {
				self::$instances[$clsName] = new static();
			}
			return self::$instances[$clsName];
		}
		catch ( Exception $e ) {
			return null;
		}
	}
	
	protected function __construct() {}
	
	protected function __clone() {}
}
?>