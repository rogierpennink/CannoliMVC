<?php
namespace Cannoli\Framework\Core\Net;

use Cannoli\Framework\Core\Context;

class HttpWebResponse extends Context\Response
{
	private $statusCode = HttpStatus::OK;

	private $headers = array();

	private $charset = "utf-8";

	private $contentType = "text/html";

	private $version = "1.0";

	public function __construct($version) {
		$this->version = $version;
	}

	public function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
		header("HTTP/".$this->version." ".$this->statusCode." ".HttpStatus::getDescription($this->statusCode));
	}

	public function getStatusCode() {
		return $this->statusCode;
	}

	public function addHeader($header, $value) {
		$this->headers[$header][] = $value;
	}

	public function setHeader($header, $value) {
		$this->headers[$header] = array($value);
	}

	public function setCharset($charset) {
		$this->charset = $charset;
	}

	public function getCharset() {
		return $this->charset;
	}

	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}

	public function getContentType() {
		return $this->contentType;
	}
}
?>