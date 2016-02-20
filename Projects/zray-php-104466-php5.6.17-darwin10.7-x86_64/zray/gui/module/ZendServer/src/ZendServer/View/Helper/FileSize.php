<?php

namespace ZendServer\View\Helper;

use Zend\View\Helper\AbstractHelper;

use ZendServer\Exception;


class FileSize extends AbstractHelper {
	const AUTO 	= 'AUTO';
	const B		= 'B';
	const KB	= 'KB';
	const MB	= 'MB';
	const GB	= 'GB';
	
	const SHOW_UNIT = true;
	const HIDE_UNIT = false;
	
	protected $sizes = array(self::GB		=> 30,
							self::MB		=> 20,
							self::KB 		=> 10,
							self::B 		=> 1);
	
	/**
	 * Get a filesize label.
	 *
	 * @param integer $size
	 * @param string $outputUnit
	 * @return string
	 * @throws ZendServer\Exception
	 */
	public function __invoke($size, $outputUnit = self::AUTO, $showUnit = self::SHOW_UNIT) {		
		if (!defined("ZendServer\View\Helper\FileSize::$outputUnit")) {
			throw new Exception("Wrong parameter type {$outputUnit}.");
		}
		if (self::AUTO == $outputUnit) {
			$outputUnit = $this->outputSize($size);
		}
		$size = ( $size / ( 1 << $this->sizes[$outputUnit]) );
	
		// if the number is float, we keep only 2 digits after the decimal point
		if ($size != (int)$size) {
			$size = number_format($size, 2);
		}
	
		// in the previous if we may have gone in it with 2.0000001 and after it ended with
		// $size = 2.00 so we should trim the 00
		if ($size == (int)$size) {
			$size = (int)$size;
		}
	
		if ($showUnit) {
			return "$size {$outputUnit}";
		} else {
			return $size;
		}
	}
	
	/**
	* Calculate the best output size (KB, MB...)
	 *
	 * @param int $size, size in bytes
	 * @return string - Zwas_View_Helper_CE_FileSize constant
	 */
	 private function outputSize($size) {
		 foreach ($this->sizes as $type => $mul){
			 if ($size >= (1<<$mul)) {
			 	return $type;
			 }
		 }
		 // return the smallest (last one itarated) size type
		 return $type;
	}
}

