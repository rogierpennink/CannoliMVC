<?php
namespace Cannoli\Framework\Core;

interface IUserAccount
{
	function getId();

	function getEmail();

	function getPassword();

	function getSalt();

	function getRoleId();

	function getRegistrationDate();
}
?>