<?php
namespace Cannoli\Framework\Core;

interface IConfigurable
{
	function getConfigurationDomains();

	function configure(IConfiguration &$configuration);
}
?>