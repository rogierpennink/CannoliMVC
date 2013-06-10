<?php
namespace Cannoli\Framework\Core\Event;

interface IEvent
{
	function getType();
	
	function getData();
}
?>