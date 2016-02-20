<?php

namespace Servers\Configuration;

use ZendServer\FS\FS;
use ZendServer\Ini\IniReader;
use ZendServer\Exception;
use ZendServer\Configuration\Manager;

class Mapper {
	
	public function getServerNodeId() {
		$configIni = new IniReader();
		$globalDirectivesFile = FS::getGlobalDirectivesFile();
		$globalDirectives = $configIni->fromFile($globalDirectivesFile, false); // no sections
		
		if (! isset($globalDirectives['zend.node_id'])) {
			throw new Exception(_t("Parameter 'node_id' not found"));
		}
		return $globalDirectives['zend.node_id'];
	}
	
	public function isClusterSupport() {
		$manager = new Manager();
		return ($manager->getOsType() != Manager::OS_TYPE_IBMI && $manager->getOsType() != Manager::OS_TYPE_MAC);
	}
	
}

