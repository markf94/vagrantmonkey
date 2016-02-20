<?php
return array(
    'controllers' => array(
        'invokables' => array(
			'KeysWebAPI-1_3' => 'WebAPI\Controller\WebAPIKeysController',
        	'ApiKeys' => 'WebAPI\Controller\ApiKeysController',
        ),
    ),
	'view_helpers' => array(
		'invokables' => array(
			'apiKeyXml' 					=> 'WebAPI\View\Helper\apiKeyXml',
			'apiKeyJson' 					=> 'WebAPI\View\Helper\apiKeyJson',
		)
	),
    'view_manager' => array(
        'template_path_stack' => array(
            'WebAPI' => __DIR__ . '/../views',
        ),
    ),
	'webapi_routes_bootstrap' => array(
		'apiKeysAddKey' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/apiKeysAddKey',
						'defaults' => array(
								'controller' => 'KeysWebAPI',
								'action'     => 'apiKeysAddKey',
								'versions'	 => array('1.3'),
								'bootstrap'	 => true
						),
				),
		),
	),
	'webapi_routes' => array(
			'apiKeysEnableKey' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/apiKeysEnableKey',
						'defaults' => array(
								'controller' => 'KeysWebAPI',
								'action'     => 'apiKeysEnableKey',
								'versions'	 => array('1.3')
						),
				),
		),
		'apiKeysDisableKey' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/apiKeysDisableKey',
						'defaults' => array(
								'controller' => 'KeysWebAPI',
								'action'     => 'apiKeysDisableKey',
								'versions'	 => array('1.3')
						),
				),
		),
		'apiKeysEnableStudioKey' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/apiKeysEnableStudioKey',
						'defaults' => array(
								'controller' => 'KeysWebAPI',
								'action'     => 'apiKeysEnableStudioKey',
								'versions'	 => array('1.3')
						),
				),
		),
		'apiKeysDisableStudioKey' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/apiKeysDisableStudioKey',
						'defaults' => array(
								'controller' => 'KeysWebAPI',
								'action'     => 'apiKeysDisableStudioKey',
								'versions'	 => array('1.3')
						),
				),
		),
		'apiKeysGetList' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/apiKeysGetList',
						'defaults' => array(
								'controller' => 'KeysWebAPI',
								'action'     => 'apiKeysGetList',
								'versions'	 => array('1.3')
						),
				),
		),
		'apiKeysRemoveKey' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/apiKeysRemoveKey',
						'defaults' => array(
								'controller' => 'KeysWebAPI',
								'action'     => 'apiKeysRemoveKey',
								'versions'	 => array('1.3')
						),
				),
		),
	)
);
