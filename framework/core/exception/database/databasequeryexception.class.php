<?php
namespace Cannoli\Framework\Core\Exception\Database;

class DatabaseQueryException extends DatabaseException
{
	private $sql;

	private $args;

	public function __construct($message, $sql, array $args) {
		parent::__construct($message);
		$this->sql = $sql;
		$this->args = $args;
	}

	public function getSql() {
		return $this->sql;
	}

	public function getSqlArgs() {
		return $this->args;
	}
}
?>