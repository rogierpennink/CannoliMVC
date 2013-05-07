<?php
namespace Cannoli\Framework\Core\Context;

class CliOperationContext extends OperationContext
{
	private static $current = null;

	public static function getCurrent() {
		if ( empty(self::$current) ) {
			self::$current = new CliOperationContext(CliRequest::getCurrent(),
													 CliInputManager::getInstance(),
													 new CliResponse());
		}

		return self::$current;
	}

	private $request;

	private $response;

	private $input;

	public function __construct(CliRequest $request, CliInputManager $input, CliResponse $response) {
		parent::__construct(OperationContext::TYPE_CLI);

		$this->request 	= $request;

		$this->input 	= $input;

		$this->response	= $response;
	}

	public function getRequest() {
		return $this->request;
	}

	public function getInput() {
		return $this->input;
	}

	public function getResponse() {
		return $this->response;
	}
}
?>