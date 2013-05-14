<?php
namespace Cannoli\Framework\Core\Exception\Database;

class DatabaseNotConnectedException extends DatabaseException
{
	public function __construct($message) {
		parent::__construct($message);
	}	
}
?>