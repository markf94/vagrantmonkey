<?php
namespace ZfServerUrl;

class Module
{
    public function getConfig()
    {
        return array(
            'view_helpers' => array(
                'invokables' => array(
                    'serverurl' => 'ZfServerUrl\ServerUrl',
                ),
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(array(
                __NAMESPACE__ . '\ServerUrl' => __DIR__ . '/ServerUrl.php',
            )),
        );
    }
}
