<?php
namespace DevBar;

use DevBar\Data;
use DevBar\Data\InternalFunctionsMapper;
use Zend\Di\ServiceLocator;

class FunctionStatsContainer {
	/**
	 * @var array
	 */
	protected $functionStats;
	
	/**
	 * @var array;
	 */
	private static $internalFunctions = null;
	private static $internalClasses = null;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $functionStats, $key=null) {
		$this->functionStats = $functionStats;
	}
	
	public function toArray() {
		return $this->functionStats;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->functionStats['id']) ? $this->functionStats['id'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getRequestId() {
		return (isset($this->functionStats['request_id']) ? $this->functionStats['request_id'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getFunctionName() {
		return (isset($this->functionStats['function_name']) ? $this->functionStats['function_name'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getFunctionScope() {
		return (isset($this->functionStats['function_scope']) ? $this->functionStats['function_scope'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getTimesCalled() {
		return (isset($this->functionStats['times_called']) ? $this->functionStats['times_called'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getTimeExclusive() {
		return (isset($this->functionStats['time_exclusive']) ? $this->functionStats['time_exclusive'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getTimeInclusive() {
		return (isset($this->functionStats['time_inclusive']) ? $this->functionStats['time_inclusive'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getFilename() {
		return (isset($this->functionStats['filename']) ? $this->functionStats['filename'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getLine() {
		return (isset($this->functionStats['line']) ? $this->functionStats['line'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getIsInternal() {
		// get internal functions and classes
		$internalFunctionsMapper = null;
		if (is_null(self::$internalFunctions) || is_null(self::$internalClasses)) {
			$internalFunctionsMapper = new InternalFunctionsMapper();
			self::$internalFunctions = $internalFunctionsMapper->getInternalFunctions();
			self::$internalClasses = $internalFunctionsMapper->getInternalClasses();
		}
		
		// get scope and function name
		$scope = $this->getFunctionScope();
		$functionName = str_replace(array('(', ')'), '', $this->functionStats['function_name']);
		
		// check if internal
		if (!empty($scope) && in_array($scope, self::$internalClasses)) return 1;
		if (empty($scope) && in_array($functionName, self::$internalFunctions)) return 1;
		return 0;
	}
	
}