<?php
return array(
	'view_helpers' => array(
		'invokables' => array(
			'highcharts' => 'Statistics\View\Helper\Highcharts',
		)
	),
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../views',
		),
	),
	'controllers' => array(
			'invokables' => array(
				'StatisticsWebAPI-1_3' => 'Statistics\Controller\WebAPIController',
				'StatisticsWebAPI-1_5' => 'Statistics\Controller\WebAPIController',
			),
			'map' => array(
			)
	),
	'webapi_routes' => array(
		'statisticsGetSeries' => array(
			'type'    => 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'    => '/Api/statisticsGetSeries',
				'defaults' => array(
					'controller' => 'StatisticsWebAPI',
					'action'     => 'statisticsGetSeries',
    				'versions'	 => array('1.3')
				),
			),
		),
		'statisticsGetMap' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/statisticsGetMap',
						'defaults' => array(
								'controller' => 'StatisticsWebAPI',
								'action'     => 'statisticsGetMap',
								'versions'	 => array('1.5')
						),
				),
		),
		'statisticsClearData' => array(
			'type'    => 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'    => '/Api/statisticsClearData',
				'defaults' => array(
						'controller' => 'StatisticsWebAPI',
						'action'     => 'statisticsClearData',
						'versions'	 => array('1.3')
				),
			),
		),
	)
);
