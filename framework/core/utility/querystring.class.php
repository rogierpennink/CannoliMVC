<?php
namespace Cannoli\Framework\Core\Utility;

class QueryString
{
	protected $data;
	protected $valid;
	
	public function __construct($queryString = "") {
		$this->parse($queryString);
	}
	
	public function parse($queryString) {
		/* No matter what happens, the querystring is reset. */
		$this->reset();
		
		if ( strlen($queryString) <= 1 ) return false;
		
		/* If the string starts with a questionmark, remove it. */
		if ( strpos($queryString, "?") === 0 ) {
			$queryString = substr($queryString, 1);
		}
		
		/* Url decode. */
		$queryString = rawurldecode($queryString);
		parse_str($queryString, $this->data);
		
		$this->valid = true;
	}
	
	public function get($key) {
		if ( isset($this->data[$key]) ) return $this->data[$key];
		return null;
	}
	
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function __toString() {
		if ( !$this->valid ) return "";
		
		$output = "";
		foreach ( $this->data as $key => $value ) $output .= $key ."=". $value ."&";
		return substr($output, 0, strlen($output) - 1);
	}
	
	private function reset() {
		$this->data = array();
		$this->valid = false;
	}
}
?>