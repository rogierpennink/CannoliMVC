<?php
namespace Cannoli\Framework\Core\Exception\Database;

class DatabaseConnectionException extends \Exception
{
	private $host;

	private $user;

	private $pass;

	private $dbName;

	public function __construct($message, $host, $user, $pass, $dbName) {
		parent::__construct($message);

		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->dbName = $dbName;
	}

	public function getHost() {
		return $this->host;
	}

	public function getUser() {
		return $this->user;
	}

	public function getPass() {
		return $this->pass;
	}

	public function getDbName() {
		return $this->dbName;
	}
}
?>