<?php
namespace Servers\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ServerStatus extends AbstractHelper {
	// No error
	// (this error code is found in the table ZSD_NODES, column:STATUS_CODE)
	const STATUS_OK                   = 0;
	
	// Global error code
	// (this error code is found in the table ZSD_NODES, column:STATUS_CODE)
	const STATUS_ERROR                = 1;
	
	// Restart is required
	// (this error code is found in the table ZSD_NODES, column:STATUS_CODE)
	const STATUS_RESTART_REQUIRED     = 3;
	
	// Node is disconnecting from cluster
	// (this error code is found in the table ZSD_NODES, column:STATUS_CODE)
	const STATUS_DISCONNECTING_FROM_CLUSTER = 12;
	
	// Node is reloading it configuration
	// (this error code is found in the table ZSD_NODES, column:STATUS_CODE)
	const STATUS_RELOADING                  = 13;
	
	// Node is in the process of being disabled
	const STATUS_DISABLING_SERVER	 = 14;
	
	// Node is disabled
	const STATUS_DISABLED			 = 15;
	
	// Server is being restarted
	const STATUS_SERVER_RESTARTING 	 = 16;
	
	// Server is being redeploying cluster apps
	const STATUS_SERVER_REDEPLOYING  = 18;

	// 
	const STATUS_SERVER_PENDING_REMOVAL  = 19;

	// 
	const STATUS_SERVER_PENDING_DISABLE  = 20;
	
	// Non-DB status values from here
	
	// Server in Warning
	const STATUS_WARNING = 100;
	
	// Server not responding
	const STATUS_NOT_RESPONDING = 200;

	// No Status
	const STATUS_NOT_EXIST = 300;
	
	static public function getServerStatusAsString($statusCode) {
		if (is_numeric($statusCode)) $statusCode = intval($statusCode);
		
		if ($statusCode === self::STATUS_WARNING) {
			return 'Warning';
		} elseif ($statusCode === self::STATUS_NOT_RESPONDING) {
			return 'notResponding';
		} elseif ($statusCode === self::STATUS_NOT_EXIST) {
			return 'notExists';
		} elseif ($statusCode === self::STATUS_OK) {
			return 'OK';
		} elseif ($statusCode === self::STATUS_RESTART_REQUIRED) {
			return 'pendingRestart';
		} elseif ($statusCode === self::STATUS_DISCONNECTING_FROM_CLUSTER) {
			return 'disconnecting';
		} elseif ($statusCode === self::STATUS_RELOADING) {
			return 'reloadingConfigurations';
		} elseif ($statusCode === self::STATUS_DISABLING_SERVER) {
			return 'disabling';
		} elseif ($statusCode === self::STATUS_DISABLED) {
			return 'disabled';
		} elseif ($statusCode === self::STATUS_SERVER_RESTARTING) {
			return 'restarting';
		} elseif ($statusCode === self::STATUS_SERVER_REDEPLOYING) {
			return 'redeploying';
		} elseif ($statusCode === self::STATUS_SERVER_PENDING_REMOVAL) {
			return 'pendingRemoval';
		} elseif ($statusCode === self::STATUS_SERVER_PENDING_DISABLE) {
			return 'pendingDisable';
		} elseif ($statusCode === self::STATUS_ERROR) {
			return 'Error';
		}
		
		return 'unknown';		
	}
	
	/**
	 * @param integer $statusCode
	 * @return string
	 */
	public function __invoke($statusCode) {
		return self::getServerStatusAsString($statusCode);
	}
}