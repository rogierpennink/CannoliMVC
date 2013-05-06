<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Core\Net,
	Cannoli\Framework\Core\Utility;

class HttpOperationContext extends OperationContext
{
	private $current = null;

	public static function getCurrent() {
		if ( empty(self::$current) ) {
			self::$current = new HttpOperationContext(Net\HttpWebRequest::getCurrent(),
														HttpInputManager::getCurrent(),
														new Net\HttpWebResponse());
		}

		return self::$current;
	}

	public function __construct(Net\HttpWebRequest $request, HttpInputManager $input, HttpWebResponse $response) {
		parent::__construct(RequestContext::TYPE_HTTP);

		$this->requst  	= $request;

		$this->input 	= $input;

		$this->response	= $response;
	}

	/**
	 * Returns the fully qualified URL for the current http web request.
	 *
	 * @access public
	 * @return URL
	 */
	public function getRequestUrl() {
		$urlString = ((!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") ? "https" : "http")
					 ."://". $_SERVER["HTTP_HOST"] .":". $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];

		$uri = new Utility\URL($urlString);
		return $uri;
	}

	public function getRequest() {
		// Let each "part" of the current context figure out its own state
		return Net\HttpWebRequest::getCurrent();
	}

	public function getInput() {
		return new HttpInputManager();
	}

	public function getResponse() {
		return null;
	}
}
?>