<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Application;

class CliRequest
{
	private static $current;

	public static function getCurrent() {
		if ( isset($_SERVER['argv']) ) {
			self::$current = new CliRequest($_SERVER['argv']);
		}

		return self::$current;
	}

	private $argv;

	private $options = array();

	private $longOptions = array();

	private $args = array();

	private $scriptName;

	public function __construct($argv) {
		$this->argv = $argv;
		$this->parse();
	}

	/**
	 * Returns whether or not the given option is set. By convention, an option
	 * is always one character, prefixed by a dash.
	 *
	 * @access public
	 * @return boolean
	 */
	public function isOptionSet($option) {
		return isset($this->options[$option]);
	}

	/**
	 * Returns whether or not the given long option is set. By convention,
	 * a long option is prefixed by two dashes (--) and it can have a name
	 * that is longer than a single character.
	 *
	 * @access public
	 * @return boolean
	 */
	public function isLongOptionSet($longOption) {
		return isset($this->longOptions[$longOption]);
	}

	public function getOptionValue($option) {
		return $this->isOptionSet($option) ? $this->options[$option] : false;
	}

	public function getLongOptionValue($longOption) {
		return $this->isLongOptionSet($longOption) ? $this->longOptions[$longOption] : false;
	}

	public function hasArgument($arg) {
		return isset($this->args[$arg]);
	}

	public function getArgument($arg) {
		return $this->hasArgument() ? $this->args[$arg] : false;
	}

	public function getArguments() {
		return $this->args;
	}

	private function parse() {
		$this->scriptName = array_shift($this->argv);

		$optionContext = false;

		foreach ( $this->argv as $arg ) {
			if ( substr($arg, 0, 2) == '--' ) {
				$eqPos = strpos($arg, '=');
				if ( $eqPos === false ) {
					$key = substr($arg, 2);
					$this->longOptions[$key] = isset($this->longOptions[$key]) ? $this->longOptions[$key] : true;
				}
				else {
					$key = substr($arg, 2, $eqPos - 2);
					$this->longOptions[$key] = substr($arg, $eqPos + 1);
				}
			}
			else if ( substr($arg, 0, 1) == '-' ) {
				if ( substr($arg, 2, 1) == '=' ) {
					$key = substr($arg, 1, 1);
					$this->options[$key] = substr($arg, 3);
				}
				else {
					$chars = str_split(substr($arg, 1));
					foreach ( $chars as $char ) {
						$key = $char;
						$this->options[$key] = isset($this->options[$key]) ? $this->options[$key] : true;
					}

					if ( count($chars) <= 1 ) $optionContext = $chars[1];
				}
			}
			else {
				if ( $this->isOptionSet($optionContext) ) {
					$this->options[$optionContext] = $arg;
					$optionContext = false;
				}

				if ( ($pos = strpos($arg, '=')) !== false ) {
					$key = substr($arg, 0, $pos);
					$value = substr($arg, $pos + 1);
					$this->args[$key] = $value;
				}
				else {
					$this->args[$arg] = true;
				}
			}
		}
	}
}
?>