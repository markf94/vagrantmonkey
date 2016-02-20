<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper,
Deployment\Application,
ZendServer\Log\Log,
Deployment\Model;

class AppHealthCheckStatus extends AbstractHelper {
    public function __invoke($status) {
        switch ($status) {
    
            case Model::HEALTH_OK:
                return 'ok';
    
            case Model::HEALTH_ERROR:
                return 'error';
    
            default:
                Log::notice('Invalid status ' . $status);
                return 'unknown';
        }
    }
}