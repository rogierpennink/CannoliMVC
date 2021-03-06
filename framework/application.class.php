<?php
namespace Cannoli\Framework;

// These includes must happen here because the classes within the included
// files are required before the autoload configuration has been loaded.
require_once dirname(__FILE__) ."/core/exception/frameworkexception.class.php";
require_once dirname(__FILE__) ."/core/utility/configurablesingleton.class.php";
require_once dirname(__FILE__) ."/core/configuration/configurationmanager.class.php";
require_once dirname(__FILE__) ."/core/configuration/configurationcontainer.class.php";

require_once dirname(__FILE__) ."/router.class.php";

require_once dirname(__FILE__) ."/core/cache/appsettings.class.php";

use Cannoli\Framework\Core,
	Cannoli\Framework\Core\Configuration,
	Cannoli\Framework\Core\Context,
	Cannoli\Framework\Core\Exception,
	Cannoli\Framework\Core\Ioc,
	Cannoli\Framework\Core\Ioc\Modules,
	Cannoli\Framework\Core\Plugin,
	Cannoli\Framework\Core\Cache\AppSettings,
	Cannoli\Framework\Core\Utility;

class Application extends Utility\ConfigurableSingleton
{	
	/* Instance member variables. */
	protected $session;
	
	protected $router;

	protected $iocContainer;

	protected $eventQueue;
	
	private $al_directories = array();
	
	/**
	 * Protected constructor to avoid instantiation by anyone but itself. The
	 * constructor will initialize the framework right up to the point where
	 * the configuration is loaded.
	 * Other systems are initialized in the run method.
	 *
	 * @access protected
	 */
	protected function __construct() {
		parent::__construct();

		// First thing should always be to register the autoload function
		spl_autoload_register(array($this, "autoload"), false);

		// Next, we should add all the required autoload directories that
		// are needed for 'boot'
		$this->addSystemAutoloadPaths();

		// Initialize configuration manager and register this class as a configurable
		$cm =& $this->getConfigurationManager();
		$cm->registerConfigurable($this);

		// Then we need to check if all the prerequisites are in place
		$this->checkPrerequisites();

		// Load the framework configuration
		$this->loadConfiguration();

		// Initialize the system event queue
		$this->eventQueue = new Core\Event\EventQueue();
		
		// TODO:
		// Quick hack; session cache should probably be a member of the http
		// operation context or something...
		if ( $this->getOperationContext()->isHttpContext() ) {
			$this->session = new Core\Session\SessionCache();	
		}

		$this->createRouter();
	}

	/**
	 * IConfigurable enforced method.
	 *
	 * Returns the array of configuration domains this class is interested
	 * in receiving configurations for.
	 *
	 * @access public
	 * @return array 			An array of domain strings
	 */
	public function getConfigurationDomains() {
		return array(
			"Cannoli.Framework.Autoload",
			"Cannoli.Framework.Plugins",
			"Cannoli.Framework.Ioc",
			"Cannoli.Application.Ioc",
			"Cannoli.Application.Routing",
			"Cannoli.Application.Autoload"
		);
	}
	
	public function &getSession() {
		return $this->session;
	}
	
	public function redirect($target) {
		header("Location: " . $target);
		exit;
	}
	
	/**
	 * Uses the router instance to process the current request and generate output.
	 * The run method simply outputs the data.
	 *
	 * @access public
	 * @return void
	 */
	public function run() {
		// Start by loading all the plugins
		$this->loadPlugins();

		// Onbeforerouting call should go here
		$this->onBeforeRouting();

		// Do routing
		$routeContext = $this->router->route();
		$route = $routeContext->routeData;

		// Catch http exceptions upon execution of the resolved controller/action pair
		try {
			// This is where application-level initialization should occur
			$route->getController()->_initialize();

			// TODO: the actual execution of the controller method should probably
			// be delegated to some other class so that applications can possibly
			// hook into it
			$renderable = call_user_func_array(array($route->getController(), $route->getAction()), $route->getArgs());
		}
		catch ( \Exception $e ) {
			$result = $route->getController()->_getExceptionHandler($routeContext)->handleException($e);
			if ( $result instanceof Core\IRenderable ) {
				$this->getOperationContext()->getResponse()->setResponseBody($result);
			}
		}

		// OnAfterRouting call should go here
		$this->onAfterRouting();

		if ( !empty($renderable) ) {
			$this->onBeforeRendering($renderable);

			$this->getOperationContext()->getResponse()->setResponseBody($renderable);

			//$this->onAfterRendering($renderable);
		}

		// Render the response
		echo $this->getOperationContext()->getResponse()->render();
	}

	/**
	 *
	 * @access public
	 */
	public function executeRequest(IRequestContext &$context, Controller &$controller, $method, array $args = array()) {
		if ( !method_exists($controller, $method) ) {
			throw new Exception\RouteException("Controller method \"". $method ."\" was not found in ". get_class($controller));
		}

		$result = call_user_func_array(array($controller, $method), $args);
	}
	
	/**
	 * Uses server variables to establish the URL that was requested by the
	 * user.
	 *
	 * @access public
	 * @return object			An instance of the URL class.
	 */
	public function getRequestedURL() {
		if ( php_sapi_name() == 'cli' || defined('STDIN') ) {
			$args = array_slice($_SERVER['argv'], 1);
			$urlString = "console://cli" . ($args ? '/' . implode('/', $args) : '');
		}
		else {
			/* First construct the complete URL string from server variables. */
			$urlString = ((!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") ? "https" : "http")
					 ."://". $_SERVER["HTTP_HOST"] .":". $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		}

		/* Pass it to the URI class to parse it. */	
		$uri = new Utility\URL($urlString);
		return $uri;
	}

	/**
	 * Proxy for the OperationContext::getCurrent method which establishes the
	 * current operation's context by inspecting certain server variables and
	 * defines.
	 *
	 * @access public
	 * @return OperationContext A OperationContext-derived object instance
	 */
	public function getOperationContext() {
		return Context\OperationContext::getCurrent();
	}

	/**
	 * Adds a new directory to search for class names in files of the format specified by
	 * the $fileTemplate string. If the directory has been previously added, it
	 * will be overwritten.
	 *
	 * @access public
	 * @param $directory		The directory to check for classes
	 * @param $fileTemplate		What the class filename for this particular directory should look like
	 * @return void
	 */
	public function addAutoloadDirectory($directory, $fileTemplate = "{0}.class.php") {
		$this->al_directories[$directory] = $fileTemplate;
	}
	
	/**
	 * Adds an array of directories to search for class names in files of the format specified
	 * by the fileTemplate string. Overwrites previously added directories with the same name.
	 * Array structure:
	 * array(
	 * 	'/some/path/to' => '{0}.template.ext'
	 * )
	 * 
	 * @access public
	 * @param $directories		The directories array in the expected format.
	 * @return void
	 */
	public function addAutoloadDirectories(array $directories) {
		$defaultFileTemplate = "{0}.class.php";
		foreach ( $directories as $str1 => $str2 ) {
			if ( is_numeric($str1) )
				$this->addAutoloadDirectory($str2, $defaultFileTemplate);
			else
				$this->addAutoloadDirectory($str1, $str2);
		}
	}

	/**
	 * Returns the IocContainer instance. If it does not exist yet, it will be created
	 * using BindingModules that have been specified in the configuration files.
	 *
	 * @access public
	 * @return IocContainer 	The IocContainer instance
	 */
	public function getIocContainer() {
		if ( $this->iocContainer == null ) {
			$this->createIocContainer();
		}

		return $this->iocContainer;
	}

	/**
	 * Gets the Plugin Manager
	 * 
	 * Since the PluginManager extends Singleton, this method simply acts as a
	 * proxy to the PluginManager::getInstance static method.
	 * The manager itself allows the user to register new Plugins.
	 *
	 * @access public
	 * @return object 			The Pluginmanager instance.
	 */
	public function &getPluginManager() {
		return $this->getIocContainer()->getInstance("Cannoli\\Framework\\Core\\Plugin\\PluginManager");
	}

	/**
	 * Gets the Configuration Manager
	 *
	 * Since the ConfigurationManager extends Singleton, this method simply acts
	 * as a proxy to the ConfigurationManager::getInstance static method.
	 * The manager itself allows the user to register IConfigurable and
	 * IConfiguration instances.
	 *
	 * @access public
	 * @return object 			The ConfigurationManager instance.
	 */
	public function &getConfigurationManager() {
		return Configuration\ConfigurationManager::getInstance();
	}

	/**
	 * Queries the IoC container for a database connection manager instance.
	 *
	 * @access public
	 * @return IDatabaseConnectionManager
	 */
	public function &getDatabaseConnectionManager() {
		return $this->getIocContainer()->getInstance("Cannoli\\Framework\\Contract\\Database\\IDatabaseConnectionManager");
	}
	
	/**
	 * Gets the AppSettings instance.
	 *
	 * Since AppSettings extends Singleton, this method simply acts as a proxy
	 * to the AppSettings::getInstance static method. As a result of this,
	 * the AppSettings cache is lazy-loaded.
	 *
	 * @access public
	 * @return object			The AppSettings instance.
	 */
	public function &getAppSettings() {
		return AppSettings::getInstance();
	}
	
	/**
	 * Retrieves a previously set App setting. This method acts as a proxy
	 * to the AppSettings::get method.
	 *
	 * @access public
	 * @param $key				The key to search on for associated data.
	 * @return mixed			Whatever data has been associated with $key, or
	 * 							null if $key does not exist.
	 */
	public function get($key) {
		return $this->getAppSettings()->get($key);
	}
	
	/**
	 * Sets an App setting. This method acts as a proxy to the AppSettings::set
	 * method.
	 *
	 * @access public
	 * @param $key				The key to associate the data value with
	 * @param $value			The data to store.
	 */
	public function set($key, $value) {
		$this->getAppSettings()->set($key, $value);
	}

	/**
	 * The autoload method walks through all the paths that have been specified
	 * for it to look through.
	 *
	 * @access private
	 * @param $name			The class name to look for
	 * @return void
	 */
	private function autoload($name) {
		foreach ( $this->al_directories as $directory => $fileTemplate ) {
			$name = basename(str_replace("\\", "//", $name));
			$filename = $directory ."/". str_replace("{0}", strtolower($name), $fileTemplate);
			if ( file_exists($filename) ) {
				require_once $filename;
			}
		}
	}

	/**
	 * Creates a new IoC container using the configuration files to resolve the
	 * various binding modules.
	 *
	 * @access private
	 * @return void
	 */
	private function createIocContainer() {
		// Get the array of BindingModules for system bindings
		$systemBindingModules = $this->config("Cannoli.Framework.Ioc", "systemModules", array());

		// Get the array of BindingModules for the application
		$applicationBindingModules = $this->config("Cannoli.Application.Ioc", "applicationModules", array());

		// TODO: check if both modules arrays are valid arrays
		$modules = array_merge($systemBindingModules, $applicationBindingModules);
		$modules = array_map(function($el) {
			return new $el();
		}, $modules);
		$this->iocContainer = new Ioc\IocContainer($modules);
	}

	/**
	 * Creates the Router instance that will translate requests into controller
	 * method calls.
	 *
	 * @access private
	 * @return void
	 */
	private function createRouter() {
		$this->router = new Router($this);

		// Set configured defaults if they've been set
		if ( ($defaultController = $this->config("Cannoli.Application.Routing", "defaultController", "")) != "" ) {
			$this->router->setDefaultController($defaultController);
		}
		if ( ($defaultMethod = $this->config("Cannoli.Application.Routing", "defaultMethod", "")) != "" ) {
			$this->router->setDefaultMethod($defaultMethod);
		}

		// Add configured routes to the router
		$routes = $this->config("Cannoli.Application.Routing", "routes", array());
		foreach ( $routes as $route ) {
			if ( !is_array($route) || count($route) != 2 ) {
				throw new Exception\RouteException("Invalid route configured in Cannoli.Application.Routing::routes, routes should be arrays with 2 elements each.");
			}
			$this->router->addRoute($route[0], $route[1]);
		}
	}

	/**
	 * Scans the registered plugin folders for plugin configuration files and
	 * based on those loads the plugins.
	 *
	 * @access private
	 * @return void
	 */
	private function loadPlugins() {
		$pluginPaths = $this->config("Cannoli.Framework.Plugins", "paths");

		$pm =& $this->getPluginManager();

		foreach ( $pluginPaths as $path ) {
			$pm->registerPlugin($path);
		}
	}

	/**
	 * The loadConfiguration method attempts to load the default system configuration
	 * file by registering it with the ConfigurationManager. To this end, the
	 * ConfigurationManager's path must be added to the autoloader first.
	 *
	 * @access private
	 * @return void
	 */
	private function loadConfiguration() {
		// Initialize configuration manager.
		$cm =& $this->getConfigurationManager();

		// Attempt to load the system configuration file
		$cm->loadFromFile(FILE_CONFIG);
		
		// Now that the configuration has been loaded, fire the onConfigurationLoaded handler
		$this->onConfigurationLoaded();
	}

	/**
	 * This method is called when the system configuration file has been loaded. As a result,
	 * all actions that are dependant on some configuration value should be initiated here.
	 *
	 * @access private
	 * @return void 
	 */
	private function onConfigurationLoaded() {
		// Load the autoload directories from the configuration
		$this->addAutoloadDirectories($this->config("Cannoli.Framework.Autoload", "systemDirectories"));
		$this->addAutoloadDirectories($this->config("Cannoli.Application.Autoload", "directories"));
	}

	/**
	 * This helper method exists to run and manage all pre-routing events
	 * and extensions. In practice this means it will just notify plugins
	 * of the event, but if any other system functionality needs doing 
	 * before routing occurs, it should be initiated from here as well.
	 *
	 * @access private
	 * @return void
	 */
	private function onBeforeRouting() {
		// System updates/code first

		// Notify plugins next
		$pm = &$this->getPluginManager();
		$pm->onBeforeRouting();
	}

	private function OnAfterRouting() {
		// System updates/code first

		// Notify plugins next
		$pm = &$this->getPluginManager();
		$newRenderable = $pm->onAfterRouting();

		return $newRenderable;
	}

	private function OnBeforeRendering(Core\IRenderable &$renderable) {

	}

	/**
	 * The checkPrerequisites method attempts to ensure the integrity of the
	 * framework's runtime environment
	 * 
	 * @access private
	 * @return void
	 */
	private function checkPrerequisites() {
		if ( !defined("PATH_SYSTEM") ) {
			throw new Exception\FrameworkException("PATH_SYSTEM constant must be set and should point to the directory that contain's the framework's application class.");
		}
		if ( !defined("FILE_CONFIG") || !is_readable(FILE_CONFIG) ) {
			throw new Exception\FrameworkException("FILE_CONFIG constant must be set and should point to a valid configuration file.");
		}
	}

	private function addSystemAutoloadPaths() {
		// Add the exceptions directory, otherwise the initialization exceptions cannot be thrown
		$this->addAutoloadDirectory(PATH_SYSTEM."/core/exception");
		
		// Add all the configuration related autoload directories
		$this->addAutoloadDirectory(PATH_SYSTEM."/core");
		$this->addAutoloadDirectory(PATH_SYSTEM."/core/configuration");
		$this->addAutoloadDirectory(PATH_SYSTEM."/core/exception/configuration");

		$this->addAutoloadDirectory(PATH_SYSTEM."/core/utility");
	}
}
?>