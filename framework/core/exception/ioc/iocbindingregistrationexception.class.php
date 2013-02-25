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

	public getInterface() {
		return $this->interface;
	}

	public getImplementation() {
		return $this->implementation;
	}
}
?>