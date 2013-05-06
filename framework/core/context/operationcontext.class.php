<?php
namespace Cannoli\Framework\Core\Context;

abstract class OperationContext
{
	const TYPE_HTTP				= "http";
	const TYPE_CLI				= "cli";
	const TYPE_INTERNAL			= "internal";

	private $type = "";

	private static $curContext = null;

	public static function getCurrent() {
		if ( empty(self::$curContext) ) {
			if ( php_sapi_name() == 'cli' || defined('STDIN') ) {
				self::$curContext = new CliOperationContext();
			}
			else {
				self::$curContext = HttpOperationContext::getCurrent();
			}
		}

		return self::$curContext;
	}

	public function __construct($type) {
		$this->setType($type);
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$supportedTypes = array(self::TYPE_HTTP, self::TYPE_CLI, self::TYPE_INTERNAL);
		if ( !in_array($type, $supportedTypes) ) {
			throw new \InvalidArgumentException("Type must be one of the OperationContext constants.");
		}

		$this->type = $type;
	}

	public function isHttpContext() {
		return $this->getType() == self::TYPE_HTTP;
	}

	public function isCliContext() {
		return $this->getType() == self::TYPE_CLI;
	}

	public function isInternalContext() {
		return $this->getType() == self::TYPE_INTERNAL;
	}

	abstract public function getInput();

	abstract public function getRequest();

	abstract public function getResponse();
}
?>