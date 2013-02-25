<?php
namespace Cannoli\Framework\Core;

interface IConfiguration extends \ArrayAccess
{
	const ACL_PRIVATE	= "private";

	const ACL_PUBLIC	= "public";

	function getConfigurationDomain();

	function getAccessLevel();

	function update(IConfiguration &$configuration);
	
	function set($key, $value);

	function get($key);
}
?>