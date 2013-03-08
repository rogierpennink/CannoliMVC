<?php
namespace Cannoli\Framework\Core\Plugin;

use Cannoli\Framework\Core\Configuration,
	Cannoli\Framework\Core\Utility;

abstract class PluginContractDeclaration extends Utility\ConfigurableClass
{
	public function __construct(Configuration\ConfigurationManager &$configManager) {
		$configManager->registerConfigurable($this);
	}

	public function getConfigurationDomains() {
		return array();
	}
}
?>