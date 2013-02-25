<?php
namespace Cannoli\Framework\Core\Ioc\Scope;

interface IInstantiationStrategy
{
	function instantiate(\ReflectionClass &$rc, array $constructorArguments);
}
?>