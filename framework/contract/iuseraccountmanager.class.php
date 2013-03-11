<?php
namespace Cannoli\Framework\Contract;

interface IUserAccountManager
{
	function registerUserAccount(IUserAccount &$account);
}
?>