<?php
namespace Cannoli\Framework\Core\Ioc\Scope;

abstract class BaseInstantiationStrategy implements IInstantiationStrategy
{
	private $closure;

	public function __construct(\Closure $closure = null) {
		$this->closure = $closure;
	}

	abstract public function instantiate(\ReflectionClass &$rc, array $constructorArguments);

	protected function onNewInstanceCreated($instance) {
		if ( !is_null($this->closure) ) {
			$closure = $this->closure;
			$closure($instance);
		}
	}
}
?>