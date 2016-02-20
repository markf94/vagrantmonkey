<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Audit' => 'Audit\Controller\IndexController',
        	'AuditWebAPI-1_3' => 'Audit\Controller\WebAPIController',
        ),
    ),
	'view_helpers' => array(
		'invokables' => array(
			'ZGridAuditInfo'				=> 'Audit\View\Helper\ZGridAuditInfo',
			'auditMessageXml' 				=> 'Audit\View\Helper\auditMessageXml',
			'auditMessageJson' 				=> 'Audit\View\Helper\auditMessageJson',
			'auditMessageProgressXml' 		=> 'Audit\View\Helper\auditMessageProgressXml',
			'auditMessageProgressJson' 		=> 'Audit\View\Helper\auditMessageProgressJson',
		    'auditExtraData'                => 'Audit\View\Helper\auditExtraData',
			'sendMailActionXml' 			=> 'Audit\View\Helper\sendMailActionXml',
			'sendUrlActionXml' 				=> 'Audit\View\Helper\sendUrlActionXml',
	)),
    'view_manager' => array(
        'template_path_stack' => array(
            'Audit' => __DIR__ . '/../views',
        ),
    ),
	'webapi_routes' => array(
		'auditGetList' => array(
			'type'    => 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'    => '/Api/auditGetList',
				'defaults' => array(
						'controller' => 'AuditWebAPI',
						'action'     => 'auditGetList',
						'versions'	 => array('1.3')
				),
			),
		),
		'auditGetDetails' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/auditGetDetails',
						'defaults' => array(
								'controller' => 'AuditWebAPI',
								'action'     => 'auditGetDetails',
								'versions'	 => array('1.3')
						),
				),
		),
		'auditSetSettings' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/auditSetSettings',
						'defaults' => array(
								'controller' => 'AuditWebAPI',
								'action'     => 'auditSetSettings',
								'versions'	 => array('1.3')
						),
				),
		),
		'auditExport' => array(
				'type'    => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'    => '/Api/auditExport',
						'defaults' => array(
								'controller' => 'AuditWebAPI',
								'action'     => 'auditExport',
								'versions'	 => array('1.3')
						),
				),
		),
	),
		
);
