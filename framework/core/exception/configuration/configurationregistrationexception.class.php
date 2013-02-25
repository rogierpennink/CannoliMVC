<?php
namespace Cannoli\Framework\Core\Exception\Configuration;

class ConfigurationRegistrationException extends \Exception
{
	public function __construct($message) {
		parent::__construct($message);
	}	
}
?>