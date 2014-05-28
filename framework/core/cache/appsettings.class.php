<?php
namespace Cannoli\Framework\Core\Cache;

require_once dirname(__FILE__) ."/../icache.class.php";
require_once dirname(__FILE__) ."/../utility/singleton.class.php";

use Cannoli\Framework\Core\Utility\Singleton,
	Cannoli\Framework\Core\ICache;

class AppSettings extends Singleton implements ICache
{
	private $settings;
	
	protected function __construct() {
		$this->settings = array();
	}
	
	public function has($key) {
		return !empty($this->settings[$key]);
	}
	
	public function get($key) {
		if ( $this->has($key) )
			return $this->settings[$key];
		return null;
	}
	
	public function set($key, $value) {
		$this->settings[$key] = $value;
	}
	
	public function invalidate($key) {
		unset($this->settings[$key]);
	}
	
	public function flush() {
		unset($this->settings);
		$this->settings = array();
	}
}
?>