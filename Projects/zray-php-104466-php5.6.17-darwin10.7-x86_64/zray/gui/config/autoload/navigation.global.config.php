<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overridding configuration values from modules, etc.  
 * You would place values in here that are agnostic to the environment and not 
 * sensitive to security. 
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source 
 * control, so do not include passwords or other sensitive information in this 
 * file.
 */

return array(
    'navigation' => array(
    		'default' => array(
    				'guidepage' => array(
						'label' => 'Getting Started',
						'controller' => 'GuidePage',
						'route' => 'home/guidepage',
    				    'order' => 10,
						'class' => 'glyphicons white lightbulb topMenuItem',
						'pages' => array(
						)
					),
        		    'dashboard' => array(
        		        'label' => 'Dashboard',
        		        'class' => 'glyphicons white pie_chart topMenuItem',
        		        'controller' => 'Dashboard',
						'route' => 'home/dashboard',
        		        'order' => 11,
        		        'pages' => array(
        		        )
        		    ),
//         		    'ci' => array(
//         		        'label' => 'CI/CD',
//         		        'controller' => 'Ci',
//         		        'route' => 'default',
//         		        'class' => 'glyphicons white cargo no-menu-arrow',
//         		        'pages' => array(
//         		        )
//         		    ),
    				'monitoring' => array(
    						'label' => 'Monitoring',
    						'route' => 'home',
    				        'order' => 20,
    						'class' => 'glyphicons white dashboard',
    						'pages' => array(
									array(
										'label' => 'URL Insight',
										'controller' => 'UrlInsight',
										'route' => 'default',
									    'order' => 21,
									),
									array(
										'label' => 'Events',
										'controller' => 'IssueList',
										'route' => 'default',
									    'order' => 22,
									),
    								array(
    									'label' => 'Event Rules',
    									'controller' => 'MonitorRules', 
    									'route' => 'default',
                                        'order' => 23,
    								),
        						    array(
        						        'label' => 'Logs',
        						        'controller' => 'Logs',
        						        'route' => 'default',
        						        'order' => 24,
        						    ),
    								array(
    									'label' => 'Settings',
    									'controller' => 'ZendMonitor', 
    									'route' => 'default',
    								    'order' => 25,
    								),
							)
						),
        		    'zray' => array(
        		        'label' => 'Z-Ray',
        		        'controller' => 'ZrayLive',
        		        'route' => 'default',
        		        'order' => 30,
        		        'class' => 'glyphicons white iphone no-menu-arrow',
        		        'pages' => array(
        		            array(
        		                'label' => 'Z-Ray Live!',
        		                'controller' => 'ZrayLive',
        		                'route' => 'default',
        		                'order' => 31,
        		            ),
        		            array(
        		                'label' => 'Mode',
        		                'controller' => 'Z-Ray',
        		                'action' => 'AccessMode',
        		                'route' => 'default',
        		                'order' => 32,
        		            ),
        		            array(
        		                'label' => 'Gallery',
        		                'controller' => 'Z-Ray',
        		                'action' => 'Gallery',
        		                'route' => 'default',
        		                'order' => 33,
        		            ),
        		            array(
        		                'label' => 'History',
        		                'controller' => 'ZrayHistory',
        		                'route' => 'default',
        		                'order' => 34,
        		            ),
        		        	array(
        		        		'label' => 'Settings',
        		        		'controller' => 'Z-Ray',
        		        		'action' => 'Settings',
        		        		'route' => 'default',
        		        		'order' => 35,
        		        	),
        		        )
        		    ),
					'php' => array(
    						'label' => 'PHP',
    						'route' => 'home',
					        'order' => 40,
    						'class' => 'glyphicons white cogwheels',
    						'pages' => array(
    								array(
    									'label' => 'Extensions',
    									'controller' => 'Extensions', 
    									'action' => 'phpExtensions', 
    									'route' => 'default',
    								    'order' => 41,
    								),
									array(
										'label' => 'phpinfo()',
										'controller' => 'ServerInfo', 
										'route' => 'default',
									    'order' => 42,
									),
							)
						),
    				'applications' => array(
    						'label' => 'Applications',
    						'controller' => 'Deployment',
    						'route' => 'default',
    				        'order' => 50,
    						'class' => 'glyphicons white show_thumbnails',
    						'pages' => array(
    								array(
    									'label' => 'Manage Apps',
    									'controller' => 'Deployment', 
    									'route' => 'default',
    								    'order' => 51,
    								),
    								array(
    									'label' => 'Virtual Hosts',
    									'controller' => 'Vhost',
    									'action' => 'index',
    									'route' => 'default',
    								    'order' => 52,
    								),
    								array(
    									'label' => 'Libraries',
    									'controller' => 'DeploymentLibrary',
    									'route' => 'default',
    								    'order' => 53,
    								),
    						),
    				),
    				'servers' => array(
    						'label' => 'Servers',
    						'route' => 'home',
    				        'order' => 60,
    						'class' => 'glyphicons white server',
    						'pages' => array(
								array(
									'label' => 'Manage Servers',
									'controller' => 'Servers', 
									'route' => 'default',
								    'order' => 61,
								),
								array(
									'label' => 'Session Clustering',
									'controller' => 'SessionClustering', 
									'route' => 'default',
								    'order' => 62,
								),
    						)
    				),
    				'performance' => array(
    						'label' => 'Job Queue',
    						'route' => 'home',
    				        'order' => 70,
    						'class' => 'glyphicons white sort',
    						'pages' => array(
    							array(
	    							'label' => 'Jobs',
	    							'controller' => 'JobQueue',
	    							'action' => 'index',
	    							'route' => 'default',
    							    'order' => 71,
    							),
								array(
									'label' => 'Queues',
									'controller' => 'Queues',
									'route' => 'default',
								    'order' => 72,
								),
								array(
									'label' => 'Recurring Jobs',
									'controller' => 'RecurringJobs',
									'route' => 'default',
								    'order' => 73,
								),
								array(
									'label' => 'Settings',
									'controller' => 'JobQueue',
							        'action' => 'settings',
									'route' => 'default',
								    'order' => 74,
								),
    						)
    				),
    				'caching' => array(
    						'label' => 'Caching',
    						'route' => 'home',
    				        'order' => 80,
    						'class' => 'glyphicons white globe',
    						'pages' => array(
								array(
									'label' => 'Page Cache',
									'controller' => 'PageCache', 
									'route' => 'default',
								    'order' => 81,
								),
    						)
    				),
    				'debugging' => array(
    						'label' => 'Debugging',
    						'route' => 'home',
    				        'order' => 90,
    						'class' => 'glyphicons white bug',
    						'pages' => array(
								array(
									'label' => 'Debugger',
									'controller' => 'IDEIntegration',
									'route' => 'default',
								    'order' => 91,
								),
    							array(
	    							'label' => 'Code Tracing',
	    							'controller' => 'CodeTracing',
	    							'route' => 'default',
    							    'order' => 92,
    							),
    						),
    				),
        		    'plugins' => array(
        		        'label' => 'Plugins',
        		        'controller' => 'Plugins',
        		        'route' => 'default',
        		        'order' => 100,
        		        'class' => 'glyphicons white electrical_socket_us no-menu-arrow',
        		        'pages' => array(
        		            array(
        		                'label' => 'Manage Plugins',
        		                'controller' => 'Plugins',
        		                'route' => 'default',
        		                'order' => 101,
        		            ),
        		            array(
        		                'label' => 'Gallery',
        		                'controller' => 'PluginsGallery',
        		                'route' => 'pluginsGalleryPage',
        		                'order' => 102,
        		            ),
        		        )
        		    ),
    				'administration' => array(
    						'label' => 'Administration',
    						'controller' => 'ZendComponents',
    						'route' => 'default',
    				        'order' => 110,
    						'class' => 'glyphicons white user',
    						'pages' => array(
		    						array(
		    							'label' => 'Components',
		    							'controller' => 'ZendComponents',
		    							'route' => 'default',
		    						    'order' => 111,
		    						),
		    						array(
		    							'label' => 'Audit Trail',
		    							'controller' => 'Audit',
		    							'route' => 'default',
		    						    'order' => 112,
		    						),
    								array(
    									'label' => 'Users',
    									'controller' => 'Users', 
    									'route' => 'default',
    								    'order' => 113,
    								),
    								array(
    									'label' => 'Web API Keys',
    									'controller' => 'ApiKeys', 
    									'route' => 'default',
    								    'order' => 114,
    								),
    								array(
    									'label' => 'License',
    									'controller' => 'License', 
    									'route' => 'default',
    								    'order' => 115,
    								),
    								array(
    									'label' => 'Import/Export',
    									'controller' => 'ImportExport', 
    									'route' => 'default',
    								    'order' => 116,
    								),
    								array(
    									'label' => 'Settings',
    									'controller' => 'Settings',
    									'route' => 'default',
    								    'order' => 117,
    								),
    						)
    				),
        		    
    		),
    ),
    
);