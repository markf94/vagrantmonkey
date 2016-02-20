<?php
return array(
    'view_helpers' => array(
        'invokables' => array(
            'pluginXml'  => 'Plugins\View\Helper\PluginXml',
            'pluginJson' => 'Plugins\View\Helper\PluginJson',
            'PluginInfoXml'  => 'Plugins\View\Helper\PluginInfoXml',
            'PluginInfoJson' => 'Plugins\View\Helper\PluginInfoJson',
            'ZGridPluginInfo' => 'Plugins\View\Helper\ZGridPluginInfo',
        )),
	'controllers' => array(
		'invokables' => array(
			'Plugins' => 'Plugins\Controller\IndexController',
			'PluginsGallery' => 'Plugins\Controller\GalleryController',
			'PluginsWizard' => 'Plugins\Controller\WizardController',
			'SetUpdateCookie' => 'Plugins\Controller\SetUpdateCookie',
		    'PluginsWebAPI-1_10' => 'Plugins\Controller\WebAPIController',
		),
	),
	'view_manager' => array(
		'template_path_stack' => array(
				__DIR__ . '/../views',
		),
	),
	'service_manager' => array(
		'invokables' => array(
			'pluginsFileSystemMapper' => 'Plugins\Mapper\FileSystem',
		),
	),
	'router' => array(
        'routes' => array(
            'pluginsGalleryPage' => array(
                'type'	=> 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/Plugins/Gallery',
                    'defaults' => array(
                        'controller' => 'PluginsGallery',
                        'action' => 'index',
                    ),
                ),
            )
        ),
    ),
	'webapi_routes' => array(
		'pluginGetList' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/pluginGetList',
				'defaults' => array(
					'controller' => 'PluginsWebAPI',
					'action'	 => 'pluginGetList',
					'versions'	 => array('1.10'),
				),
			),
		),
	    'pluginCancelPendingDeployment' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginCancelPendingDeployment',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginCancelPendingDeployment',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'pluginRemove' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginRemove',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginRemove',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'disablePlugins' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/disablePlugins',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'disablePlugins',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'enablePlugins' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/enablePlugins',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'enablePlugins',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'pluginSynchronize' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginSynchronize',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginSynchronize',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'pluginDeploy' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginDeploy',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginDeploy',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'pluginUpdate' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginUpdate',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginUpdate',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'pluginGetDetails' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginGetDetails',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginGetDetails',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'pluginSaveSingleUpdate' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginSaveSingleUpdate',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginSaveSingleUpdate',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
	    'pluginSaveUpdates' => array(
	        'type'    => 'Zend\Mvc\Router\Http\Literal',
	        'options' => array(
	            'route'    => '/Api/pluginSaveUpdates',
	            'defaults' => array(
	                'controller' => 'PluginsWebAPI',
	                'action'     => 'pluginSaveUpdates',
	                'versions'	 => array('1.10')
	            ),
	        ),
	    ),
    )
);