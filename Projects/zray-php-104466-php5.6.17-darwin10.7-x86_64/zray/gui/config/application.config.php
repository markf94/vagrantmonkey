<?php

function isAzureEnv() {
	if (isAzureDebugMode()) {
		return true;
	}
	return (getenv('WEBSITE_SKU') !== false);
}

function isAzureDebugMode() {
	return false;
}

function getAzureLicense() {
	if (! isAzureEnv()) {
		return null;
	}
	
	if (function_exists('zray_get_azure_license')) {
		$license = \zray_get_azure_license();
	} else { return null;
	}
	
	// prevent undefined constants warnings
	if (isAzureDebugMode()) {
		return 'basic';
	}
	
	$licenseMap = array(
		ZRAY_AZURE_LICENSE_DISABLED   => 'disabled',
		ZRAY_AZURE_LICENSE_FREE       => 'free',
		ZRAY_AZURE_LICENSE_BASIC      => 'basic',
		ZRAY_AZURE_LICENSE_STANDARD   => 'standard',
	);
	
	if (isset($licenseMap[$license])) {
		return $licenseMap[$license];
	} else {
		return 'basic';
	}
}

function getAzureWebsiteLicense() {
	if (! isAzureEnv()) {
		return null;
	}
	
	if (function_exists('zray_get_azure_website_license')) {
		$license = \zray_get_azure_website_license();
	} else {
		return null;
	}
	
	// prevent undefined constants warnings
	if (isAzureDebugMode()) {
		return 'basic';
	}
	
	$licenseMap = array(
		ZRAY_AZURE_WEBSITE_LICENSE_DISABLED     => 'disabled',
		ZRAY_AZURE_WEBSITE_LICENSE_FREE         => 'free',
		ZRAY_AZURE_WEBSITE_LICENSE_SHARED       => 'shared',
		ZRAY_AZURE_WEBSITE_LICENSE_BASIC        => 'basic',
		ZRAY_AZURE_WEBSITE_LICENSE_STANDARD     => 'standard',
		ZRAY_AZURE_WEBSITE_LICENSE_PREMIUM      => 'premium',
	);
	
	if (isset($licenseMap[$license])) {
		return $licenseMap[$license];
	} else {
		return 'basic';
	}
}

/**
 * Zray Standalone
 */

function isZrayStandaloneDebugMode() {
	return false;
}

function isZrayStandaloneEnv() {
	if (isZrayStandaloneDebugMode()) {
		return true;
	}

	return defined('ZRAY_STANDALONE');
}

/**
 * @brief manually trigger tasks executer
 * 		/opt/zray/bin/zdd -e /opt/zray/runtime/etc/zdd.ini --cli
 * @return
 */
function zrayStandaloneExecuteTasks($async = false) {
	if (!isZrayStandaloneEnv()) {
		return;
	}
	
	// on linux the ZDD is at /opt/zray/bin/zdd
	$command = dirname(getCfgVar('zend.install_dir')).DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'zdd';
	$zddINIPath = getCfgVar('zend.ini_scandir').DIRECTORY_SEPARATOR.'zdd.ini';
	$suffix = $async ? ' >/dev/null 2>&1 &' : '';
	$fullCommand = $command.' -e '.escapeshellarg($zddINIPath).' --cli'.$suffix;
	
	\ZendServer\Log\Log::debug(_t("Manually executing ZDD %s - %s", array( 
		$async?'asynchronously':'synchronously', 
		$fullCommand 
	)));
	
	shell_exec($fullCommand);
}

/**
 * @TODO implement
 */
function getZrayStandaloneLicense() {
	return 'basic';

	/*
	if (! isAzureEnv()) {
		return null;
	}
	
	if (function_exists('zray_get_azure_license')) {
		$license = \zray_get_azure_license();
	} else {
		return null;
	}
	
	// prevent undefined constants warnings
	if (isAzureDebugMode()) {
		return 'basic';
	}
	
	$licenseMap = array(
		ZRAY_AZURE_LICENSE_DISABLED   => 'disabled',
		ZRAY_AZURE_LICENSE_FREE       => 'free',
		ZRAY_AZURE_LICENSE_BASIC      => 'basic',
		ZRAY_AZURE_LICENSE_STANDARD   => 'standard',
	);
	
	if (isset($licenseMap[$license])) {
		return $licenseMap[$license];
	} else {
		return 'basic';
	}
	*/
}

/**
 * @TODO implement
 */
function getZrayStandaloneWebsiteLicense() {
	return 'basic';

	/*
	if (! isAzureEnv()) {
		return null;
	}
	
	if (function_exists('zray_get_azure_website_license')) {
		$license = \zray_get_azure_website_license();
	} else {
		return null;
	}
	
	// prevent undefined constants warnings
	if (isAzureDebugMode()) {
		return 'basic';
	}
	
	$licenseMap = array(
		ZRAY_AZURE_WEBSITE_LICENSE_DISABLED     => 'disabled',
		ZRAY_AZURE_WEBSITE_LICENSE_FREE         => 'free',
		ZRAY_AZURE_WEBSITE_LICENSE_SHARED       => 'shared',
		ZRAY_AZURE_WEBSITE_LICENSE_BASIC        => 'basic',
		ZRAY_AZURE_WEBSITE_LICENSE_STANDARD     => 'standard',
		ZRAY_AZURE_WEBSITE_LICENSE_PREMIUM      => 'premium',
	);
	
	if (isset($licenseMap[$license])) {
		return $licenseMap[$license];
	} else {
		return 'basic';
	}
	*/
}

/**
 * Gets the value of a PHP configuration option and replace some for Azure compatability
 * @param string $name
 * @return string
 */
function getCfgVar($name) {
	if (isAzureEnv()) {
		$root = (isAzureDebugMode()) ? dirname(get_cfg_var('zend.conf_dir')) : 'D:\home\data\zray';
		$replacementMap = array(
			'zend.ini_scandir'  => 'cfg',
			'zend.conf_dir'     => $root . '\etc',
			'zend.install_dir'  => $root,
			'zend.temp_dir'     => getenv('TEMP'),
			'zend.log_dir'      => (isAzureDebugMode()) ? dirname(get_cfg_var('zend.conf_dir')) . DIRECTORY_SEPARATOR . 'logs' : 'D:\home\LogFiles',
			'zend.data_dir'     => $root . '\data',
		);
		
		if (isset($replacementMap[$name])) {
			return $replacementMap[$name];
		}
	}
	
	if (isZrayStandaloneEnv()) {
		$root = get_cfg_var('zray.install_dir');
		$root = $root . DIRECTORY_SEPARATOR . 'runtime';

		if (!is_dir($root) || !is_readable($root)) {
			die('please define zray.install_dir for the module');
			return false;
		}

		$replacementMap = array(
			'zend.ini_scandir'  => $root . DIRECTORY_SEPARATOR . 'etc',
			'zend.conf_dir'     => $root . DIRECTORY_SEPARATOR . 'etc',
			'zend.install_dir'  => $root,
			'zend.temp_dir'     => $root . DIRECTORY_SEPARATOR . 'tmp',
			'zend.log_dir'      => $root . DIRECTORY_SEPARATOR . 'logs',
			'zend.data_dir'     => $root . DIRECTORY_SEPARATOR . 'var',
		);
		
		if (isset($replacementMap[$name])) {
			return $replacementMap[$name];
		}
	}

	return get_cfg_var($name);
}

if (isAzureEnv()) {
	return array(
		'modules' => array(
			'ZfServerUrl',
			'Application',
			'ZendServer',
			'WebAPI',
			'DevBar',
			'ZRay',
			'Audit',
			'Configuration',
			'GuidePage',
		),
		'module_listener_options' => array(
			'config_glob_paths' => array(
				getCfgVar('zend.conf_dir').DIRECTORY_SEPARATOR.'zend_database.ini',
				ZEND_SERVER_GUI_PATH. '/config/autoload/azure/{,*.}{global,local}.config.php',
				(isAzureDebugMode()) ? 
					ZEND_SERVER_GUI_PATH.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'zs_ui.ini' : 
					getCfgVar('zend.conf_dir').DIRECTORY_SEPARATOR.'zs_ui.ini',
			),
			'module_paths' => array(
				'./module',
			),
		),
		'listeners' => array(
			'DevBar\Listener\RegisterProducersListener'
		)
	);
}

if (isZrayStandaloneEnv()) {
	return array(
		'modules' => array(
			'ZfServerUrl',
			'Application',
			'ZendServer',
			'WebAPI',
			'DevBar',
			'ZRay',
			'Audit',
			'ZendDeployment',
			'Deployment',
			'Configuration',
			'StudioIntegration',
			'GuidePage',
			'Plugins',
			'Michelf',
		),
		'module_listener_options' => array(
			'config_glob_paths' => array(
				getCfgVar('zend.ini_scandir').DIRECTORY_SEPARATOR.'zend_database.ini',
				ZEND_SERVER_GUI_PATH. '/config/autoload/zray-standalone/{,*.}{global,local}.config.php',
				getCfgVar('zend.ini_scandir').DIRECTORY_SEPARATOR.'zs_ui.ini',
				getCfgVar('zend.ini_scandir').DIRECTORY_SEPARATOR.'package.ini',
			),
			'module_paths' => array(
				'./module',
				'./vendor',
			),
		),
		'listeners' => array(
			'DevBar\Listener\RegisterProducersListener'
		)
	);
}

return array(
	'modules' => array(
		'ZfServerUrl',
		'Application',
		'ZendServer',
		'WebAPI',
		'DevBar',
		'ZRay',
		'Audit',
		'Cache',
		'ZendDeployment',
		'Deployment',
		'Monitor',
		'Statistics',
		'Configuration',
		'StudioIntegration',
		'JobQueue',    		
		'Codetracing',
		'PageCache',
		'GuidePage',
		'UrlInsight',
		'Plugins',
		'Michelf'
	),
	'module_listener_options' => array(
		'config_glob_paths' => array(
			getCfgVar('zend.conf_dir').DIRECTORY_SEPARATOR.'zend_database.ini',
				
			ZEND_SERVER_GUI_PATH. '/config/autoload/{,*.}{global,local}.config.php',
			ZEND_SERVER_GUI_PATH. '/config/zs_ui.ini',
			ZEND_SERVER_GUI_PATH. '/config/zs_ui_user.ini',
			
			getCfgVar('zend.conf_dir') . DIRECTORY_SEPARATOR . 'packaging.ini',
		),
		'module_paths' => array(
			'./module',
			'./vendor',
			'./3rdparty'
		),
	),
	'listeners' => array(
		'DevBar\Listener\RegisterProducersListener'
	)
);
