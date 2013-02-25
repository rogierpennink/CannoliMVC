<?php
namespace Cannoli\Framework\Core\Utility;

class Path
{
	const PATH_RELATIVE				= 0x01;
	const PATH_ABSOLUTE				= 0x02;
	const PATH_RELATIVE_TO_ROOT		= 0x03;

	private $parts = array();

	public function __construct($pathStr, $pathType = self::PATH_RELATIVE) {
		// Translate backslashes to forward slashes
		$pathStr = str_replace("\\", "/", $pathStr);
		$this->parse($pathStr, $pathType);
	}

	public function getAbsolute() {

	}

	public function getRelative() {

	}

	public function getRelativeToRoot() {

	}

	private function parse($pathStr, $pathType) {
		// get current working directory
		if ( ($curdir = getcwd()) === false ) {
			$curdir = dirname($_SERVER["SCRIPT_FILENAME"]);
		}

		$parts = explode("/", $pathStr);
	}
}
?>