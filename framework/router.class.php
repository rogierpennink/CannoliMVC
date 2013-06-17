<?php
namespace Cannoli\Framework;

use Application\Controller,
	Cannoli\Framework\Controller\Controller as BaseController,
	Cannoli\Framework\Core\Context,
	Cannoli\Framework\Core\Exception,
	Cannoli\Framework\Core\Routing\RouteContext,
	Cannoli\Framework\Core\Routing\RouteData;

class Router
{
	protected $app;
	
	protected $defaultController;
	protected $defaultMethod;
	
	protected $routes;
	
	public function __construct(Application &$app) {
		$this->app = $app;
		
		/* Set default controller and methods to their default values. */
		$this->defaultController = "";
		$this->defaultMethod = "";
		
		$this->routes = array();
	}
	
	public function setDefaultController($controller) {
		$this->defaultController = $controller;
	}
	
	public function setDefaultMethod($method) {
		$this->defaultMethod = $method;
	}

	/**
	 * Add a route to the internal custom routes array. A route can be seen as a
	 * virtual path. It does not actually exist but it is routed to an existing
	 * resource. Routes always take precedence before anything else, so it is
	 * possible to make resources inaccessible if a route with the same path is
	 * added.
	 *
	 * @access public
	 * @param $path 			The virtual path that must be resolved
	 * @param $resource 		The actual, existing, resource that must be associated with the path
	 * @return void
	 */
	public function addRoute($path, $resource) {
		if ( isset($this->routes[$path]) ) {
			throw new Exception\RouteException("Cannot add route with path \"$path\". Path already exists.");
		}

		/* Store without trailing/starting slashes (makes it easier to split into segments) */
		$path = trim($path, "/");
		$resource = trim($resource, "/");

		$this->routes[$path] = $resource;
	}

	// public function hasRoute($path) {
	// 	$path = trim($path, "/");

	// 	 Get array keys and walk through them to test any and all
	// 	   regular expressions against the current path. 
	// 	$keys = array_keys($this->routes);
	// 	foreach ( $keys as $key ) {
	// 		if ( preg_match())
	// 	}

	// 	return isset($this->routes[$path]);
	// }

	public function getRoutedPath($path) {
		$path = trim($path, "/");

		/* Get array keys and walk through them to test any and all
		   regular expressions against the current path. */
		foreach ( $this->routes as $key => $value ) {
			if ( $key == $path ) return $value;
			if ( preg_match("#^".$key."$#", $path) == 1 ) {
				if ( ($routedPath = @preg_replace("#^".$key."$#", $value, $path)) == null )
					continue;
				return $routedPath;
			}
		}

		return $path;
	}

	/**
	 * Uses the requested URL resource as a mapping to a method in a Controller-derived
	 * class. The route method returns a RouteResult instance which contains the fully
	 * specified controller name, the method name, and the arguments to the method.
	 * 
	 * @access public
	 * @return RouteResult			The result of the routing operation
	 * @throws RouteException
	 */
	public function route() {
		$context = $this->app->getOperationContext();

		$segments = $this->getSegmentsFromContext($context);
		
		// TODO: Take subdirectories into account here
		$strController = trim(empty($segments) ? $this->defaultController : $segments[0]);
		$strMethod = trim(count($segments) < 2 ? $this->defaultMethod : $segments[1]);
		
		//TODO: Controller base class, methods like index(), error404() etc. with default implementations

		/* In the case that the root URL was specified - no controller specified at all. */
		if ( $strController == "" ) {
			/* Fetch the default controller */
			if ( $this->defaultController == "" ) {
				/* Fetch the controller base class. */
				$controller = $this->getController("Cannoli\\Framework\\Controller\\Controller");
				if ( empty($controller) ) {
					/* Could not find default controller, check configuration. */
					throw new Exception\RouteException("No Controller could be found, check your configuration!");
				}
			}
			elseif ( ($controller = $this->getController("Application\\Controller\\". ucfirst($this->defaultController) ."Controller")) == null ) {
				if ( ($controller = $this->getController("Cannoli\\Framework\\Controller\\". ucfirst($this->defaultController) ."Controller")) == null ) {
					throw new Exception\RouteException("No Controller could be found, check your configuration!");
				}
			}
		}
		/* In the case that the requested controller was not found. */
		elseif ( ($controller = $this->getController("Application\\Controller\\". ucfirst($strController) ."Controller")) == null ) {
			/* Fetch the controller base class. */
			$controller = $this->getController("Cannoli\\Framework\\Controller\\Controller");
			if ( empty($controller) ) {
				/* Could not find default controller, check configuration. */
				throw new Exception\RouteException("No Controller could be found, check your configuration!");
			}

			return $controller->_http_404();
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
				if ( method_exists($controller, "index") )
					return $controller->index();
				else
					return $controller->_http_404();
			}
			
			return $controller->{$this->defaultMethod}();
		}
		else {
			/* Check if we can run the requested method on the controller. */
			if ( !method_exists($controller, $strMethod) ) {
				/* Since we know we have a Controller-derived instance, call the 404 method */
				return $controller->_http_404();
			}
		}

		/* If the method name starts with an underscore, it must not be associated with a URL. */
		if ( substr($strMethod, 0, 1) == "_" ) {
			return $controller->_http_403();
		}
		
		/* No special cases have occurred, method must be valid, so call it. */
		if ( count($segments) > 2 ) {
			return call_user_func_array(array($controller, $strMethod), array_splice($segments, 2));
		}
		return $controller->{$strMethod}();
	}

	private function createRouteContext($controller, $action) {
		// Create route data
		$routeData = new RouteData($controller, $action);

		// Create new route context
		$routeContext = new RouteContext($routeData, $this->app->getOperationContext()->getRequestUrl());
	}

	/**
	 * Returns the array of segments from the given operation context. The reason why
	 * this helper is in place is because the operation context can be any of a number
	 * of different types, which means there can also be any number of different ways
	 * to access the segments.
	 *
	 * @access private
	 * @return array 		Array of segments, can be empty
	 */
	private function getSegmentsFromContext(Context\OperationContext &$context) {
		if ( $context->isHttpContext() ) {
			// This small block of code figures out the segments correctly even if cannoli
			// mvc is installed in a subdirectory or if the url is of the format:
			// index.php/segments/here
			$path = $context->getRequestUrl()->getPath();

			if ( strpos($path, $_SERVER['SCRIPT_NAME']) === 0 ) {
				$path = substr($path, strlen($_SERVER['SCRIPT_NAME']));
			}
			elseif ( strpos($path, dirname($_SERVER['SCRIPT_NAME'])) === 0 ) {
				$path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
			}

			// If there is a route for the requested url, this method will find it and apply it.
			// If no route exists, $path will remain unaltered
			$path = $this->getRoutedPath($path);

			return explode("/", trim($path, "/"));
		}
		elseif ( $context->isCliContext() ) {
			$request = $context->getRequest();

			return array_keys($request->getArguments());
		}
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
			// This is where application-level initialization should occur
			$controller->_initialize();
			
			/* Check that the controller is an instance of Controller. */
			if ( !($controller instanceof BaseController) )
				throw new Exception\RouteException("The requested \"$strController\" exists but does not inherit from Controller.");
			
			return $controller;
		}

		return null;
	}
}
?>