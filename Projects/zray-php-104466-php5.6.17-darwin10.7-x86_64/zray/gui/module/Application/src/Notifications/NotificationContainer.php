<?php

namespace Notifications;

class NotificationContainer {
	// SEVERITY
	const SEVERITY_INFO     = 0;
	const SEVERITY_WARNING	= 1;
	const SEVERITY_ERROR	= 2;
	
	// TYPE
	const TYPE_UNKNOWN 							= -1;
	const TYPE_SERVER_OFFLINE 					= 0;
	const TYPE_RESTART_REQUIRED 				= 1;
	const TYPE_DAEMON_OFFLINE 					= 2;
	const TYPE_PHP_EXT_DIRECTIVE_MISSMATCH 		= 3;
	const TYPE_PHP_EXT_NOT_LOADED 				= 4;
	const TYPE_PHP_EXT_NOT_INSTALLED 			= 5;
	const TYPE_ZEND_EXT_DIRECTIVE_MISSMATCH 	= 6;
	const TYPE_ZEND_EXT_NOT_LOADED 				= 7;
	const TYPE_ZEND_EXT_NOT_INSTALLED 			= 8;
	const TYPE_DEPLOYMENT_FAILURE 				= 9;
	const TYPE_DEPLOYMENT_UPDATE_FAILURE 		= 10;
	const TYPE_DEPLOYMENT_REDEPLOY_FAILURE 		= 11;
	const TYPE_DEPLOYMENT_HEALTHCHECK_FAILURE	= 12;
	const TYPE_DEPLOYMENT_REMOVE_FAILURE 		= 13;
	const TYPE_DEPLOYMENT_ROLLBACK_FAILURE 		= 14;
	const TYPE_DEPLOYMENT_DEFINE_APP_FAILURE 	= 15;
	const TYPE_SERVER_ADD_ERROR 				= 16;
	const TYPE_SERVER_REMOVE_ERROR 				= 17;
	const TYPE_SERVER_ENABLE_ERROR 				= 18;
	const TYPE_SERVER_DISABLE_ERROR 			= 19;
	const TYPE_SERVER_FORCE_REMOVE_ERROR 		= 20;
	const TYPE_JOBQUEUE_HIGH_CONCURRENCY 		= 21;
	const TYPE_SERVER_RESTARTING				= 22;
	const TYPE_SC_SESSION_HANDLER_FILES			= 23;
	const TYPE_SC_NO_BACKUP						= 24;
	const TYPE_LICENSE_INVALID					= 26;
	const TYPE_LICENSE_ABOUT_TO_EXPIRE			= 27;
	const TYPE_WEBSERVER_NOT_RESPONDING			= 28;
	const TYPE_MAX_SERVERS_IN_CLUSTER			= 29;
	const TYPE_LICENSE_ABOUT_TO_EXPIRE_45		= 30;
	const TYPE_LICENSE_ABOUT_TO_EXPIRE_15		= 31;
	const TYPE_DATABASE_CONNECTION_RESTORED		= 32;
	const TYPE_SCD_STDBY_MODE					= 33;
	const TYPE_ZSD_OFFLINE						= 34;
	const TYPE_SCD_ERROR						= 35;
	const TYPE_MAIL_SETTINGS_NOT_SET			= 36;
	const TYPE_SCD_SHUTDOWN_ERROR				= 37;
	const TYPE_NO_SUPPORT						= 38;
	const TYPE_LIBRARY_SET_DEFAULT_ERROR		= 39;
	const TYPE_LIBRARY_DEPLOY_ERROR				= 40;
	const TYPE_LIBRARY_REMOVE_ERROR				= 41;
	const TYPE_VHOST_MISSING					= 42;
	const TYPE_VHOST_MODIFIED					= 43;
	const TYPE_VHOST_CONFIG_FAILED				= 44;
	
	
	//// internal types
	const TYPE_LICENSE_EXPIRES_TODAY			= -10;
	const TYPE_LICENSE_EXPIRES_TOMORROW			= -11;
	
	const TYPE_LIBRARY_UPDATE_AVAILABLE			= 500;
	const TYPE_PLUGIN_UPDATE_AVAILABLE			= 501;
	const TYPE_PLUGIN_BROKEN_DEPENDENCY			= 502;
	const TYPE_RSS_NEWS_AVAILABLE               = 600; // rss news occupied 600-610
	
	private $titles = array(
			self::TYPE_SERVER_OFFLINE => 'Offline server',
			self::TYPE_RESTART_REQUIRED => 'Restart required',
			self::TYPE_DAEMON_OFFLINE => 'Offline daemon',
			self::TYPE_PHP_EXT_DIRECTIVE_MISSMATCH => 'Mismatched extension directive',
			self::TYPE_PHP_EXT_NOT_LOADED => 'PHP extension not loaded',
			self::TYPE_PHP_EXT_NOT_INSTALLED => 'PHP extension not installed',
			self::TYPE_ZEND_EXT_DIRECTIVE_MISSMATCH => 'Mismatched component directive',
			self::TYPE_ZEND_EXT_NOT_LOADED => 'Component not loaded',
			self::TYPE_ZEND_EXT_NOT_INSTALLED => 'Component not installed',
			self::TYPE_DEPLOYMENT_FAILURE => 'Failed to deploy',
            self::TYPE_DEPLOYMENT_UPDATE_FAILURE => 'Failed to update',
			self::TYPE_DEPLOYMENT_REDEPLOY_FAILURE => 'Failed to redeploy',
            self::TYPE_DEPLOYMENT_HEALTHCHECK_FAILURE => 'Failed to health check',
			self::TYPE_DEPLOYMENT_REMOVE_FAILURE => 'Failed to remove',
			self::TYPE_DEPLOYMENT_ROLLBACK_FAILURE => 'Failed to rollback',
			self::TYPE_DEPLOYMENT_DEFINE_APP_FAILURE => 'Failed to define',
			self::TYPE_SERVER_ADD_ERROR => 'Failed to add server',
			self::TYPE_SERVER_REMOVE_ERROR => 'Failed to remove server',
			self::TYPE_SERVER_ENABLE_ERROR => 'Failed to enable server',
			self::TYPE_SERVER_DISABLE_ERROR => 'Failed to disable server',
			self::TYPE_SERVER_FORCE_REMOVE_ERROR => 'Failed to force remove server',
			self::TYPE_JOBQUEUE_HIGH_CONCURRENCY => 'Job Queue reached a high concurrency level',
			self::TYPE_SC_SESSION_HANDLER_FILES => 'Session Clustering is disabled',
			self::TYPE_SC_NO_BACKUP => 'Session Clustering failed to locate backup servers',
			self::TYPE_LICENSE_INVALID => 'Invalid license',
			self::TYPE_LICENSE_ABOUT_TO_EXPIRE => 'License is about to expire',
			self::TYPE_WEBSERVER_NOT_RESPONDING => 'Webserver is not responding',
			self::TYPE_MAX_SERVERS_IN_CLUSTER => 'You have reached the maximum amount of allowed servers for this license',
			self::TYPE_LICENSE_ABOUT_TO_EXPIRE_45 => 'License is about to expire',
			self::TYPE_LICENSE_ABOUT_TO_EXPIRE_15 => 'License is about to expire',
			self::TYPE_LICENSE_EXPIRES_TODAY => 'License is about to expire',
			self::TYPE_LICENSE_EXPIRES_TOMORROW => 'License is about to expire',
			self::TYPE_DATABASE_CONNECTION_RESTORED => 'Database Connection Restored',
			self::TYPE_SCD_STDBY_MODE => 'Sessions may not be tracked',
			self::TYPE_ZSD_OFFLINE => 'Zend server daemon offline',
			self::TYPE_MAIL_SETTINGS_NOT_SET => 'Mail server settings are needed',
			self::TYPE_NO_SUPPORT => 'Your license does not include support',
			self::TYPE_LIBRARY_UPDATE_AVAILABLE => 'Library update available',
			self::TYPE_PLUGIN_UPDATE_AVAILABLE => 'Plugin update available',
			self::TYPE_PLUGIN_BROKEN_DEPENDENCY => 'Broken plugin dependencies',
			self::TYPE_SCD_SHUTDOWN_ERROR => 'Graceful Shutdown failed',
			self::TYPE_LIBRARY_SET_DEFAULT_ERROR => 'Failed to update library default version',
			self::TYPE_LIBRARY_DEPLOY_ERROR => 'Failed to deploy library',
			self::TYPE_LIBRARY_REMOVE_ERROR => 'Failed to remove library',
			self::TYPE_VHOST_MISSING => 'Missing Vhost',
			self::TYPE_VHOST_MODIFIED => 'Modified Vhost',
			self::TYPE_VHOST_CONFIG_FAILED => 'Web server configuration error', 
	        self::TYPE_RSS_NEWS_AVAILABLE => '%s',
	);


	/**
	 * @var array
	 */
	protected $notification;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $notification) {
		$this->notification = $notification;
		$this->setTitle($this->getType());
		$this->setUrl($this->getType());		
	}
	
	public function toArray() {
		return $this->notification;
	}
	
	/**
	 * @return integer
	 */
	public function getId() {
		return isset($this->notification['ID']) ? (integer) $this->notification['ID'] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getSeverity() {
		return (integer) $this->notification['SEVERITY'];
	}
	
	/**
	 * @return integer
	 */
	public function getCreationTime() {
		return (integer) $this->notification['CREATION_TIME'];
	}
	
	/**
	 * @return integer
	 */
	public function getType() {
		return isset($this->notification['TYPE']) ? (integer) $this->notification['TYPE'] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getRepeats() {
		return (integer) $this->notification['REPEATS'];
	}
	
	public function getTitle() {
		return $this->notification['TITLE'];
	}
	
	public function getDescription() {
		return isset($this->notification['DESCRIPTION']) ? $this->notification['DESCRIPTION'] : '';
	}
	
	public function getUrl() {
		return $this->notification['URL'];
	}
	
	public function getName() {
		return $this->notification['NAME'];
	}
	
	public function getNodeId() {
		return $this->notification['NODE_ID'];
	}
	
	public function getExtraData() {
		if (isset($this->notification['EXTRA_DATA']) && $this->notification['EXTRA_DATA']) {
			return json_decode($this->notification['EXTRA_DATA'], true);
		} else {
			return array();
		}
	}
	
	private function setTitle($type) {
	    if ($type >= self::TYPE_RSS_NEWS_AVAILABLE && $type <= self::TYPE_RSS_NEWS_AVAILABLE + 10) {
	        $type = self::TYPE_RSS_NEWS_AVAILABLE;
	    }
	    
		if (isset($this->titles[$type])) {
		    $extraData = $this->getExtraData();
		    if (isset($extraData['title'])) {
		        $this->notification['TITLE'] = sprintf(_t($this->titles[$type], array($extraData['title'])));
		    } else {
		        $this->notification['TITLE'] = _t($this->titles[$type]);
		    }
		} else {		
			$this->notification['TITLE'] = '';
		}
	}
	
	private function setUrl($type) {
		switch ($type) {
			case (self::TYPE_DEPLOYMENT_FAILURE):
			case (self::TYPE_DEPLOYMENT_UPDATE_FAILURE):
			case (self::TYPE_DEPLOYMENT_REDEPLOY_FAILURE):
			case (self::TYPE_DEPLOYMENT_HEALTHCHECK_FAILURE):
			case (self::TYPE_DEPLOYMENT_REMOVE_FAILURE):
			case (self::TYPE_DEPLOYMENT_ROLLBACK_FAILURE):
			case (self::TYPE_DEPLOYMENT_DEFINE_APP_FAILURE):
				$this->notification['URL'] = '/Deployment';
				break;
			case (self::TYPE_PLUGIN_UPDATE_AVAILABLE):
			case (self::TYPE_PLUGIN_BROKEN_DEPENDENCY):
			    $this->notification['URL'] = '/Plugins';
			    break;
			case (self::TYPE_LIBRARY_UPDATE_AVAILABLE):
			case (self::TYPE_LIBRARY_SET_DEFAULT_ERROR):
			case (self::TYPE_LIBRARY_DEPLOY_ERROR):
			case (self::TYPE_LIBRARY_REMOVE_ERROR):
				$this->notification['URL'] = '/DeploymentLibrary';
				break;
			case (self::TYPE_VHOST_MISSING):
			case (self::TYPE_VHOST_MODIFIED):
				$this->notification['URL'] = '/Vhost';
				break;
			case (self::TYPE_SERVER_ADD_ERROR):
			case (self::TYPE_SERVER_REMOVE_ERROR):
			case (self::TYPE_SERVER_ENABLE_ERROR):
			case (self::TYPE_SERVER_DISABLE_ERROR):
			case (self::TYPE_SERVER_FORCE_REMOVE_ERROR):
			case (self::TYPE_SERVER_OFFLINE):
			case (self::TYPE_PHP_EXT_DIRECTIVE_MISSMATCH):
			case (self::TYPE_ZSD_OFFLINE):				
			case (self::TYPE_ZEND_EXT_DIRECTIVE_MISSMATCH):
				$this->notification['URL'] = '/Servers';
				break;
			case (self::TYPE_PHP_EXT_NOT_LOADED):
			case (self::TYPE_PHP_EXT_NOT_INSTALLED):
				$this->notification['URL'] = '/Extensions/phpExtensions';
				break;
			case (self::TYPE_SCD_STDBY_MODE):
				$this->notification['URL'] = '/ZendComponents/#grid=Zend--Session--Clustering';
				break;
			case (self::TYPE_ZEND_EXT_NOT_LOADED):
			case (self::TYPE_ZEND_EXT_NOT_INSTALLED):
			case (self::TYPE_DAEMON_OFFLINE):
			case (self::TYPE_SCD_SHUTDOWN_ERROR):
				$this->notification['URL'] = '/ZendComponents';
				break;
			case (self::TYPE_JOBQUEUE_HIGH_CONCURRENCY):
				$this->notification['URL'] = '/ZendComponents/#search=zend_jobqueue.max_http_jobs&daemon-tab=1';
				break;
			case (self::TYPE_SC_SESSION_HANDLER_FILES):
				$this->notification['URL'] = '/SessionClustering';
				break;				
			case (self::TYPE_MAX_SERVERS_IN_CLUSTER):
			case (self::TYPE_LICENSE_INVALID):
			case (self::TYPE_LICENSE_ABOUT_TO_EXPIRE):
			case (self::TYPE_LICENSE_ABOUT_TO_EXPIRE_45):
			case (self::TYPE_LICENSE_ABOUT_TO_EXPIRE_15):
			case (self::TYPE_NO_SUPPORT):
				$this->notification['URL'] = '/License';
				break;
			case (self::TYPE_MAIL_SETTINGS_NOT_SET):
				$this->notification['URL'] = '/Settings#panel=mail-settings';
				break;
			case (self::TYPE_VHOST_CONFIG_FAILED):
				$this->notification['URL'] = '/Vhost';
				break;
			default:
				$this->notification['URL'] = '';
				break;
		}
	}
}