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

if (getZrayStandaloneLicense() == 'disabled') {
	return array(
		'navigation' => array(
			'default' => array(
				'overview' => array(
					'label' => 'Home',
					'controller' => 'GuidePage',
					'action' => 'home',
					'route' => 'home',
					'order' => 10,
					'class' => 'glyphicons white home no-menu-arrow',
					'pages' => array(
					)
				)
			)
		)
	);
}
return array(
	'navigation' => array(
		'default' => array(
			'overview' => array(
				'label' => 'Home',
				'controller' => 'GuidePage',
				'action' => 'index',
				'route' => 'home',
				'order' => 10,
				'class' => 'glyphicons white home no-menu-arrow',
				'pages' => array(
				)
			),
			'zray' => array(
				'label' => 'Z-Ray Live!',
				'controller' => 'ZrayLive',
				'route' => 'default',
				'order' => 20,
				'class' => 'glyphicons white iphone no-menu-arrow',
				'pages' => array(
				)
			),
			'history' => array(
				'label' => 'History',
				'controller' => 'ZrayHistory',
				'route' => 'default',
				'order' => 35,
				'class' => 'glyphicons white history',
			),
			'accessModes' => array(
				'label' => 'Mode',
				'controller' => 'Z-Ray',
				'action' => 'AccessMode',
				'route' => 'default',
				'order' => 30,
				'class' => 'glyphicons white keys',
				'pages' => array(),
			),
			'plugins' => array(
				'label' => 'Plugins',
				'controller' => 'Plugins',
				'route' => 'default',
				'order' => 40,
				'class' => 'glyphicons white electrical_socket_us no-menu-arrow',
				'pages' => array(
					array(
						'label' => 'Manage Plugins',
						'controller' => 'Plugins',
						'route' => 'default',
						'order' => 41,
					),
					array(
						'label' => 'Gallery',
						'controller' => 'PluginsGallery',
						'route' => 'pluginsGalleryPage',
						'order' => 42,
					),
				)
			),
			'settings' => array(
				'label' => 'Settings',
				'controller' => 'Z-Ray',
				'action' => 'Advanced',
				'route' => 'default',
				'order' => 50,
				'class' => 'glyphicons white settings no-menu-arrow',
				'pages' => array(
				)
			),
		),
	),
);