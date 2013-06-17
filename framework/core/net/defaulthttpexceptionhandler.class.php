<?php
namespace Cannoli\Framework\Core\Net;

use Cannoli\Framework\Application;
use Cannoli\Framework\Core\Exception;
use Cannoli\Framework\Core\Exception\IExceptionHandler;

class DefaultHttpExceptionHandler implements IExceptionHandler
{
	private $app;

	private $context;

	public function __construct(Application &$app) {
		$this->app = $app;
		$this->context = $this->app->getOperationContext();
	}

	public function handleException(\Exception $e) {
		if ( $e instanceof Exception\Net\HttpException ) {
			if ( $this->context->isHttpContext() ) {
				$this->context->getResponse()->setStatusCode($e->getCode());
			}
		}
	}
}
?>