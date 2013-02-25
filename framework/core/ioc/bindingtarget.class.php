<?php
namespace Cannoli\Framework\Core\Ioc;

/**
 * Represents the binding target, or put differently, the type
 * that a given binding will resolve to when it's type is injected.
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Ioc
 * @author Rogier Pennink
 * @category Ioc
 */
class BindingTarget extends NamespacedTypeNameContainer
{
	private $typeName;

	private $binding;

	private $bindingScope;

	private $isClosure;

	private $closure;

	public function __construct(Binding &$binding, BindingScope &$bindingScope) {
		$this->binding = $binding;

		$this->bindingScope = $bindingScope;

		$this->isClosure = false;
	}

	// TODO: maybe abstract out the difference between a closure and a raw typename?
	// Otherwise the IOC container has to check what type of bindingtarget this is and
	// that shouldn't really be the ioc container's responsibility.
	public function to($typeName) {
		if ( $typeName instanceof \Closure ) {
			$this->isClosure = true;
			$this->closure = $typeName;
		}
		else {
			$this->setTypeName($typeName);
		}

		return $this->bindingScope;
	}

	public function isClosure() {
		return $this->isClosure;
	}

	public function getClosure() {
		return $this->closure;
	}
}
?>