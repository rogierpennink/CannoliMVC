<?php
namespace Cannoli\Framework\Core\Context;

interface IInputManager extends \ArrayAccess, \Iterator
{
	function has($key);

	function data($key, $defaultValue = null);
}
?>