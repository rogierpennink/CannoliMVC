<?php
namespace Cannoli\Framework\Core\Net;

class HttpWebResponse
{
	private $statusCode = HttpStatus::OK;

	public function __construct() {

	}

	public function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
	}

	public function getStatusCode() {
		return $this->statusCode;
	}
}
?>