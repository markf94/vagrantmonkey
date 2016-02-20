<?php
return array(
	'allowedWebAPIActions' => array(
			'StudioWebApi' => array(	'studioIsDebugModeEnabled','monitorExportIssueByEventsGroup','studioStartDebugMode','studioStopDebugMode',
										'saveAlternateServer', 'studioStartDebug', 'studioStartProfile', 'studioShowSourceAction',
			                            'enableXdebug', 'enableZendDebugger',
			 ),
	),
	'controllers' => array(
		'invokables' => array(
			'StudioWebApi-1_10' => 'StudioIntegration\Controller\WebAPIController',
			'StudioWebApi-1_3' 	=> 'StudioIntegration\Controller\WebAPIController',
			'StudioWebApi-1_2' 	=> 'StudioIntegration\Controller\WebAPIController',
			'StudioIntegration' => 'StudioIntegration\Controller\IndexController',
			'IDEIntegration' 	=> 'StudioIntegration\Controller\IndexController',
		),
		'aliases' => array(
			'IDEIntegration' => 'StudioIntegration',
		),
	),
	'view_helpers' => array(
			'invokables' => array(
				'formHostsList' => 'StudioIntegration\Form\View\Helper\HostsList',
				'ZendStudioSettingsJsString' => 'StudioIntegration\Debugger\View\Helper\ZendStudioSettingsJsString',
			)),
	'view_manager' => array(
		'template_path_stack' => array(
				__DIR__ . '/../views',
		),
	),
	'service_manager' => array(
		'invokables' => array(
			'StudioIntegration\Form\ChooseDebugger' => 'StudioIntegration\Form\ChooseDebugger',
			'StudioIntegration\Form\IdeIntegration' => 'StudioIntegration\Form\IdeIntegration',
			'StudioIntegration\Form\Xdebug' => 'StudioIntegration\Form\Xdebug',
			'StudioIntegration\Form\Configuration' => 'StudioIntegration\Form\Configuration',
			'StudioIntegration\Form\HostsList' => 'StudioIntegration\Form\HostsList',
			'StudioIntegration\Form\DebuggerSettings' => 'StudioIntegration\Form\DebuggerSettings',
		)
	),
	'webapi_routes' => array(
		'saveAlternateServer' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/saveAlternateServer',
						'defaults' => array(
								'controller' => 'StudioWebApi',
								'action'	 => 'saveAlternateServer',
								'versions'	 => array('1.3')
						),
				),
		),
		'studioStartDebug' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/studioStartDebug',
				'defaults' => array(
					'controller' => 'StudioWebApi',
					'action'	 => 'studioStartDebug',
					'versions'	 => array('1.2'),
					'viewsmap'	=> array('1.2' => '1.3')
				),
			),
		),
		'studioStartProfile' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/studioStartProfile',
				'defaults' => array(
					'controller' => 'StudioWebApi',
					'action'	 => 'studioStartProfile',
					'versions'	 => array('1.2'),
					'viewsmap'	=> array('1.2' => '1.3')
				),
			),
		),
		'studioShowSourceAction' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/studioShowSource',
						'defaults' => array(
								'controller' => 'StudioWebApi',
								'action'	 => 'studioShowSource',
								'versions'	 => array('1.3')
						),
				),
		),
		'monitorExportIssueByEventsGroupAction' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorExportIssueByEventsGroup',
				'defaults' => array(
					'controller' => 'StudioWebApi',
					'action'	 => 'monitorExportIssueByEventsGroup',
					'versions'	 => array('1.2'),
					'viewsmap'	=> array('1.2' => '1.3')
				),
			),
		),		
		'studioStartDebugMode' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/studioStartDebugMode',
						'defaults' => array(
								'controller' => 'StudioWebApi',
								'action'	 => 'studioStartDebugMode',
								'versions'	 => array('1.3')
						),
				),
		),
		'studioStopDebugMode' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/studioStopDebugMode',
						'defaults' => array(
								'controller' => 'StudioWebApi',
								'action'	 => 'studioStopDebugMode',
								'versions'	 => array('1.3')
						),
				),
		),
		'studioIsDebugModeEnabled' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/studioIsDebugModeEnabled',
						'defaults' => array(
								'controller' => 'StudioWebApi',
								'action'	 => 'studioIsDebugModeEnabled',
								'versions'	 => array('1.3')
						),
				),
		),
		'debuggerSettings' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/debuggerSettings',
						'defaults' => array(
								'controller' => 'StudioWebApi',
								'action'	 => 'debuggerSettings',
								'versions'	 => array('1.10')
						),
				),
		),
	),
);
