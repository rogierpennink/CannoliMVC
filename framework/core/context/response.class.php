<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Core;

abstract class Response implements Core\IRenderable
{
	protected $body;

	public function setResponseBody(Core\IRenderable $body) {
		$this->body = $body;
	}

	public function getResponseBody() {
		return $this->body;
	}

	/**
	 * Implements the Core\IRenderable interface. Since the response body
	 * is supposed to be an IRenderable, this method simply calls the body's
	 * render method, or if the body is not set, returns an empty string.
	 *
	 * @access public
	 * @return string
	 */
	public function render() {
		if ( is_null($this->body) ) return "";
		return $this->body->render();
	}
}
?>