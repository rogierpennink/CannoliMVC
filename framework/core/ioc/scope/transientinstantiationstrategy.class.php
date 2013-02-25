<?php
namespace Cannoli\Framework\Core\Ioc\Scope;

/**
 * Represents the transient instantiation strategy, to be used in
 * the dependency injection framework. Builds a new object everytime
 * a type must be instantiated. This is the default instantiation strategy.
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Ioc\Scope
 * @author Rogier Pennink
 * @category Ioc
 */
class TransientInstantiationStrategy implements IInstantiationStrategy
{
	/**
	 * @access public
	 * @param $typeName 			The name of the class that must be instantiated as new object
	 * @param $constructorArgs 		The array of arguments for the constructor
	 */
	public function instantiate(\ReflectionClass &$rc, array $constructorArgs) {
	    return $rc->newInstanceArgs($constructorArgs);
	}	
}
?>