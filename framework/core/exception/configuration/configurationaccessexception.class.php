<?php
namespace Cannoli\Framework\Core\Exception;

class ConfigurationAccessException extends \Exception
{
	public function __construct($message) {
		parent::__construct($message);
	}
}
?>