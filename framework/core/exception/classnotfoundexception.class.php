<?php
namespace Cannoli\Framework\Core\Exception;

class ClassNotFoundException extends \Exception
{
	private $className;

	public function __construct($class) {
		parent::__construct("Class not found: ". $class);
		$this->className = $class;
	}

	public function getClass() {
		return $this->className;
	}
}
?>