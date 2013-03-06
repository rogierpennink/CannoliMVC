<?php
namespace Cannoli\Framework\Core\Plugin;

use Cannoli\Framework\Core\Exception;

/**
 * Container class for plugin declarations with helper methods to test
 * for validity and the availability of the specified interface that the
 * declaration promises to implement
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Plugin
 * @author Rogier Pennink
 * @category Plugin
 */
class PluginDeclarationResolver
{
	private $contractNamespace = "Cannoli\\Framework\\Contract";

	private $contract;

	private $class;

	public function __construct($declaration) {
		$this->parse($declaration);
	}

	public function getClass() {
		return $this->class;
	}

	public function getContract() {
		return $this->contract;
	}

	/**
	 * Parses a plugin declaration section for the relevant information
	 * concerning one of a plugin's declarations.
	 *
	 * @access private
	 * @param object 		The declaration object
	 * @return void
	 */
	private function parse($declaration) {
		if ( empty($declaration->class) ) {
			throw new Exception\Plugin\PluginBadConfigurationException("Plugin declaration must contain \"class\" member.");
		}

		if ( empty($declaration->contract) ) {
			throw new Exception\Plugin\PluginBadConfigurationException("Plugin declaration must contain \"contract\" member.");
		}
		
		$this->class = $declaration->class;

		$this->contract = $declaration->contract;
	}

	

	/**
	 * Returns the instantiated PluginBase-derived class that implements the contract specified
	 * by this plugin declaration.
	 * 
	 * If the plugin declaration instance has not yet been loaded, this method will create it.
	 * Since the plugin is necessarily always a singleton (PluginBase inherits from Singleton),
	 * this method simply wraps around the plugin class' getInstance static method.
	 *
	 * @access public
	 * @return object 		The plugin's class instance
	 */
	public function &getInstance() {
		$callable = array($this->getClass(), "getInstance");
		if ( ($instance = call_user_func($callable)) === false ) {
			throw new Exception\Plugin\PluginClassLoaderException("Failed call to \"". $this->getClass() ."::getInstance()\"");
		}
		return $instance;
	}
}
?>