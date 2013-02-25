<?php
namespace Cannoli\Framework\Core\Ioc;

abstract class NamespaceContainer
{
	private $namespace;
	
	public function getNamespace() {
		return $this->namespace;
	}

	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}

	public function clearNamespace() {
		$this->namespace = "";
	}
}
?>