<?php
namespace Cannoli\Framework\Contract;

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