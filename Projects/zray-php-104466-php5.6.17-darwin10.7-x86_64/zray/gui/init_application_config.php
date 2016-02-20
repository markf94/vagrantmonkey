<?php
use Zend\Config\Config;

if (function_exists('zend_monitor_event_reporting')) {
    zend_monitor_event_reporting(0);
}

$appConfig = include_once 'config/application.config.php';

$currentConfig = new Config($appConfig);

if (file_exists('3rdparty/modules.config.php')) {
	$currentConfig->merge(new Config(include_once '3rdparty/modules.config.php'));
}

$zrayExtensions = array();
$zrayExtensionsPaths = array();
$zrayExtensionsDirs = array();
if (isAzureEnv() && ! isAzureDebugMode()) {
    $pluginsDir = dirname(getenv('ZEND_BIN_PATH')) . DIRECTORY_SEPARATOR . 'plugins';
    if (file_exists($pluginsDir) && is_dir($pluginsDir)) {
        $zrayExtensionsDirs[] = new DirectoryIterator($pluginsDir);
    }
    
    if (file_exists('D:\home\data\zray\data\plugins') && is_dir('D:\home\data\zray\data\plugins')) {
        $zrayExtensionsDirs[] = new DirectoryIterator('D:\home\data\zray\data\plugins');
    }
} elseif (isZrayStandaloneEnv()) {
	$pluginsDir = getCfgVar('zend.data_dir') . DIRECTORY_SEPARATOR . 'plugins';
    if (is_dir($pluginsDir) && is_readable($pluginsDir)) {
        $zrayExtensionsDirs[] = new DirectoryIterator($pluginsDir);
    }
} else {
    if (file_exists(get_cfg_var('zend.data_dir') . DIRECTORY_SEPARATOR . 'plugins') && is_dir(get_cfg_var('zend.data_dir') . DIRECTORY_SEPARATOR . 'plugins')) {
        $zrayExtensionsDirs[] = new DirectoryIterator(get_cfg_var('zend.data_dir') . DIRECTORY_SEPARATOR . 'plugins');
    }
}

foreach ($zrayExtensionsDirs as $zrayExtensionsDir) {
    foreach ($zrayExtensionsDir as $extInfo) {
    	if (!$extInfo->isDot() && $extInfo->isDir()) {
    	    if (file_exists($extInfo->getPathname())) {
        		$zrayExtensionsDirVersions = new DirectoryIterator($extInfo->getPathname());
        		foreach($zrayExtensionsDirVersions as $fileinfo) {
        		    foreach (array('zray', 'ui') as $zfModule) {
            			if ($fileinfo->isDir() && is_dir($fileinfo->getPathname() . DIRECTORY_SEPARATOR . $zfModule)) {
            			    $modulePath = realpath("{$fileinfo->getPathname()}/{$zfModule}/Module.php");
            				if (file_exists($modulePath)) {
            				    // try to locate the namespace for each module 
            				    $handle = fopen($modulePath, 'r');
            				    $namespace = null;
            				    if ($handle) {
            				        while (is_null($namespace) && ($line = fgets($handle)) !== false) {
            				            preg_match("/^namespace (.*);/", $line, $outputArray);
            				            if (count($outputArray) == 2) {
            				                $namespace = $outputArray[1];
            				            }
            				        }
            				        fclose($handle);
            				    }
            				    
            				    // if namespace found - pass it to zf2
            				    if (! is_null($namespace)) {
            					   $zrayExtensions[] = $namespace;
            					   $zrayExtensionsPaths[$namespace] = realpath($fileinfo->getPathname() . '/' . $zfModule);
            				    }
            				}
            			}
        		    }
        		}
    	    }
    	}
    }
}

// merge plugins modules
$currentConfig->merge(new Config(array('modules' => $zrayExtensions)));
$currentConfig->merge(new Config(array('module_listener_options' => array('module_paths' => $zrayExtensionsPaths))));

// @link ZSRV-14138
// Should be removed after we upgrade from 2.2.1
if (isset($_SERVER['HTTPS'])) {
    $_SERVER['HTTPS'] = strtolower($_SERVER['HTTPS']);
}

return $currentConfig->toArray();