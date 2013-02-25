<?php
namespace Cannoli\Framework\Core\Plugin\Contracts;

interface ILoginManager
{
	function login($email, $password);

	function logout(IUserAccount &$userAccount);

	function isLoggedIn(IUserAccount &$userAccount);
}
?>