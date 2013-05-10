<?php
namespace Cannoli\Framework\Core\Configuration;

require_once dirname(__FILE__) . "/../iconfiguration.class.php";

use Cannoli\Framework\Core,
	Cannoli\Framework\Core\Exception;

/**
 * ConfigurationContainer
 *
 * Contains the settings for a single configuration domain, as
 * well as metadata such as the domain name and the access level.
 *
 * @package		Cannoli
 * @subpackage	Framework\Core\Configuration
 * @author 		Rogier Pennink
 * @category	Configuration
 */
class ConfigurationContainer implements Core\IConfiguration
{
	private $domain;

	private $accessLevel;

	private $settings;

	public function __construct(\stdClass $configSection) {
		$this->parse($configSection);
	}

	/**
	 * Returns the current domain this Configuration container operates in.
	 * The domain is used by the configuration manager in combination with
	 * the getConfigurationDomain method of IConfigurables in deciding which
	 * configuration values to assign.
	 *
	 * @access public
	 * @return string 			The domain identifier
	 */
	public function getConfigurationDomain() {
		return $this->domain;
	}

	/**
	 * Retrieves the current access level of this Configuration container
	 *
	 * @access public
	 * @return string 			The access level (ACL_PRIVATE or ACL_PUBLIC)
	 */
	public function getAccessLevel() {
		return $this->accessLevel;
	}

	/**
	 * Updates the configurationContainer with a new IConfiguration object
	 *
	 */
	// TODO: better implementation, we may want to look inside values and see
	// if we can update parts rather than replace etc.
	public function update(Core\IConfiguration &$configuration) {
		foreach ( $configuration as $key => $value ) {
			if ( !isset($this[$key]) ) {
				$this[$key] = $value;
			}
			else {
				// Inspect the values, if they're similar types, try to update, rather
				// than replace.
				// Since we only update one 'level', we don't need to worry about making
				// this functionality recursive
				if ( is_array($value) && is_array($this[$key]) ) {
					$this[$key] = array_merge($this[$key], $value);
				}
				elseif ( is_object($value) && is_object($this[$key]) ) {
					$objectVars = get_object_vars($value);
					foreach ( $objectVars as $objectKey => $objectValue ) {
						$this[$key]->{$objectKey} = $objectValue;
					}
				}
				else {
					$this[$key] = $value;
				}
			}
		}
	}

	/**
	 * Associates a value with a key.
	 *
	 * @access public
	 * @param string 			They key to associate the value with
	 * @param mixed 			The value to associate with the key
	 * @return void
	 */
	public function set($key, $value) {
		if ( $this->accessLevel == self::ACL_PRIVATE ) {
			throw new Exception\Configuration\ConfigurationAccessException("Plugin container for domain \"{$this->domain}\" has access level set to ACL_PRIVATE.");
		}

		$this->settings[$key] = $value;
	}

	/**
	 * Retrieves a setting
	 *
	 * @access public
	 * @param string 			The key for which to retrieve the associated setting
	 * @return mixed 			The settings value associated with $key
	 */
	public function get($key) {
		if ( !$this->has($key) ) return null;
		return $this->settings[$key];
	}

	/**
	 * Checks whether a setting with the specified key is present in this
	 * ConfigurationContainer
	 *
	 * @access public
	 * @param string 			The key to check for a value for
	 * @return boolean			True if key was found to be associated with a value,
	 *							false otherwise
	 */
	public function has($key) {
		return isset($this->settings[$key]);
	}

	/**
	 * Associates a value with a key.
	 *
	 * @access public
	 * @param string 			They key to associate the value with
	 * @param mixed 			The value to associate with the key
	 * @return void
	 */
	public function offsetSet($key, $value) {
		if ( is_null($key) ) {
			throw new Exception\Configuration\ConfigurationAccessException("Key must not be null in Configuration container for domain \"{$this->domain}\".");
		}

		$this->set($key, $value);
	}

	/**
	 * Checks whether a setting with the specified key is present in this
	 * ConfigurationContainer
	 *
	 * @access public
	 * @param string 			The key to check for a value for
	 * @return boolean			True if key was found to be associated with a value,
	 *							false otherwise
	 */
    public function offsetExists($key) {
    	return $this->has($key);
    }

    /**
     * Attempts to unset the value associated with the given key. Throws
     * an exception if the container's access level is set to private.
     *
     * @access public
     * @param string 			The key for which to unset the value
     * @return void
     */
    public function offsetUnset($key) {
    	if ( $this->accessLevel == self::ACL_PRIVATE ) {
			throw new Exception\Configuration\ConfigurationAccessException("Configuration container for domain \"{$this->domain}\" has access level set to ACL_PRIVATE.");
		}

    	unset($this->settings[$key]);
    }

    /**
	 * Retrieves a setting
	 *
	 * @access public
	 * @param string 			The key for which to retrieve the associated setting
	 * @return mixed 			The settings value associated with $key
	 */
    public function offsetGet($key) {
    	return $this->get($key);
    }

    /////////////////////////////////////
    ////// ITERATOR IMPLEMENTATION //////
    /////////////////////////////////////

    public function rewind() {
        reset($this->settings);
    }

    public function current() {
        return current($this->settings);
    }

    public function key() {
        return key($this->settings);
    }

    public function next() {
		if ( !$this->valid() ) 
      		throw new \NoSuchElementException('at end of array');
        next($this->settings);
    }

    public function valid() {
        return current($this->settings) !== false;
    }

    /**
     * Parses a configuration section and sets the internal state of this configuration
     * container object as appropriate.
     *
     * @access private
     * @param object 			A single configuration section object.
     * @return void
     */    
    private function parse(\stdClass $configSection) {
    	$this->reset();

    	// Each config section must be an object with certain properties
    	if ( !isset($configSection->domain) || !isset($configSection->settings) ) {
    		throw new Exception\Configuration\ConfigurationParseException("Configuration section must contain valid domain and settings fields.");
    	}

    	$this->domain = $configSection->domain;
    	if ( isset($configSection->accessLevel) ) {
    		$this->accessLevel = $configSection->accessLevel == self::ACL_PUBLIC ? self::ACL_PUBLIC : self::ACL_PRIVATE;
    	}
    	
    	$settings = get_object_vars($configSection->settings);
    	foreach ( $settings as $key => $value ) {
    		$this->settings[$key] = $value;
    	}
    }

	/**
	 * Resets the ConfigurationContainer's internal state. Default
	 * access level is private.
	 *
	 * @access private
	 * @return void
	 */
	private function reset() {
		$this->accessLevel = "private";
		$this->domain = "";
		$this->settings = array();
	}
}
?>