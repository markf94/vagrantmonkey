<?php
return array(
	// Allow specific methods for integration with studio
	'allowedWebAPIActions' => array(
			'DeploymentWebAPI' => array('applicationDeploy','applicationGetStatus', 'applicationRemove','applicationUpdate'),
			'DeploymentLibraryWebAPI' => array('libraryGetStatus','libraryVersionGetStatus', 'libraryVersionDeploy','libraryVersionRemove', 'downloadLibraryVersionFile'),
	),
	'dependencies' => array(
		'extensions' => array(
			'Zip' => array(),
		),
	),
		'view_helpers' => array(
				'invokables' => array(
				'appStatus' => 'Deployment\View\Helper\AppStatus',
				'appHealthCheckStatus' => 'Deployment\View\Helper\AppHealthCheckStatus',
				'appDetails' => 'Deployment\View\Helper\AppDetails',
				'appPrerequisites' => 'Deployment\View\Helper\AppPrerequisites',
				'appPrerequisitesJson' => 'Deployment\View\Helper\AppPrerequisitesJson',
				'appMessages' => 'Deployment\View\Helper\AppMessages',
				'AppLogo' => 'Deployment\View\Helper\AppLogo',
				'ApplicationInfoJson' => 'Deployment\View\Helper\ApplicationInfoJson',
				'ApplicationInfoXml' => 'Deployment\View\Helper\ApplicationInfoXml',
				'downloadStatus' => 'Deployment\View\Helper\DownloadStatus',
				'VhostInfoJson' => 'Vhost\View\Helper\VhostInfoJson',
				'VhostFullInfoJson' => 'Vhost\View\Helper\VhostFullInfoJson',
				'VhostInfoXml' => 'Vhost\View\Helper\VhostInfoXml',
				'VhostFullInfoXml' => 'Vhost\View\Helper\VhostFullInfoXml',
				'ZGridApplicationInfo' => 'Deployment\View\Helper\ZGridApplicationInfo',
				'LibraryInfoJson' => 'DeploymentLibrary\View\Helper\LibraryInfoJson',
				'LibraryVersionInfoJson' => 'DeploymentLibrary\View\Helper\LibraryVersionInfoJson',
				'LibraryInfoXml' => 'DeploymentLibrary\View\Helper\LibraryInfoXml',
				'LibraryVersionInfoXml' => 'DeploymentLibrary\View\Helper\LibraryVersionInfoXml',
				'LibraryVersionServerInfoXml' => 'DeploymentLibrary\View\Helper\LibraryVersionServerInfoXml',
				'LibraryVersionServerInfoJson' => 'DeploymentLibrary\View\Helper\LibraryVersionServerInfoJson',
				'libStatus' => 'DeploymentLibrary\View\Helper\LibStatus',
				'normalizeStatus' => 'DeploymentLibrary\View\Helper\NormalizeStatus',
				'normalizeUpdateUrl' => 'DeploymentLibrary\View\Helper\NormalizeUpdateUrl',
				'normalizeDefaultVersion' => 'DeploymentLibrary\View\Helper\NormalizeDefaultVersion',
				'libraryUpdateCheck' => 'DeploymentLibrary\View\Helper\LibraryUpdateCheck',
				'zgridVhostInfo' => 'Vhost\View\Helper\ZGridVhostInfo',
						)
		),
	'controllers' => array(
		'invokables' => array(
                'DeploymentLibrary' => 'DeploymentLibrary\Controller\IndexController',
                'Deployment' => 'Deployment\Controller\IndexController',
		        'DeploymentWebAPI-1_9' => 'Deployment\Controller\WebAPI19Controller',
				'DeploymentWebAPI-1_6' => 'Deployment\Controller\WebAPIController',
                'DeploymentWebAPI-1_3' => 'Deployment\Controller\WebAPIController',
                'DeploymentWebAPI-1_2' => 'Deployment\Controller\WebAPIController',
                'DeploymentLibraryWebAPI-1_5' => 'DeploymentLibrary\Controller\WebAPIController',
				'DeploymentLibraryWebAPI-1_6' => 'DeploymentLibrary\Controller\WebAPIController',
				'Vhost' => 'Vhost\Controller\IndexController',
				'VhostWebAPI-1_6' => 'Vhost\Controller\WebAPIController',
				'VhostWebAPI-1_7' => 'Vhost\Controller\WebAPIController',
            	'Wizard' => 'Deployment\Controller\WizardController',
            	'LibraryWizard' => 'DeploymentLibrary\Controller\WizardController',
				'LibraryUpdateWizard' => 'DeploymentLibrary\Controller\UpdateWizardController',
            	'DefineWizard' => 'Deployment\Controller\DefineWizardController',
		),
	),
	'view_manager' => array(
		
		'template_path_stack' => array(
				__DIR__ . '/../views',
		),
	),
    'webapi_routes' => array(
        'libraryGetStatus' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/libraryGetStatus',
                        'defaults' => array(
                                'controller' => 'DeploymentLibraryWebAPI',
                                'action'     => 'libraryGetStatus',
                                'versions'	 => array('1.5')
                        ),
                ),
        ),
        'libraryVersionCheckDependents' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/libraryVersionCheckDependents',
                        'defaults' => array(
                                'controller' => 'DeploymentLibraryWebAPI',
                                'action'     => 'libraryVersionCheckDependents',
                                'versions'	 => array('1.5')
                        ),
                ),
        ),
        'libraryCheckDependents' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/libraryCheckDependents',
                        'defaults' => array(
                                'controller' => 'DeploymentLibraryWebAPI',
                                'action'     => 'libraryCheckDependents',
                                'versions'	 => array('1.5')
                        ),
                ),
        ),
    	'librarySetDefault' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/librarySetDefault',
    					'defaults' => array(
    							'controller' => 'DeploymentLibraryWebAPI',
    							'action'     => 'librarySetDefault',
    							'versions'	 => array('1.6'),
    							'viewsmap'	 => array('1.6' => '1.5')
    					),
    			),
    	),
    	'libraryVersionGetStatus' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/libraryVersionGetStatus',
    					'defaults' => array(
    							'controller' => 'DeploymentLibraryWebAPI',
    							'action'     => 'libraryVersionGetStatus',
    							'versions'	 => array('1.5')
    					),
    			),
    	),
    	'downloadLibraryVersionFile' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/downloadLibraryVersionFile',
    					'defaults' => array(
    							'controller' => 'DeploymentLibraryWebAPI',
    							'action'     => 'downloadLibraryVersionFile',
    							'versions'	 => array('1.5')
    					),
    			),
    	),
    	'deploymentDownloadFile' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/deploymentDownloadFile',
    					'defaults' => array(
    							'controller' => 'DeploymentWebAPI',
    							'action'     => 'deploymentDownloadFile',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'deploymentDownloadFileStatus' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/deploymentDownloadFileStatus',
    					'defaults' => array(
    							'controller' => 'DeploymentWebAPI',
    							'action'     => 'deploymentDownloadFileStatus',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'libraryVersionRemove' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/libraryVersionRemove',
    					'defaults' => array(
    							'controller' => 'DeploymentLibraryWebAPI',
    							'action'     => 'libraryVersionRemove',
    							'versions'	 => array('1.5')
    					),
    			),
    	),
    	'libraryRemove' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/libraryRemove',
    					'defaults' => array(
    							'controller' => 'DeploymentLibraryWebAPI',
    							'action'     => 'libraryRemove',
    							'versions'	 => array('1.5')
    					),
    			),
    	),
    	'libraryVersionSynchronize' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/libraryVersionSynchronize',
    					'defaults' => array(
    							'controller' => 'DeploymentLibraryWebAPI',
    							'action'     => 'libraryVersionSynchronize',
    							'versions'	 => array('1.5')
    					),
    			),
    	),
    	'libraryVersionDeploy' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/libraryVersionDeploy',
    					'defaults' => array(
    							'controller' => 'DeploymentLibraryWebAPI',
    							'action'     => 'libraryVersionDeploy',
    							'versions'	 => array('1.5')
    					),
    			),
    	),
    	'vhostGetStatus' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostGetStatus',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostGetStatus',
    							'versions'	 => array('1.6', '1.7')
    					),
    			),
    	),
    	'vhostRedeploy' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostRedeploy',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostRedeploy',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostRemove' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostRemove',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostRemove',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostValidateSsl' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostValidateSsl',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostValidateSsl',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostValidateTemplate' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostValidateTemplate',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostValidateTemplate',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostAdd' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostAdd',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostAdd',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostAddSecure' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostAddSecure',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostAddSecure',
    							'versions'	 => array('1.6'),
    							'limitedos'	 => array('aix'),
    					),
    			),
    	),
    	'vhostAddSecureIbmi' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostAddSecureIbmi',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostAddSecureIbmi',
    							'versions'	 => array('1.6'),
    							'limitedos'	 => array('linux','win','darwin'),
    					),
    			),
    	),
    	'vhostEdit' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostEdit',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostEdit',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostEnableDeployment' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostEnableDeployment',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostEnableDeployment',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostDisableDeployment' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostDisableDeployment',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostDisableDeployment',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
    	'vhostGetDetails' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/vhostGetDetails',
    					'defaults' => array(
    							'controller' => 'VhostWebAPI',
    							'action'     => 'vhostGetDetails',
    							'versions'	 => array('1.6')
    					),
    			),
    	),
        'applicationGetDetails' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/applicationGetDetails',
                        'defaults' => array(
                                'controller' => 'DeploymentWebAPI',
                                'action'     => 'applicationGetDetails',
                                'versions'	 => array('1.3')
                        ),
                ),
        ),
        'redeployAllApplications' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/redeployAllApplications',
                        'defaults' => array(
                                'controller' => 'DeploymentWebAPI',
                                'action'     => 'redeployAllApplications',
                                'versions'	 => array('1.3')
                        ),
                ),
        ),
    	'applicationDefine' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/applicationDefine',
    					'defaults' => array(
    							'controller' => 'DeploymentWebAPI',
    							'action'     => 'applicationDefine',
    							'versions'	 => array('1.3')
    					),
    			),
    	),
        'deployDemoApplication' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/deployDemoApplication',
                        'defaults' => array(
                                'controller' => 'DeploymentWebAPI',
                                'action'     => 'deployDemoApplication',
                                'versions'	 => array('1.3')
                        ),
                ),
        ),
    	'applicationDeploy' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/applicationDeploy',
    					'defaults' => array(
    							'controller' => 'DeploymentWebAPI',
    							'action'     => 'applicationDeploy',
    							'versions'	 => array('1.2', '1.9'),
    							'viewsmap'   => array('1.2' => '1.3', '1.9' => '1.3')
    					),
    			),
    	),
        'applicationGetStatus' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/applicationGetStatus',
                        'defaults' => array(
                                'controller' => 'DeploymentWebAPI',
                                'action'     => 'applicationGetStatus',
                                'versions'	 => array('1.2'),
                        		'viewsmap'	=> array('1.2' => '1.3')
                        ),
                ),
        ),
    	'applicationUpdate' => array(
    		'type'    => 'Zend\Mvc\Router\Http\Literal',
    		'options' => array(
    			'route'    => '/Api/applicationUpdate',
    			'defaults' => array(
    				'controller' => 'DeploymentWebAPI',
    				'action'     => 'applicationUpdate',
    				'versions'	 => array('1.2'),
    				'viewsmap'   => array('1.2' => '1.3')
    			),
    		),
    	),
        'applicationSynchronize' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                    'route'    => '/Api/applicationSynchronize',
                    'defaults' => array(
                            'controller' => 'DeploymentWebAPI',
                            'action'     => 'applicationSynchronize',
                            'versions'	 => array('1.2'),
    						'viewsmap'   => array('1.2' => '1.3')
                    ),
            ),
        ),
        'applicationRemove' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                    'route'    => '/Api/applicationRemove',
                    'defaults' => array(
                            'controller' => 'DeploymentWebAPI',
                            'action'     => 'applicationRemove',
                            'versions'	 => array('1.2'),
                    		'viewsmap'	=> array('1.2' => '1.3')
                    ),
            ),
        ),
    	'changeApplicationName' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/changeApplicationName',
    					'defaults' => array(
    							'controller' => 'DeploymentWebAPI',
    							'action'     => 'changeApplicationName',
    							'versions'	 => array('1.3')
    					),
    			),
    	),
        'applicationRollback' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                        'route'    => '/Api/applicationRollback',
                        'defaults' => array(
                                'controller' => 'DeploymentWebAPI',
                                'action'     => 'applicationRollback',
                                'versions'	 => array('1.2'),
                    		'viewsmap'	=> array('1.2' => '1.3')
                        ),
                ),
        ),
    	'applicationCancelPendingDeployment' => array(
    			'type'    => 'Zend\Mvc\Router\Http\Literal',
    			'options' => array(
    					'route'    => '/Api/applicationCancelPendingDeployment',
    					'defaults' => array(
    							'controller' => 'DeploymentWebAPI',
    							'action'     => 'applicationCancelPendingDeployment',
    							'versions'	 => array('1.3')
    					),
    			),
    	),
    ),
);
