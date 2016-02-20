<?php

namespace Audit;
use Audit\AuditTypeInterface;

class Dictionary implements AuditTypeInterface, AuditTypeGroupsInterface {
    
	protected $auditGroupsStrings = array(
		self::AUDIT_GROUP_AUTHENTICATION => 'Authentication',
		self::AUDIT_GROUP_AUTHORIZATION => 'Permissions',
		self::AUDIT_GROUP_BOOTSTRAP => 'Bootstrap',
		self::AUDIT_GROUP_CLEAR_CACHE => 'Clear Cache',
		self::AUDIT_GROUP_CLUSTER_MANAGEMENT => 'Cluster Management',
		self::AUDIT_GROUP_CODETRACING => 'Codetracing',
		self::AUDIT_GROUP_CONFIGURATION => 'Configuration',
		self::AUDIT_GROUP_DEPLOYMENT => 'Deployment',
		self::AUDIT_GROUP_JOBQUEUE_RULES => 'Recurring Jobs',
		self::AUDIT_GROUP_LICENSE => 'License',
		self::AUDIT_GROUP_MONITOR => 'Monitor Rules',
		self::AUDIT_GROUP_PAGE_CACHE_RULES => 'Caching Rules',
		self::AUDIT_GROUP_PHPINFO => 'PHP Info',
		self::AUDIT_GROUP_RESTART => 'Restart',
		self::AUDIT_GROUP_SETTINGS_CHANGES => 'Settings Change',
		self::AUDIT_GROUP_STUDIO => 'IDE Operations',
		self::AUDIT_GROUP_WEBAPI => 'WebAPI Keys',
		self::AUDIT_GROUP_DEPLOYMENT_LIBRARY => 'Library Deployment',
		self::AUDIT_GROUP_DEPLOYMENT_VHOST => 'Deployment Vhosts',
		self::AUDIT_GROUP_DEVELOPER => 'Developer Access',
		self::AUDIT_GROUP_UrlInsight => 'UrlInsight',
		self::AUDIT_GROUP_ZRAY => 'Zray',
		);
		
		protected $auditTypeStrings = array(
		self::AUDIT_APPLICATION_DEPLOY => 'Application deployed',
		self::AUDIT_APPLICATION_REMOVE => 'Application removed',
		self::AUDIT_APPLICATION_UPGRADE => 'Application upgraded',
		self::AUDIT_APPLICATION_ROLLBACK => 'Application rolled back',
		self::AUDIT_APPLICATION_REDEPLOY => 'Application redeployed',
		self::AUDIT_APPLICATION_REDEPLOY_ALL => 'Applications redeployed',
		self::AUDIT_APPLICATION_DEFINE => 'Application defined',
		
		self::AUDIT_PLUGIN_DEPLOY => 'Plugin deployed',
		self::AUDIT_PLUGIN_REMOVE => 'Plugin removed',
		self::AUDIT_PLUGIN_UPGRADE => 'Plugin upgraded',
		self::AUDIT_PLUGIN_ROLLBACK => 'Plugin rolled back',
		self::AUDIT_PLUGIN_REDEPLOY => 'Plugin redeployed',
		self::AUDIT_PLUGIN_REDEPLOY_ALL => 'Plugins redeployed',
		self::AUDIT_PLUGIN_ENABLE => 'Plugins enabled',
		self::AUDIT_PLUGIN_DISABLE => 'Plugins disabled',
		    
		self::AUDIT_DIRECTIVES_MODIFIED => 'Directives modified',
		self::AUDIT_EXTENSION_ENABLED => 'Extension enabled',
		self::AUDIT_EXTENSION_DISABLED => 'Extension disabled',
		self::AUDIT_RESTART_DAEMON => 'Daemon restarted',
		self::AUDIT_RESTART_PHP => 'PHP restarted',
		
		self::AUDIT_GUI_AUTHENTICATION => 'User authenticated',
		self::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS => 'GUI authentication settings changed',
		self::AUDIT_GUI_CHANGE_PASSWORD => 'Password changed',
		self::AUDIT_GUI_AUTHORIZATION => 'GUI authorization',
		self::AUDIT_GUI_AUTHENTICATION_LOGOUT => 'GUI authentication log out', // not used
		
		self::AUDIT_GUI_AUDIT_SETTINGS_SAVE => 'Audit settings saved',
		self::AUDIT_GUI_BOOTSTRAP_CREATEDB => 'Bootstrap database created',
		self::AUDIT_GUI_BOOTSTRAP_SAVELICENSE => 'Bootstrap license saved',
		self::AUDIT_GUI_CHANGE_SERVER_PROFILE => 'Server profile changed',
		
		self::AUDIT_SERVER_JOIN => 'Cluster joined',
		self::AUDIT_SERVER_ADD => 'Server added',
		self::AUDIT_SERVER_DISABLE => 'Cluster server disabled',
		self::AUDIT_SERVER_ENABLE => 'Cluster server enabled',
		self::AUDIT_SERVER_REMOVE => 'Cluster disconnected',
		self::AUDIT_SERVER_REMOVE_FORCE => 'Server force removed',
		self::AUDIT_SERVER_RENAME => 'Server renamed',
		self::AUDIT_SERVER_SETPASSWORD => 'Server password set', // not used
		
		self::AUDIT_CODETRACING_CREATE => 'Codetracing created',
		self::AUDIT_CODETRACING_DELETE => 'Codetracing deleted',
		self::AUDIT_CODETRACING_DEVELOPER_ENABLE => 'Codetracing Developer enabled',
		self::AUDIT_CODETRACING_DEVELOPER_DISABLE => 'Codetracing Developer disabled',
		
		self::AUDIT_MONITOR_RULES_ENABLE => 'Monitor rule enabled',
		self::AUDIT_MONITOR_RULES_DISABLE => 'Monitor rule disabled',
		self::AUDIT_MONITOR_RULES_SAVE => 'Monitor rules saved',
		self::AUDIT_MONITOR_RULES_ADD => 'Monitor rule added',
		self::AUDIT_MONITOR_RULES_REMOVE => 'Monitor rules removed',
		
		self::AUDIT_STUDIO_DEBUG => 'IDE event debugged',
		self::AUDIT_STUDIO_PROFILE => 'IDE event profiled',
		self::AUDIT_STUDIO_SOURCE => 'IDE viewed source',
		self::AUDIT_STUDIO_DEBUG_MODE_START => 'IDE started Debug Mode',
		self::AUDIT_STUDIO_DEBUG_MODE_STOP => 'IDE stopped Debug Mode',
		
		self::AUDIT_CLEAR_OPTIMIZER_PLUS_CACHE => 'Optimizer Plus cache cleared',
		self::AUDIT_CLEAR_DATA_CACHE_CACHE => 'Data Cache cleared',
		self::AUDIT_CLEAR_PAGE_CACHE_CACHE => 'Page Cache cleared',
		self::AUDIT_CLEAR_STATISTICS => 'Statistics data cleared',
		self::AUDIT_CLEAR_URL_TRACKING => 'Url tracking data cleared',
		
		self::AUDIT_PAGE_CACHE_SAVE_RULE => 'Caching rule saved',
		self::AUDIT_PAGE_CACHE_DELETE_RULES => 'Caching rule deleted',
		
		self::AUDIT_JOB_QUEUE_SAVE_RULE => 'Job Queue rule saved',
		self::AUDIT_JOB_QUEUE_DELETE_RULES => 'Job Queue rule deleted',
		self::AUDIT_JOB_QUEUE_DELETE_JOBS => 'Job Queue job deleted',
		self::AUDIT_JOB_QUEUE_REQUEUE_JOBS => 'Job Queue job requeued',
		self::AUDIT_JOB_QUEUE_RESUME_RULES => 'Job Queue rule resumed',
		self::AUDIT_JOB_QUEUE_DISABLE_RULES => 'Job Queue rule disabled',
		self::AUDIT_JOB_QUEUE_RUN_NOW_RULE => 'Job Queue rule run',
		self::AUDIT_JOB_QUEUE_ADD_JOB => 'Job Queue add job',
		
	    self::AUDIT_JOB_QUEUE_ADD_QUEUE => 'JobQueue queue added',
	    self::AUDIT_JOB_QUEUE_DELETE_QUEUE => 'JobQueue queue deleted',
	    self::AUDIT_JOB_QUEUE_UPDATE_QUEUE => 'JobQueue queue updated',
	    self::AUDIT_JOB_QUEUE_SUSPEND_QUEUE => 'JobQueue queue suspended',
	    self::AUDIT_JOB_QUEUE_ACTIVATE_QUEUE => 'JobQueue queue activated',
		
	    self::AUDIT_JOB_QUEUE_QUEUES_EXPORT => 'JobQueue queues exported',
	    self::AUDIT_JOB_QUEUE_QUEUES_IMPORT => 'JobQueue queues imported',
		    
		    
		self::AUDIT_GET_PHPINFO => 'PHPinfo retrieved', // not used
		self::AUDIT_WEBAPI_KEY_ADD => 'WebAPI key added',
		self::AUDIT_WEBAPI_KEY_REMOVE => 'WebAPI key removed',
		
		self::AUDIT_GUI_SAVELICENSE => 'New license stored',
		
		self::AUDIT_CONFIGURATION_EXPORT => 'Configuration exported',
		self::AUDIT_CONFIGURATION_IMPORT => 'Configuration imported',
		self::AUDIT_CONFIGURATION_RESET => 'Configuration reset',
		self::AUDIT_RELOAD_CONFIGURATION => 'Configuration reloaded',
				
		self::AUDIT_LIBRARY_DEPLOY => 'Library deployed',
		self::AUDIT_LIBRARY_REMOVE => 'Library removed',
		self::AUDIT_LIBRARY_VERSION_REMOVE => 'Library version removed',
		self::AUDIT_LIBRARY_REDEPLOY => 'Library redeployed',
		self::AUDIT_LIBRARY_SET_DEFAULT => 'Library set default',
				
		self::AUDIT_VHOST_ADD => 'Vhost added',
		self::AUDIT_VHOST_EDIT => 'Vhost edited',
		self::AUDIT_VHOST_REMOVE => 'Vhost removed',
		self::AUDIT_VHOST_RESCAN => 'Vhosts rescanned',
		self::AUDIT_VHOST_REDEPLOY => 'Vhost redeployed',
		self::AUDIT_VHOST_ENABLE_DEPLOYMENT => 'Vhost deployment enabled',
		self::AUDIT_VHOST_DISABLE_DEPLOYMENT => 'Vhost deployment disabled',
		
		self::AUDIT_DEVELOPER_TOKEN_ADD => 'Developer Token Added',
		self::AUDIT_DEVELOPER_TOKEN_REMOVE => 'Developer Token Removed',
		self::AUDIT_DEVELOPER_TOKEN_EXPIRE => 'Developer Token Expired Manually',
		
		self::AUDIT_DEVBAR_ACCESS_ELEVATE => 'Z-Ray Anonymous Access',
				
		self::AUDIT_UrlInsight_RULE_ADD => 'UrlInsight rule added',
		self::AUDIT_UrlInsight_RULE_REMOVE => 'UrlInsight rule removed',
		
		self::AUDIT_DEBUGGER_EDITED => 'Debugger settings edited',
		    
		self::AUDIT_ZRAY_DELETE => 'Zray removed',
    );
    
    public function getAuditTypeStrings() {
        return $this->auditTypeStrings;
    }
    
    public function getAuditTypeGroups() {
    	return $this->auditGroupsStrings;
    }
}
