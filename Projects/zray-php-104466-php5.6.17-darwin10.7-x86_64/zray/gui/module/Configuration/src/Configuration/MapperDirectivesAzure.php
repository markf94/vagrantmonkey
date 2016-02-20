<?php

namespace Configuration;
use ZendServer\Set;

class MapperDirectivesAzure extends MapperDirectivesStandalone {
    
    /**
     * Collect all directives from the different ini files in AZURE folder
     */
    public function __construct() {
        $confDir = getCfgVar('zend.conf_dir');
        $scanDir =  getCfgVar('zend.ini_scandir');
        
        $this->iniFiles = array(
            'zs_ui.ini'         => $confDir . DIRECTORY_SEPARATOR . 'zs_ui.ini',
            'php.ini'           => $confDir . DIRECTORY_SEPARATOR . 'php.ini',
            'zend_database.ini' => $confDir . DIRECTORY_SEPARATOR . 'zend_database.ini',
            'zray.ini'          => $confDir . DIRECTORY_SEPARATOR . $scanDir . DIRECTORY_SEPARATOR . 'zray.ini',
        );
        
        $directives = array();
        $files = array();
        foreach ($this->iniFiles as $iniKey => $iniFile) {
            if (file_exists($iniFile)) {
                $parsed = parse_ini_file($iniFile);
                foreach ($parsed as $key => $directive) {
                    $directives[$key] = $directive;
                    $files[$key] = $iniKey;
                }
            }
        }
        
        $this->directives = $directives;
        $this->fileMapper = $files;
    }

}
