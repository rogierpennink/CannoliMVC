<?php
namespace Cannoli\Framework\Core\Context;

class HttpContextInputManager implements IInputManager
{
	public function __construct() {
	}

	public function get($key, $defaultValue = false) {
		if ( !$this->has($key) ) return $defaultValue;

		
	}

	public function has($key) {

	}
}
?>