<?php
namespace Cannoli\Framework\Core\Exception\Database;

class DatabaseQueryExecutionException extends \Exception
{
	public function __construct($message) {
		parent::__construct($message);
	}
}
?>