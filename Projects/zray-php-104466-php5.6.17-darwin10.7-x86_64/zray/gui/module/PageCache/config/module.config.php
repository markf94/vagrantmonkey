<?php
return array(
		'view_helpers' => array(
				'invokables' => array(
				'cacheRuleXml' => 'PageCache\View\Helper\RuleXml',
				'cacheRuleJson' => 'PageCache\View\Helper\RuleJson',
				'cacheRuleDataXml' => 'PageCache\View\Helper\RuleDataXml',
				'cacheRuleDataJson' => 'PageCache\View\Helper\RuleDataJson',
						)
		),
	'controllers' => array(
		'invokables' => array(
        	'PageCache' => 'PageCache\Controller\IndexController',
        	'PageCacheEditRule' => 'PageCache\Controller\EditController',
		    'PageCacheWebApi-1_3' => 'PageCache\Controller\WebAPI13Controller',
			'PageCacheWebApi-1_4' => 'PageCache\Controller\WebAPIController',
		),
	),
	'view_manager' => array(
		
		'template_path_stack' => array(
				__DIR__ . '/../views',
		),
	),
	'webapi_routes' => array(
			'pagecacheRulesList' => array(
 					'type'	=> 'Zend\Mvc\Router\Http\Literal',
 					'options' => array(
 							'route'	=> '/Api/pagecacheRulesList',
 							'defaults' => array(
 									'controller' => 'PageCacheWebApi',
 									'action'	 => 'pagecacheRulesList',
 									'versions'	 => array('1.3')
 							),
 					),
 			),
			'pagecacheDeleteRules' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheDeleteRules',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheDeleteRules',
									'versions'	 => array('1.3')
							),
					),
			),
			'pagecacheClearRulesCache' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheClearRulesCache',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheClearRulesCache',
									'versions'	 => array('1.4')
							),
					),
			),
			'pagecacheRuleInfo' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheRuleInfo',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheRuleInfo',
									'versions'	 => array('1.3')
							),
					),
			),
			'pagecacheSaveRule' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheSaveRule',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheSaveRule',
									'versions'	 => array('1.3')
							),
					),
			),
			'pagecacheSaveApplicationRule' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheSaveApplicationRule',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheSaveApplicationRule',
									'versions'	 => array('1.3')
							),
					),
			),
			'pagecacheDeleteRulesByApplicationId' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheDeleteRulesByApplicationId',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheDeleteRulesByApplicationId',
									'versions'	 => array('1.3')
							),
					),
			),
			'pagecacheClearCacheByRuleName' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheClearCacheByRuleName',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheClearCacheByRuleName',
									'versions'	 => array('1.3')
							),
					),
			),
			'pagecacheExportRules' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheExportRules',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheExportRules',
									'versions'	 => array('1.3'),
									'output' 	 => array('xml')
							),
					),
			),
			'pagecacheImportRules' => array(
					'type'	=> 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
							'route'	=> '/Api/pagecacheImportRules',
							'defaults' => array(
									'controller' => 'PageCacheWebApi',
									'action'	 => 'pagecacheImportRules',
									'versions'	 => array('1.3')
							),
					),
			),			
			
		),
);
