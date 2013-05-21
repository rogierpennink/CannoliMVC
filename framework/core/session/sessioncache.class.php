<?php
namespace Cannoli\Framework\Core\Session;

require_once dirname(__FILE__) ."/../icache.class.php";

use Cannoli\Framework\Core;

class SessionCache implements Core\ICache
{
	protected $session_id;
	protected $session_name;
	
	public function __construct() {
		if ( session_id() == "" ) {
			session_start();
			$this->session_id = session_id();
			$this->session_name = session_name();
		}
	}
	
	public function has($key) {
		return isset($_SESSION[$key]);
	}
	
	public function get($key) {
		if ( !isset($_SESSION[$key]) ) return false;
		return unserialize($_SESSION[$key]);
	}
	
	public function set($key, $value) {
		$_SESSION[$key] = serialize($value);
	}
	
	public function invalidate($key) {
		if ( !isset($_SESSION[$key]) ) return false;
		unset($_SESSION[$key]);
		return true;
	}
	
	public function flush() {
		if ( ini_get("session.use_cookies") ) {
			// Also remove session cookie
			$params = session_get_cookie_params();
			setcookie($this->session_name, $this->session_id, 1, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}

		// Kill all session data
		$_SESSION = array();
		session_destroy();

		// Make session data available for use again
		session_start();
	}
		
	public function getSessionId() {
		return $this->session_id;
	}
	
	public function getSessionName() {
		return $this->session_name;
	}

	public function setSessionId($id) {
		if ( $id != $this->session_id ) {
			session_id($id);
			$this->session_id = $id;
		}
	}
}
?>