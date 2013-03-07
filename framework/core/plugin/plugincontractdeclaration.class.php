<?php
namespace Cannoli\Framework\Core\Plugin;

use Cannoli\Framework\Core\Utility;

abstract class PluginContractDeclaration extends Utility\ConfigurableClass
{
	public function getConfigurationDomains() {
		return array();
	}
}
?>