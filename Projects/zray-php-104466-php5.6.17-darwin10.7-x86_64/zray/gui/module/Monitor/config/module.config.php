<?php
return array(
	'allowedWebAPIActions' => array(
			'EventsGroupWebApi' => array('monitorGetEventGroupDetails'),
			'IssueWebApi' => array('monitorGetIssueDetails','monitorGetIssuesByPredefinedFilter', 'monitorGetIssuesListPredefinedFilter'),
	),
	'dependencies' => array(
		'extensions' => array(
// 			'Zend Monitor UI' => array(),
		),
	),
		'view_helpers' => array(
			'invokables' => array(
				'issueSeverity' => 'Issue\View\Helper\IssueSeverity',
				'issueStatus' => 'Issue\View\Helper\IssueStatus',
				'issueXml' => 'Issue\View\Helper\IssueXml',
				'issueJson' => 'Issue\View\Helper\IssueJson',
				'eventsGroupXml' => 'EventsGroup\View\Helper\EventsGroupXml',
				'eventsGroupJson' => 'EventsGroup\View\Helper\EventsGroupJson',
				'eventsGroupDataXml' => 'EventsGroup\View\Helper\EventsGroupDataXml',
				'eventsGroupDataJson' => 'EventsGroup\View\Helper\EventsGroupDataJson',
				'variablesTree' => 'EventsGroup\View\Helper\VariablesTree',
				'selectAll' => 'EventsGroup\View\Helper\SelectAll',
				'issuesCount' 	=> 'Issue\View\Helper\IssuesCount',
				'ruleJson' => 'MonitorRules\View\Helper\RuleJson',
				'ruleXml' => 'MonitorRules\View\Helper\RuleXml',
				'ZGridMonitorRules' => 'MonitorRules\View\Helper\ZGridMonitorRules',
				'EventTracingMode' => 'MonitorRules\View\Helper\EventTracingMode',
			)
		),
	'view_manager' => array(
		
		'template_path_stack' => array(
			__DIR__ . '/../views',
		),
	),
	'controllers' => array(
		'invokables' => array(
			'IssueWebApi-1_3' => 'Issue\Controller\WebAPIController',
			'IssueWebApi-1_2' => 'Issue\Controller\WebAPI12Controller',
			'IssueList' => 'Issue\Controller\ListController',
			'Issue' 	=> 'Issue\Controller\IssueController',
			'EventsGroup' => 'EventsGroup\Controller\EventsGroupController',
			'EventsGroupWebApi-1_3' => 'EventsGroup\Controller\WebAPIController',
			'EventsGroupWebApi-1_2' => 'EventsGroup\Controller\WebAPIController',
			'MonitorRulesWebApi-1_3' => 'MonitorRules\Controller\WebAPIController',
			'MonitorRulesWebApi-1_7' => 'MonitorRules\Controller\WebAPI17Controller',
			'MonitorRules' => 'MonitorRules\Controller\IndexController',
			'MonitorEditRule' => 'MonitorRules\Controller\EditController',
				
		),
		'map' => array(
		)
	),
	'webapi_routes' => array(
		'monitorGetBacktraceFile' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorGetBacktraceFile',
						'defaults' => array(
								'controller' => 'EventsGroupWebApi',
								'action'	 => 'monitorGetBacktraceFile',
								'versions'	 => array('1.3')
						),
				),
		),
		'monitorCountIssuesByPredefinedFilter' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorCountIssuesByPredefinedFilter',
				'defaults' => array(
					'controller' => 'IssueWebApi',
					'action'	 => 'monitorCountIssuesByPredefinedFilter',
					'versions'	 => array('1.3'),
				),
			),
		),
		'monitorGetIssuesByPredefinedFilter' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorGetIssuesByPredefinedFilter',
				'defaults' => array(
					'controller' => 'IssueWebApi',
					'action'	 => 'monitorGetIssuesByPredefinedFilter',
					'versions'	 => array('1.2'),
					'viewsmap'	=> array('1.2' => '1.3')
				),
			),
		),
		'monitorGetIssuesListPredefinedFilter' => array( // for backward compatability
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorGetIssuesListPredefinedFilter',
						'defaults' => array(
								'controller' => 'IssueWebApi',
								'action'	 => 'monitorGetIssuesByPredefinedFilter',
								'versions'	 => array('1.2'),
								'viewsmap'	=> array('1.2' => '1.3')
						),
				),
		),
		'monitorGetIssueDetails' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorGetIssueDetails',
				'defaults' => array(
					'controller' => 'IssueWebApi',
					'action'	 => 'monitorGetIssueDetails',
					'versions'	 => array('1.2', '1.3'),
					'viewsmap'	=> array('1.2' => '1.3')
				),
			),
		),
		'monitorGetEventGroupDetails' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorGetEventGroupDetails',
				'defaults' => array(
					'controller' => 'EventsGroupWebApi',
					'action'	 => 'monitorGetEventGroupDetails',
					'versions'	 => array('1.2'),
					'viewsmap'	=> array('1.2' => '1.3')
				),
			),
		),
		'monitorGetRulesList' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorGetRulesList',
				'defaults' => array(
					'controller' => 'MonitorRulesWebApi',
					'action'	 => 'monitorGetRulesList',
					'versions'	 => array('1.3')
				),
			),
		),
		'monitorEnableRules' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorEnableRules',
				'defaults' => array(
					'controller' => 'MonitorRulesWebApi',
					'action'	 => 'monitorEnableRules',
					'versions'	 => array('1.3')
				),
			),
		),
		'monitorDisableRules' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorDisableRules',
				'defaults' => array(
					'controller' => 'MonitorRulesWebApi',
					'action'	 => 'monitorDisableRules',
					'versions'	 => array('1.3')
				),
			),
		),
		'monitorSetRule' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorSetRule',
				'defaults' => array(
					'controller' => 'MonitorRulesWebApi',
					'action'	 => 'monitorSetRule',
					'versions'	 => array('1.3')
				),
			),
		),
		'monitorSetRuleUpdated' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorSetRuleUpdated',
						'defaults' => array(
								'controller' => 'MonitorRulesWebApi',
								'action'	 => 'monitorSetRuleUpdated',
								'versions'	 => array('1.3')
						),
				),
		),
		'monitorRemoveRules' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorRemoveRules',
				'defaults' => array(
					'controller' => 'MonitorRulesWebApi',
					'action'	 => 'monitorRemoveRules',
					'versions'	 => array('1.3')
				),
			),
		),
		'monitorExportRules' => array(
			'type'	=> 'Zend\Mvc\Router\Http\Literal',
			'options' => array(
				'route'	=> '/Api/monitorExportRules',
				'defaults' => array(
					'controller' => 'MonitorRulesWebApi',
					'action'	 => 'monitorExportRules',
					'versions'	 => array('1.3', '1.7'),
					'output'	=> array('xml'),
					'viewsmap' => array('1.7' => '1.3')
				),
			),
		),
		'monitorImportRules' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorImportRules',
						'defaults' => array(
								'controller' => 'MonitorRulesWebApi',
								'action'	 => 'monitorImportRules',
								'versions'	 => array('1.3')
						),
				),
		),			
		'monitorDeleteIssues' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorDeleteIssues',
						'defaults' => array(
								'controller' => 'IssueWebApi',
								'action'	 => 'monitorDeleteIssues',
								'versions'	 => array('1.3')
						),
				),
		),
		'monitorDeleteIssuesByPredefinedFilter' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorDeleteIssuesByPredefinedFilter',
						'defaults' => array(
								'controller' => 'IssueWebApi',
								'action'	 => 'monitorDeleteIssuesByPredefinedFilter',
								'versions'	 => array('1.3')
						),
				),
		),
		'monitorGetIssueEventGroups' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorGetIssueEventGroups',
						'defaults' => array(
								'controller' => 'IssueWebApi',
								'action'	 => 'monitorGetIssueEventGroups',
								'versions'	 => array('1.3')
						),
				),
		),
		'monitorChangeIssueStatus' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/monitorChangeIssueStatus',
						'defaults' => array(
								'controller' => 'IssueWebApi',
								'action'	 => 'monitorChangeIssueStatus',
								'versions'	 => array('1.2', '1.3'),
								'viewsmap'	=> array('1.2' => '1.3')
						),
				),
		),
	),
);
