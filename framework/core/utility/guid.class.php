<?php
namespace Cannoli\Framework\Core\Utility;

class Guid
{
	private $guid;

	/**
	 * Simply instantiates a new Guid and returns it.
	 *
	 * @access public
	 * @return Guid 		A new Guid instance
	 */
	public static function create() {
		return new Guid();
	}

	/**
	 * Constructor; this is where the Guid is actually generated or if the $guid
	 * parameter is specified, this is where it will be validated and set.
	 *
	 * @access public
	 * @param $guid 		Optional guid string
	 * @return void
	 */
	public function __construct($guid = "") {
		$guid = strtoupper($guid);
		if ( preg_match("/^[A-Z0-9]{8}-(?:[A-Z0-9]{4}-){3}[A-Z0-9]{12}$/", $guid) ) {
			$this->guid = $guid;
			return;
		}

		if ( function_exists('com_create_guid') === true ) {
	        $this->guid = trim(com_create_guid(), '{}');
	    }

	    $this->guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

	public function __toString() {
		return $this->guid;
	}
}
?>