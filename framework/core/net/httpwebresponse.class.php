<?php
namespace Cannoli\Framework\Core\Net;

use Cannoli\Framework\Core;
use Cannoli\Framework\Core\Context;
use Cannoli\Framework\View\JsonView;

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

	/**
	 * Overwriting the setResponseBody method allows us to set the content
	 * type header properly.
	 */
	public function setResponseBody(Core\IRenderable $body) {
		parent::setResponseBody($body);
		if ( $body instanceof JsonView ) {
			// TODO: this is waaaayyyy too hardcoded for my liking
			$this->setHeader("Content-Type", "application/json; charset=utf-8");
		}
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
		header(trim($header, ":") .": ". $value, false);
	}

	public function setHeader($header, $value) {
		$this->headers[$header] = array($value);
		header(trim($header, ":") .": ". $value);
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