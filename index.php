<?php
use Cannoli\Framework\Application;

/**
 * If the framework is ran from the command line interface (CLI), make sure
 * we're in the correct working directory.
 */
if ( defined('STDIN') ) {
	chdir(dirname(__FILE__));
}

/**
 * Define path variables. 
 */
define('PATH_SYSTEM', 'framework');

// TODO: we need to work out some real paths etc. but leave that for later
// if (realpath(PATH_SYSTEM) !== FALSE)
// {
// 	$system_path = realpath($system_path).'/';
// }

define('PATH_CONFIG', PATH_SYSTEM."/config");

define('FILE_CONFIG', PATH_CONFIG."/framework.conf");

define('PATH_APPLICATION', 'application');

require_once "framework/application.class.php";

$app = Application::getInstance();

$app->addAutoloadDirectories(array(
	"application/controller",
	"application/model",
));

$app->run();
?>