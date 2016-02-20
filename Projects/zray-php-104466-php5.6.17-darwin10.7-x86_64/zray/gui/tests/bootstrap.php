<?php

use Zend\Loader\StandardAutoloader;

error_reporting ( E_ALL | E_STRICT);
ini_set ( 'display_errors', true );
ini_set ( 'date.timezone', 'Asia/Jerusalem');

define('ZEND_SERVER_GUI_PATH', dirname ( __DIR__ ));
define('ZEND_SERVER_INSTALL_PATH', dirname ( ZEND_SERVER_GUI_PATH ));
define('ZEND_SERVER_REAL_ROOT', getCfgVar('zend.install_dir'));

set_include_path(get_include_path() . PATH_SEPARATOR . dirname ( __DIR__ ) .
PATH_SEPARATOR . ZEND_SERVER_INSTALL_PATH . '/share/pear/PHPUnit');

require_once 'init_autoloader.php';

\Zend\Loader\AutoloaderFactory::factory ( array (
		'Zend\Loader\ClassMapAutoloader' => array (
				ZEND_SERVER_GUI_PATH . '/vendor/ZendDeployment/autoload_classmap.php',
				array('ZendDeployment\Module' => ZEND_SERVER_GUI_PATH . '/vendor/ZendDeployment/Module.php'),
				array('Application\Module' => ZEND_SERVER_GUI_PATH . '/module/Application/Module.php'),
				array('ZendServer\Module' => ZEND_SERVER_GUI_PATH . '/module/ZendServer/Module.php'),
				array('WebAPI\Module' => ZEND_SERVER_GUI_PATH . '/module/WebAPI/Module.php'),
				array('Audit\Module' => ZEND_SERVER_GUI_PATH . '/module/Audit/Module.php'),
				array('Cache\Module' => ZEND_SERVER_GUI_PATH . '/module/Cache/Module.php'),
				array('Codetracing\Module' => ZEND_SERVER_GUI_PATH . '/module/Codetracing/Module.php'),
				array('Deployment\Module' => ZEND_SERVER_GUI_PATH . '/module/Deployment/Module.php'),
				array('Configuration\Module' => ZEND_SERVER_GUI_PATH . '/module/Configuration/Module.php'),
				array('JobQueue\Module' => ZEND_SERVER_GUI_PATH . '/module/JobQueue/Module.php'),
				array('Monitor\Module' => ZEND_SERVER_GUI_PATH . '/module/Monitor/Module.php'),
				array('PageCache\Module' => ZEND_SERVER_GUI_PATH . '/module/PageCache/Module.php'),
				array('Statistics\Module' => ZEND_SERVER_GUI_PATH . '/module/Statistics/Module.php'),
				array('StudioIntegration\Module' => ZEND_SERVER_GUI_PATH . '/module/StudioIntegration/Module.php'),
				array('GuidePage\Module' => ZEND_SERVER_GUI_PATH . '/module/GuidePage/Module.php'),
				array('DevBar\Module' => ZEND_SERVER_GUI_PATH . '/module/DevBar/Module.php'),
				array('Configuration\MapperAbstractTest' => ZEND_SERVER_GUI_PATH . '/module/Configuration/tests/MapperAbstractTest.php'),
		),
		'Zend\Loader\StandardAutoloader' => array (
				StandardAutoloader::LOAD_NS => array(
					'Application' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Application',
					'GuiConfiguration' => ZEND_SERVER_GUI_PATH . '/module/Application/src/GuiConfiguration',
					'Acl' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Acl',
					'Audit' => ZEND_SERVER_GUI_PATH . '/module/Audit/src/Audit',
					'Bootstrap' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Bootstrap',
					'Email' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Email',
					'Logs' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Logs',
					'Messages' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Messages',
					'Notifications' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Notifications',
					'Servers' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Servers',
					'Snapshots' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Snapshots',
					'Tasks' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Tasks',
					'Users' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Users',
					'WebAPI' => ZEND_SERVER_GUI_PATH . '/module/WebAPI/src/WebAPI',
					'Zsd' => ZEND_SERVER_GUI_PATH . '/module/Application/src/Zsd',
					'Cache' => ZEND_SERVER_GUI_PATH . '/module/Cache/src/Cache',
					'Codetracing' => ZEND_SERVER_GUI_PATH . '/module/Codetracing/src/Codetracing',
					'Deployment' => ZEND_SERVER_GUI_PATH . '/module/Deployment/src/Deployment',
					'DeploymentLibrary' => ZEND_SERVER_GUI_PATH . '/module/Deployment/src/DeploymentLibrary',
					'Vhost' => ZEND_SERVER_GUI_PATH . '/module/Deployment/src/Vhost',
					'Configuration' => ZEND_SERVER_GUI_PATH . '/module/Configuration/src/Configuration',
					'JobQueue' => ZEND_SERVER_GUI_PATH . '/module/JobQueue/src/JobQueue',
					'Monitor' => ZEND_SERVER_GUI_PATH . '/module/Monitor/src/Monitor',
					'Issue' => ZEND_SERVER_GUI_PATH . '/module/Monitor/src/Issue',
					'MonitorUi' => ZEND_SERVER_GUI_PATH . '/module/Monitor/src/MonitorUi',
					'MonitorRules' => ZEND_SERVER_GUI_PATH . '/module/Monitor/src/MonitorRules',
					'EventsGroup' => ZEND_SERVER_GUI_PATH . '/module/Monitor/src/EventsGroup',
					'PageCache' => ZEND_SERVER_GUI_PATH . '/module/PageCache/src/PageCache',
					'Statistics' => ZEND_SERVER_GUI_PATH . '/module/Statistics/src/Statistics',
					'StudioIntegration' => ZEND_SERVER_GUI_PATH . '/module/StudioIntegration/src/StudioIntegration',
					'ZendServer' => ZEND_SERVER_GUI_PATH . '/module/ZendServer/src/ZendServer',
					'GuidePage' => ZEND_SERVER_GUI_PATH . '/module/GuidePage/src/GuidePage',
					'DevBar' => ZEND_SERVER_GUI_PATH . '/module/DevBar/src/DevBar',
					'Michelf' => ZEND_SERVER_GUI_PATH . '/vendor/Michelf/src/Michelf',
				),
		),
) );

if (! extension_loaded('Zend Monitor UI')) {
	require_once 'tests/api/monitorConstants.php';
}
// require_once 'PHPUnit/Framework/TestCase.php';
// 

require_once 'tests/functions.php';
@include_once 'PHPUnit/Extensions/Database/Autoload.php';
