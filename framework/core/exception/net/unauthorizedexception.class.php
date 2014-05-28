<?php
namespace Cannoli\Framework\Core\Exception;

use Cannoli\Framework\Core\Net;

class UnauthorizedException extends HttpException
{
	public function __construct($message) {
		parent::__construct($message, Net\HttpStatus::UNAUTHORIZED);
	}
}
?>