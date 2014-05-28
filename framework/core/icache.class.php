<?php
namespace Cannoli\Framework\Core;

interface ICache
{
	public function has($key);
	
	public function get($key);
	
	public function set($key, $value);
	
	public function invalidate($key);
	
	public function flush();
}
?>