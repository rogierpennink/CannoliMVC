<?php
namespace Cannoli\Framework\Controller;

use Cannoli\Framework\Application,
	Cannoli\Framework\View;

class Controller
{
	/* Protected member fields. */
	protected $app;
	
	public final function __construct(Application &$app) {
		$this->app = $app;
	}

	public function _initialize() {
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
}
?>