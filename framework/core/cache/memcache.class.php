<?php
namespace Cannoli\Framework\Core\Cache;

use Cannoli\Framework\Application;
use Cannoli\Framework\Core\Exception\ClassNotFoundException;
use Cannoli\Framework\Core\ICache;
use Cannoli\Framework\Core\Utility\ConfigurableClass;

class MemCache extends ConfigurableClass implements ICache
{
	const SERIALIZER_JSON			= "json";
	const SERIALIZER_PHP 			= "php";
	const SERIALIZER_IGBINARY 		= "igbinary";

	private static $configDomain = "Cannoli.Application.Caching.Memcache";

	private $memcache;

	private $settings;

	public function __construct(Application &$app) {
		parent::__construct();

		$app->getConfigurationManager()->registerConfigurable($this);

		if ( !class_exists("Memcache", false) )
			throw new ClassNotFoundException("Memcache");

		// Instantiate the memcache class
		$this->memcache = new \Memcache();

		// Read the servers from configuration
		$servers = $this->config(self::$configDomain, "servers", array());
		foreach ( $servers as $server ) {
			$port = isset($server->port) ? $server->port : 11211;
			$weight = isset($server->weight) ? $server->weight : 1;
			$persistent = isset($server->persistent) ? $server->persistent == true : false;
			$this->memcache->addServer($server->host, $port, $persistent, $weight);
		}

		// Read the serialization mode from the config
		$serialization = $this->config(self::$configDomain, "serialization", self::SERIALIZER_JSON);
		if ( !in_array($serialization, array(self::SERIALIZER_JSON, self::SERIALIZER_PHP, self::SERIALIZER_IGBINARY)) )
			$serialization = self::SERIALIZER_JSON;
		$this->memcache->setOption(\Memcached::OPT_SERIALIZER, $this->getSerializerOptValue($serialization));
	}

	public function getConfigurationDomains() {
		return array(
			self::$configDomain
		);
	}

	public function has($key) {
		if ( isset($this->settings[$key]) )
			return true;

		// Store value in local cache to prevent having to talk to the memcache
		// server again in a possibly subsequent get() call
		if ( ($value = $this->memcache->get($key)) !== false ) {
			$this->settings[$key] = $value;
			return true;
		}

		return false;
	}
	
	public function get($key, $default = null) {
		if ( isset($this->settings[$key]) )
			return $this->settings[$key];

		if ( ($value = $this->memcache->get($key)) !== false )
			return $value;

		return $default;
	}
	
	public function set($key, $value, $ttl = 300) {
		if ( $this->memcache->set($key, $value, false, $ttl) !== false )
			$this->settings[$key] = $value;
	}
	
	public function invalidate($key) {
		if ( $this->memcache->delete($key) !== false && isset($this->settings[$key]) )
			unset($this->settings[$key]);
	}
	
	public function flush() {
		if ( $this->memcache->flush() !== false ) {
			unset($this->settings);
			$this->settings = array();
		}
	}

	private function getSerializerOptValue($opt) {
		switch ( $opt ) {
			case self::SERIALIZER_PHP:
				return \Memcached::SERIALIZER_PHP;
			case self::SERIALIZER_JSON:
				return \Memcached::SERIALIZER_JSON;
			case self::SERIALIZER_IGBINARY:
				return \Memcached::SERIALIZER_IGBINARY;
		}
		return \Memcached::SERIALIZER_JSON;
	}
}
?>