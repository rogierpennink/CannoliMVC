<?php
namespace Cannoli\Framework\Core\Context;

interface IInputManager extends \ArrayAccess
{
	function has($key);

	function data($key, $defaultValue = null);
}
?>