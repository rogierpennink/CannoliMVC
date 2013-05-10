<?php
use Cannoli\Framework\Application;

/**
 * If the framework is ran from the command line interface (CLI), make sure
 * we're in the correct working directory.
 */
if ( defined('STDIN') ) {
	chdir(dirname(__FILE__));
}

/* Define important CMVC folder paths relative to the index.php file here */
$path_system 		= "framework";
$path_application 	= "application";

/**
 * Define path variables. 
 */
if ( realpath($path_system) !== false ) {
	DEFINE('PATH_SYSTEM', str_replace("\\", "/", realpath($path_system)) .'/');
}

if ( realpath($path_application) !== false ) {
	DEFINE('PATH_APPLICATION', str_replace("\\", "/", realpath($path_application)) .'/');
}

define('PATH_CONFIG', PATH_SYSTEM."config/");

define('FILE_CONFIG', PATH_CONFIG."framework.conf");

require_once "framework/application.class.php";

$app = Application::getInstance();

$app->addAutoloadDirectories(array(
	"application/controller",
	"application/model",
));

$app->run();
?>