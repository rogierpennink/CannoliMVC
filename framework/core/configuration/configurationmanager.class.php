<?php
namespace Cannoli\Framework\Core\Configuration;

use Cannoli\Framework\Core\Utility,
	Cannoli\Framework\Core\IConfigurable,
	Cannoli\Framework\Core\IConfiguration,
	Cannoli\Framework\Core\Exception;

/**
 * Manages IConfiguration and IConfigurable instances.
 *
 * When an IConfigurable is registered with the ConfigurationManager, all
 * IConfiguration instances that are registered for the IConfigurable's
 * domain will be used to configure the IConfigurable.
 * Likewise, if an IConfiguration object is registered, it will immediately
 * be applied against all currently registered IConfigurables
 *
 * @package Cannoli
 * @subpackage Framework\Core\Configuration
 * @author Rogier Pennink
 * @category Configuration
 */
class ConfigurationManager extends Utility\Singleton
{
	private $configurables = array();

	private $configurations = array();

	private $loadedFiles = array();

	/**
	 * Attempts to load configuration sections from a configuration file
	 *
	 * @access public
	 * @return void
	 * @throws ConfigurationLoadException
	 */
	public function loadFromFile($filename) {
		if ( isset($this->loadedFiles[$filename]) )
			return;

		if ( ($file = file_get_contents($filename)) === false ) {
			throw new Exception\Configuration\ConfigurationLoadException("Failed to read configuration file: ". $filename, $filename);
		}

		// Attempt to json_decode
		if ( ($config = json_decode($file)) == null || !is_array($config) ) {
			throw new Exception\Configuration\ConfigurationLoadException("Configuration file (". $filename .") is corrupted.", $filename);
		}

		// Walk through the configuration sections
		foreach ( $config as $configSection ) {
			// If the section's type is an include, load those files first
			if ( isset($configSection->type) && $configSection->type == "includes" ) {
				if ( !is_array($configSection->files) ) {
					throw new Exception\Configuration\ConfigurationLoadException("Configuration include section is corrupted. Files not declared as array.", $filename);
				}

				foreach ( $configSection->files as $includeFile ) {
					$this->loadFromFile($includeFile);	
				}
				
			}
			else {
				$container = new ConfigurationContainer($configSection);
				$this->registerConfiguration($container);
			}
		}

		// Save the raw parsed configuration array
		$this->loadedFiles[$filename] =	$config;
	}

	/**
	 * Register a new Configurable object with the configuration manager.
	 *
	 * Registration of a new Configurable, provided it was successful, will
	 * trigger any existing IConfiguration objects for the Configurable's
	 * domain to be applied against it.
	 *
	 * @access private
	 * @param IConfigurable 	The Configurable object
	 * @return void
	 */
	public function registerConfigurable(IConfigurable &$configurable) {
		$domains = $configurable->getConfigurationDomains();
		
		$this->configurables[] = $configurable;

		// Check if there are any configurations that can be applied
		$configurations = $this->getConfigurationsForDomains($domains);
		foreach ( $configurations as &$configuration ) {
			$configurable->configure($configuration);
		}
	}

	/**
	 * Register a new Configuration object with the configuration manager.
	 *
	 * Registration of a new Configuration, provided it was successful, will
	 * trigger any existing IConfigurable objects for the Configuration's
	 * domain to be updated with the new Configuration.
	 *
	 * @access public
	 * @param IConfiguration 	The Configuration object
	 * @return void
	 */
	public function registerConfiguration(IConfiguration &$configuration) {
		$domain = $configuration->getConfigurationDomain();

		// If a configuration for this domain already exists, update it
		if ( isset($this->configurations[$domain]) ) {
			if ( $this->configurations[$domain]->getAccessLevel() != IConfiguration::ACL_PUBLIC ) {
				throw new Exception\Configuration\ConfigurationRegistrationException("Cannot overwrite configuration with restricted access level (\"$domain\").");
			}

			$this->configurations[$domain]->update($configuration);
			// TODO: may have to raise an event of sorts on configurables here
		}
		else {
			$this->configurations[$domain] = $configuration;

			// Check if there are any configurables that could use this
			$configurables = $this->getConfigurablesForDomain($domain);
			foreach ( $configurables as &$configurable ) {
				$configurable->configure($configuration);
			}
		}
	}

	/**
	 * Collects the configurables for which the configuration domain matches
	 * the requested domain.
	 *
	 * @access private
	 * @param string 			The domain to match configurables against
	 * @return array 			An array of matched configurables
	 */
	private function getConfigurablesForDomain($domain) {
		$configurables = array();
		foreach ( $this->configurables as &$configurable ) {
			if ( in_array($domain, $configurable->getConfigurationDomains()) ) {
				$configurables[] = $configurable;
			}
		}
		return $configurables;
	}

	/**
	 * Collects the configurations for which the configuration domain matches
	 * the requested domains
	 *
	 * @access private
	 * @param array  			The array of domains to match configurations against
	 * @return array 			An array of matched configurations
	 */
	private function getConfigurationsForDomains(array $domains) {
		$configurations = array();
		foreach ( $this->configurations as $domain => &$configuration ) {
			if ( in_array($domain, $domains) ) {
				$configurations[] = $configuration;
			}
		}
		return $configurations;
	}
}
?>