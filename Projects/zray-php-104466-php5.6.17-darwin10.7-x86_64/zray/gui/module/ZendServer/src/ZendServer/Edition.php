<?php

namespace ZendServer;

use ZendServer\Ini\IniReader,
	Application\Module as appModule;

class Edition {
	
	/**
	 * @return boolean
	 */
	public function isClusterManager() {
		return appModule::config('package', 'edition') == 'cm';
	}
	
	/**
	 * @return boolean
	 */
	public function isSingleServer() {
	    if (isAzureEnv() || isZrayStandaloneEnv()) {
	        return true;
	    }
	    
		$globalDirectives = $this->getUIGlobalDirectives();
		// in the single zend server edition the zend.node_id=0
		return ( 	appModule::config('package', 'edition') == 'zs' && 
					isset($globalDirectives['zend.node_id']) &&
					!$globalDirectives['zend.node_id']);
	}
	
	/**
	 * @return boolean
	 */
	public function isClusterServer() {
	    if (isAzureEnv() || isZrayStandaloneEnv()) {
	        return false;
	    }
	    
		$globalDirectives = $this->getUIGlobalDirectives();
		return ( 	appModule::config('package', 'zend_gui', 'edition') == 'zs' && 
					isset($globalDirectives['zend.node_id']) &&
					$globalDirectives['zend.node_id']);
	}
	
	/**
	 * @return boolean
	 */
	public function isCluster() {
		return $this->isClusterServer() || $this->isClusterManager();
	}
	
	/**
	 * @return integer
	 */
	public function getServerId() {
		$globalDirectives = $this->getUIGlobalDirectives();
		if (isset($globalDirectives['zend.node_id'])) {
			return intval($globalDirectives['zend.node_id']);
		}
		
		return 0;
	}
	
	/**
	 * @return array
	 */
	private function getUIGlobalDirectives() {
		$config = new IniReader();
		$globalDirectivesFile = \ZendServer\FS\FS::getGlobalDirectivesFile();
		$globalDirectives = $config->fromFile($globalDirectivesFile, false); // flat reading, important for windows
		return $globalDirectives;
	}
	
}