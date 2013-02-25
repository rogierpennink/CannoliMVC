<?php
namespace Cannoli\Framework\Core\Configuration;

use Cannoli\Framework\Core,
	Cannoli\Framework\Core\Exception;

/**
 * Manages a set of IConfiguration objects.
 *
 * The intent of this class is to manage a collection of IConfiguration elements
 * and to thus help establish a composite pattern for IConfigurable-derived
 * classes. The class exposes methods that offer shortcuts to the actual
 * configuration values in the collection of IConfiguration elements. 
 *
 * @package Cannoli
 * @subpackage Framework\Core\Configuration
 * @author Rogier Pennink
 * @category Configuration
 */
class ConfigurationCollection
{
	private $configurations = array();

	/**
	 * Adds a new IConfiguration object to the managed collection
	 *
	 * @access public
	 * @param IConfiguration		A new configuration element to manage
	 * @return void
	 */
	public function add(Core\IConfiguration &$configuration) {
		// Check if configuration with same domain is already registered
		if ( isset($this->configurations[$configuration->getConfigurationDomain()]) ) {
			throw new Exception\Configuration\ConfigurationRegistrationException("Duplicate IConfiguration instance with the domain \"{$configuration->getConfigurationDomain()}\".");
		}

		$this->configurations[$configuration->getConfigurationDomain()] = $configuration;
	}

	/**
	 * Retrieves the value specified by the $key parameter from an IConfiguration
	 * instance addressed by the $domain parameter.
	 *
	 * @access public
	 * @param string 				The domain against which to match IConfigurations
	 * @param string 				The key to search for if a matching IConfiguration was found
	 * @param mixed 				The default value in case the requested value wasn't found
	 * @return mixed 				The default value if either the domain or key was not found, the configuration
	 *								value otherwise.
	 */
	public function get($domain, $key, $defaultValue = null) {
		if ( !isset($this->configurations[$domain]) ) return $defaultValue;

		if ( !isset($this->configurations[$domain][$key]) ) return $defaultValue;

		return $this->configurations[$domain][$key];
	}
}
?>