<?php

namespace ZendServer\Ini;

use ZendServer\Log\Log;

class IniWriter extends \Zend\Config\Writer\Ini {
	
	/**
	 * Allows updating a group of directives which belong to the same section (or have no section all together) 
	 * 
	 * @param string $iniPath
	 * @param array $directives
	 * @param string $section
	 * @throws Exception
	 */
	public function updateDirectives($iniPath, array $directives, $section=null) {
		$config = array();
		
		try {
			$reader = new IniReader();
			$config = $reader->fromFile($iniPath, ($section !== null)); // will process section is section was passed
		} catch (\Exception $e) {
			Log::debug("Ini::fromFile() failed - '$iniPath' is still empty?");
		}
		 
		try {
			if ($section) {
				if (!isset($config[$section])) {
					$config[$section] = array();
				}
				$config[$section] = $directives + $config[$section];
			}else {
				$config = $directives + $config;
			}

			$this->toFile($iniPath, $config);
		} catch (\Exception $e) {
			log::err("failed to update '$iniPath' - bad permissions?: " . $e->getMessage());
			throw $e;
		}	

		Log::debug("updated file '$iniPath' in section '$section' with directives: " . print_r($directives, true));
	}	

	/**
	 * we assume that either the file has no sections, or has a single section which all directives belong to
	 * 
	 * @param string $iniPath
	 * @param array $directives
	 */
	public function updateZendDirectives($iniPath, array $directives) {
		$reader = new IniReader();
		$zendDirectives = $reader->fromFile($iniPath, false);
		$zendDirectivesSectioned = $reader->fromFile($iniPath);
	
		/// if the keys are completely different, this is probably a section
		$isSectioned = (! array_intersect_key($zendDirectives, $zendDirectivesSectioned));
	
		foreach ($directives as $name => $directive) {
			$zendDirectives[$name] = $directive;
		}
	
		if ($isSectioned) {
			$zendDirectivesSectioned[key($zendDirectivesSectioned)] = $zendDirectives;
		} else {
			$zendDirectivesSectioned = $zendDirectives;
		}
	
		$this->toFile($iniPath, $zendDirectivesSectioned);
		Log::info("Stored db configuration directives in {$iniPath}");
	}
	
}