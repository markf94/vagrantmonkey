<?php
namespace EventsGroup;

use ZendServer\Exception as ZSException,
ZendServer\Log\Log as Log;

class DataContainer {
	/**
	 * @var array
	 */
	protected $eventsGroup;
	
	/**
	 * @var array
	 */
	protected $superGlobals = array();	
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $eventsGroup) {
		$this->eventsGroup = $eventsGroup;
	}
	
	/**
	 * @return integer
	 */
	public function getIssueId() {
		return $this->eventsGroup[ZM_DATA_ISSUE_ID];
	}
	
	/**
	 * @return integer
	 */
	public function getLoad() {
		return $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_LOAD];
	}
	
	/**
	 * @return integer
	 */
	public function getAvgOutputSize() {
		return $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_OUT_SIZE];
	}
	
	/**
	 * @return integer
	 */
	public function getAvgMemUsage() {
		return $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_MEM_USAGE];
	}
	
	/**
	 * @return integer
	 */
	public function getMemUsage() {
		return $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_MEM_USAGE];
	}
	
	/**
	 * @return integer
	 */
	public function getAvgExecTime() {
		return $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_EXEC_TIME];
	}
	
	/**
	 * @return integer
	 */
	public function getExecTime() {
		return $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_EXEC_TIME];
	}
	
	/**
	 * @return string
	 */
	public function getJavaBacktrace() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_JAVA_BACKTRACE]) && $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_JAVA_BACKTRACE]
			? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_JAVA_BACKTRACE] : '';
	}
	
	/**
	 * @return string
	 */
	public function getBacktrace() {
		return isset($this->eventsGroup[ZM_DATA_BACKTRACE]) && $this->eventsGroup[ZM_DATA_BACKTRACE]
			? $this->eventsGroup[ZM_DATA_BACKTRACE] : array();
	}
	
	/**
	 * @return string
	 */
	public function getUserData() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA]) && $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA]
			? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA] : '';
	}
	
	/**
	 * @return string
	 */
	public function getClass() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_CLASS]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_CLASS] : '';
	}

	/**
	 * @return string
	 */
	public function getErrorString() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_ERROR_STRING]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_ERROR_STRING] : '';
	}	
	
	/**
	 * @return string
	 */
	public function getErrorType() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_ERROR_TYPE]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_ERROR_TYPE] : '';
	}
	
	/**
	 * @return integer
	 */
	public function getServerId() {
		return isset($this->eventsGroup[ZM_DATA_NODE_ID]) ? $this->eventsGroup[ZM_DATA_NODE_ID] : '';
	}
	
	/**
	 * @return integer
	 */
	public function getstartTime() {
		return $this->eventsGroup[ZM_DATA_FIRST_TIMESTAMP];
	}
	
	/**
	 * @return integer
	 */
	public function getEventsCount() {
		return $this->eventsGroup[ZM_DATA_REPEATS];
	}
	
	/**
	 * @return integer
	 */
	public function getEventsGroupId() {
		return $this->eventsGroup[ZM_DATA_EVENT_ID];
	}
	
	/**
	 * @return string
	 */
	public function getFunctionName() {
		return $this->eventsGroup[ZM_DATA_FUNC_DETAILS][ZM_DATA_FUNCTION_NAME];
	}
	
	/**
	 * @return string
	 */
	public function getFunctionArgs() {
		return $this->eventsGroup[ZM_DATA_FUNC_DETAILS][ZM_DATA_FUNCTION_ARGS];
	}
	
	/**
	 * @return string
	 */
	public function getExtraData() {
		return $this->eventsGroup[ZM_DATA_EVENT_GROUP_EXTRA_DATA];
	}
	
	/**
	 * @return array()
	 */
	public function getSuperGlobalsData() {
		if ($this->superGlobals) return $this->superGlobals;
		
		foreach ($this->eventsGroup[ZM_DATA_SUPER_GLOBALS] as $key => $serializedData) {
			try {		
				$this->superGlobals[$key] = self::unpackData($serializedData);
			} catch (ZSException $e) {
				Log::err("Trying to unpack invalid data: $serializedData");// Data is not valid				
			}
		}
		
		return $this->superGlobals;
	}
	
	/**
	 * @return string
	 */
	public function getSuperGlobalGet() {
		$superGlobals = $this->getSuperGlobalsData();
		return $superGlobals[ZM_DATA_VAR_GET];
	}

	/**
	 * @return string
	 */
	public function getSuperGlobalPost() {
		$superGlobals = $this->getSuperGlobalsData();
		return $superGlobals[ZM_DATA_VAR_POST];
	}		

	/**
	 * @return string
	 */
	public function getSuperGlobalCookie() {
		$superGlobals = $this->getSuperGlobalsData();
		return $superGlobals[ZM_DATA_VAR_COOCKIE];
	}	
	
	/**
	 * @return string
	 */
	public function getSuperGlobalSession() {
		$superGlobals = $this->getSuperGlobalsData();
		return $superGlobals[ZM_DATA_VAR_SESSION];
	}	

	/**
	 * @return string
	 */
	public function getSuperGlobalEnv() {
		$superGlobals = $this->getSuperGlobalsData();
		return $superGlobals[ZM_DATA_VAR_ENV];
	}	

	/**
	 * @return string
	 */
	public function getSuperGlobalServer() {
		$superGlobals = $this->getSuperGlobalsData();
		return $superGlobals[ZM_DATA_VAR_SERVER];
	}		

	/**
	 * @return string
	 */
	public function getSuperGlobalRawPost() {
		$superGlobals = $this->getSuperGlobalsData();
		return $superGlobals[ZM_DATA_VAR_RAW_POST_DATA];
	}
		
	/**
	 *
	 * @return array()
	 */
	public function getCodeTracingPath() {
		$attributes = $this->eventsGroup [ZM_DATA_TRACER];
		// Normalize the path - bug 26681
//		$attributes [ZM_DATA_TRACER_DUMP_FILE] = Zwas_Path::create ( $attributes [ZM_DATA_TRACER_DUMP_FILE] );
		if (isset($attributes[ZM_DATA_TRACER_DUMP_FILE]) && $attributes[ZM_DATA_TRACER_DUMP_FILE]) {			
			return $attributes [ZM_DATA_TRACER_DUMP_FILE];
		} else {
			return '';
		}
	}	


	/**
	 * @return string
	 */
	public function getEmail() {
		if (isset($this->eventsGroup[ZM_DATA_ACTION_EMAIL]) && $this->eventsGroup[ZM_DATA_ACTION_EMAIL]) {
			return $this->eventsGroup[ZM_DATA_ACTION_EMAIL];
		}
		
		return '';
	}
	
	/**
	 * @return string
	 */
	public function getActionUrl() {
		if (isset($this->eventsGroup[ZM_DATA_ACTION_URL]) && $this->eventsGroup[ZM_DATA_ACTION_URL]) {
			return $this->eventsGroup[ZM_DATA_ACTION_URL];
		}
		
		return '';
	}
	
	/**
	 * @return boolean
	 */
	public function hasCodetracing() {
		return isset($this->eventsGroup[ZM_DATA_HAS_TRACE_FILES]) && $this->eventsGroup[ZM_DATA_HAS_TRACE_FILES];
	}
	
	/**
	 * @return integer
	 */
	public function getRelExecTime() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_EXEC_TIME_CHANGE_PERCENT])
		? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_EXEC_TIME_CHANGE_PERCENT] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getRelMemUsage() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_MEM_USAGE_CHANGE_PERCENT])
		? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_MEM_USAGE_CHANGE_PERCENT] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getRelOutputSize() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_OUT_SIZE_CHANGE_PERCENT]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_OUT_SIZE_CHANGE_PERCENT] : 0;
	}
	
	public function getMvcData() {
		if (isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_REQUEST_COMPONENTS])) {
			return $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_REQUEST_COMPONENTS];
		} else {
			return array();
		}
	}
	
	public function setMvcData($data) {
		$this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_REQUEST_COMPONENTS] = $data;
	}
	
	/**
	 * The data is serialized.
	 * We have to check that serialized data is serializable
	 *
	 * @param string $data
	 * @return mixed
	 * @throws ZSException
	 */
	private static function unpackData($data) {
		if (! $data) {
			return $data;
		}
	
		$data = trim($data); // see bug 24488 for details
	
		// Set a temporary new unserialize callback function
		$previousUnserializeCallback = ini_get('unserialize_callback_func');
		ini_set('unserialize_callback_func', '\EventsGroup\DataContainer::createMissingClass'); // @todo - does this actually work??
	
		// Silent errors due to Zend_Loader autoloads and possible warning during unserialization of objects
		$unserializedData = @unserialize($data);
	
		// Set the previous unserialize callback
		ini_set('unserialize_callback_func', $previousUnserializeCallback);
	
		if ($unserializedData !== false) {
			return $unserializedData;
		}
		// If the return value of unserialize is flase, we have to check if the unserialize failed or tha value was really false
		if ($data == 'b:0;') {
			return false;
		}
	
		throw new ZSException('Wrong data format');
	}	

	/**
	 * Create a missing class definition, used as unserialize_callback_func
	 *
	 * @param string $className
	 * @throws ZSException
	 */
	private static function createMissingClass($className) {
		if (! preg_match('|^\w*$|', $className)) {
			throw new ZSException('Invalid class name in serialized data');
		}
		eval("class $className {}");
	}	
	
}