<?php
return array(
	'controllers' => array(
		'aliases' => array('Z-Ray' => 'DevBar'),
		'invokables' => array(
        	'DevBar' => 'DevBar\Controller\IndexController',
			'DevBarWebApi-1_8' => 'DevBar\Controller\WebAPIController',
			'DevBarWebApi-1_9' => 'DevBar\Controller\WebAPIController',
		    'DevBarWebApi-1_10' => 'DevBar\Controller\WebAPIController',
			'ZrayLive' => 'DevBar\Controller\ZrayLiveController',
			'ZrayHistory' => 'DevBar\Controller\ZrayHistoryController',
		),
	),
	'view_manager' => array(
		'template_path_stack' => array(
				__DIR__ . '/../views',
		),
	),
    'service_manager' => array(
    	'aliases' => array(
    		'devbar_dictionary' => 'DevBar\Filter\Dictionary',
    	)
    ),
	'router' => array(
		'routes' => array(
			'DevBar' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/Z-Ray[/:action]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'DevBar',
						'action' => 'index',
						'requireIdentity' => false,
						'bootstrap' => true,
					),
				),
			),
		),
	),
	'view_helpers' => array(
		'invokables' => array(
			'functionsStatsXml'=> 'DevBar\View\Helper\FunctionsStatsXml',
			'FunctionsStatsJson'=> 'DevBar\View\Helper\FunctionsStatsJson',
			'backtraceXml'		=> 'DevBar\View\Helper\BacktraceXml',
			'backtraceJson'		=> 'DevBar\View\Helper\BacktraceJson',
			'RequestInfoJson'	=> 'DevBar\View\Helper\RequestInfoJson',
			'RequestInfoXml' 	=> 'DevBar\View\Helper\RequestInfoXml',
			'RuntimeJson'		=> 'DevBar\View\Helper\RuntimeJson',
			'RuntimeXml' 		=> 'DevBar\View\Helper\RuntimeXml',
			'SqlQueriesJson' 	=> 'DevBar\View\Helper\SqlQueriesJson',
			'SqlQueriesXml' 	=> 'DevBar\View\Helper\SqlQueriesXml',
			'LogEntriesJson' 	=> 'DevBar\View\Helper\LogEntriesJson',
			'LogEntriesXml' 	=> 'DevBar\View\Helper\LogEntriesXml',
			'exceptionsJson' 	=> 'DevBar\View\Helper\ExceptionsJson',
			'exceptionsXml' 	=> 'DevBar\View\Helper\ExceptionsXml',
			'SqlQueriesStatus' 	=> 'DevBar\View\Helper\SqlQueriesStatus',
			'SuperglobalStructureJson'  => 'DevBar\View\Helper\SuperglobalStructureJson',
			'SuperglobalStructure19Json'  => 'DevBar\View\Helper\SuperglobalStructure19Json',
			'SuperglobalStructureXml'	=> 'DevBar\View\Helper\SuperglobalStructureXml',
			'DevBarPager' 		=> 'DevBar\View\Helper\DevBarPager',
			'DevBarSearch' 		=> 'DevBar\View\Helper\DevBarSearch',
			'DevBarExpandAll' 		=> 'DevBar\View\Helper\DevBarExpandAll',
			'AccessTokenJson' 		=> 'DevBar\View\Helper\AccessTokenJson',
			'SqlQueryFormat' 		=> 'DevBar\View\Helper\SqlQueryFormat',
			'ZrayTable' 		=> 'DevBar\View\Helper\ZrayTable',
			'ZrayHeader' 		=> 'DevBar\View\Helper\ZrayHeader',
			'ZrayFooter' 		=> 'DevBar\View\Helper\ZrayFooter',
			'ZrayInject' 		=> 'DevBar\View\Helper\ZrayInject',
		    'NotificationsJson' => 'DevBar\View\Helper\NotificationsJson',
		)
	),
	'webapi_routes' => array(
		'zrayGetRequestsInfo' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayGetRequestsInfo',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'devBarGetRequestsInfo',
					'versions'	 => array('1.8'),
					'skipauth'	=> true,
					'bootstrap' => true,
					'devbar'	=> true
				),
			),
		),
		'zrayGetAllRequestsInfo' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayGetAllRequestsInfo',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'devBarGetAllRequestsInfo',
					'versions'	 => array('1.9'),
					'viewsmap' 	 => array('1.9' => '1.8'),
				),
			),
		),
		'zrayGetCustomData' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayGetCustomData',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'devBarGetCustomData',
					'versions'	 => array('1.9'),
					'bootstrap' => true
				),
			),
		),
		'zrayCreateAccessToken' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayCreateAccessToken',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'zrayCreateAccessToken',
					'versions'	 => array('1.8'),
				),
			),
		),
	    'zrayCreateSelectiveAccess' => array(
	        'type'	=> 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'	=> '/Api/zrayCreateSelectiveAccess',
	            'defaults' => array(
	                'controller' => 'DevBarWebApi',
	                'action'	 => 'zrayCreateSelectiveAccess',
	                'versions'	 => array('1.10'),
	            ),
	        ),
	    ),
		'zrayRemoveAccessToken' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayRemoveAccessToken',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'zrayRemoveAccessToken',
					'versions'	 => array('1.8'),
				),
			),
		),
		'zrayExpireAccessToken' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayExpireAccessToken',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'zrayExpireAccessToken',
					'versions'	 => array('1.8'),
				),
			),
		),
		'zrayDeleteByIds' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayDeleteByIds',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'zrayDeleteByIds',
					'versions'	 => array('1.10'),
				),
			),
		),
		'zrayListAccessTokens' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayListAccessTokens',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'devBarListAccessTokens',
					'versions'	 => array('1.8'),
				),
			),
		),
		'zrayGetRequestEnvironment' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayGetRequestEnvironment',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'devBarGetRequestEnvironment',
					'versions'	 => array('1.8', '1.9'),
					'skipauth'	=> true,
					'bootstrap' => true,
					'devbar'	=> true
				),
			),
		),
		'zrayGetRequestFunctions' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/zrayGetRequestFunctions',
				'defaults' => array(
					'controller' => 'DevBarWebApi',
					'action'	 => 'devBarGetRequestFunctions',
					'versions'	 => array('1.8'),
					'skipauth'	=> true,
					'bootstrap' => true,
					'devbar'	=> true
				),
			),
		),
		'zrayGetBacktrace' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/zrayGetBacktrace',
						'defaults' => array(
								'controller' => 'DevBarWebApi',
								'action'	 => 'devBarGetBacktrace',
								'versions'	 => array('1.8'),
								'skipauth'	=> true,
								'bootstrap' => true,
								'devbar'	=> true
						),
				),
		),
		'zrayGetDebuggerConfigurations' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/zrayGetDebuggerConfigurations',
						'defaults' => array(
								'controller' => 'DevBarWebApi',
								'action'	 => 'devBarGetDebuggerConfigurations',
								'versions'	 => array('1.8'),
								'skipauth'	=> true,
								'bootstrap' => true,
								'devbar'	=> true
						),
				),
		),
	),
);