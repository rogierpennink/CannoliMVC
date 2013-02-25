<?php
namespace Cannoli\Framework\Core\Plugin\Contracts;

interface IUserAccount
{
	function getId();

	function getEmail();

	function getPassword();

	function getRoleId();

	function getRegistrationDate();
}
?>