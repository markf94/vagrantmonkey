<?php
return array(
		'allowedWebAPIActions' => array(
				'CodetracingWebApi' => array('codetracingDisable', 'codetracingDownloadTraceFile', 'codetracingEnable', 'codetracingIsEnabled', 'codetracingList')
		),
	'dependencies' => array(
			'extensions' => array(
					'curl' => array(),
			),
	),
	'controllers' => array(
		'invokables' => array(
			'CodeTracing' => 'Codetracing\Controller\IndexController',
           	'CodetracingWebApi-1_7' => 'Codetracing\Controller\WebAPI17Controller',
           	'CodetracingWebApi-1_3' => 'Codetracing\Controller\WebAPIController',
           	'CodetracingWebApi-1_2' => 'Codetracing\Controller\WebAPIController',
		),
	),
	'router' => array(
		'routes' => array(
			'amfdata' => array (
        										'type' => 'Zend\Mvc\Router\Http\Segment',
        										'options' => array (
        												'route' => '/CodeTracing/AMFData/id/:traceFile',
        												'defaults' => array (
        														'controller' => 'CodeTracing',
        														'action' => 'AMFData',
        												),
        												'constraints' => array(
        														'traceFile' => '\d+\.\d+\.\d+$',
        												),
        										)
        								),
		)
	),
		
	'view_helpers' => array(
			'invokables' => array(
				'FormatTargetUrl' => 'Codetracing\View\Helper\FormatTargetUrl',
				'TraceFileXml' => 'Codetracing\View\Helper\TraceFileXml',
	        	'TraceFileJson' => 'Codetracing\View\Helper\TraceFileJson',
	        	'DumpReason' => 'Codetracing\View\Helper\DumpReason',
					)
	),
		
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../views',
		),
	),
		
	'webapi_routes' => array(
			'codetracingList' => array(
					'type'    => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'    => '/Api/codetracingList',
							'defaults' => array(
									'controller' => 'CodetracingWebApi',
									'action'     => 'codetracingList',
									'versions'	 => array('1.2'),
									'viewsmap'	=> array('1.2' => '1.3'),
							),
					),
			),
			'codetracingDownloadTraceFile' => array(
					'type'    => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'    => '/Api/codetracingDownloadTraceFile',
							'defaults' => array(
									'controller' => 'CodetracingWebApi',
									'action'     => 'codetracingDownloadTraceFile',
									'versions'	 => array('1.2'),
									'viewsmap'	=> array('1.2' => '1.3')
							),
					),
			),
	        'codetracingGetInfo' => array(
					'type'    => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'    => '/Api/codetracingGetInfo',
							'defaults' => array(
									'controller' => 'CodetracingWebApi',
									'action'     => 'codetracingGetInfo',
									'versions'	 => array('1.3')
							),
					),
			),
			'codetracingDelete' => array(
					'type'    => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'    => '/Api/codetracingDelete',
							'defaults' => array(
									'controller' => 'CodetracingWebApi',
									'action'     => 'codetracingDelete',
									'versions'	 => array('1.2', '1.7'),
									'viewsmap'	=> array('1.2' => '1.3', '1.7' => '1.3')
							),
					),
			),
			'codetracingEnable' => array(
					'type'    => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'    => '/Api/codetracingEnable',
							'defaults' => array(
									'controller' => 'CodetracingWebApi',
									'action'     => 'codetracingEnable',
									'versions'	 => array('1.2'),
									'viewsmap'	=> array('1.2' => '1.3'),
									'limitedos'		=> array('aix')
							),
					),
			),
			'codetracingDisable' => array(
					'type'    => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'    => '/Api/codetracingDisable',
							'defaults' => array(
									'controller' => 'CodetracingWebApi',
									'action'     => 'codetracingDisable',
									'versions'	 => array('1.2'),
									'viewsmap'	=> array('1.2' => '1.3'),
									'limitedos'	=> array('aix')
							),
					),
			),
			'codetracingIsEnabled' => array(
					'type'    => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'    => '/Api/codetracingIsEnabled',
							'defaults' => array(
									'controller' => 'CodetracingWebApi',
									'action'     => 'codetracingIsEnabled',
									'versions'	 => array('1.2'),
									'viewsmap'	=> array('1.2' => '1.3'),
									'limitedos'		=> array('aix')
							),
					),
			),
	        'codetracingCreate' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/codetracingCreate',
						'defaults' => array(
								'controller' => 'CodetracingWebApi',
								'action'     => 'codetracingCreate',
								'versions'	 => array('1.2'),
								'viewsmap'	=> array('1.2' => '1.3')
						),
				),
	        ),
	),
);
