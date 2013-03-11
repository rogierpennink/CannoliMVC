<?php
namespace Cannoli\Framework\Core\Exception\Plugin;

class PluginDeclarationInstantiationException extends \Exception
{
	public function __construct($message) {
		parent::__construct($message);
	}	
}
?>