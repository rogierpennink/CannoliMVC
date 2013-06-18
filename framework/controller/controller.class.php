<?php
namespace Cannoli\Framework\Controller;

use Cannoli\Framework\Application,
	Cannoli\Framework\Core\Exception\Net\HttpException,
	Cannoli\Framework\Core\Context,
	Cannoli\Framework\Core\Net,
	Cannoli\Framework\Core\Net\DefaultHttpExceptionHandler,
	Cannoli\Framework\Core\Routing\RouteContext,
	Cannoli\Framework\View;

class Controller
{
	/* Protected member fields. */
	protected $app;

	/**
	 * @var Cannoli\Framework\Core\Context\OperationContext
	 */
	protected $context;

	protected $request;

	protected $input;

	protected $response;

	public final function __construct(Application &$app) {
		$this->app = $app;

		$this->context 	= $this->app->getOperationContext();

		$this->request 	= $this->context->getRequest();

		$this->input 	= $this->context->getInput();

		$this->response = $this->context->getResponse();
	}

	public function _initialize() {
	}

	public function _getExceptionHandler(RouteContext $routeContext) {
		return new DefaultHttpExceptionHandler($this->app);
	}

	public function _index() {
		return new View\View("framework/view/controller/index.view.php");
	}
	
	public function _http_400() {
		return new View\View("framework/view/controller/400.view.php");
	}
	
	public function _http_401() {
		return new View\View("framework/view/controller/401.view.php");
	}
	
	public function _http_403() {
		return new View\View("framework/view/controller/403.view.php");
	}
	
	public function _http_404() {
		return new View\View("framework/view/controller/404.view.php");
	}
	
	public function _http_405() {
		return new View\View("framework/view/controller/405.view.php");
	}

	public function _http_500() {
		return new View\View("framework/view/controller/500.view.php");
	}

	/** 
	 * The _acceptMethods throws a HttpException when the method for the
	 * current request is not found in the given methods array.
	 *
	 * @access protected
	 * @param array 			The allowed methods
	 * @return void
	 * @throws HttpException
	 */
	protected function _acceptMethods(array $methods) {
		if ( !$this->context->isHttpContext() ) return;
		if ( !in_array($this->request->getVerb(), $methods) ) {
			throw new HttpException("Only the following methods are allowed: ". implode(", ", $methods), Net\HttpStatus::METHOD_NOT_ALLOWED);
		}
	}
}
?>