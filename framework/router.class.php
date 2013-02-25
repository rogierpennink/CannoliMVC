<?php
namespace Cannoli\Framework;

use Application\Controller,
	Cannoli\Framework\Controller\Controller as BaseController,
	Cannoli\Framework\Core\Exception;

class Router
{
	protected $app;
	
	protected $defaultController;
	protected $defaultMethod;
	
	protected $paths;
	
	public function __construct(Application &$app) {
		$this->app = $app;
		
		/* Set default controller and methods to their default values. */
		$this->defaultController = "";
		$this->defaultMethod = "index";
		
		$this->paths = array();
	}
	
	public function setDefaultController($controller) {
		$this->defaultController = $controller;
	}
	
	public function setDefaultMethod($method) {
		$this->defaultMethod = $method;
	}
	
	/**
	 * Uses the requested URL resource as a mapping to a method in a Controller-derived
	 * class.
	 */
	public function route() {
		/* Get the requested URL from $app */
		$url = $this->app->getRequestedURL();

		/* We only use path info, break it up into parts first. */
		$segments = $url->getSegments();
		
		$strController = trim(empty($segments) ? $this->defaultController : $segments[0]);
		$strMethod = trim(count($segments) < 2 ? $this->defaultMethod : $segments[1]);
		
		//TODO: Controller base class, methods like index(), error404() etc. with default implementations

		/* Update the controller class name. */
		if ( $strController == "" || ($controller = $this->getController("Application\\Controller\\". ucfirst($strController) ."Controller")) == null ) {
			/* Fetch the default controller */
			if ( $this->defaultController == "" || ($controller = $this->getController("Application\\Controller\\". ucfirst($this->defaultController) ."Controller")) == null ) {
				/* Fetch the controller base class. */
				$controller = $this->getController("Cannoli\\Framework\\Controller\\Controller");
				if ( empty($controller) ) {
					/* Could not find default controller, check configuration. */
					throw new Exception\RouteException("No Controller could be found, check your configuration!");
				}
			}
		}
		
		/**
		 * The method chain is simple:
		 * - If the requested method can be found on the controller, use it, otherwise:
		 * - If the default method can be found on the controller, use it, otherwise:
		 * - Use the http_404() method which always has a default implementation because
		 * of the Controller base class.
		 */
		
		/* If the requested method is empty, first attempt to call the default
		 * method, and if there isn't one, use the index() method.
		 */
		if ( $strMethod == "" ) {
			/* Check for the presence of the default method. */
			if ( !method_exists($controller, $this->defaultMethod) ) {
				return $controller->index();
			}
			
			return $controller->{$this->defaultMethod}();
		}
		else {
			/* Check if we can run the requested method on the controller. */
			if ( !method_exists($controller, $strMethod) ) {
				/* Since we know we have a Controller-derived instance, call the 404 method */
				return $controller->http_404();
			}
		}
		
		/* No special cases have occurred, method must be valid, so call it. */
		if ( $url->getSegmentCount() > 2 ) {
			return call_user_func_array(array($controller, $strMethod), $url->getSegments(2));
		}
		return $controller->{$strMethod}();
	}

	/**
	 * Checks whether the requested controller class exists and if it is a valid
	 * instance of Controller.
	 * @return mixed		null if invalid controller classname, a controller instance otherwise
	 */
	private function getController($strController) {
		/* Attempt to instantiate the controller. */
		if ( class_exists($strController) ) {
			//$controller = new $strController($this->app);
			$controller = $this->app->getIocContainer()->getInstance($strController);
			
			/* Check that the controller is an instance of Controller. */
			if ( !($controller instanceof BaseController) )
				throw new Exception\RouteException("The requested \"$strController\" exists but does not inherit from Controller.");
			
			return $controller;
		}

		return null;
	}
}
?>