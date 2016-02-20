<?php
return array(
	'view_helpers' => array(
		'invokables' => array(
			'urlinsightRulesJson'            => 'UrlInsight\View\Helper\UrlInsightRulesJson',
			'urlinsightRulesXml'             => 'UrlInsight\View\Helper\UrlInsightRulesXml',
			'urlinsightRequestsJson'         => 'UrlInsight\View\Helper\UrlInsightRequestsJson',
			'urlinsightRequestsXml'          => 'UrlInsight\View\Helper\UrlInsightRequestsXml',
			'urlinsightRequestJson'          => 'UrlInsight\View\Helper\UrlInsightRequestJson',
			'urlinsightRequestXml'           => 'UrlInsight\View\Helper\UrlInsightRequestXml',
			'urlinsightZraySnapshotsJson'    => 'UrlInsight\View\Helper\UrlInsightZraySnapshotsJson',
			'urlinsightZraySnapshotsXml'     => 'UrlInsight\View\Helper\UrlInsightZraySnapshotsXml',
			'urlinsightUrlsJson'             => 'UrlInsight\View\Helper\UrlInsightUrlsJson',
			'urlinsightUrlsXml'              => 'UrlInsight\View\Helper\UrlInsightUrlsXml',
			'urlinsightUrlJson'              => 'UrlInsight\View\Helper\UrlInsightUrlJson',
			'urlinsightUrlXml'               => 'UrlInsight\View\Helper\UrlInsightUrlXml',
			'urlinsightUrlFunctionsJson'     => 'UrlInsight\View\Helper\UrlInsightUrlFunctionsJson',
			'urlinsightUrlFunctionsXml'      => 'UrlInsight\View\Helper\UrlInsightUrlFunctionsXml',
		)
	),
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../views',
		),
	),
	'controllers' => array(
			'invokables' => array(
				'UrlInsight' => 'UrlInsight\Controller\IndexController',
				'UrlInsightWebAPI-1_9' => 'UrlInsight\Controller\WebAPIController',
			),
			'map' => array(
			)
	),
	'webapi_routes' => array(
		'urlinsightGetRules' => array(
			'type'    => 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'    => '/Api/urlinsightGetRules',
				'defaults' => array(
					'controller' => 'UrlInsightWebAPI',
					'action'     => 'urlinsightGetRules',
    				'versions'	 => array('1.9')
				),
			),
		),
		'urlinsightAddRule' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/urlinsightAddRule',
						'defaults' => array(
								'controller' => 'UrlInsightWebAPI',
								'action'     => 'urlinsightAddRule',
								'versions'	 => array('1.9')
						),
				),
		),
		'urlinsightRemoveRule' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/urlinsightRemoveRule',
						'defaults' => array(
								'controller' => 'UrlInsightWebAPI',
								'action'     => 'urlinsightRemoveRule',
								'versions'	 => array('1.9')
						),
				),
		),
		'urlinsightGetUrls' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/urlinsightGetUrls',
						'defaults' => array(
								'controller' => 'UrlInsightWebAPI',
								'action'     => 'urlinsightGetUrls',
								'versions'	 => array('1.9')
						),
				),
		),
		'urlinsightGetUrlInfo' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/urlinsightGetUrlInfo',
						'defaults' => array(
								'controller' => 'UrlInsightWebAPI',
								'action'     => 'urlinsightGetUrlInfo',
								'versions'	 => array('1.9')
						),
				),
		),
		'urlinsightGetUrlFunctions' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/urlinsightGetUrlFunctions',
						'defaults' => array(
								'controller' => 'UrlInsightWebAPI',
								'action'     => 'urlinsightGetUrlFunctions',
								'versions'	 => array('1.9')
						),
				),
		),
		'urlinsightGetZraySnapshots' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/urlinsightGetZraySnapshots',
						'defaults' => array(
								'controller' => 'UrlInsightWebAPI',
								'action'     => 'urlinsightGetZraySnapshots',
								'versions'	 => array('1.9')
						),
				),
		),
	)
);
