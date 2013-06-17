<?php
namespace Cannoli\Framework\Core\Routing;

class RouteData
{
	public function __construct($controller, $action) {
		$this->controller = $controller;
		$this->action = $action;
	}

	public $controller;

	public $action;
}
?>