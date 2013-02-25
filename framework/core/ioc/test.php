<?php
namespace Cannoli\Framework\Core\Ioc;

interface Foo {
	function say();
}

class Dog {
	public function bark() {
		echo "Whoof whoof";
	}
}

class Bar implements Foo {
	public function __construct() {
		echo "constructed";
	}
	public function say() {
		echo "Hello World";
	}

	public function injectDog(Dog &$dog) {
		$dog->bark();
	}
}

include("scope/iinstantiationstrategy.class.php");
include("scope/transientinstantiationstrategy.class.php");
include("scope/singletoninstantiationstrategy.class.php");
include("namespacecontainer.class.php");
include("namespacedtypenamecontainer.class.php");
include("bindingscope.class.php");
include("bindingtarget.class.php");
include("binding.class.php");
include("bindingmodule.class.php");
include("../exception/ioc/iocresolveexception.class.php");
include("ioccontainer.class.php");

class TestBindingModule extends BindingModule
{
	public function load() {
		$this->setNamespace("Cannoli\Framework\Core");
		$this->bind("Ioc\Foo")->to("Ioc\Bar")->inSingletonScope();
	}
}

// Create an array of bindingmodules.

$modules = array(new TestBindingModule());
$container = new IocContainer($modules);

$obj = $container->getInstance("Cannoli\Framework\Core\Ioc\Foo");
$obj->say();

$obj = $container->getInstance("Cannoli\Framework\Core\Ioc\Foo");
?>