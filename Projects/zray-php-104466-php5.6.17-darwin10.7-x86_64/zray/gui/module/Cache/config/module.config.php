<?php
return array(
		'controllers' => array(	'invokables' => array(
								'CacheWebApi-1_3' => 'Cache\Controller\WebAPIController',
								'CacheWebApi-1_7' => 'Cache\Controller\WebAPIController',
		),
						),
		'router' => array('routes' => array()),
		'view_manager' => array('template_path_stack' => array(__DIR__ . '/../views',),),
	
		'webapi_routes' => array(
				'cacheClear' => array(	'type'	=> 'Zend\Mvc\Router\Http\Literal',
						'options' => array(
								'route'	=> '/Api/cacheClear',
								'defaults' => array(
										'controller' => 'CacheWebApi',
										'action'	 => 'cacheClear',
										'versions'	 => array('1.3')
								),
						),),
				'datacacheClear' => array(	'type'	=> 'Zend\Mvc\Router\Http\Literal',
						'options' => array(
								'route'	=> '/Api/datacacheClear',
								'defaults' => array(
										'controller' => 'CacheWebApi',
										'action'	 => 'datacacheClear',
										'versions'	 => array('1.7'),
										'viewsmap'	=> array('1.7' => '1.3')
								),
						),)
			),
);
