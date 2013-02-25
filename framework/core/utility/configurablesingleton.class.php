<?php
namespace Cannoli\Framework\Core\Utility;

require_once dirname(__FILE__) ."/singleton.class.php";
require_once dirname(__FILE__) ."/../iconfigurable.class.php";
require_once dirname(__FILE__) ."/../configuration/configurationcollection.class.php";

use Cannoli\Framework\Core,
	Cannoli\Framework\Core\Configuration;

/**
 * Provides a way for classes that want to inherit Singleton functionality to still
 * be Configurable as well
 *
 * @package Cannoli
 * @subpackage Framework\Core\Utility
 * @author Rogier Pennink
 * @category Configuration
 */
abstract class ConfigurableSingleton extends Singleton implements Core\IConfigurable
{
	private $configurationCollection;

	public function configure(Core\IConfiguration &$configuration) {
		$this->configurationCollection->add($configuration);
	}

	/**
	 * Acts as a thin wrapper around ConfigurationCollection::get
	 *
	 * @access protected
	 * @param string 			The domain to search in
	 * @param string 			The key to look for if a domain is found
	 * @param mixed 			The default value to return in case no value was found
	 * @return mixed 			the default value if no configuration value is found, the desired value otherwise
	 */
	protected function config($domain, $key, $defaultValue = null) {
		return $this->configurationCollection->get($domain, $key, $defaultValue);
	}

	protected function __construct() {
		parent::__construct();

		$this->configurationCollection = new Configuration\ConfigurationCollection();
	}
}
?>