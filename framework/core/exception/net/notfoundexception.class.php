<?php
namespace Cannoli\Framework\Core\Exception\Net;

use Cannoli\Framework\Core\Net;

class NotFoundException extends HttpException
{
	public function __construct($message) {
		parent::__construct($message, Net\HttpStatus::NOT_FOUND);
	}
}
?>