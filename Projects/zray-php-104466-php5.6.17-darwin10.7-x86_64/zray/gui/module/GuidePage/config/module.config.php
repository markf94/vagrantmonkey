<?php
return array(
	'controllers' => array(
		'invokables' => array(
        	'GuidePage' => 'GuidePage\Controller\IndexController',
		),
	),
	'view_manager' => array(
		'template_path_stack' => array(
				__DIR__ . '/../views',
		),
	),
);
