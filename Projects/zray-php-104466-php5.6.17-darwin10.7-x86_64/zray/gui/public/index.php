<?php

// patch for IIS bug with double slash after rewrite rule
if (isset($_SERVER['UNENCODED_URL']) && strpos($_SERVER['UNENCODED_URL'], '//') === 0) {
    $_SERVER['UNENCODED_URL'] = substr($_SERVER['UNENCODED_URL'], 1); 
}

/**
* This makes our life easier when dealing with paths. Everything is relative
* to the application root now.
*/
chdir(dirname(__DIR__));
define('ZEND_SERVER_GUI_PATH', getcwd());
// Setup autoloading
include 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(include 'init_application_config.php')->run();