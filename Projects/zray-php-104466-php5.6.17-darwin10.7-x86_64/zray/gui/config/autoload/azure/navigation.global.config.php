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

if (getAzureLicense() == 'disabled') {
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
            'accessModes' => array(
                'label' => 'Mode',
                'controller' => 'Z-Ray',
                'action' => 'Settings',
                'route' => 'default',
                'order' => 30,
                'class' => 'glyphicons white keys',
                'pages' => array(
                    array(
                        'label' => 'Mode',
                        'controller' => 'Z-Ray',
                        'action' => 'Mode',
                        'route' => 'default',
                        'order' => 31,
                    ),
                    array(
                        'label' => 'Tokens',
                        'controller' => 'Z-Ray',
                        'action' => 'Tokens',
                        'route' => 'default',
                        'order' => 32,
                    ),
                )
            ),
            'settings' => array(
                'label' => 'Settings',
                'controller' => 'Z-Ray',
                'action' => 'Advanced',
                'route' => 'default',
                'order' => 40,
                'class' => 'glyphicons white settings no-menu-arrow',
                'pages' => array(
                )
            ),
        ),
    ),
);