<?php
namespace Cannoli\Framework\Core\Configuration;

use Cannoli\Framework\Core;

abstract class Configurable implements Core\IConfigurable
{
	abstract public function getConfigurationDomains();

	public function configure(Core\IConfiguration &$configuration) {

	}
}
?>