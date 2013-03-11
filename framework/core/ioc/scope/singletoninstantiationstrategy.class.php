<?php
namespace Cannoli\Framework\Core\Ioc\Scope;

/**
 * Represents the singleton instantiation strategy, to be used in
 * the dependency injection framework.
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Ioc\Scope
 * @author Rogier Pennink
 * @category Ioc
 */
class SingletonInstantiationStrategy extends BaseInstantiationStrategy
{
	private static $objCache = array();

	/**
	 * @access public
	 * @param $typeName 			The name of the class that must be instantiated as singleton
	 * @param $constructorArgs 		The array of arguments for the constructor
	 */
	public function &instantiate(\ReflectionClass &$rc, array $constructorArgs) {
		$typeName = $rc->getName();

		// Check the object cache for instances of $typeName
		if ( isset(self::$objCache[$typeName]) ) {
			return self::$objCache[$typeName];
		}

		// Construct a new instance of the requested type name
		if ( !class_exists($typeName) ) {
			// TODO: possibly throw exception here.
			return null;
		}

		// TODO: See if there is a way of doing this without reflection
		self::$objCache[$typeName] = $rc->newInstanceArgs($constructorArgs);
		
		$this->onNewInstanceCreated(self::$objCache[$typeName]);

		return self::$objCache[$typeName];
	}
}
?>