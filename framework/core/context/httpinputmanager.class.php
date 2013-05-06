<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Core\Net,
	Cannoli\Framework\Core\Utility;

class HttpInputManager extends Utility\Singleton implements IInputManager
{
	private $data = array();

	private $request;

	protected function __construct() {
		parent::__construct();

		// TODO: this is not entirely clean since ideally we'd want to getting
		// this from an HttpOperationContext
		$this->request = Net\HttpWebRequest::getCurrent();

		// GET data is a special case since it's appended to the URL, trust
		// PHP's default $_GET for this. For every other method, read from
		// the input stream.
		$this->data = array_merge($_GET);

	}

	public function get($key, $defaultValue = null) {

	}

	public function has($key) {
		return isset($this->data[$key]);
	}

	public function data($key, $defaultValue = null) {
		if ( !$this->has($key) ) return $defaultValue;

		return $this->data[$key];
	}

	private function parseQueryString
}
?>