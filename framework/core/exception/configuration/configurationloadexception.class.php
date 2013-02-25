<?php
namespace Cannoli\Framework\Core\Exception\Configuration;

class ConfigurationLoadException extends \Exception
{
	private $filename;

	public function __construct($message, $filename) {
		parent::__construct($message);

		$this->filename = $filename;
	}

	public function getFilename() {
		return $this->filename;
	}
}
?>