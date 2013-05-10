<?php
namespace Cannoli\Framework\Core\Plugin;

require_once dirname(__FILE__) ."/../utility/singleton.class.php";

use Cannoli\Framework\Application,
	Cannoli\Framework\Core,
	Cannoli\Framework\Core\Configuration,
	Cannoli\Framework\Core\Exception,
	Cannoli\Framework\Core\Utility,
	Cannoli\Framework\View;

/**
 * The PluginManager is responsible for the registration and keeping
 * track of all plugins. Through the PluginManager, the system can
 * post events to plugins as well as request plugins of a certain type
 * to help with, for example, database connections, or account management.
 *
 * @package Cannoli
 * @subpackage Framework\Core\Plugin
 * @author Rogier Pennink
 * @category Plugin
 */
class PluginManager extends Utility\ConfigurableClass
{
	private static $configDomain = "Cannoli.Framework.Core.Plugin.PluginManager";

	private $plugins = array();

	private $defaultPluginConfigurationFile = "plugin.conf";

	private $configurationManager;

	private $app;

	/**
	 * Since PluginManager should only be created by the IoC Container, we
	 * assume that the constructor's parameters will be injected.
	 */
	public function __construct(Application &$app) {
		parent::__construct();

		$this->app = $app;

		// TODO: Preferably, this is injected rather than requested...
		$this->configurationManager =& $app->getConfigurationManager();
		$this->configurationManager->registerConfigurable($this);
	}

	public function getConfigurationDomains() {
		return array(
			self::$configDomain
		);
	}

	/**
	 * Attempts to register a new plugin with the specified key and
	 * type. The method throws an exception if registration failed
	 * (for example, because another plugin with the same key was
	 * already found, or if the specified path contained an invalid
	 * plugin script).
	 *
	 * @access public
	 * @param string 		The path, relative to the plugin root folder(s),
	 *						where this plugin may be found.
	 * @return void
	 */
	public function registerPlugin($path) {
		// Can throw a PluginBadConfigurationException
		$pluginContainer = $this->loadPlugin($path);

		// Check if the plugin doesn't already exist
		if ( isset($this->plugins[$pluginContainer->getId()]) ) {
			throw new Exception\Plugin\PluginRegistrationException("A plugin with id \"{$pluginContainer->getId()}\" has already been registered.");
		}

		if ( !$this->validatePlugin($pluginContainer) ) {
			throw new Exception\Plugin\PluginRegistrationException("Plugin with id \"{$pluginContainer->getId()}\" did not pass validation.");
		}

		// Plugin was found to be valid so we add it to our internal list
		$this->plugins[$pluginContainer->getId()] = $pluginContainer;

		// Now that the plugin has been successfully registered, we can add the configuration sections
		foreach ( $pluginContainer->getConfigurations() as $configuration ) {
			$this->configurationManager->registerConfiguration($configuration);
		}

		// Register contracts with the ioc container
		foreach ( $pluginContainer->getDeclarations() as $declaration ) {
			$this->bindContractDeclaration($declaration);
		}

		// Notify the plugin that it has been registered successfully
		if ( $pluginContainer->isInstantiable() ) {
			$pluginContainer->trigger("onRegistrationComplete");	
		}
	}

	/**
	 * Attemps to remove the specified plugin from the internal list.
	 *
	 * @access public
	 * @param string 		The key which uniquely identifies a plugin
	 * @return void
	 */
	public function unregisterPlugin($key) {
		if ( !isset($this->plugins[$key]) ) {
			throw new Exception\Plugin\PluginRemoveException("Plugin with id \"$key\" was not found and cannot be removed.");
		}

		if ( $this->plugins[$key]->isInstantiable() ) {
			$this->plugins[$key]->getInstance()->onRegistrationUndone();
		}

		unset($this->plugins[$key]);
	}

	/**
	 * Walks through the internal collection of plugins to find plugins with
	 * declarations that match the requested contract.
	 * 
	 * @access public
	 * @param $contract 					The contract for which to search plugins
	 * @return PluginDeclarationResolver	The requested PluginDeclarationResolver, or false if no plugin
	 * 										for the requested contract could be found.
	 * @throws PluginConflictException
	 */
	public function getDeclaration($contract) {
		$requestedPluginContainer = false;

		foreach ( $this->plugins as $key => &$pluginContainer ) {
			$declarations = $pluginContainer->getDeclarations();
			foreach ( $declarations as &$declaration ) {
				if ( $declaration->getContract() == $contract ) {
					if ( $requestedPluginContainer !== false ) {
						throw new Exception\Plugin\PluginConflictException("More than one active plugin that implements ". $contract ." was found.");
					}

					$requestedPluginContainer = $pluginContainer;
					$requestedDeclaration = $declaration;
				}
			}
		}

		return $requestedDeclaration;
	}

	/**
	 * Notifies registered plugins that the onBeforeRouting phase has
	 * been reached.
	 *
	 * @access public
	 * @return void
	 */
	public function onBeforeRouting() {
		// TODO: need some way of allowing plugins to specify whether they want
		// to be notified of this request or not
		// This is true for all plugin events
		foreach ( $this->getInstantiablePlugins() as $pluginContainer ) {
			$pluginContainer->trigger("onBeforeRouting");
		}
	}

	/**
	 * Notifies registered plugins that the onAfterRouting phase has
	 * been reached.
	 *
	 * @access public
	 * @return void
	 */
	public function onAfterRouting() {
		foreach ( $this->getInstantiablePlugins() as $pluginContainer ) {
			$pluginContainer->trigger("onAfterRouting");
		}
	}

	/**
	 * Constructs and returns an array of plugins that can be instantiated as a
	 * PluginBase-derived instance. Plugins that only implement system contracts
	 * are left out.
	 *
	 * @access public
	 * @return array 				The array of instantiable plugins
	 */
	public function getInstantiablePlugins() {
		return array_filter($this->plugins, function($el) {
			return $el->isInstantiable();
		});
	}

	/**
	 * Given a valid pathname, attempts to load the plugin that
	 * is allegedly found in $path.
	 * Throws a PluginLoadException if plugin loading failed for
	 * whichever reason.
	 * @param $path 		The path to the plugin file
	 * @return object 		The PluginContainer object
	 */
	protected function loadPlugin($path) {
		// Load plugin configuration
		$config = $this->loadPluginConfiguration($path);

		// If no error occurred, the path must be valid so add to autoload directories
		$this->app->addAutoloadDirectory($path);

		// Attempt to construct a PluginContainer. If construction of the container
		// is successful we can proceed querying it for information from the config
		// in order to load the appropriate classes etc.
		$container = new PluginContainer($config, $this->app);
		$configurations = $container->getConfigurations();

		// Create and register ConfigurationContainers for the configSections
		// with the application's configuration manager.
		foreach ( $configurations as &$configuration ) {
			$this->configurationManager->registerConfiguration($configuration);
		}

		return $container;
	}

	/**
	 * Given a valid plugin directory or configuration file path, this method
	 * will load the configuration file for the plugin and return the raw
	 * json-decoded data found within.
	 *
	 * @access protected
	 * @param string 		The path in which to look for the configuration
	 * @return object 		The raw json-decoded configuration
	 */
	protected function loadPluginConfiguration($path) {
		$file = "";

		// If the path is a directory, check for the default 
		// plugin configuration file. If the path refers to
		// an actual file, assume it to be the configuration
		// file.
		if ( is_dir($path) ) {
			//TODO: Implement and use dedicated file loader class
			$path = substr($path, -1, 1) == "/" ? $path : $path . "/";
			$file = file_get_contents($path.$this->defaultPluginConfigurationFile);
		}
		elseif ( is_readable($path) ) {
			$file = file_get_contents($path);
		}

		// Attempt to json_decode
		if ( ($config = json_decode($file)) == null ) {
			throw new Exception\Plugin\PluginBadConfigurationException("Corrupted plugin configuration file failed to load.");
		}

		return $config;
	}

	/**
	 * 
	 */
	protected function validatePlugin(PluginContainer &$pluginContainer) {
		$pluginInst = $pluginContainer->getInstance();
		// No matter what, the plugin needs to inherit from PluginBase
		if ( $pluginInst != null && !($pluginInst instanceof PluginBase) ) {
			throw new Exception\Plugin\PluginRegistrationException("Plugin class must inherit from Cannoli\\Framework\\Core\\Plugin\\PluginBase.");
		}

		// Check for contracts validity
		if ( !$this->validatePluginDeclarations($pluginContainer) ) {
			throw new Exception\Plugin\PluginRegistrationException($pluginContainer->getId() .": One or more of this plugin's declarations failed validation.");
		}

		return true;
	}

	/**
	 * If the given plugin declares to implement system contracts, this method
	 * checks whether those contracts actually exist.
	 * 
	 * @access protected
	 * @param PluginContainer 	The to-be validated plugin container
	 * @return bool 			true if plugin container has passed validation, false otherwise
	 */
	protected function validatePluginDeclarations(PluginContainer &$pluginContainer) {
		// TODO: right now we're only checking against system contracts, but in the future
		// we might want to allow users to specify their own contracts, in which case we
		// would also have to validate against the collection of user-defined contracts

		// Get the array of available contracts from the configuration
		$availableContracts = $this->config(self::$configDomain, "contracts");
		$declarations = &$pluginContainer->getDeclarations();

		// TODO check app config for user-defined contracts.

		foreach ( $declarations as &$declaration ) {
			if ( !in_array($declaration->getContract(), $availableContracts) ) {
				return false;
			}

			// Using reflection we can test if the declared class actually
			// implements the contract, without having to instantiate it.
			$rc = new \ReflectionClass($declaration->getClass());

			// TODO: See if there's some way to do this without hard-coding the contract namespace here
			if ( !in_array($this->addContractNamespace($declaration->getContract()), $rc->getInterfaceNames()) ) {
				return false;
			}

			if ( !is_subclass_of($declaration->getClass(), "Cannoli\\Framework\\Core\\Plugin\\PluginContractDeclaration") ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Adds the full contract namespace if it's not already added.
	 *
	 * @access private
	 * @param $contract 	The contract to add the namespace to
	 * @return string 		The (possibly) updated fully namespaced contract name
	 */
	private function addContractNamespace($contract) {
		$contractNamespace = $this->config(self::$configDomain, "contractNamespace", "Cannoli\\Framework\\Contract");
		if ( substr($contract, 0, strlen($contractNamespace)) == $contractNamespace ) {
			return $contract;
		}

		return $contractNamespace ."\\". trim($contract, "\\");
	}

	/**
	 * Binds a plugin declaration contract to a valid plugin classname so that the
	 * declaration can be instantiated using the IoC container. Also checks if the
	 * contract implementation exposes the mandatory configuration domains.
	 *
	 * @access private
	 * @param $declaration	The declaration container to use for the binding
	 * @return void
	 */
	private function bindContractDeclaration(PluginDeclarationResolver $declaration) {
		$contract = $declaration->getContract();
		$namespacedContract = $this->addContractNamespace($contract);
		$declarationClsName = $declaration->getClass();

		// Early exit condition - we don't need to bind if a system module has already
		// bound an implementation for this declaration
		if ( $this->app->getIocContainer()->hasBindingWithTypeName($namespacedContract) ) {
			return;
		}
		
		// Find the scope from plugin manager configuration
		$scopeMethod = "inTransientScope";
		$scopes = $this->config(self::$configDomain, "contractScopes", "transient");
		foreach ( $scopes as $scope ) {
			if ( $scope->contract != $contract ) continue;
			$scopeMethod = "in". ucfirst($scope->scope) ."Scope";
		}

		// Add a closure as argument to the scope method, to throw an exception if after
		// instantiation it appears that the wrong configuration domains are exposed
		// NOTE: we have to expose these variables the way we are because 5.3.6 and lower
		// do not allow access to $this from the closure. We also cannot proxy $this through
		// a different variable because it won't allow calls to private methods.
		$app = $this->app;
		$requiredDomains = $this->getRequiredConfigurationDomainsForContract($contract);
		$args = array(function($instance) use($app, $requiredDomains, $contract) {
			if ( !($instance instanceof PluginContractDeclaration) ) {
				// This should never happen
				throw new Exception\Plugin\PluginDeclarationInstantiationException("Plugin declaration instance was constructed but does not inherit from PluginContractDeclaration.");
			}

			$exposedDomains = $instance->getConfigurationDomains();
			
			if ( !empty($requiredDomains) ) {
				if ( count(array_intersect($exposedDomains, $requiredDomains)) != count($requiredDomains) ) {
					throw new Exception\Plugin\PluginDeclarationInstantiationException("Plugin declaration instance for contract ($contract) does not expose required configuration domains.");
				}
			}
		});
		
		$bindingScope =& $app->getIocContainer()->bind($namespacedContract)->to($declarationClsName);
		call_user_func_array(array($bindingScope, $scopeMethod), $args);
	}

	/**
	 * Searches the contract-configdomain mappings for a matching
	 *
	 * @access private
	 * @param $contract 	The contract to search config domain requirements for
	 * @return array 		The array of required config domains (can be empty)
	 */
	private function getRequiredConfigurationDomainsForContract($contract) {
		$contractMappings = $this->config(self::$configDomain, "contractConfigDomains", array());

		foreach ( $contractMappings as $mapping ) {
			if ( $mapping->contract == $contract ) {
				if ( is_array($mapping->domains) ) {
					return $mapping->domains;
				}
				break;
			}
		}

		return array();
	}

	/**
	 * If a non-user-defined plugin type is specified, this method
	 * checks whether the created plugin object implements the
	 * required interface(s).
	 * @param PluginContainer 	The to-be-validated plugin object
	 * @return bool 			true if $object is valid, false otherwise
	 */
	// protected function validatePluginType(PluginContainer &$pluginContainer) {
	// 	// No matter what, the plugin needs to inherit from PluginBase
	// 	if ( !($pluginContainer->getInstance() instanceof PluginBase) ) {
	// 		throw new Exception\Plugin\PluginRegistrationException("Plugin class must inherit from Cannoli\\Framework\\Core\\Plugin\\PluginBase.");
	// 	}

	// 	// Get the required base type name.
	// 	if ( ($baseTypeName = $pluginContainer->getType()->getRequiredBaseTypeName()) !== null ) {
	// 		if ( !($pluginContainer->getInstance() instanceof $baseTypeName) ) {
	// 			return false;
	// 		}
	// 	}

	// 	return true;
	// }
}
?>