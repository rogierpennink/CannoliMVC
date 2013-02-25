<?php
namespace Cannoli\Framework\Core\Exception;

class FrameworkException extends \Exception
{
	public function __construct($message) {
		parent::__construct($message);
	}
}
?>