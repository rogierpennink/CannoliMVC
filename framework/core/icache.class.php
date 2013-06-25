<?php
namespace Cannoli\Framework\Core;

interface ICache
{
	public function has($key);
	
	public function get($key, $default = null);
	
	public function set($key, $value, $ttl = 300);
	
	public function invalidate($key);
	
	public function flush();
}
?>