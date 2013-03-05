<?php
namespace Cannoli\Framework\Core\Plugin;

use Cannoli\Framework\Core\Configuration,
	Cannoli\Framework\Core\Exception;

// TODO: This file needs to extract more than just the domain from the plugin configuration file,
// 		 but in order to do that, the plugin system needs to be fleshed out more.

class PluginContainer
{
	private $domain;

	private $configurations = array();

	private $declarations = array();

	private $path;

	public function __construct($config) {
		$this->parse($config);
	}

	/**
	 * Returns the IConfiguration objects that are associated with the
	 * plugin's configuration sections. 
	 *
	 * @access public
	 * @return array 		An array of IConfiguration-derived objects
	 */
	public function getConfigurations() {
		return $this->configurations;
	}

	public function &getDeclarations() {
		return $this->declarations;
	}

	public function getId() {
		$this->checkDomain();
		return $this->domain->id;
	}

	public function getName() {
		$this->checkDomain();
		return $this->domain->name;
	}
	
	/**
	 * The getClass method returns the plugin's fully qualified (with namespace)
	 * class name.
	 * @access public
	 * @return string 		The plugin's fully qualified class name
	 */
	public function getClass() {
		$this->checkDomain();
		return $this->domain->class;
	}

	/**
	 * Returns the instantiated PluginBase-derived class
	 * 
	 * If the plugin instance has not yet been loaded, this method will create it.
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

	/**
	 * Parses a configuration section for the relevant information concerning
	 * a plugin.
	 *
	 * @access private
	 * @param object 		The configSection object
	 * @return void
	 */
	private function parse($config) {
		$this->domain = $this->getDomainFromConfig($config);

		$this->configurations = $this->getSettingsFromConfig($config);

		$this->declarations = $this->getDeclarationsFromConfig($config);
	}

	private function checkDomain() {
		if ( $this->domain == null ) {
			throw new Exception("Plugin Container failed to initialize and cannot supply domain information.");
		}
	}

	private function getDomainFromConfig($config) {
		// Test for the presence of the domain
		if ( empty($config->domain) ) {
			throw new Exception\Plugin\PluginBadConfigurationException("A valid domain must be specified in the plugin configuration.");
		}

		// Validate the domain
		return $this->validateAndUpdateDomain($config->domain);
	}

	private function validateAndUpdateDomain($domain) {
		// Regular expressions for validation
		$idRegex = "/^[a-zA-Z_]+[a-zA-Z0-9_\.\-]+$/";
		$nameRegex = "/^[a-zA-Z0-9\s_\-\.,;\:]{3,50}$/";

		if ( empty($domain->id) || !preg_match($idRegex, $domain->id) ) {
			throw new Exception\Plugin\PluginBadConfigurationException("Invalid domain id (Regex: $idRegex).");
		}

		// if ( ($type = PluginType::get($domain->type)) === false ) {
		// 	throw new Exception\Plugin\PluginBadConfigurationException("Invalid domain type.");
		// }

		if ( empty($domain->name) || !preg_match($nameRegex, $domain->name) ) {
			throw new Exception\Plugin\PluginBadConfigurationException("Invalid domain name (Regex: $nameRegex).");
		}

		// Don't need to bother checking the domain class, if it can't be found, the
		// registerPlugin method will complain.

		// Validation has been completed, update the domain with a valid type instance
		//$domain->type = $type;

		return $domain;
	}

	/**
	 * Retrieves the configurationSections from the plugin configuration file
	 * as IConfigurations
	 *
	 * @access private
	 * @param object 			The json-decoded plugin configuration object
	 * @return array 			The list of IConfigurations
	 */
	private function getSettingsFromConfig($config) {
		// Test for the presence of a config section
		if ( empty($config->config) ) {
			return array();
		}

		if ( !is_array($config->config) ) {
			throw new PluginBadConfigurationException("Invalid configuration sections.");
		}

		// Build ConfigurationContainers from the config sections
		$configurations = array();
		foreach ( $config->config as $configSection ) {
			$configurations[] = new Configuration\ConfigurationContainer($configSection);
		}

		return $configurations;
	}

	/**
	 * Retrieves the declarations from the plugin configuration file and returns
	 * them as PluginDeclarationResolver containers.
	 * 
	 * @access private
	 * @param object 			The json-decoded plugin configuration object
	 * @return array 			The list of PluginDeclarationResolver objects
	 */
	private function getDeclarationsFromConfig($config) {
		// Test for the presence of a declarations section
		if ( empty($config->declarations) ) {
			return array();
		}

		if ( !is_array($config->declarations) ) {
			throw new PluginBadConfigurationException("Invalid declarations section.");
		}

		// Build a new PluginDeclarationResolver for each of the declarations
		$declarations = array();
		foreach ( $config->declarations as $declaration ) {
			$declarations[] = new PluginDeclarationResolver($declaration);
		}

		return $declarations;
	}
}
?>