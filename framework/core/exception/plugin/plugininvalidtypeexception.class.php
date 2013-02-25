<?php
namespace Cannoli\Framework\Core\Exception\Plugin;

class PluginInvalidTypeException extends \Exception
{
	private $typeStr;

	public function __construct($typeStr, $message) {
		parent::__construct($message);

		$this->typeStr = $typeStr;
	}
}
?>