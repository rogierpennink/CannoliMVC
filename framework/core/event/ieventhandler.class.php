<?php
namespace Cannoli\Framework\Core\Event;

interface IEventHandler
{
	function handle(IEvent $event);
}
?>