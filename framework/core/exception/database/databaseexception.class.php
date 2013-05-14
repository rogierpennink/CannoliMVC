<?php
namespace Cannoli\Framework\Core\Exception\Database;

class DatabaseException extends \Exception
{
	public function __construct($message, $code = null) {
		parent::__construct($message, $code);
	}
}
?>