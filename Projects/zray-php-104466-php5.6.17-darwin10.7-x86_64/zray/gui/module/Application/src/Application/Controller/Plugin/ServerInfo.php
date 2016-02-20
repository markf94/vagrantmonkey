<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Application\Module;

class ServerInfo extends AbstractPlugin {
    
    public function get() {
        $serverInfo = array(
            'zs' 		 => Module::config('package', 'version'),
            'php'		 => phpversion(),
            'os'		 => \ZendServer\FS\FS::getOSAsString(),
            'arch'		 => php_uname('m'),
            'serverMode' => Module::config('package', 'zend_gui', 'serverProfile'),
            'api'        => \DevBar\ZRayModule::PLUGIN_CURRENT_VERSION,
            'uniqueId'	 => Module::config('license', 'zend_gui', 'uniqueId')
        );
        
        if (strtolower($serverInfo['os']) == 'linux') {
            $serverInfo['os'] = $this->getLinuxDistro();
            if (empty($serverInfo['os'])) {
                $serverInfo['os'] = 'Linux';
            }
        }
        
        return $serverInfo;
    }
    
    /**
     * Return the linux distro name
     * @return mixed
     */
    private function getLinuxDistro() {
        exec('less /etc/issue', $output);
        if (count($output) > 0) {
    
            $distros = array(
                'Ubuntu' => 'Ubuntu',
                'Fedora' => 'Fedora',
                'OEL'	 => 'Oracle',
                'RHEL'	 => 'Red Hat',
                'OpenSUSE'	=> 'openSUSE',
                'SUSE'	 => 'SUSE',
                'Debian' => 'Debian',
                'CentOS' => 'CentOS',
            );
    
            foreach ($distros as $distro => $keyword) {
                foreach ($output as $outputRow) {
                    if (strpos($outputRow, $keyword) !== false) {
                        return $distro;
                    }
                }
            }
        }
    
        return '';
    }
}