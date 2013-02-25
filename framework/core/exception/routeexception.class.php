<?php
namespace Cannoli\Framework\Core\Exception;

class RouteException extends \Exception
{
	public function __construct($message = "") {
		parent::__construct($message);
	}
}
?>