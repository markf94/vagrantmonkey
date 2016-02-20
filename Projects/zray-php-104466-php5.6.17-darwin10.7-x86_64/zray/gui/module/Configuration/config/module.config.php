<?php
return array(
	'allowedWebAPIActions' => array(
			'ConfigurationWebApi' => array('getSystemInfo', 'tasksComplete'),
	),
	'view_helpers' => array(
			'invokables' => array(
			'ExtensionXml' => 'Configuration\View\Helper\ExtensionXml',
			'ExtensionJson' => 'Configuration\View\Helper\ExtensionJson',
			'DirectiveXml' => 'Configuration\View\Helper\DirectiveXml',
			'DirectiveJson' => 'Configuration\View\Helper\DirectiveJson',
			'DaemonXml' => 'Configuration\View\Helper\DaemonXml',
			'DaemonJson' => 'Configuration\View\Helper\DaemonJson',
			'zGridDirectives' => 'Configuration\View\Helper\ZGridDirectives',
			'licenseInfoXml' 				=> 'Configuration\View\Helper\licenseInfoXml',
			'licenseInfoJson' 				=> 'Configuration\View\Helper\licenseInfoJson',
			'licenseInfoManagerXml' 		=> 'Configuration\View\Helper\licenseInfoManagerXml',
			'licenseInfoManagerJson' 		=> 'Configuration\View\Helper\licenseInfoManagerJson',
			'licenseChangeXml' 				=> 'Configuration\View\Helper\licenseChangeXml',
			'licenseChangeJson' 			=> 'Configuration\View\Helper\licenseChangeJson',
					)
	),
	'controllers' => array(
			'invokables' => array(
						'ConfigurationWebApi-1_3' => 'Configuration\Controller\WebAPIController',
						'ConfigurationWebApi-1_2' => 'Configuration\Controller\WebAPI12Controller',
						'Extensions' => 'Configuration\Controller\ExtensionsController',
						'ZendComponents' => 'Configuration\Controller\ZendComponentsController',
						'ZendMonitor' => 'Configuration\Controller\ZendMonitorController',
						'SessionClustering' => 'Configuration\Controller\SessionClusteringController',
						'ServerInfo' => 'Configuration\Controller\ServerInfoController',
						'License' => 'Configuration\Controller\LicenseController',
			),
		),
		'view_manager' => array(
				
				'template_path_stack' => array(
						__DIR__ . '/../views',
				),
		),
	'webapi_routes_bootstrap' => array(
			'serverStoreLicense' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/serverStoreLicense',
						'defaults' => array(
								'controller' => 'ConfigurationWebApi',
								'action'     => 'serverStoreLicense',
								'versions'	 => array('1.3'),
								'bootstrap'	 => true
						),
				),
			),
	),
	'webapi_routes' => array(
			'serverValidateLicense' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/serverValidateLicense',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'serverValidateLicense',
									'versions'	 => array('1.3'),
									'bootstrap'	 => true
							),
					),
			),
			'licenseUpdated' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/licenseUpdated',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'licenseUpdated',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationExtensionsList' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationExtensionsList',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationExtensionsList',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationComponentsList' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationComponentsList',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationComponentsList',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationRevertChanges' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationRevertChanges',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationRevertChanges',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationApplyChanges' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationApplyChanges',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationApplyChanges',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationDirectivesList' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationDirectivesList',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationDirectivesList',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationExtensionsOn' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationExtensionsOn',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationExtensionsOn',
									'versions'	 => array('1.3')
							),
					),
			),
			'setZendMonitorDefaultSettings' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/setZendMonitorDefaultSettings',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'setZendMonitorDefaultSettings',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationStoreDirectives' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationStoreDirectives',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationStoreDirectives',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationExtensionsOff' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/configurationExtensionsOff',
						'defaults' => array(
								'controller' => 'ConfigurationWebApi',
								'action'	 => 'configurationExtensionsOff',
								'versions'	 => array('1.3')
						),
				),
			),
			'configurationValidateDirectives' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationValidateDirectives',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationValidateDirectives',
									'versions'	 => array('1.3')
							),
					),
			),
			'tasksComplete' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/tasksComplete',
						'defaults' => array(
								'controller' => 'ConfigurationWebApi',
								'action'	 => 'tasksComplete',
								'versions'	 => array('1.3'),
                                'bootstrap' => true
						),
				),
			),
			'serverInfoTaskComplete' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/serverInfoTaskComplete',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'serverInfoTaskComplete',
									'versions'	 => array('1.3')
							),
					),
			),
			'getServerInfo' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/getServerInfo',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'getServerInfo',
									'versions'	 => array('1.3')
							),
					),
			),
			'getSystemInfo' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/getSystemInfo',
						'defaults' => array(
								'controller' => 'ConfigurationWebApi',
								'action'	 => 'getSystemInfo',
								'versions'	 => array('1.2', '1.3'),
								'viewsmap'	=> array('1.3' => '1.2')
						),
				),
			),
			'configurationReset' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationReset',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationReset',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationExport' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationExport',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationExport',
									'versions'	 => array('1.3')
							),
					),
			),
			'configurationImport' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/configurationImport',
							'defaults' => array(
									'controller' => 'ConfigurationWebApi',
									'action'	 => 'configurationImport',
									'versions'	 => array('1.3')
							),
					),
			),			
		),
);
