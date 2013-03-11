<?php
namespace Cannoli\Framework\Core\Ioc;

use Cannoli\Framework\Core\Exception;

class IocContainer extends NamespaceContainer
{
	private $bindings = array();

	public function __construct(array $modules) {
		foreach ( $modules as $module ) {
			$this->registerModule($module);
		}
	}

	/**
	 * Registers a new binding module with the container. The binding module
	 * is immediately loaded so that any conflicts occur before any application code
	 * is executed.
	 *
	 * @access public
	 * @param $module 				The binding module that needs to be loaded
	 * @return void
	 */
	public function registerModule(BindingModule &$module) {
		// We have to clear the namespace before and after loading the module
		// so that other modules don't have to worry about a namespace already
		// having been set.
		$module->setIocContainer($this);
		$this->clearNamespace();
		$module->load();
		$this->clearNamespace();
	}

	/**
	 * Registers a new Binding with the IoC container.
	 *
	 * @access public
	 * @param $binding 				The to-be-registered binding
	 * @return void
	 */
	public function addBinding(Binding &$binding) {
		// Check if there isn't already a binding with the given typename
		if ( ($conflictingBinding = $this->getBindingByTypeName($binding->getTypeName())) ) {
			// Throw an exception because a wrong binding is a configuration problem
			// so no use in failing silently
			$implementation = $conflictingBinding->getBindingTarget() == null ? "" : $conflictingBinding->getBindingTarget()->getTypeName();
			throw new Exception\Ioc\IocBindingRegistrationException("Binding already exists for given typeName (". $conflictingBinding->getTypeName() .").", $conflictingBinding->getTypeName(), $implementation);
		}

		$this->bindings[$binding->getTypeName()] = $binding;
	}

	/**
	 * Acts as a shortcut to prevent the user from having to hassle with
	 * creating and adding Binding instances to IocContainer.
	 *
	 * @access public
	 * @param $typeName 		The typeName that is going to be bound to an implementation
	 * @return BindingTarget 	The created BindingTarget instance
	 */
	public function &bind($typeName) {
		// Create new binding
		$binding = new Binding($typeName);
		$binding->setNamespace($this->getNamespace());
		$bindingTarget =& $binding->getBindingTarget();

		// Register with IoC container
		$this->addBinding($binding);

		return $bindingTarget;
	}

	/**
	 * 
	 *
	 * @access public
	 * @param $typeName 			The type name for which to resolve
	 * @return object 				The implementation for the requested interface
	 * @throws IocResolveException
	 */
	public function &getInstance($typeName) {
		if ( ($binding = $this->getBindingByTypeName($typeName)) === false ) {
			// If the class exists, attempt to instantiate it nonetheless
			if ( class_exists($typeName) ) {
				try {
					$rc = new \ReflectionClass($typeName);
					if ( !$rc->isInstantiable() ) {
						throw new Exception\Ioc\IocResolveException("Unbound typeName ($typeName) is not instantiable.", $typeName);
					}
				}
				catch (\ReflectionException $e) {
					throw new Exception\Ioc\IocResolveException("Cannot locate implementation for typeName ". $typeName, $typeName);
				}

				return $this->instantiate($rc, new Scope\TransientInstantiationStrategy());
			}
			// Throw an exception because a wrong binding is a configuration problem
			throw new Exception\Ioc\IocResolveException("Binding for requested typeName could not be found.", $typeName);
		}

		// Get the binding target
		if ( ($bindingTarget = $binding->getBindingTarget()) == null ) {
			throw new Exception\Ioc\IocResolveException("Binding target for requested typeName could not be found.", $typeName);
		}

		// Get the binding scope
		if ( ($bindingScope = $binding->getBindingScope()) == null ) {
			// This should never happen because there is always a default binding scope
			throw new Exception\Ioc\IocResolveException("Binding scope for requested typeName could not be found.", $typeName);
		}
		
		// If binding target is a closure, we assume the IOC framework is not responsible for managing scope or injecting
		// anything, we just invoke the closure to retrieve our object instance.
		if ( $bindingTarget->isClosure() ) {
			$closure = $bindingTarget->getClosure();
			$instance = $closure();
			return $instance;
		}

		// Get reflection class for target and pass it into the instantiate method
		// (which will resolve all the class' dependencies)
		try {
			$rc = new \ReflectionClass($bindingTarget->getTypeName());
			if ( !$rc->isInstantiable() ) {
				throw new Exception\Ioc\IocResolveException("Binding target for requested typeName is not instantiable.", $bindingTarget->getTypeName());
			}
		}
		catch (\ReflectionException $e) {
			throw new Exception\Ioc\IocResolveException("Cannot locate implementation for typeName ". $typeName, $bindingTarget->getTypeName());
		}

		return $this->instantiate($rc, $bindingScope->getInstantiationStrategy());
	}

	/**
	 * Scan the given object for injectable members (indicated by a setter method
	 * in the format "injectXxx")
	 *
	 * @access public
	 * @param $object 				The object that must have its dependencies setter-injected.
	 * @return void
	 */
	public function inject(&$object) {
		try {
			$rc = new \ReflectionClass($object);
		}
		catch (\ReflectionException $e) {
			// TODO: different exception
			throw new Exception\Ioc\IocResolveException("Cannot initiate reflection for given object.", "Object");
		}

		$methods = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach ( $methods as $method ) {
			// We don't look at the rest of the method name at all; the only restriction is
			// that it starts with "inject". This means that one injection method can accept
			// multiple dependencies. So type hinting is absolutely required for injection methods.
			if ( substr($method->getName(), 0, 6) == "inject" ) {
				// Get parameters
				$params = $method->getParameters();
				$paramslist = array();
				foreach ( $params as $param ) {
					$paramslist[] = $this->resolveParameter($param);
				}

				// TODO: catch reflection exception and throw an appropriate cannoli exception in its place
				$method->invokeArgs($object, $paramslist);
			}
		}
	}

	/**
	 * Checks whether a binding exists for the given typeName.
	 *
	 * @access private
	 * @param $typeName 			The type name for which to check for bindings
	 * @return bool 				True if a binding was found, false otherwise
	 */
	public function hasBindingWithTypeName($typeName) {
		return isset($this->bindings[$typeName]);
	}

	/**
	 * @access private
	 * @param $typeName 			The type name for which to find a binding
	 * @return mixed 				If found, returns the requested binding, otherwise returns false
	 */
	private function getBindingByTypeName($typeName) {
		if ( !isset($this->bindings[$typeName]) ) {
			return false;
		}

		return $this->bindings[$typeName];
	}

	/**
	 * @access private
	 * @param $rc 					The ReflectionClass for the to-be-instantiated type
	 * @return object
	 */
	private function &instantiate(\ReflectionClass &$rc, Scope\IInstantiationStrategy &$instantiationStrategy) {
		// Get the constructor reflection method first
		$rm = $rc->getConstructor();

		$instance = null;
		$paramslist = array();

		// No constructor, construct new object without constructor args
		if ( !empty($rm) ) {
			$params = $rm->getParameters();
			foreach ( $params as $param ) {
				$paramslist[] = $this->resolveParameter($param);
			}
		}

		$instance = $instantiationStrategy->instantiate($rc, $paramslist);

		// Now inject the newly created instance before returning.
		$this->inject($instance);

		return $instance;
	}

	/**
	 * @access private
	 * @param $param 				The ReflectionParameter that needs to be resolved
	 * @return object
	 */
	private function resolveParameter(\ReflectionParameter &$param) {
		$pClass = $param->getClass();

		// Simply pass null if the parameter is not type-hinted, not a class instance, or
		// no binding has been set up.
		if ( empty($pClass) || !$this->hasBindingWithTypeName($pClass->getName() ) ) {
			if ( !empty($pClass) ) {
				return $this->instantiate($pClass, new Scope\TransientInstantiationStrategy());
			}
			
			return null;
		}
		
		// Don't catch any exceptions... A binding was found so all configuration
		// checks are appropriate at this point.
		return $this->getInstance($pClass->getName());
	}
}
?>