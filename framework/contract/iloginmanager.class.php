<?php
namespace Cannoli\Framework\Contract;

interface ILoginManager
{
	function login($email, $password);

	function logout(IUserAccount &$userAccount);

	function isLoggedIn(IUserAccount &$userAccount);
}
?>