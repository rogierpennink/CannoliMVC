<?php
namespace Cannoli\Framework\Core\Exception;

interface IExceptionHandler
{
	function handleException(\Exception $e);
}
?>