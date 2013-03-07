<?php
namespace Cannoli\Framework\Core\Ioc;

/**
 * Acts as a base class for user BindingModules that set up
 * the dependencies for the ioc container.
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Ioc
 * @author Rogier Pennink
 * @category Ioc
 */
abstract class BindingModule
{
	private $container;

	private $bindings;

	private $namespace = "";

	abstract public function load();

	public function setIocContainer(IocContainer &$container) {
		$this->container = $container;
	}

	public function setNamespace($namespace) {
		$this->container->setNamespace($namespace);
	}

	public function clearNamespace() {
		$this->container->clearNamespace();
	}

	/**
	 * Acts as a shortcut to prevent the user from having to hassle with
	 * creating and adding Binding instances to IocContainer.
	 *
	 * @access protected
	 * @param $typeName 		The typeName that is going to be bound to an implementation
	 * @return BindingTarget 	The created BindingTarget instance
	 */
	protected function &bind($typeName) {
		return $this->container->bind($typeName);
	}
}
?>