<?php

namespace DevBar\Data;

/**
 * Mapper to the JSON file with list of all available internal functions and classes
 */
class InternalFunctionsMapper {
	
	/**
	 *
	 * @var string
	 */
	protected static $internalFunctionsJsonFileName = null;
	
	/**
	 * 
	 * @var array
	 */
	protected static $data = null;
	
	/**
	 *
	 * @param string $internalFunctionsJsonFileName        	
	 */
	public function __construct() {
		self::$internalFunctionsJsonFileName = __DIR__.'/../../../../../data/php_internal.list.json';
		$this->_loadFile ();
	}
	
	/**
	 * Get list of internal functions including extensions' functions
	 * 
	 * @return array
	 */
	public function getInternalFunctions() {
		return self::$data ['functions'];
	}
	
	/**
	 * Get list of internal classes including extensions' classes
	 * 
	 * @return array
	 */
	public function getInternalClasses() {
		return self::$data ['classes'];
	}
	
	/**
	 * read and decode list of functions and classes from JSON file
	 */
	protected function _loadFile() {
		if (!is_null(self::$data)) {
			return;
		}
		
		// read the file
		$result = !empty(self::$internalFunctionsJsonFileName) && file_exists(self::$internalFunctionsJsonFileName) ? file_get_contents(self::$internalFunctionsJsonFileName) : '';		
		if ($result === false) {
			$result = '';
		}
			
			// decode
		$result = json_decode($result, true);
		if ($result === false) {
			$internalFunctions = get_defined_functions();
			$internalFunctions = $internalFunctions ['internal'];
			$result = array (
					'functions' => $internalFunctions,
					'classes' => array(),
			);
		}
		
		self::$data = $result;
	}
}