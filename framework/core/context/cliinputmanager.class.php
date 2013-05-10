<?php
namespace Cannoli\Framework\Core\Context;

class CliInputManager extends InputManager
{
	private $request;

	private $options = array();

	private $longOptions = array();

	public function __construct() {
		parent::__construct();

		$request = CliRequest::getCurrent();

		$this->options = $request->getOptions();

		$this->longOptions = $request->getLongOptions();

		$this->data = array_merge($this->options, $this->longOptions);
	}
}
?>