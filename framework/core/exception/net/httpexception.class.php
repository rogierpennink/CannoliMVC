<?php
namespace Cannoli\Framework\Core\Exception\Net;

class HttpException extends \Exception
{
	public function __construct($message, $code) {
		parent::__construct($message, $code);
	}
}
?>