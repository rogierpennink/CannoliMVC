<?php
namespace Cannoli\Framework\Core\Plugin;

/**
 * The PluginType abstract class serves as an enumeration with
 * extended functionality. By letting the members be PluginType-
 * derived instances, functions that require PluginTypes as 
 * arguments, can request details and settings that are specific
 * to each unique plugin type.
 */
abstract class PluginType
{
	private static $areProvidersInitialized = false;

	public static $DATABASEPROVIDER;

	public static $ACCOUNTSPROVIDER;

	public static $RIGHTSPROVIDER;

	public static $CUSTOMPROVIDER;

	/**
	 * Attempts to map the given type string onto one of the predefined
	 * type objects.
	 * @return mixed		(boolean)false if no mapping exists, the mapped type otherwise
	 */
	public static function get($typeStr) {
		self::initProviders();

		switch ( $typeStr ) {
			case self::$DATABASEPROVIDER:
				return self::$DATABASEPROVIDER;
			case self::$ACCOUNTSPROVIDER:
				return self::$ACCOUNTSPROVIDER;
			case self::$RIGHTSPROVIDER:
				return self::$RIGHTSPROVIDER;
			case self::$CUSTOMPROVIDER:
				return self::$CUSTOMPROVIDER;
		}

		return false;
	}

	// Can return null, in which case it is assumed that no specific base
	// class is required.
	abstract public function getRequiredBaseTypeName();

	private static function initProviders() {
		if ( self::$areProvidersInitialized ) return;

		self::$DATABASEPROVIDER = new DatabaseProviderPluginType();
		self::$ACCOUNTSPROVIDER = new AccountsProviderPluginType();
		self::$RIGHTSPROVIDER 	= new RightsProviderPluginType();
		self::$CUSTOMPROVIDER 	= new CustomProviderPluginType();

		self::$areProvidersInitialized = true;
	}
}

class DatabaseProviderPluginType extends PluginType
{
	public function __toString() {
		return "db";
	}

	public function getRequiredBaseTypeName() {
		return "DatabaseConnection";
	}
}

class AccountsProviderPluginType extends PluginType
{
	public function __toString() {
		return "accounts";
	}

	public function getRequiredBaseTypeName() {
		return "AccountManagementProvider";
	}
}

class RightsProviderPluginType extends PluginType
{
	public function __toString() {
		return "rights";
	}

	public function getRequiredBaseTypeName() {
		return "RightsManagementProvider";
	}
}

class CustomProviderPluginType extends PluginType
{
	public function __toString() {
		return "custom";
	}

	public function getRequiredBaseTypeName() {
		return null;
	}
}
?>