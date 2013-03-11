<?php
namespace Cannoli\Framework\Core\Plugin;

use Cannoli\Framework\Core\Configuration,
	Cannoli\Framework\Core\Utility;

abstract class PluginContractDeclaration extends Utility\ConfigurableClass
{
	public final function __construct(Configuration\ConfigurationManager &$configManager) {
		parent::__construct();
		$configManager->registerConfigurable($this);

		// TODO: not sure if this is the way to go, method-injected dependencies are not yet available
		// at this point.
		$this->initialize();
	}

	public function getConfigurationDomains() {
		return array();
	}

	protected function initialize() {
	}
}
?>