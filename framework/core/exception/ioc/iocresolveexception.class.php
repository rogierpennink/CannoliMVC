<?php
namespace Cannoli\Framework\Core\Exception\Ioc;

class IocResolveException extends \Exception
{
	private $typeName;

	public function __construct($message, $typeName) {
		parent::__construct($message);

		$this->typeName = $typeName;
	}

	public function getTypeName() {
		return $this->typeName;
	}
}
?>