<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Core\Net,
	Cannoli\Framework\Core\Utility;

class HttpOperationContext extends OperationContext
{
	private static $current = null;

	public static function getCurrent() {
		if ( empty(self::$current) ) {
			$request = Net\HttpWebRequest::getCurrent();
			self::$current = new HttpOperationContext($request,
													  HttpInputManager::getInstance(),
													  new Net\HttpWebResponse($request->getProtocolVersion()));
		}

		return self::$current;
	}

	private $request;

	private $response;

	private $input;

	public function __construct(Net\HttpWebRequest $request, HttpInputManager $input, Net\HttpWebResponse $response) {
		parent::__construct(OperationContext::TYPE_HTTP);

		$this->request 	= $request;

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