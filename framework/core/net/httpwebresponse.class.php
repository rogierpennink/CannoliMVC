<?php
namespace Cannoli\Framework\Core\Net;

use Cannoli\Framework\Core\Context;

class HttpWebResponse extends Context\Response
{
	private $statusCode = HttpStatus::OK;

	private $headers = array();

	private $charset = "utf-8";

	private $contentType = "text/html";

	public function __construct() {
	}

	public function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
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