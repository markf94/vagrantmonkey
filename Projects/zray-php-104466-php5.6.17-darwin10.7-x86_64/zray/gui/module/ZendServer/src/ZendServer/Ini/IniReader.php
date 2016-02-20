<?php

namespace ZendServer\Ini;

use ZendServer\Log\Log;

use ZendServer\Exception as ZSException;

class IniReader extends \Zend\Config\Reader\Ini {
	
	protected $nestSeparator = null; // by default we don't want nested arrays based on a '.'	
	
	/**
	 * @see \Zend\Config\Reader\Ini::fromFile(). We override this method, as to allow parsing the files flatly (no processSections)
	 */
	public function fromFile($filename, $processSections=true) {
		$iniData = parent::fromFile($filename);
				
		if ($processSections) {
			return $iniData; // nothing further to do
		}
		
		return $this->arrayFlatten($iniData);
	}
		
	protected function arrayFlatten($sourceArray, $targetArray=array()) {
		if (!$sourceArray || !is_array($sourceArray)) {
			return '';
		}
		
		foreach($sourceArray as $k => $v){
			if(is_array($v)) {
				$targetArray = $this->arrayFlatten($v, $targetArray);
			}
			else {
				$targetArray[$k] = $v;
			}
		}
		
		return $targetArray;
	}
}