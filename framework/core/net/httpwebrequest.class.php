<?php
namespace Cannoli\Framework\Core\Net;

class HttpWebRequest
{
	private $verb;

	private $headers = array();
	
	private $protocol;

	private $protocolVersion;

	private $resource;

	private $body = "";

	/**
	 * If the code is evaluated in the context of a web request, the static getCurrent
	 * method will return the current request's HttpWebRequest object for easy access
	 * to, for example, request headers.
	 *
	 * @access public
	 * @return mixed 			HttpWebRequest object, false if no web request context is available.
	 */
	public static function getCurrent() {
		// We have to return a bogus webrequest because if we return false,
		// controllers won't be instantiated
		// TODO: this is bad, it seems controller construction should be CLI/Request independent
		// So it probably needs to be updated so that controllers don't depend on the webrequest
		// anymore
		//if ( defined('STDIN') ) return null;

		static $webRequest = null;

		if ( $webRequest == null ) {
			$webRequest = new HttpWebRequest();

			// Get HTTP_ server variables for headers
			if ( !function_exists('getallheaders') ) {
				foreach ( $_SERVER as $key => $value ) {
					if ( substr($key, 0, 5) == "HTTP_" ) {
						$webRequest->setHeader(self::transformPhpHeaderName($key), $value);
					}
				}
			}
			else {
				foreach ( getallheaders() as $key => $value )
					$webRequest->setHeader(self::transformPhpHeaderName($key), $value);
			}

			$webRequest->verb = strtoupper($_SERVER['REQUEST_METHOD']);

			// Parse protocol string into protocol and version component
			$protocolAndVersion = self::parseProtocolAndVersion($_SERVER["SERVER_PROTOCOL"]);
			$webRequest->protocol = $protocolAndVersion[0];
			$webRequest->protocolVersion = $protocolAndVersion[1];

			// Get resource
			$resource = $_SERVER["REQUEST_URI"];
			// Cut the query string from the resource
			if ( ($pos = strpos($resource, "?")) !== false) {
				$resource = substr($resource, 0, $pos);
			}
			$webRequest->resource = $resource;

			// Read raw request body from php://input (ignore errors)
			$body = @file_get_contents("php://input");
			$webRequest->body = trim($body !== false ? $body : "");
		}

		return $webRequest;
	}

	/**
	 * Simple getter for the headers in this HttpWebRequest. Returns NULL if the HttpWebRequest object
	 * is not initialized or has not parsed a http request message yet.
	 *
	 * @access public
	 * @return array 			array([headerName] => "header value")
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * The setHeaders method replaces the current internal headers array with the one
	 * specified as function argument.
	 *
	 * @access public
	 * @return void
	 */
	public function setHeaders(array &$headers) {
		$this->headers = $headers;
	}

	/**
	 * The setHeader method (over)writes the header value with
	 * the given key in the internal headers array.
	 *
	 * @access public
	 * @param $key 				The header name
	 * @param $value 			The value for the specified header name
	 * @return void
	 */
	public function setHeader($key, $value) {
		$this->headers[$key] = $value;
	}

	/**
	 *
 	 *
	 * @access public
	 * @param $headerName 		The header name for which 
	 * @return mixed 			The header value for the requested header name,
	 * 							but if not found, returns false.
	 */
	public function getHeaderValue($headerName) {
		$headerName = strtolower($headerName);

		foreach ( $this->headers as $key => $value ) {
			if ( $headerName == strtolower($key) ) {
				return $value;
			}
		}

		return false;
	}

	/**
	 * Checks whether the given header name is set in this web request.
	 *
	 * @access public
	 * @param $key 				The header name
	 * @return bool
	 */
	public function hasHeader($headerName) {
		$headerName = strtolower($headerName);

		// TODO: not sure if isset does a case-insensitive check on array keys,
		// but if it does, just use isset here.
		foreach ( $this->headers as $key => $value ) {
			if ( $headerName == strtolower($key) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Getter for the protocol version field. Returns NULL if the HttpWebRequest object
	 * is not initialized or has not parsed a http request message yet.
	 *
	 * @access public
	 * @return string 			The version number as a string
	 */
	public function getProtocolVersion() {
		return $this->protocolVersion;
	}

	/**
	 * Getter for the protocol field. Returns NULL if the HttpWebRequest object is
	 * not initialized or has not parsed a http request message yet.
	 *
	 * @access public
	 * @return string 			The protocol (HTTP/HTTPS) as a string in uppercase
	 */
	public function getProtocol() {
		return $this->protocol;
	}

	/**
	 * Getter for the requested resource field. Returns NULL if the HttpWebRequest object is
	 * not initialized or has not parsed a http request message yet.
	 *
	 * @access public
	 * @return string 			The requested resource string
	 */
	public function getResource() {
		return $this->resource;
	}

	/**
	 * Getter for the HTTP verb field. Returns NULL if the HttpWebRequest object is
	 * not initialized or has not parsed a http request message yet.
	 *
	 * @access public
	 * @return string 			The HTTP verb as an uppercase string
	 */
	public function getVerb() {
		return strtoupper($this->verb);
	}

	/**
	 * Fast access to verb GET check, compares verb value against HttpMethod::GET constant
	 *
	 * @access public
	 * @return boolean			Whether or not the method for this request was GET
	 */
	public function isGET() {
		return $this->getVerb() == HttpMethod::GET;
	}

	/**
	 * Fast access to verb POST check, compares verb value against HttpMethod::POST constant
	 *
	 * @access public
	 * @return boolean			Whether or not the method for this request was POST
	 */
	public function isPOST() {
		return $this->getVerb() == HttpMethod::POST;
	}

	/**
	 * Fast access to verb PUT check, compares verb value against HttpMethod::PUT constant
	 *
	 * @access public
	 * @return boolean			Whether or not the method for this request was PUT
	 */
	public function isPUT() {
		return $this->getVerb() == HttpMethod::PUT;
	}

	/**
	 * Fast access to verb DELETE check, compares verb value against HttpMethod::DELETE constant
	 *
	 * @access public
	 * @return boolean			Whether or not the method for this request was DELETE
	 */
	public function isDELETE() {
		return $this->getVerb() == HttpMethod::DELETE;
	}

	/**
	 * Return the contents of the HTTP request's body. Since the request body is read from the
	 * php://input stream, this method is the preferred way of accessing request data since in
	 * the case of a PUT request (or maybe other requests too), php://input is only readable
	 * once. This method caches the request body so it can be accessed more than once.
	 *
	 * @access public
	 * @return string 			The request body
	 */
	public function getBody() {
		return $this->body;
	}


	/**
	 * PHP Magic toString method. Returns the http request in raw text format, as
	 * it would have been sent to the server.
	 *
	 * @access public
	 * @return string 			The http request as raw text
	 */
	public function __toString() {
		$output = $this->getVerb() ." ". $this->getResource() ." ". $this->getProtocol() ."/". $this->getProtocolVersion() ."\n";
		if ( is_array($this->headers) ) {
			foreach ( $this->headers as $headerName => $headerValue ) {
				$output .= "\n". $headerName .": ". $headerValue;
			}
		}

		return $output;
	}

	/**
	 * The transformPhpHeaderName helps transform the $_SERVER['HTTP_*'] variables
	 * into proper header names.
	 *
	 * @access private
	 * @param $value 			The header name to transform
	 * @return string 			The properly formatted header name string
	 */
	private static function transformPhpHeaderName($value) {
		if ( substr($value, 0, 5) == "HTTP_" ) {
			$value = substr($value, 5);
		}

		$parts = array_map(function($el) {
			return ucfirst(strtolower($el));
		}, explode("_", $value));

		return implode("-", $parts);
	}

	/**
	 * The parseProtocolAndVersion takes the typical HTTP/1.1 string
	 * and splits it into the protocol name and the version.
	 *
	 * @access private
	 * @param $value 			The raw protocol and version string
	 * @return array 			Array(0 => Protocol, 1 => Version)
	 */
	private static function parseProtocolAndVersion($value) {
		$parts = explode("/", $value);
		return $parts;
	}
}
?>