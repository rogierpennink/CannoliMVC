<?php
namespace Cannoli\Framework\Core\Exception\Database;

class DatabaseNotConnectedException extends \Exception
{
	public function __construct($message) {
		parent::__construct($message);
	}	
}
?>