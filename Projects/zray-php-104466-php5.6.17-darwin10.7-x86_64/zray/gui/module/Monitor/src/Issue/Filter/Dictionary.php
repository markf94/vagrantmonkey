<?php

namespace Issue\Filter;

use ZendServer\Log\Log;

class Dictionary {
	
	const TYPE_PHP_ERROR					= 'zend-error';
	const TYPE_FUNCTION_ERROR				= 'function-error';
	const TYPE_SLOW_SCRIPT					= 'request-slow-exec';
	const TYPE_SLOW_FUNCTION				= 'function-slow-exec';
	const TYPE_JAVA_EXCEPTION				= 'java-exception';
	const TYPE_MEMORY_USAGE					= 'request-large-mem-usage';
	const TYPE_OUTPUT_SIZE					= 'request-relative-large-out-size';
	const TYPE_CUSTOM						= 'custom';
	// non-IDE events
	const TYPE_JQ_JOB_EXECUTION_ERROR		= 'jq-job-exec-error';
	const TYPE_JQ_JOB_LOGICAL_FAILURE		= 'jq-job-logical-failure';
	const TYPE_JQ_JOB_EXECUTION_DELAY		= 'jq-job-exec-delay';
	const TYPE_TRACER_WRITE_FILE_FAIL		= 'tracer-write-file-fail';

	
	// for IDE sake - when changing constants, make sure they confrom to eventGroup.xsd, otherwise exporting to IDE  would fail!	
	const STUDIO_PHP_ERROR					= 'PHP Error';
	const STUDIO_FUNCTION_ERROR				= 'Function Error';
	const STUDIO_SLOW_SCRIPT				= 'Slow Script Execution';
	const STUDIO_SLOW_FUNCTION				= 'Slow Function Execution';
	const STUDIO_JAVA_EXCEPTION				= 'Java Exception';
	const STUDIO_MEMORY_USAGE				= 'Excess Memory Usage';
	const STUDIO_OUTPUT_SIZE				= 'Inconsistent Output Size';
	const STUDIO_CUSTOM						= 'Custom Event';
	
	const SEVERITY_SEVERE					= 'severe';
	const SEVERITY_NORMAL					= 'normal';
	const SEVERITY_INFO						= 'info';
	
	public function getIssueTimeRange() {
		return array (
				'all' => _t ( 'All' ),
				'2hours' => _t ( '2 Hours' ),
				'day' => _t ( '24 Hours' ),
				'week' => _t ( 'Week' ),
				'2weeks' => _t ( '2 Weeks' ),
				'month' => _t ( 'Month' ),
				'3months' => _t ( '3 Months' ),
				'6months' => _t ( '6 Months' ),
				'year' => _t ( 'Year' )
		);
	}
	
	public function getTimeRanges() {
		$timeRangesArray = array('all' => array());	
		
		$timeRangesArray['year'] = array(date('m/d/Y H:i', strtotime('-1 year')), date('m/d/Y H:i'), strtotime('-1 year'), time());
		$timeRangesArray['6months'] = array(date('m/d/Y H:i', strtotime('-6 month')), date('m/d/Y H:i'), strtotime('-6 month'), time());
		$timeRangesArray['3months'] = array(date('m/d/Y H:i', strtotime('-3 month')), date('m/d/Y H:i'), strtotime('-3 month'), time());
		$timeRangesArray['month'] = array(date('m/d/Y H:i', strtotime('-1 month')), date('m/d/Y H:i'), strtotime('-1 month'), time());
		
		$timeRangesArray['2weeks']	= array(date('m/d/Y H:i', time() - 14*24*60*60), date('m/d/Y H:i'), time() - 14*24*60*60, time());
		$timeRangesArray['week'] = array(date('m/d/Y H:i', time() - 7*24*60*60), date('m/d/Y H:i'), time() - 7*24*60*60, time());
		$timeRangesArray['day'] = array(date('m/d/Y H:i', time() - 24*60*60), date('m/d/Y H:i'), time() - 24*60*60, time());
		$timeRangesArray['2hours'] = array(date('m/d/Y H:i', time() - 2*60*60), date('m/d/Y H:i'), time() - 2*60*60, time());
		$timeRangesArray['2hour'] =	array(date('m/d/Y H:i', time() - 60*60), date('m/d/Y H:i'), time() - 60*60, time());
		
		return $timeRangesArray;
		
		/*return array(
			'all'		=> array(),
			'year'		=> array(date('m/d/Y H:i', time() - 12*30*24*60*60) , date('m/d/Y H:i'), time() - 12*30*24*60*60, time()),
			'6months'	=> array(date('m/d/Y H:i', time() - 6*30*24*60*60) 	, date('m/d/Y H:i'), time() - 6*30*24*60*60, time()),
			'3months'	=> array(date('m/d/Y H:i', time() - 3*30*24*60*60) 	, date('m/d/Y H:i'), time() - 3*30*24*60*60, time()),
			'month'		=> array(date('m/d/Y H:i', time() - 30*24*60*60) 	, date('m/d/Y H:i'), time() - 30*24*60*60, time()),
			'2weeks'	=> array(date('m/d/Y H:i', time() - 14*24*60*60) 	, date('m/d/Y H:i'), time() - 14*24*60*60, time()),
			'week'		=> array(date('m/d/Y H:i', time() - 7*24*60*60) 	, date('m/d/Y H:i'), time() - 7*24*60*60, time()),
			'day'		=>	array(date('m/d/Y H:i', time() - 24*60*60)		, date('m/d/Y H:i'), time() - 24*60*60, time()),
			'2hours'	=>	array(date('m/d/Y H:i', time() - 2*60*60)		, date('m/d/Y H:i'), time() - 2*60*60, time()),
			'2hour'		=>	array(date('m/d/Y H:i', time() - 60*60 )		, date('m/d/Y H:i'), time() - 60*60, time()),
		);*/
	}
	
	/**
	 * Get a translation array from severity constants to strings
	 * 
	 * @return array
	 */
	public function getIssueSeverities() {
		return array (
				self::SEVERITY_NORMAL 	=> _t ( 'Warning' ),
				self::SEVERITY_SEVERE	=> _t ( 'Critical' ),
				self::SEVERITY_INFO		=> _t ('Notice'),
		);
	}
	
	public function getIssueSeveritiesConstants() {
		return array (
				self::SEVERITY_NORMAL =>	ZM_SEVERITY_NORMAL,
				self::SEVERITY_SEVERE =>	ZM_SEVERITY_SEVERE,
				self::SEVERITY_INFO =>	ZM_SEVERITY_INFO,
		);
	}
	
	/**
	 *
	 * @param integer $severity        	
	 * @return string
	 */
	public function severityToText($severity) {
		foreach ($this->getIssueSeveritiesConstants() as $name=>$constant) {
			if ($severity === $constant) {
				$textToTranslation = $this->getIssueSeverities();
				return $textToTranslation[$name];
			}
		}
		
		return '';
	}
	
	public function getIssueEventGroups() {
	
		return array(
				self::TYPE_FUNCTION_ERROR					=> 'Function Error',
				self::TYPE_SLOW_FUNCTION					=> 'Slow Function Execution',
				self::TYPE_SLOW_SCRIPT						=> 'Slow Request Execution',
				self::TYPE_MEMORY_USAGE						=> 'High Memory Usage',
				self::TYPE_OUTPUT_SIZE						=> 'Inconsistent Output Size',
				self::TYPE_JAVA_EXCEPTION					=> 'Uncaught Java Exception',
				self::TYPE_CUSTOM							=> 'Custom Event',
				self::TYPE_PHP_ERROR						=> 'PHP Error',
				self::TYPE_JQ_JOB_EXECUTION_DELAY			=> 'Job Execution Delay',
				self::TYPE_JQ_JOB_EXECUTION_ERROR			=> 'Job Execution Error',
				self::TYPE_JQ_JOB_LOGICAL_FAILURE			=> 'Job Logical Failure',
				self::TYPE_TRACER_WRITE_FILE_FAIL			=> 'Failed Writing Code Tracing Data',

		);
	}
	
	/**
	 * Get a translation array from event type constants to strings
	 */
	public function getIssueEventTypes() {
		
		return array(
				self::TYPE_CUSTOM							=> self::TYPE_CUSTOM,
				self::TYPE_SLOW_FUNCTION					=> self::TYPE_SLOW_FUNCTION,
				self::TYPE_FUNCTION_ERROR					=> self::TYPE_FUNCTION_ERROR,
				self::TYPE_SLOW_SCRIPT						=> self::TYPE_SLOW_SCRIPT,
				self::TYPE_MEMORY_USAGE						=> self::TYPE_MEMORY_USAGE,
				self::TYPE_OUTPUT_SIZE						=> self::TYPE_OUTPUT_SIZE,
				self::TYPE_PHP_ERROR						=> self::TYPE_PHP_ERROR,
				self::TYPE_JAVA_EXCEPTION					=> self::TYPE_JAVA_EXCEPTION,
				self::TYPE_TRACER_WRITE_FILE_FAIL			=> self::TYPE_TRACER_WRITE_FILE_FAIL,
				self::TYPE_JQ_JOB_EXECUTION_DELAY			=> self::TYPE_JQ_JOB_EXECUTION_DELAY,
				self::TYPE_JQ_JOB_EXECUTION_ERROR			=> self::TYPE_JQ_JOB_EXECUTION_ERROR,
				self::TYPE_JQ_JOB_LOGICAL_FAILURE			=> self::TYPE_JQ_JOB_LOGICAL_FAILURE,					
		);	
	}
	
	public function getIssueEventTypesConstants() {	
		return array(
				self::TYPE_CUSTOM							=> ZM_TYPE_CUSTOM,
				self::TYPE_SLOW_FUNCTION					=> ZM_TYPE_FUNCTION_SLOW_EXEC,
				self::TYPE_FUNCTION_ERROR					=> ZM_TYPE_FUNCTION_ERROR,
				self::TYPE_SLOW_SCRIPT						=> ZM_TYPE_REQUEST_SLOW_EXEC,
				self::TYPE_MEMORY_USAGE						=> ZM_TYPE_REQUEST_LARGE_MEM_USAGE,
				self::TYPE_OUTPUT_SIZE						=> ZM_TYPE_REQUEST_RELATIVE_LARGE_OUT_SIZE,
				self::TYPE_PHP_ERROR						=> ZM_TYPE_ZEND_ERROR,
				self::TYPE_JAVA_EXCEPTION					=> ZM_TYPE_JAVA_EXCEPTION,
				self::TYPE_TRACER_WRITE_FILE_FAIL			=> ZM_TYPE_TRACER_FILE_WRITE_FAIL,
				self::TYPE_JQ_JOB_EXECUTION_DELAY			=> ZM_TYPE_JQ_JOB_EXEC_DELAY,
				self::TYPE_JQ_JOB_EXECUTION_ERROR			=> ZM_TYPE_JQ_JOB_EXEC_ERROR,
				self::TYPE_JQ_JOB_LOGICAL_FAILURE			=> ZM_TYPE_JQ_JOB_LOGICAL_FAILURE,					
		);
	}

	public function getIssueEventTypesStudioConstants() {	
		return array(
				self::STUDIO_CUSTOM							=> ZM_TYPE_CUSTOM,
				self::STUDIO_SLOW_FUNCTION					=> ZM_TYPE_FUNCTION_SLOW_EXEC,
				self::STUDIO_FUNCTION_ERROR					=> ZM_TYPE_FUNCTION_ERROR,
				self::STUDIO_SLOW_SCRIPT					=> ZM_TYPE_REQUEST_SLOW_EXEC,
				self::STUDIO_MEMORY_USAGE					=> ZM_TYPE_REQUEST_LARGE_MEM_USAGE,
				self::STUDIO_OUTPUT_SIZE					=> ZM_TYPE_REQUEST_RELATIVE_LARGE_OUT_SIZE,
				self::STUDIO_PHP_ERROR						=> ZM_TYPE_ZEND_ERROR,
				self::STUDIO_JAVA_EXCEPTION					=> ZM_TYPE_JAVA_EXCEPTION,
		);
	}	
	
	/**
	 * used by IDE export issue
	 * @param integer $type
	 * @return string
	 */
	public function eventTypeToStudioText($type) {	
		$texts = array_flip($this->getIssueEventTypesStudioConstants());
		$availableTypes = $this->getIssueEventTypesConstants();
		foreach ($availableTypes as $name=>$constant) {
			if ($type === $constant) {
				return $texts[$type];
			}
		}
	
		return '';
	}
	
	public function severitiesToConstants(array $severities = array()) {
	    $severitiesDictionary = $this->getIssueSeveritiesConstants();
	    foreach ($severities as &$severity) {
	        if (isset($severitiesDictionary[$severity])) {
	            $severity = $severitiesDictionary[$severity];
	        }
	    }
	     
	    return $severities;
	}
}