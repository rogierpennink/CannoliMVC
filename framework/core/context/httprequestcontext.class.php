<?php
namespace Cannoli\Framework\Core\Context;

class HttpRequestContext extends RequestContext
{
	public function __construct() {
		parent::__construct(RequestContext::TYPE_HTTP);
	}

	public function getRequest() {
		// For now, we'll do it this way..
		return Net\HttpWebRequest::getCurrent();
	}

	public function getInput() {

	}
}
?>