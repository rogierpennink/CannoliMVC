<?php
namespace Cannoli\Framework\Core\Plugin;

use Cannoli\Framework\Application,
	Cannoli\Framework\Core,
	Cannoli\Framework\Core\Utility;

abstract class PluginBase extends Utility\ConfigurableSingleton
{
	protected $app;

	private static $instances = array();

	/**
	 * The getInstance static method overrides the Singleton getInstance
	 * method because it is required that plugin instances are supplied
	 * with an Application instance (which Singleton cannot provide because
	 * it is too generic).
	 *
	 * @access public
	 * @return PluginBase 		The requested plugin object
	 */
	public static function &getInstance() {
		$clsName = get_called_class();

		if ( $clsName == __CLASS__ ) return null;
		
		try {
			if ( !isset(self::$instances[$clsName]) )
				self::$instances[$clsName] = new $clsName(Application::getInstance());
			return self::$instances[$clsName];
		}
		catch ( Exception $e ) {
			return null;
		}
	}

	public function __construct(Application &$app) {
		$this->app = $app;
	}

	// Takes place when the plugin has been successfully registered and will
	// start receiving events
	abstract public function onRegistrationComplete();

	abstract public function onRegistrationUndone();

	// Takes place before the request is processed by a controller
	abstract public function onBeforeRouting();

	// Takes the Renderable object that was returned by the routing process.
	// This is typically a view or a view collection.
	// Returns the (un)modified renderable
	abstract public function onAfterRouting(Core\IRenderable &$renderable);

	// Takes the Output (string, or output object) that is going to be sent
	// before it is sent, this allows caching or modification of the output.
	// Returns the (un)modified output
	abstract public function onBeforeRender($output);

	// No input as everything has already been sent to the browser.
	abstract public function onAfterRender();
}
?>