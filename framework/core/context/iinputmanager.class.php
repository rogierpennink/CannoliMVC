<?php
namespace Cannoli\Framework\Core\Context;

interface IInputManager
{
	function get($key, $defaultValue = false);

	function has($key);
}
?>