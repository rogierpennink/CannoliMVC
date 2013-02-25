<?php
namespace Cannoli\Framework\Core\Plugin\Contracts;

use Cannoli\Framework\Core\Net\HttpWebRequest;

interface IAuthenticationService
{
	/**
	 *
	 * @access public
	 * @param 
	 * @return IUserAccount
	 */
	function authenticate(HttpWebRequest &$request);
}
?>