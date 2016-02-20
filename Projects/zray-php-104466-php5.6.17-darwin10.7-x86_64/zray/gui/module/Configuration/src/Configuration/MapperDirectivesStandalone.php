<?php

namespace Configuration;
use ZendServer\Set;
use ZendServer\Log\Log;

class MapperDirectivesStandalone extends MapperDirectives {
	
	private $directives = array(); 
	
	private $fileMapper = array();
	
	private $iniFiles = array();
	
	/**
	 * Collect all directives from the different ini files
	 */
	public function __construct() {
		$installDir = getCfgVar('zend.install_dir');
		$confDir = getCfgVar('zend.conf_dir');
		$scanDir =  getCfgVar('zend.ini_scandir');
		
		$this->iniFiles = array(
			'zs_ui.ini'			=> $scanDir . DIRECTORY_SEPARATOR . 'zs_ui.ini',
			'php.ini'			=> php_ini_loaded_file(),
			'zend_database.ini'	=> $scanDir . DIRECTORY_SEPARATOR . 'zend_database.ini',
			'zray.ini'			=> $scanDir . DIRECTORY_SEPARATOR . 'conf.d' . DIRECTORY_SEPARATOR . 'zray.ini',
			'package.ini'		=> $installDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'packaging.ini',
			'deployment.ini'	=> $scanDir . DIRECTORY_SEPARATOR . 'deployment.ini',
		);
		
		$directives = array();
		$files = array();
		foreach ($this->iniFiles as $iniKey => $iniFile) {
			if (file_exists($iniFile) && is_readable($iniFile)) {
				$parsed = parse_ini_file($iniFile);
				foreach ($parsed as $key => $directive) {
					$directives[$key] = $directive;
					$files[$key] = $iniKey;
				}
			} else {
				Log::warn(_t("INI file %s does not exist or is not accessible", array($iniFile)));
			}
		}
		
		$this->directives = $directives;
		$this->fileMapper = $files;
	}
	
	/**
	 * @param string $directive directive name
	 * @return boolean
	 */
	public function directiveExists($directive) {
		return isset($this->directives[$directive]);
	}
	
	/**
	 * @see \Configuration\MapperDirectives::setDirectives()
	 */
	public function setDirectives($newDirectives) {
		$filesToBeChanged = array();
		foreach ($newDirectives as $directive => $value) {
			if (isset($this->fileMapper[$directive])) {
				$iniKey = $this->fileMapper[$directive];
				$filesToBeChanged[$iniKey] = parse_ini_file($this->iniFiles[$iniKey], true);
			}
		}
		
		// store new directives
		foreach ($filesToBeChanged as $filename => $parsed) {
			foreach ($parsed as $ns => $directives) {
				foreach ($directives as $key => $value) {
					if (isset($newDirectives[$key])) {
						$filesToBeChanged[$filename][$ns][$key] = $newDirectives[$key];
					}
				} 
			}
		}
		
		// save the files
		foreach ($filesToBeChanged as $filename => $parsed) {
			$this->writePhpIni($parsed,  $this->iniFiles[$filename]);
		}
	}
	
	/**
	 *
	 * @return array('user_name', 'serial_number
	 */
	public function getLicenseDetails(){
		$userNameArray = $this->directivesToSet(array('zend.user_name'));
		$serialNumberArray = $this->directivesToSet(array('zend.serial_number'));
		
		if (isset($userNameArray[0]) && isset($serialNumberArray[0])){
			return array('user_name' => $userNameArray[0]['DISK_VALUE'],'serial_number' => $serialNumberArray[0]['DISK_VALUE']);
		} else {
			return array();
		}
	}
	
	/**
	 * param array $directivesNames
	 * @return array
	 */
	public function getDirectivesValues(array $directivesNames) {
		$values = array();
		foreach ($directivesNames as $directivesName) {
			if (isset($this->directives[$directivesName])) {
				$values[$directivesName] = $this->directives[$directivesName];
			}
		}
		 
		return $values;
	}
	
	/**
	 * @see \Configuration\MapperDirectives::getDirective()
	 */
	public function getDirective($directiveName) {
		$resultSet = $this->directivesToSet(array($directiveName));
		$directiveContainer = $resultSet[0]; /* @var $directiveContainer \Configuration\DirectiveContainer */
	
		return $directiveContainer;
	}
	
	/**
	 * @see \Configuration\MapperDirectives::getDirectiveMemoryValue()
	 */
	public function getDirectiveMemoryValue($directiveName, $clean = false) {
		$resultSet = $this->directivesToSet(array($directiveName));
		
		// @todo add protection for case where no directive of that name is found
		$directiveContainer = $resultSet[0]; /* @var $directiveContainer \Configuration\DirectiveContainer */
		if ($clean && $directiveContainer->getType() == DirectiveContainer::TYPE_STRING) {
			return preg_replace('#^"(.+)"$#', '$1', $directiveContainer->getFileValue());
		}
		return $directiveContainer->getDefaultValue();
	}
	
	/**
	 * @see \Configuration\MapperDirectives::getDirectiveValue()
	 */
	public function getDirectiveValue($directiveName, $clean = false) {
		$resultSet = $this->directivesToSet(array($directiveName));
		if ($resultSet->count() == 0) {
			return false;
		}
		
		// @todo add protection for case where no directive of that name is found
		/* @var \Configuration\DirectiveContainer */
		$directiveContainer = $resultSet[0]; 
		if ($clean && $directiveContainer->getType() == DirectiveContainer::TYPE_STRING) {
			return preg_replace('#^"(.+)"$#', '$1', $directiveContainer->getFileValue());
		}
		
		return $directiveContainer->getFileValue();
	}
	
	/**
	 * @see \Configuration\MapperDirectives::selectSpecificDirectives()
	 */
	public function selectSpecificDirectives(array $directives) {
		return $this->directivesToSet($directives);
	}
	
	/**
	 * Return a set of directives objects
	 * @param array $directives
	 * @return \ZendServer\Set
	 */
	private function directivesToSet(array $directives = array()) {
		$directivesArray = array();
		foreach ($directives as $directive) {
			if (isset($this->directives[$directive])) {
				$directivesArray[] = array(
					'NAME'          => $directive,
					'DISK_VALUE'    => $this->directives[$directive],
					'MEMORY_VALUE'  => $this->directives[$directive],
					'EXTENSION'     => true,
					'DAEMON'        => '',
				);
			}
		}
		
		return new Set($directivesArray, $this->setClass);
	}
	
	private function writePhpIni($array, $file) {
		$res = array();
		$firstSection = true;
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				if ($firstSection) {
					$res[] = "[$key]";
					$firstSection = false;
				} else {
					$res[] = "";
					$res[] = "[$key]";
				}
				foreach ($val as $skey => $sval) {
					$res[] = "$skey = " . (is_numeric($sval) ? $sval : '"' . $sval . '"');
				}
			} else
				$res[] = "$key = " . (is_numeric($val) ? $val : '"' . $val . '"');
		}
		$this->safeFilereWrite($file, implode("\r\n", $res));
	}
	
	private function safeFilereWrite($fileName, $dataToSave) {
		if ($fp = fopen($fileName, 'w')) {
			$startTime = microtime();
			do {
				$canWrite = flock($fp, LOCK_EX);
				// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if (! $canWrite)
					usleep(round(rand(0, 100) * 1000));
			} while ((! $canWrite) and ((microtime() - $startTime) < 1000));
				
			// file was locked so now we can store information
			if ($canWrite) {
				fwrite($fp, $dataToSave);
				flock($fp, LOCK_UN);
			}
			fclose($fp);
		}
	}
}