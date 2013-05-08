<?php
namespace Cannoli\Framework\Core\Utility;

require_once dirname(__FILE__) . "/querystring.class.php";

class URL
{
	private $protocol;
	private $host;
	private $port;
	private $path;
	private $segments;				// Array of individual path parts
	private $querystring;
	private $fragment;
	
	private $valid;
	
	public function __construct($urlString) {
		$this->parse($urlString);
	}
	
	public function parse($urlString) {
		/* Reset member vars. */
		$this->reset();
		
		/* Regular expression. */
		$regExp = "/^([a-z]+\:){0,1}\/\/([a-zA-Z0-9\-\.]+)(:\d*){0,1}(\/[^\?]+){0,1}(\?[a-zA-Z0-9=%\-\_\.\/&]+){0,1}(#[a-zA-Z0-9=%\-\_\.\/]+){0,1}/";
		if ( preg_match_all($regExp, urldecode($urlString), $matches, PREG_SET_ORDER) ) {
			$data = $matches[0];
			$this->protocol = substr($data[1], 0, strlen($data[1]) - 1);
			$this->host = $data[2];
			$this->port = !empty($data[3]) ? intval(substr($data[3], 0, strlen($data[3]) - 1)) : "";
			$this->path = !empty($data[4]) ? substr($data[4], 1) : "";
			$this->path = "/". trim($this->path, "/");
			$this->segments = explode("/", trim($this->path, "/"));
			$this->querystring = !empty($data[5]) ? new QueryString(substr($data[5], 1)) : new QueryString();
			$this->fragment = !empty($data[6]) ? substr($data[6], 1) : "";
			$this->valid = true;
		}
	}
	
	public function getProtocol() {
		return $this->protocol;
	}
	
	public function getHost() {
		return $this->host;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function getPath() {
		return $this->path;
	}

	/**
	 * Gets the path segments
	 *
	 * Segments are the individual parts of the path element of the URL. The
	 * getSegments method allows an offset and a length to be specified in
	 * order to address a subset of the URL's segments
	 *
	 * @access public
	 * @param integer 			The offset from where to start getting segments
	 * @param integer 			The amount of segments to retrieve
	 * @return array 			The requested set of segments
	 */
	public function getSegments($offset = 0, $length = 0) {
		if ( $offset == 0 && $length == 0 ) {
			return $this->segments;
		}

		$segments = $this->segments;
		if ( $length == 0 ) 
			$result = array_splice($segments, $offset);
		else
			$result = array_splice($segments, $offset, $length);
		
		return $result;
	}

	public function getSegmentCount() {
		return count($this->segments);
	}
	
	public function getQueryString() {
		return $this->querystring;
	}
	
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * Restores internal state of the URL class
	 */	
	private function reset() {
		$this->valid = false;
		$this->protocol = $this->host = $this->path =
		$this->fragment = "";
		$this->querystring = null;
		$this->segments = array();
		$this->port = 0;
	}
	
	public function __toString() {
		if ( !$this->valid ) return "Invalid URL";
		return ($this->protocol == "" ? "" : $this->protocol .":") ."//". $this->host . ($this->port == 0 ? "" : ":". $this->port) ."/".
			   $this->path . ($this->querystring == "" ? "" : "?". $this->querystring) . ($this->fragment == "" ? "" : "#". $this->fragment);
	}
}
?>