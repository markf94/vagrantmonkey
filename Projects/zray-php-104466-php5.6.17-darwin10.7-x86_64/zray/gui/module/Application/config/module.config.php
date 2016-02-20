<?php

$dependenciesExtensions = array(
    'PDO' => array(),
    'pdo_sqlite' => array(),
    'pdo_mysql' => array('clusteronly' => true), /// cluster only?
);
if (! isAzureEnv() && ! isZrayStandaloneEnv()) {
    $dependenciesExtensions['Zend Utils'] = array();
}

return array(
	'allowedWebAPIActions' => array(
		'EmailWebApi' => array('emailSend'),
		'NotificationsWebApi' => array('sendNotification', 'updateNotification')
	),
	'dependencies' => array(
		'directives' => array(
			'session.auto_start' => array('type' => 'boolean', 'required' => false),
			'auto_append_file' => array('type' => 'string', 'required' => ''),
			'auto_prepend_file' => array('type' => 'string', 'required' => ''),
			'variables_order' => array('type' => 'options', 'required' => array('GPCS', 'EGPCS')),
			'arg_separator.output' => array('type' => 'string', 'required' => '&'),
		),
		'extensions' => $dependenciesExtensions,
	),
	'layout'				=> 'layouts/layout.phtml',
	'baseUrl'				=> '/ZendServer',
	'loginUrl'				=> '/ZendServer/Login',
	'logs' => array(
		'log_files' => array(
				'codetracing',
    			'datacache',
    			'deployment',
    			'jobqueue',
    			'jqd',
    			'monitor',
    			'monitor_node',
    			'pagecache',
    			'php',
    			'sc',
    			'scd',
    			'zdd',
    			'zsd',
			)
	),
		
	'service_manager' => array(
			'factories' => array(
					'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
			),
	),
	'translator' => array(
			'locale' => 'en_US', // full locale string, can be overridden by configuration in zs_ui.ini
			'event_manager_enabled' => false, /// override in zs_ui.ini 
			/*
			 * [translator]
			 * locale = en_US
			 * event_manager_enabled = false
			 */
			'translation_file_patterns' => array(
					array(
							'type' => 'phparray', /// phparray or gettext
							'base_dir' => __DIR__ . '/../language',
							'pattern' => '%s.php', /// this string goes into sprintf as a pattern and should adhere to its syntax, %s will be replaced by the full locale string
					),
			),
	),
		
	'router' => array(
		'routes' => array(
			'default' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '[/:controller[/:action]][/]',
					'constraints' => array(
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'index',
						'action' => 'index',
					),
				),
			),
			'login' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/Login[/]',
					'defaults' => array(
						'controller' => 'Login',
						'action' => 'index',
						'requireIdentity' => false
					),
				),
			),
			'logout' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/Login/logout[/]',
					'defaults' => array(
						'controller' => 'Login',
						'action' => 'logout',
						'licenseexpired' => true
					),
				),
			),
			'home' => array(
				'type' => '\Application\HomeSwitchRoute',
				'options' => array(
					'route' => '[/]',
					'defaults' => array(
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
						'dashboard' => array(
								'type' => '\Application\HomeSwitchRoute',
								'options' => array(
										'route' => 'Dashboard[/]',
										'defaults' => array(
												'controller' => 'Dashboard',
										),
								),
						),
						'guidepage' => array(
								'type' => '\Application\HomeSwitchRoute',
								'options' => array(
										'route' => 'GuidePage[/]',
										'defaults' => array(
												'controller' => 'GuidePage',
										),
								),
						),
						'homeindex' => array(
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array(
										'route' => 'index.php',
										'defaults' => array(
												'controller' => 'Login',
												'action' => 'redirect',
										),
								),
						),
					)
			),
			
			'index' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/Index/Index',
					'defaults' => array(
						'controller' => 'Dashboard',
						'action' => 'index',
					),
				),
			),
		),
	),
	'controllers' => array(
		'invokables' => array(
			'Dashboard' => 'Application\Controller\IndexController',
			'Settings' => 'Application\Controller\SettingsController',
			'ImportExport' => 'Application\Controller\ImportExportController',
			'Expired' => 'Expired\Controller\IndexController',
			'Login' => 'Application\Controller\LoginController',
			'Bootstrap' => 'Bootstrap\Controller\BootstrapController',
			'ServersWebAPI-1_3' => 'Servers\Controller\WebAPIController',
			'ServersWebAPI-1_2' => 'Servers\Controller\WebAPI12Controller',
			'UsersWebAPI-1_3' => 'Users\Controller\WebAPIController',
			'Servers' => 'Servers\Controller\IndexController',
			'Logs' => 'Logs\Controller\IndexController',
			'LogsWebApi-1_3' => 'Logs\Controller\WebAPIController',
			'Underconstruction' => 'Application\Controller\UnderconstructionController',
		    'Users' => 'Users\Controller\IndexController',
		    'EmailWebApi-1_3' => 'Email\Controller\WebAPIController',
			'NotificationsWebApi-1_3' => 'Notifications\Controller\WebApiController',
			'NotificationsWebApi-1_6' => 'Notifications\Controller\WebApiController',
			'AclWebApi-1_3' => 'Acl\Controller\WebAPIController',
			'ApplicationWebApi-1_3' => 'Application\Controller\WebApiController',
			'BootstrapWebAPI-1_3' => 'Bootstrap\Controller\WebAPIController',
			'BootstrapWebAPI-1_10' => 'Bootstrap\Controller\WebAPIController',
			'ZsdWebAPI-1_5' => 'Zsd\Controller\WebAPIController',
		),
	),
	'view_helpers' => array(
		'invokables' => array(
			'zGridEventDetails'				=> 'Application\View\Helper\ZGridEventDetails',
			'zGridLibraryDetails'			=> 'Application\View\Helper\ZGridLibraryDetails',
			'zGridJobDetails'				=> 'Application\View\Helper\ZGridJobDetails',
			'ZGridQueueDetails'				=> 'Application\View\Helper\ZGridQueueDetails',
			'zPager' 						=> 'Application\View\Helper\ZPager',
			'uiDate' 						=> 'Application\View\Helper\UiDate',
			'contactZend'					=> 'Application\View\Helper\ContactZend',
			'dateFormat'					=> 'Application\View\Helper\DateFormat',
			'zGrid'							=> 'Application\View\Helper\ZGrid',
			'zGrid2'						=> 'Application\View\Helper\ZGrid2',
			'filter'						=> 'Application\View\Helper\Filter',
			'onOffButton'					=> 'Application\View\Helper\OnOffButton',
			'searchField'					=> 'Application\View\Helper\SearchField',
			'zGridPolling'					=> 'Application\View\Helper\ZGridPolling',
		    'Acl'                           => 'Application\View\Helper\Acl',
			'datePicker'                    => 'Application\View\Helper\DatePicker',
			'highlighter'                   => 'Application\View\Helper\Highlighter',
			'serverErrorMessageXml'			=> 'Messages\View\Helper\ServerErrorMessageXml',
			'serverErrorMessageJson'		=> 'Messages\View\Helper\ServerErrorMessageJson',
			'serverStatus'					=> 'Servers\View\Helper\ServerStatus',
			'zGridServerDetails'			=> 'Servers\View\Helper\ZGridServerDetails',
			'serverInfoXml' 				=> 'Servers\View\Helper\serverInfoXml',
			'serverInfoJson' 				=> 'Servers\View\Helper\serverInfoJson',
			'LogFileLines'					=> 'Logs\View\Helper\LogFileLines',
			'EmailSubject'					=> 'Email\View\Helper\EmailSubject',
			'CapabilitiesLabels'			=> 'Application\View\Helper\CapabilitiesLabels',
			'FreeEditionString'				=> 'Application\View\Helper\FreeEditionString',
			'EditionString'					=> 'Application\View\Helper\EditionString',
			'MessageLabels'					=> 'Zsd\View\Helper\MessageLabels',
			'DaemonMessageJson'				=> 'Zsd\View\Helper\DaemonMessageJson',
			'DaemonMessageXml'				=> 'Zsd\View\Helper\DaemonMessageXml',
		)
	),
	'view_manager' => array(
			'doctype' => 'HTML5',
			'not_found_template' => 'error/404',
			'exception_template' => 'error/index',
			'permissions_template' => 'error/permissions',
			'template_map' => array(
					'index/index' => __DIR__ . '/../views/index/index.phtml',
			),
			'template_path_stack' => array(
					__DIR__ . '/../views',
			),
	),
	'allowedWebAPIActions' => array(
			'DeploymentWebAPI' => array('applicationSynchronize'),
			'NotificationsWebApi' => array('sendNotification'),
	),
	'webapi_routes_bootstrap' => array(
		'serverAddToCluster' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/serverAddToCluster',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'serverAddToCluster',
								'versions'	 => array('1.3'),
								'bootstrap'	 => true
						),
				),
		),
		'clusterIsInitialized' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterIsInitialized',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterIsInitialized',
								'versions'	 => array('1.3'),
								'bootstrap'	 => true
						),
				),
		),
	),
	'expired_routes' => array(
		'default' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
						'route' => '/[:controller[/:action]][/]',
						'constraints' => array(
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						),
						'defaults' => array(
								'controller' => 'index',
								'action' => 'index',
						),
				),
		),
	),
	'webapi_routes' => array(
	    'bootstrapSingleServer' => array(
	        'type'	=> 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'	=> '/Api/bootstrapSingleServer',
	            'defaults' => array(
	                'controller' => 'BootstrapWebAPI',
	                'action'	 => 'bootstrapSingleServer',
	                'versions'	 => array('1.3'),
	                'bootstrap'	 => true,
	                'bootstraponly' => true
	            ),
	        ),
	    ),
	    'setServerProfile' => array(
	        'type'	=> 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'	=> '/Api/setServerProfile',
	            'defaults' => array(
	                'controller' => 'BootstrapWebAPI',
	                'action'	 => 'setServerProfile',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'getServerProfile' => array(
	        'type'	=> 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'	=> '/Api/getServerProfile',
	            'defaults' => array(
	                'controller' => 'BootstrapWebAPI',
	                'action'	 => 'getServerProfile',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
		'userAuthenticationSettings' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/userAuthenticationSettings',
						'defaults' => array(
								'controller' => 'UsersWebAPI',
								'action'	 => 'userAuthenticationSettings',
								'versions'	 => array('1.3')
						),
				),
		),
		'clusterAddServer' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterAddServer',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterAddServer',
								'versions'	 => array('1.2', '1.3')
						),
				),
		),
		'clusterGetServerStatus' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterGetServerStatus',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterGetServerStatus',
								'versions'	 => array('1.2'),
								'viewsmap' 	 => array('1.2' => '1.3')
						),
				),
		),
		'clusterGetServersCount' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterGetServersCount',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterGetServersCount',
								'versions'	 => array('1.3')
						),
				),
		),
		'restartPhp' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/restartPhp',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'restartPhp',
								'versions'	 => array('1.2', '1.3'),
								'viewsmap'	=> array('1.2' => '1.3')
						),
				),
		),
		'daemonsProbe' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/daemonsProbe',
						'defaults' => array(
								'controller' => 'ZsdWebAPI',
								'action'	 => 'daemonsProbe',
								'versions'	 => array('1.5'),
						),
				),
		),
		'restartDaemon' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/restartDaemon',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'restartDaemon',
								'versions'	 => array('1.3')
						),
				),
		),
		'clusterEnableServer' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterEnableServer',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterEnableServer',
								'versions'	 => array('1.2', '1.3')
						),
				),
		),
		'clusterDisableServer' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterDisableServer',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterDisableServer',
								'versions'	 => array('1.2', '1.3')
						),
				),
		),
		'clusterRemoveServer' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterRemoveServer',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterRemoveServer',
								'versions'	 => array('1.2', '1.3')
						),
				),
		),
		'clusterForceRemoveServer' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterForceRemoveServer',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'	 => 'clusterForceRemoveServer',
								'versions'	 => array('1.3')
						),
				),
		),
		'changeServerNameById' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/changeServerNameById',
						'defaults' => array(
								'controller' => 'ServersWebAPI',
								'action'     => 'changeServerNameById',
								'versions'	 => array('1.3')
						),
				),
		),			
		'userSetPassword' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/userSetPassword',
						'defaults' => array(
								'controller' => 'UsersWebAPI',
								'action'     => 'userSetPassword',
								'versions'	 => array('1.3')
						),
				),
		),
	    'setPassword' => array (
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/setPassword',
	            'defaults' => array(
	                'controller' => 'UsersWebAPI',
	                'action'     => 'setPassword',
	                'versions'	 => array('1.3')
	            ),
	        ),
	    ),
	    'emailSend' => array (
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/emailSend',
	            'defaults' => array(
	                'controller' => 'EmailWebApi',
	                'action'     => 'emailSend',
	                'versions'	 => array('1.3')
	            ),
	        ),
	    ),
		'getNotifications' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/getNotifications',
						'defaults' => array(
								'controller' => 'NotificationsWebApi',
								'action'     => 'getNotifications',
								'versions'	 => array('1.3', '1.6')
						),
				),
		),
		'aclSetGroups' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/aclSetGroups',
						'defaults' => array(
								'controller' => 'AclWebApi',
								'action'     => 'aclSetGroups',
								'versions'	 => array('1.3')
						),
				),
		),
		'deleteNotification' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/deleteNotification',
						'defaults' => array(
								'controller' => 'NotificationsWebApi',
								'action'     => 'deleteNotification',
								'versions'	 => array('1.3')
						),
				),
		),
		'updateNotification' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/updateNotification',
						'defaults' => array(
								'controller' => 'NotificationsWebApi',
								'action'     => 'updateNotification',
								'versions'	 => array('1.3')
						),
				),
		),
		'sendNotification' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/sendNotification',
						'defaults' => array(
								'controller' => 'NotificationsWebApi',
								'action'     => 'sendNotification',
								'versions'	 => array('1.3'),
								'bootstrap'	 => true
						),
				),
		),
		'logsReadLines' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/logsReadLines',
						'defaults' => array(
								'controller' => 'LogsWebApi',
								'action'     => 'logsReadLines',
								'versions'	 => array('1.3')
						),
				),
		),
		'logsGetLogfile' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/logsGetLogfile',
						'defaults' => array(
								'controller' => 'LogsWebApi',
								'action'     => 'logsGetLogfile',
								'versions'	 => array('1.3')
						),
				),
		),
		'clusterReconfigureServer' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/clusterReconfigureServer',
						'defaults' => array(
								'controller' => 'ConfigurationWebApi',
								'action'	 => 'configurationRevertChanges',
								'versions'	 => array('1.3')
						),
				),
		),
),
		
);
