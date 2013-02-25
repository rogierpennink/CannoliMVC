<?php
namespace Cannoli\Framework\Core\Plugin;

use Cannoli\Framework\Application,
	Cannoli\Framework\Core;

/**
 * Provides a dummy implementation for all the PluginBase-enforced
 * methods
 *
 * @package Cannoli
 * @subpackage Framework\Core\Plugin
 * @author Rogier Pennink
 * @category Plugin
 */
class Plugin extends PluginBase
{
	public function __construct(Application &$app) {
		parent::__construct($app);
	}

	public function onRegistrationComplete() {}
	
	public function onRegistrationUndone() {}

	// Takes place before the request is processed by a controller
	public function onBeforeRouting() {}

	// Takes the Renderable object that was returned by the routing process.
	// This is typically a view or a view collection.
	// Returns the (un)modified renderable
	// TODO: This is dumb, it shouldn't return renderables at all, or if it must,
	// the system should be changed to replace the original renderable.
	public function onAfterRouting(Core\IRenderable &$renderable) {
		return $renderable;
	}

	// Takes the Output (string, or output object) that is going to be sent
	// before it is sent, this allows caching or modification of the output.
	// Returns the (un)modified output
	public function onBeforeRender($output) {}

	// No input as everything has already been sent to the browser.
	public function onAfterRender() {}

	public function getConfigurationDomains() {
		return array();
	}
}
?>