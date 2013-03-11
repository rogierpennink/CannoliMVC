<?php
namespace Cannoli\Framework\Contract;

interface IUserAccount
{
	function getId();

	function getEmail();

	function getPassword();

	function getRoleId();

	function getRegistrationDate();
}
?>