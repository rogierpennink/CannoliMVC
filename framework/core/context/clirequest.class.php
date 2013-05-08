<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Application;

class CliRequest
{
	private $argv;

	public function __construct() {
		// Assume that we're in the context of a CLI request
		if ( isset($_SERVER['argv']) ) {
			$this->argv = $_SERVER['argv'];
			
		}
	}
}
?>