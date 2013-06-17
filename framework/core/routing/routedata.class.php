<?php
namespace Cannoli\Framework\Core\Routing;

use Cannoli\Framework\Controller\Controller;

class RouteData
{
	public function __construct(Controller &$controller, $action, array $args = array()) {
		$this->setController($controller);
		$this->setAction($action);
		$this->setArgs($args);
	}

	private $controller;

	private $action;

	private $args;

	public function getController() {
		return $this->controller;
	}

	public function setController(Controller &$controller) {
		$this->controller = $controller;
	}

	public function getAction() {
		return $this->action;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getArgs() {
		return $this->args;
	}

	public function setArgs(array $args) {
		$this->args = $args;
	}
}
?>