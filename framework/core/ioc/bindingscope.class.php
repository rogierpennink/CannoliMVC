<?php
namespace Cannoli\Framework\Core\Ioc;

/**
 * Represents the binding scope which tells the IoC container which
 * instantiation strategy to use for the binding to which the
 * BindingScope belongs.
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Ioc
 * @author Rogier Pennink
 * @category Ioc
 */
class BindingScope
{
	private $instantiationStrategy;

	public function __construct() {
		$this->inTransientScope();
	}

	public function &getInstantiationStrategy() {
		return $this->instantiationStrategy;
	}

	public function inTransientScope() {
		$this->instantiationStrategy = new Scope\TransientInstantiationStrategy();
	}

	public function inSingletonScope() {
		$this->instantiationStrategy = new Scope\SingletonInstantiationStrategy();
	}
}
?>