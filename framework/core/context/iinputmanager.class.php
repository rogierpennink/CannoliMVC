<?php
namespace Cannoli\Framework\Core\Context;

interface IInputManager
{
	function has($key);

	function data($key, $defaultValue = null);
}
?>