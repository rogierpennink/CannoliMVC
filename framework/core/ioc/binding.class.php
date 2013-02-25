<?php
namespace Cannoli\Framework\Core\Ioc;

/**
 * Represents a binding between a requested interface and its
 * assigned implementation.
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Ioc
 * @author Rogier Pennink
 * @category Ioc
 */
class Binding extends NamespacedTypeNameContainer
{
	private $bindingTarget = null;

	private $bindingScope = null;

	public function __construct($typeName) {
		$this->setTypeName($typeName);

		$this->createBindingScope();

		$this->createBindingTarget();
	}

	/**
	 * Overrides the NamespaceContainer's setNamespace method
	 * by also setting the binding target's namespace.
	 *
	 * @access public
	 * @param $namespace 			The namespace in which to resolve to-be-bound classes
	 * @return void
	 */
	public function setNamespace($namespace) {
		parent::setNamespace($namespace);

		$this->bindingTarget->setNamespace($namespace);
	}

	public function &getBindingTarget() {
		return $this->bindingTarget;
	}

	public function &getBindingScope() {
		return $this->bindingScope;
	}

	private function createBindingTarget() {
		$this->bindingTarget = new BindingTarget($this, $this->bindingScope);
	}

	private function createBindingScope() {
		$this->bindingScope = new BindingScope($this);
	}
}
?>