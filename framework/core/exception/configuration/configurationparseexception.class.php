<?php
namespace Cannoli\Framework\Core\Exception\Configuration;

class ConfigurationParseException extends \Exception
{
	public function __construct($message) {
		parent::__construct($message);
	}
}
?>