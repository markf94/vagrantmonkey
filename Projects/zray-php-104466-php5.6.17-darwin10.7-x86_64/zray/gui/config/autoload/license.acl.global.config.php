<?php
return array(
			'license' => array(
				'acl' => array(
					'ENTERPRISE' => array(
					),
					'DEVELOPER_ENTERPRISE' => array(
						'data:useZRaySecureMode' => false,
						'data:productionActivities' => false,
						'route:DevBarWebApi' => array('zrayRemoveAccessToken' => false, 'zrayCreateAccessToken' => false, 'zrayCreateSelectiveAccess' => false, 'zrayExpireAccessToken' => false),
					),
					'PROFESSIONAL' => array(
						/// reset developer switch
						'data:useZRaySecureMode' => true,
						'data:productionActivities' => true,
						'route:DevBarWebApi' => true,
						'dataRentention:timelimit' 	=> array('unlimited' => false),
						'auditTrail:timelimit' 	=> array('unlimited' => false),
						'service:Authentication'	=> array('extended' => false),
						'route:UsersWebAPI'			=> array('userAuthenticationSettings' => false),
						'data:components' => array('Zend Java Bridge' => false),
						'data:editAuditSettings' => false,
					),
					'SMALL BUSINESS' => array(// Basic
						'route:ServersWebAPI' => array(
							'clusterAddServer' => false,
							'serverAddToCluster' => false
						),
						'service:accessWebAPI'	=> false,
						'dataRentention:timelimit' => array('3month' => false, '2weeks' => true),
						'data:useCustomAction' => false,
						'data:useMonitorAction' => array ('custom' => false),
						'data:components' => array('Zend Session Clustering' => false, 'Zend Job Queue' => false),
						'data:useWebApiKeys' => false,
						'data:showWebApiKeysMarketing' => false,
						'data:editRestartSettings' => false,
						'data:editNotificationsSettings' => false,
						'route:EmailWebApi' => array(false, 'emailSend' => true), // deny all actions but emailSend, special syntax
						'route:JobQueueWebApi' => false,
					),
					'DEVELOPER' => array(
						'service:accessWebAPI'	=> true,
						'data:useMultipleUsers' => false,
						'route:JobQueueWebApi' => true,
						'data:useWebApiKeys' => true,
						'data:components' => array('Zend Job Queue' => true),
						'data:editNotificationsSettings' => true,
						'data:useZRaySecureMode' => false,
						'data:productionActivities' => false,
						'route:DevBarWebApi' => array('zrayRemoveAccessToken' => false, 'zrayCreateAccessToken' => false, 'zrayCreateSelectiveAccess' => false, 'zrayExpireAccessToken' => false),
					),
					'FREE' => array(
						/// reset developer switch
						'data:productionActivities' => true,
						'data:editNotificationsSettings' => false,
						'service:accessWebAPI'	=> false,
						'data:useWebApiKeys' => false,
						'data:useMonitorProRuleTypes' => false,
						'data:useMonitorAction' => array ('email' => false),
					    'dataRentention:timelimit' => array('2weeks' => false, '2hour' => true),						'data:useEmailNotification' => false,
						'data:collectEventsCodeTrace' => false,
						'data:components' => array('Zend Job Queue' => false, 'Zend Page Cache' => false, 'Zend Java Bridge' => false),
						'route:PageCacheWebApi' => false,
						'data:showWebApiKeysMarketing' => true,
						'data:hideMarketingContent' => false,
						'data:editSettings' => false,
						'route:JobQueueWebApi' => false,
						'route:VhostWebAPI' => array(false, 'vhostGetStatus' => true, 'vhostGetDetails' => true),
						'route:EmailWebApi' => array(false, 'emailSend' => true), // deny all actions but emailSend, special syntax
						'route:ConfigurationWebApi'	=> array('configurationImport' => false, 'configurationExport' => false, 'configurationReset' => false),
						'route:DevBarWebApi' => false,
						'route:DeploymentWebAPI' => array('applicationRollback' => false),
					),
					'EMPTY' => array(
						'route:Bootstrap' => true,
						'route:ConfigurationWebAPI' => array('tasksComplete' => true),
						'route:BootstrapWebAPI' => true,
						'route:Login' => true,
						'route:Expired' => true,
						'route:GuidePage' => true,
						'route:DevBar' => true,
						'route:ServersWebAPI' => array(
							'serverAddToCluster' => true,
							'serverIsInitialized' => true,
							'clusterIsInitialized' => true,
							'restartPhp' => true,
						),
						'route:ConfigurationWebApi' => array(
							'serverStoreLicense' => true,
							'serverValidateLicense' => true,
							'licenseUpdated' => true,
						),
						'route:VhostWebAPI' => array(
							'vhostGetStatus' => true,
						),
					    'route:Plugins' => true,
					    'route:PluginsWebAPI' => true,
					)
				)
			)
		);
