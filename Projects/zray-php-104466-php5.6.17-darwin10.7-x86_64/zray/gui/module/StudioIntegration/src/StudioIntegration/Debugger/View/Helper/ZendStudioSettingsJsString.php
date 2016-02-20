<?php

namespace StudioIntegration\Debugger\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ZendStudioSettingsJsString extends AbstractHelper {
    
    public function __invoke(\StudioIntegration\Configuration $ideConfig) {
        $jsString = 'window.zendStudioSettings = {' .
    		'"debug_port":' . $ideConfig->getPort() . ',' .
    		'"use_ssl":' . intval($ideConfig->getSsl())  . ',' .
    		'"debug_host":"' . $ideConfig->getCurrentHost() . '",' .
    		'"debug_fastfile":1,' .
    		'"use_tunneling":0' .
		'};
        ';
        
        return $jsString;
    }
    
}