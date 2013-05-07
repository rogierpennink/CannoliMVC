<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Core\Net,
	Cannoli\Framework\Core\Utility;

class HttpInputManager extends InputManager
{
	private $put = array();

	private $request;

	protected function __construct() {
		parent::__construct();

		// TODO: this is not entirely clean since ideally we'd want to get
		// this from an HttpOperationContext
		$this->request = Net\HttpWebRequest::getCurrent();

		// GET data is a special case since it's appended to the URL, trust
		// PHP's default $_GET for this. For every other method, read from
		// the input stream.
		$this->data = array_merge($_GET, $_POST);

		// If request was PUT, attempt to parse querystring from body.
		if ( $this->request->isPUT() && ($body = $this->request->getBody()) != "" ) {
			parse_str(rawurldecode($body), $this->put);
			$this->data = array_merge($this->data, $this->put);
		}
	}

	/**
	 * Returns the unparsed, raw content of the current HttpWebRequest. This
	 * method is a simple proxy to HttpWebRequest::getBody() and was added
	 * to make sure that all request data is accessible from the input manager.
	 *
	 * @access public
	 * @return string 		The request body content
	 */
	public function getRawRequestBody() {
		return $this->request->getBody();
	}

	/**
	 *
	 *
	 * @access public
	 * @param $key 			The key for which to find the value in the query string
	 * @param $defaultValue	If key is not found, return this value instead
	 * @return mixed 		The requested data or, if $key not found, the value of $defaultValue
	 */
	public function get($key, $defaultValue = null) {
		if ( !isset($_GET[$key]) ) return $defaultValue;
		return $_GET[$key];
	}

	/**
	 * Provides access to POSTed data separately while adding the ability
	 * to receive a default value if the requested key is not found.
	 *
	 * @access public
	 * @param $key 			The key to find the POSTed data for
	 * @param $defaultValue	If key is not found, return this value instead
	 * @return mixed 		The requested data or, if $key not found, the value of $defaultValue
	 */
	public function post($key, $defaultValue = null) {
		if ( !isset($_POST[$key]) ) return $defaultValue;
		return $_POST[$key];
	}

	/**
	 * Provides access to PUT data separately while adding the ability
	 * 
	 * @access public
	 * @param $key 			The key to find the PUT data for
	 * @param $defaultValue If key is not found, return this value instead
	 * @return mixed 		The requested data or, if $key not found, the value of $defaultValue
	 */
	public function put($key, $defaultValue = null) {
		if ( !isset($this->put[$key]) ) return $defaultValue;
		return $this->put[$key];
	}
}
?>