<?php
return array(
	'view_helpers' => array(
		'invokables' => array(
			'jobDataJson' => 'JobQueue\View\Helper\JobDataJson',
			'jobJson' => 'JobQueue\View\Helper\JobJson',
			'jobDataXml' => 'JobQueue\View\Helper\JobDataXml',
			'jobXml' => 'JobQueue\View\Helper\JobXml',
			'jobDetailsCronToHuman' => 'JobQueue\View\Helper\JobDetailsCronToHuman',
			'jobsRuleXml' => 'JobQueue\View\Helper\RuleXml',
			'jobsRuleJson' => 'JobQueue\View\Helper\RuleJson',
			'jobsRuleDataXml' => 'JobQueue\View\Helper\RuleDataXml',
			'jobsRuleDataJson' => 'JobQueue\View\Helper\RuleDataJson',
			'QueuePriority' => 'JobQueue\View\Helper\QueuePriority',
		)
	),
	'controllers' => array(
		'invokables' => array(
        	'JobQueue' => 'JobQueue\Controller\IndexController',
        	'Queues' => 'JobQueue\Controller\QueuesController',
		    'RecurringJobs' => 'JobQueue\Controller\RecurringJobsController',
			'JobQueueWebApi-1_3'  => 'JobQueue\Controller\WebAPIController',
			'JobQueueWebApi-1_6'  => 'JobQueue\Controller\WebAPIController',
			'JobQueueWebApi-1_10' => 'JobQueue\Controller\WebAPIController',
		),
	),
	'view_manager' => array(
		'template_path_stack' => array(
				__DIR__ . '/../views',
		),
	),
	'service_manager' => array(
	    'invokables' => array(
		    'queueForm' => 'JobQueue\Form\QueueForm',
		    'settingsForm' => 'JobQueue\Form\SettingsForm',
		    'settingsEventsForm' => 'JobQueue\Form\settingsEventsForm',
		    'importForm' => 'JobQueue\Form\ImportForm',
	    ),
		'aliases' => array(
			'jq_dictionary' => 'JobQueue\Filter\Dictionary',
		)
	),
	'webapi_routes' => array(
		'jobqueueStatistics' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueStatistics',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueStatistics',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueSaveRule' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueSaveRule',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueSaveRule',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueRunNowRule' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueRunNowRule',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueRunNowRule',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueAddJob' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueAddJob',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueAddJob',
								'versions'	 => array('1.6')
						),
				),
		),
		'jobqueueDeleteJobsByPredefinedFilter' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'	=> '/Api/jobqueueDeleteJobsByPredefinedFilter',
					'defaults' => array(
							'controller' => 'JobQueueWebApi',
							'action'	 => 'jobqueueDeleteJobsByPredefinedFilter',
							'versions'	 => array('1.3')
					),
				),
		),
		'jobqueueJobsList' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueJobsList',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueJobsList',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueDeleteJobs' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueDeleteJobs',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueDeleteJobs',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueRequeueJobs' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueRequeueJobs',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueRequeueJobs',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueJobInfo' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueJobInfo',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueJobInfo',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueQueueInfo' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueQueueInfo',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueQueueInfo',
								'versions'	 => array('1.10')
						),
				),
		),
		'jobqueueResumeRules' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueResumeRules',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueResumeRules',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueDisableRules' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueDisableRules',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueDisableRules',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueRulesList' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueRulesList',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueRulesList',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueRuleInfo' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueRuleInfo',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueRuleInfo',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueDeleteRules' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueDeleteRules',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueDeleteRules',
								'versions'	 => array('1.3')
						),
				),
		),
		'jobqueueGetQueues' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueGetQueues',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueGetQueues',
								'versions'	 => array('1.10')
						),
				),
		),
		'jobqueueCreateQueue' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueCreateQueue',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueCreateQueue',
								'versions'	 => array('1.10')
						),
				),
		),
		'jobqueueUpdateQueue' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueUpdateQueue',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueUpdateQueue',
								'versions'	 => array('1.10')
						),
				),
		),
		'jobqueueDeleteQueue' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueDeleteQueue',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueDeleteQueue',
								'versions'	 => array('1.10')
						),
				),
		),
		'jobqueueSuspendQueue' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueSuspendQueue',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueSuspendQueue',
								'versions'	 => array('1.10')
						),
				),
		),
		'jobqueueActivateQueue' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueActivateQueue',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueActivateQueue',
								'versions'	 => array('1.10')
						),
				),
		),
		'jobqueueExportQueuesApi' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueExportQueues',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueExportQueues',
								'versions'	 => array('1.10'),
						),
				),
		),
		'jobqueueImportQueuesApi' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueImportQueues',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueImportQueues',
								'versions'	 => array('1.10'),
						),
				),
		),
		'jobqueueUpdateSettings' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueUpdateSettings',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueUpdateSettings',
								'versions'	 => array('1.10'),
						),
				),
		),
		'jobqueueUpdateEvents' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueUpdateEvents',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueUpdateEvents',
								'versions'	 => array('1.10'),
						),
				),
		),
		'jobqueueQueueStats' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
						'route'	=> '/Api/jobqueueQueueStats',
						'defaults' => array(
								'controller' => 'JobQueueWebApi',
								'action'	 => 'jobqueueQueueStats',
								'versions'	 => array('1.10'),
						),
				),
		),
	),
);
