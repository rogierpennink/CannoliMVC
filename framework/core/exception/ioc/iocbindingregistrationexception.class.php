<?php
namespace Cannoli\Framework\Core\Exception\Ioc;

class IocBindingRegistrationException extends \Exception
{
	private $interface;

	private $implementation;

	public function __construct($message, $interface, $implementation) {
		parent::__construct($message);
		$this->interface = $interface;
		$this->implementation = $implementation;
	}

	public function getInterface() {
		return $this->interface;
	}

	public function getImplementation() {
		return $this->implementation;
	}
}
?>