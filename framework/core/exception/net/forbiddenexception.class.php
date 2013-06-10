<?php
namespace Cannoli\Framework\Core\Exception\Net;

use Cannoli\Framework\Core\Net;

class ForbiddenException extends HttpException
{
	public function __construct($message) {
		parent::__construct($message, Net\HttpStatus::FORBIDDEN);
	}
}
?>