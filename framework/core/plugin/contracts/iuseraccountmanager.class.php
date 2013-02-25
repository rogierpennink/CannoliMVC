<?php
namespace Cannoli\Framework\Core\Plugin\Contracts;

interface IUserAccountManager
{
	function registerUserAccount(IUserAccount &$account);
}
?>