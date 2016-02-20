<?php
return array(
	'view_helpers' => array(
		'invokables' => array(
			'headlink' => 'ZendServer\View\Helper\HeadLinkWithVersion',
			'headscript' => 'ZendServer\View\Helper\HeadScriptWithVersion',
			'webapiDate' => 'WebAPI\View\Helper\WebapiDate',
			'strtotimeAddTZOffset' => 'WebAPI\View\Helper\strtotimeAddTZOffset',
			'phpErrorType' => 'ZendServer\View\Helper\PhpErrorType',
			'fileSize' => 'ZendServer\View\Helper\FileSize',
			'zendForm' => 'ZendServer\View\Helper\Form\Form',
			'zendSettings' => 'ZendServer\View\Helper\Form\Settings',
			'zForm' => 'ZendServer\View\Helper\Form\ZForm',
			'zendFormTable' => 'ZendServer\View\Helper\Form\Renderer\Table',
			'zendFormDeployWizard' => 'ZendServer\View\Helper\Form\Renderer\DeployWizard',
			'zendFormSettings' => 'ZendServer\View\Helper\Form\Renderer\Settings',
	        'filterJson' => 'ZendServer\Filter\View\Helper\FilterJson',
	        'filterXml' => 'ZendServer\Filter\View\Helper\FilterXml',
			'formIpWidget'	=> 'ZendServer\Form\View\Helper\IpWidget',
			'daemonName' => 'ZendServer\View\Helper\DaemonName',
		)
	),
	'view_manager' => array(
	    'template_path_stack' => array(
	        __DIR__ . '/../views',
	    ),
	),
	'controllers' => array(
	    'invokables' => array(
	        'FilterWebApi-1_3' => 'ZendServer\Filter\Controller\WebAPIController',
	    ),
	),
    
    'webapi_routes' => array(
        'filterGetByType' => array(
            'type'	=> 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route'	=> '/Api/filterGetByType',
                'defaults' => array(
                    'controller' => 'FilterWebApi',
                    'action'	 => 'filterGetByType',
                    'versions'	 => array('1.3')
                ),
            ),
        ),
        'filterSave' => array(
            'type'	=> 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route'	=> '/Api/filterSave',
                'defaults' => array(
                    'controller' => 'FilterWebApi',
                    'action'	 => 'filterSave',
                    'versions'	 => array('1.3')
                ),
            ),
         ),
        'filterDelete' => array(
            'type'	=> 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route'	=> '/Api/filterDelete',
                'defaults' => array(
                    'controller' => 'FilterWebApi',
                    'action'	 => 'filterDelete',
                    'versions'	 => array('1.3')
                ),
            ),
         ),
     )
);
