<?php

namespace Statistics\Controller;

use Audit\Container;

use Deployment\IdentityApplicationsAwareInterface;
use Deployment\IdentityFilterInterface;
use ZendServer\Mvc\Controller\WebAPIActionController;

use Zend\Mvc\Controller\ActionController,
	WebAPI,
	ZendServer\Log\Log,
	Zend\Validator,
	ZendServer;
use Audit\Db\Mapper;
use Audit\Db\ProgressMapper;
use WebAPI\Exception;


class WebAPIController extends WebAPIActionController {

	const TYPE_OPLUS_UTILIZATION  					= 'OPLUS_UTILIZATION';
	const TYPE_OPLUS_HITS  							= 'OPLUS_HITS';
	const TYPE_OPLUS_MISSES  						= 'OPLUS_MISSES';
	const TYPE_OPLUS_FILES_CONSUMPTION  			= 'OPLUS_FILES_CONSUMPTION';
	const TYPE_OPLUS_MEMORY_CONSUMPTION  			= 'OPLUS_MEMORY_CONSUMPTION';
	const TYPE_OPLUS_MEMORY_WASTED  				= 'OPLUS_MEMORY_WASTED';
	
	const TYPE_DC_SHM_UTILITZATION  				= 'DC_SHM_UTILITZATION';
	const TYPE_DC_SHM_HITS  						= 'DC_SHM_HITS';
	const TYPE_DC_SHM_MISSES  						= 'DC_SHM_MISSES';
	const TYPE_DC_DISK_HITS  						= 'DC_DISK_HITS';
	const TYPE_DC_DISK_MISSES  						= 'DC_DISK_MISSES';
	const TYPE_DC_NUM_OF_NAMESPACES  				= 'DC_NUM_OF_NAMESPACES';
	const TYPE_DC_SHM_NUM_OF_ENTRIES  				= 'DC_SHM_NUM_OF_ENTRIES';
	
	const TYPE_PC_NUM_OF_RULES  					= 'PC_NUM_OF_RULES';
	const TYPE_PC_HITS			  					= 'PC_HITS';
	const TYPE_PC_MISSES  							= 'PC_MISSES';
	const TYPE_PC_AVG_PROC_TIME_NON_CACHED_PAGE  	= 'PC_AVG_PROC_TIME_NON_CACHED_PAGE';
	const TYPE_PC_AVG_PROC_TIME_CACHED_PAGE 		= 'PC_AVG_PROC_TIME_CACHED_PAGE';
	const TYPE_PC_NON_HANDLED_REQUESTS  			= 'PC_NON_HANDLED_REQUESTS';
	
	const TYPE_JQ_JOBS_PER_STATUS  					= 'JQ_JOBS_PER_STATUS';
	const TYPE_JQ_JOBS_ENQUEUED  					= 'JQ_JOBS_ENQUEUED';
	const TYPE_JQ_JOBS_SCHEDULED_ENQUEUED  			= 'JQ_JOBS_SCHEDULED_ENQUEUED';
	const TYPE_JQ_JOBS_DEQUEUED  					= 'JQ_JOBS_DEQUEUED';
	
	const TYPE_ACTIVE_SESSIONS 						= 'ACTIVE_SESSIONS';
	const TYPE_SC_AVG_SESSION_SIZE  				= 'SC_AVG_SESSION_SIZE';
	const TYPE_SC_MIN_SESSION_SIZE  				= 'SC_MIN_SESSION_SIZE';
	const TYPE_SC_MAX_SESSION_SIZE  				= 'SC_MAX_SESSION_SIZE';
	const TYPE_SC_SESSIONS_PER_APP  				= 'SC_SESSIONS_PER_APP';
	const TYPE_SC_SESSIONS_DATA_SPACE  				= 'SC_SESSIONS_DATA_SPACE';
	
	const TYPE_MON_NUM_OF_EVENTS  					= 'MON_NUM_OF_EVENTS';
	
	const TYPE_NUM_REQUESTS_PER_SECOND  			= 'NUM_REQUESTS_PER_SECOND';
	const TYPE_NUM_PHP_WORKERS  					= 'NUM_PHP_WORKERS';
	const TYPE_AVG_REQUEST_PROCESSING_TIME  		= 'AVG_REQUEST_PROCESSING_TIME';
	const TYPE_MAX_REQUEST_PROCESSING_TIME  		= 'MAX_REQUEST_PROCESSING_TIME';
	const TYPE_MIN_REQUEST_PROCESSING_TIME  		= 'MIN_REQUEST_PROCESSING_TIME';
	const TYPE_AVG_REQUEST_PROCESSING_TIME_PER_APP  = 'AVG_REQUEST_PROCESSING_TIME_PER_APP';
	const TYPE_AVG_MEMORY_USAGE  					= 'AVG_MEMORY_USAGE';
	const TYPE_AVG_CPU_USAGE 						= 'AVG_CPU_USAGE';
	const TYPE_AVG_REQUEST_OUTPUT_SIZE 				= 'AVG_REQUEST_OUTPUT_SIZE';
	const TYPE_AVG_DATABASE_TIME  					= 'AVG_DATABASE_TIME';
	
	const TYPE_BROWSERS_DISTRIBUTION				= 'BROWSERS_DISTRIBUTION';
	const TYPE_OS_DISTRIBUTION						= 'OS_DISTRIBUTION';
	
	// GUI special types
	const TYPE_EVENTS_PIE							= 'EVENTS_PIE';
	const TYPE_AVG_PROC_TIME						= 'AVG_PROC_TIME';
	const TYPE_MOBILE_AVG_PROC_TIME					= 'MOBILE_AVG_PROC_TIME';
	const TYPE_NUMBER_OF_EVENTS_LAYERED				= 'TYPE_NUMBER_OF_EVENTS_LAYERED';
	const TYPE_BROWSERS_PIE							= 'BROWSERS_PIE';
	const TYPE_OS_PIE								= 'OS_PIE';
	const TYPE_MOBILE_OS_PIE						= 'MOBILE_OS_PIE';
	const TYPE_REQUESTS_MAP							= 'REQUESTS_MAP';
	const TYPE_TREND_MOBILE_USAGE_LAYERED			= 'TREND_MOBILE_USAGE_LAYERED';

	public function statisticsGetMapAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('appId' => 0, 'from' => -1, 'to' => -1, 'server' => 0));
		$this->validateMandatoryParameters($params, array('type'));
		$translatedType = $this->validateStatisticsType($params['type'], 'type');
		$applicationId = $this->validateInteger($params['appId'], 'appId');
		if ($applicationId == 0) {
			$applicationId = array();
		} else {
			$applicationId = array($applicationId);
		}
		
		$from = $this->validateTimestamp($params['from'], 'from');
		$to = $this->validateTimestamp($params['to'], 'to');
		$server = $this->validateInteger($params['server'], 'server');
		
		if (($from > 0) && ($to > 0) && ($from > $to)) {
			throw new WebAPI\Exception(_t("Parameter 'to' must be heigher than 'from' parameter"), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$statsModel = $this->getLocator()->get('Statistics\Model'); /* @var $statsModel  \Statistics\Model */
		
		try {
			$container = $statsModel->getMapStatistics($translatedType, null, $applicationId, $from, $to, $server);
		} catch (\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		return array('statsContainer' => $container);
	}
	
	public function statisticsGetSeriesAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('appId' => 0, 'from' => -1, 'to' => -1, 'server' => 0, 'ignorePadding' => 'FALSE'));
		$this->validateMandatoryParameters($params, array('type'));
		$translatedType = $this->validateStatisticsType($params['type'], 'type');
		$applicationId = $this->validateInteger($params['appId'], 'appId');
		$ignorePadding = $this->validateBoolean($params['ignorePadding'], 'ignorePadding');
		if ($applicationId == 0) {
			$applicationId = array();
		} else {
			$applicationId = array($applicationId);
		}
		
		// change appId to 0 in case of statistics that not affected by application
		if (in_array($params['type'], array(ZEND_STATS_TYPE_AVG_CPU_USAGE, ZEND_STATS_TYPE_AVG_MEMORY_USAGE))) {
			$applicationId = array();			
		}
	
		$from = $this->validateTimestamp($params['from'], 'from');
		$to = $this->validateTimestamp($params['to'], 'to');
		$server = $this->validateInteger($params['server'], 'server');
		
		if (($from > 0) && ($to > 0) && ($from > $to)) {
			throw new WebAPI\Exception(_t("Parameter 'to' must be heigher than 'from' parameter"), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$getSinglePoint = false;
		$dailyInterval = $this->getInterval($from);
		if (($from > 0) && ($to > 0) && ($to - $from < $dailyInterval)) {
			$getSinglePoint = true;
			$from -= 60;
			$to += 60;
		}
		
		// round to minutr
		if ($from % $dailyInterval > 0) {
			$from = $from - ($from % $dailyInterval);
		}
		if ($to % $dailyInterval > 0) {
			$to = $to - ($to % $dailyInterval);
		}
		
		$statsModel = $this->getLocator()->get('Statistics\Model'); /* @var $statsModel  \Statistics\Model */
	
		if ($params['type'] == \Statistics\Model::STATS_EVENTS_PIE || 
			$params['type'] == \Statistics\Model::STATS_BROWSERS_PIE || 
			$params['type'] == \Statistics\Model::STATS_OS_PIE || 
			$params['type'] == \Statistics\Model::STATS_MOBILE_OS_PIE) {
			try {
				$container = $statsModel->getPieStatistics($translatedType, null, $applicationId, $from, $to, $server);
			} catch (\Exception $e) {
				throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
		} else { // line charts
			try {
				$container = $statsModel->getStatistics($translatedType, null, $applicationId, $from, $to, $server);
			} catch (\Exception $e) {
				throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}

			if ($getSinglePoint) {
				$data = $container->getData();
				if (count($data) > 0) {
					$lastValue = array_pop($data);
					$container->setData(array($lastValue));
				}
			} else {
				if (! $ignorePadding) {
					$this->addPaddingAndBlanks($container, $from, $to);
				}
			}
			
		}
	
		return array('statsContainer' => $container);
	}
	
	public function statisticsClearDataAction() {
		$this->isMethodPost();		
		$statsModel = $this->getLocator()->get('statsModel'); /* @var $statsModel  \Statistics\Model */
		$auditMessage = $this->auditMessage(Mapper::AUDIT_CLEAR_STATISTICS,
				ProgressMapper::AUDIT_PROGRESS_STARTED); /* @var $auditMessage \Audit\Container */
		try {
			$statsModel->clearDb();
		} catch (\Exception $ex) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $ex->getMessage())));
			throw new Exception('Could not clear statistics information', Exception::INTERNAL_SERVER_ERROR, $ex);
		}
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
	}

	private function getDictionary() {
		return array(
				self::TYPE_OPLUS_UTILIZATION  					=> ZEND_STATS_TYPE_OPLUS_UTILIZATION,
				self::TYPE_OPLUS_HITS  							=> ZEND_STATS_TYPE_OPLUS_HITS,
				self::TYPE_OPLUS_MISSES  						=> ZEND_STATS_TYPE_OPLUS_MISSES,
				self::TYPE_OPLUS_FILES_CONSUMPTION  			=> ZEND_STATS_TYPE_OPLUS_FILES_CONSUMPTION,
				self::TYPE_OPLUS_MEMORY_CONSUMPTION  			=> ZEND_STATS_TYPE_OPLUS_MEMORY_CONSUMPTION,
				self::TYPE_OPLUS_MEMORY_WASTED  				=> ZEND_STATS_TYPE_OPLUS_MEMORY_WASTED,
					
				self::TYPE_DC_SHM_UTILITZATION  				=> ZEND_STATS_TYPE_DC_SHM_UTILITZATION,
				self::TYPE_DC_SHM_HITS  						=> ZEND_STATS_TYPE_DC_SHM_HITS,
				self::TYPE_DC_SHM_MISSES  						=> ZEND_STATS_TYPE_DC_SHM_MISSES,
				self::TYPE_DC_DISK_HITS  						=> ZEND_STATS_TYPE_DC_DISK_HITS,
				self::TYPE_DC_DISK_MISSES  						=> ZEND_STATS_TYPE_DC_DISK_MISSES,
				self::TYPE_DC_NUM_OF_NAMESPACES  				=> ZEND_STATS_TYPE_DC_NUM_OF_NAMESPACES,
				self::TYPE_DC_SHM_NUM_OF_ENTRIES  				=> ZEND_STATS_TYPE_DC_SHM_NUM_OF_ENTRIES,
					
				self::TYPE_PC_NUM_OF_RULES  					=> ZEND_STATS_TYPE_PC_NUM_OF_RULES,
				self::TYPE_PC_HITS  							=> ZEND_STATS_TYPE_PC_HITS,
				self::TYPE_PC_MISSES  							=> ZEND_STATS_TYPE_PC_MISSES,
				self::TYPE_PC_AVG_PROC_TIME_NON_CACHED_PAGE  	=> ZEND_STATS_TYPE_PC_AVG_PROC_TIME_NON_CACHED_PAGE,
				self::TYPE_PC_AVG_PROC_TIME_CACHED_PAGE 		=> ZEND_STATS_TYPE_PC_AVG_PROC_TIME_CACHED_PAGE,
				self::TYPE_PC_NON_HANDLED_REQUESTS  			=> ZEND_STATS_TYPE_PC_NON_HANDLED_REQUESTS,
					
				self::TYPE_JQ_JOBS_PER_STATUS  					=> ZEND_STATS_TYPE_JQ_JOBS_PER_STATUS,
				self::TYPE_JQ_JOBS_ENQUEUED  					=> ZEND_STATS_TYPE_JQ_JOBS_ENQUEUED,
				self::TYPE_JQ_JOBS_SCHEDULED_ENQUEUED  			=> ZEND_STATS_TYPE_JQ_JOBS_SCHEDULED_ENQUEUED,
				self::TYPE_JQ_JOBS_DEQUEUED  					=> ZEND_STATS_TYPE_JQ_JOBS_DEQUEUED,
					
				self::TYPE_ACTIVE_SESSIONS 						=> ZEND_STATS_TYPE_ACTIVE_SESSIONS,
				self::TYPE_SC_AVG_SESSION_SIZE  				=> ZEND_STATS_TYPE_SC_AVG_SESSION_SIZE,
				self::TYPE_SC_MIN_SESSION_SIZE  				=> ZEND_STATS_TYPE_SC_MIN_SESSION_SIZE,
				self::TYPE_SC_MAX_SESSION_SIZE  				=> ZEND_STATS_TYPE_SC_MAX_SESSION_SIZE,
				self::TYPE_SC_SESSIONS_PER_APP  				=> ZEND_STATS_TYPE_SC_SESSIONS_PER_APP,
				self::TYPE_SC_SESSIONS_DATA_SPACE  				=> ZEND_STATS_TYPE_SC_SESSIONS_DATA_SPACE,
					
				self::TYPE_MON_NUM_OF_EVENTS  					=> ZEND_STATS_TYPE_MON_NUM_OF_EVENTS,
					
				self::TYPE_NUM_REQUESTS_PER_SECOND  			=> ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND,
				self::TYPE_NUM_PHP_WORKERS  					=> ZEND_STATS_TYPE_NUM_PHP_WORKERS,
				self::TYPE_AVG_REQUEST_PROCESSING_TIME  		=> ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME,
				self::TYPE_MAX_REQUEST_PROCESSING_TIME  		=> ZEND_STATS_TYPE_MAX_REQUEST_PROCESSING_TIME,
				self::TYPE_MIN_REQUEST_PROCESSING_TIME  		=> ZEND_STATS_TYPE_MIN_REQUEST_PROCESSING_TIME,
				self::TYPE_AVG_REQUEST_PROCESSING_TIME_PER_APP	=> ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME_PER_APP,
				self::TYPE_AVG_MEMORY_USAGE  					=> ZEND_STATS_TYPE_AVG_MEMORY_USAGE,
				self::TYPE_AVG_CPU_USAGE 						=> ZEND_STATS_TYPE_AVG_CPU_USAGE,
				self::TYPE_AVG_REQUEST_OUTPUT_SIZE 				=> ZEND_STATS_TYPE_AVG_REQUEST_OUTPUT_SIZE,
				self::TYPE_AVG_DATABASE_TIME  					=> ZEND_STATS_TYPE_AVG_DATABASE_TIME,
				
				// requests statistics
				self::TYPE_BROWSERS_DISTRIBUTION				=> ZEND_STATS_TYPE_BROWSERS_DISTRIBUTION,
				self::TYPE_OS_DISTRIBUTION						=> ZEND_STATS_TYPE_OS_DISTRIBUTION,
				
				// gui special types
				self::TYPE_EVENTS_PIE							=> \Statistics\Model::STATS_EVENTS_PIE,
				self::TYPE_AVG_PROC_TIME						=> \Statistics\Model::TYPE_AVG_PROC_TIME,
				self::TYPE_MOBILE_AVG_PROC_TIME					=> \Statistics\Model::TYPE_MOBILE_AVG_PROC_TIME,
				self::TYPE_NUMBER_OF_EVENTS_LAYERED				=> \Statistics\Model::TYPE_NUMBER_OF_EVENTS_LAYERED,
				self::TYPE_BROWSERS_PIE							=> \Statistics\Model::STATS_BROWSERS_PIE,
				self::TYPE_OS_PIE								=> \Statistics\Model::STATS_OS_PIE,
				self::TYPE_MOBILE_OS_PIE						=> \Statistics\Model::STATS_MOBILE_OS_PIE,
				self::TYPE_REQUESTS_MAP							=> \Statistics\Model::STATS_REQUESTS_MAP,
				self::TYPE_TREND_MOBILE_USAGE_LAYERED			=> \Statistics\Model::TYPE_TREND_MOBILE_USAGE_LAYERED,
				
		);
	}
	
	private function validateTimeframe($timeFrame, $parameterName) {
		if (0 == preg_match('#^(?:(?:\s*[[:digit:]]+[ymdh]\s*)+|e)$#', $timeFrame)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid time frame (e.g. 1y 3m 5d 7h)",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		return $timeFrame;
	}
	
	/**
	 * This function checks the supplied type against the dictionary and its flipped array
	 * We accept either a named type or an integer identifier
	 * @param string $type
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	private function validateStatisticsType($type, $parameterName) {
		$dictionary = $this->getDictionary();

		if ((! isset($dictionary[$type])) && (! in_array($type, $dictionary))) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a known statistics type",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		if (isset($dictionary[$type])) {
			return $dictionary[$type];
		} else {
			return $type;
		}
	}
	
	private function validateTimestamp($timestamp, $parameterName) {
		if ($timestamp == -1) {
			return $timestamp;
		}
		
		if(preg_match('/[^\d]/', $timestamp)) {
			$timestamp = strtotime($timestamp);
		
			if (false === $timestamp) {
				throw new WebAPI\Exception(_t("Parameter '%s' must be a valid timestamp",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
			}
		}
		
		return $timestamp;
	}
	
	private function getInterval($offset) {
		$statsModel = $this->getLocator()->get('statsModel'); /* @var $statsModel  \Statistics\Model */
		$table = $statsModel->getTableByOffset($offset);
		
		$seconds = (int) $this->getDirectivesMapper()->getDirectiveValue('zend_statistics.report_interval_daily');;
		
		if ($table == 'stats_daily') {
		    $retVal = $seconds; 
			return is_numeric($retVal) && $retVal > 0 ? $retVal : 60;
		} elseif ($table == 'stats_weekly') {
			$minutes = (int) $this->getDirectivesMapper()->getDirectiveValue('zend_statistics.report_interval_weekly');
			$retVal = $minutes * $seconds; 
			return is_numeric($retVal) && $retVal > 0 ? $retVal : 60 * 60;
		} else {
			$minutes = (int) $this->getDirectivesMapper()->getDirectiveValue('zend_statistics.report_interval_weekly');
			$hours = (int) $this->getDirectivesMapper()->getDirectiveValue('zend_statistics.report_interval_monthly');
			$retVal = $hours * $minutes * $seconds;
			return is_numeric($retVal) && $retVal > 0 ? $retVal : 60 * 60 * 24;
		}
	}

	private function addPaddingAndBlanks($container, $from, $to) {
		$statsModel = $this->getLocator()->get('statsModel'); /* @var $statsModel  \Statistics\Model */
		
		$dailyInterval = $this->getInterval($from);
		
		$originalData = $container->getData();
			
		$spread = count($originalData);
		$ceiling = $spread;
	
		if ($spread > $ceiling) {
			$batchSize = 1;
			$dilutedData = array();
			for ($i = $batchSize; $i <= $spread; $i += $batchSize) {
				$range = array_slice($originalData, $i-$batchSize, $batchSize);
				$localAverage = ceil(array_reduce($range, function($v, $w){return $v+$w[1];}) / $batchSize);
				$dilutedData[] = array($range[0][0], $localAverage);
			}
	
			$container->setData($dilutedData);
	
			\ZendServer\Log\Log::debug("Diluted {$spread} stats records to " . count($dilutedData) .' records');
		}
	
		// add last value as current timestamp
		$originalData = $container->getData();
		$addedData = array();
			
		$totalItemsCount = 0;
		foreach ($originalData as $originalRow) {
			$totalItemsCount += count($originalRow);
		}
			
		$offset = $statsModel->getTzOffset() * 3600;
		if ($totalItemsCount > 0) {
			if ($from != -1) {
				if (! isset($originalData[0][0]) || ! is_array($originalData[0][0])) { // check if the data is array (multiple charts in one)
					$addedData[] = array(($from + $offset) * 1000, 0);
					if (isset($originalData[0][0])) {
						if ($originalData[0][0] - ($dailyInterval * 1000) > $addedData[0][0]) {
							$addedData[] = array($originalData[0][0] - ($dailyInterval * 1000), 0);
							$originalData = array_merge($addedData, $originalData);
						}
					} else {
						$originalData[0] = $addedData;
					}
				} else {  // fix for charts with multiple data
					foreach (array_keys($originalData) as $key) {
						$addedData[$key][] = array(($from + $offset) * 1000, 0);
						if ($originalData[$key][0][0] - ($dailyInterval * 1000) > $addedData[$key][0]) {
							$addedData[$key][] = array($originalData[$key][0][0] - ($dailyInterval * 1000), 0);
						}
							
						$originalData[$key] = array_merge($addedData[$key], $originalData[$key]);
					}
				}
			} elseif ($from == -1 && $originalData[0][0] > strtotime("-1 year")) { // fill one year in case no lower limit choosen and there are less data then a year
				if (! is_array($originalData[0][0])) { // check if the data is array (multiple charts in one)
					$addedData[] = array((strtotime("-1 year") + $offset) * 1000, 0);
					if (isset($originalData[0][0])) {
						if ($originalData[0][0] - ($dailyInterval * 1000) > $addedData[0][0]) {
							$addedData[] = array($originalData[0][0] - ($dailyInterval * 1000), 0);
						}
						$originalData = array_merge($addedData, $originalData);
					} else {
						$originalData[0] = $addedData;
					}
	
				} else { // fix for charts with multiple data
					foreach (array_keys($originalData) as $key) {
						$addedData[$key][] = array((strtotime("-1 year") + $offset) * 1000, 0);
						if ($originalData[$key][0][0] - ($dailyInterval * 1000) > $addedData[$key][0]) {
							$addedData[$key][] = array($originalData[$key][0][0] - ($dailyInterval * 1000), 0);
						}
	
						$originalData[$key] = array_merge($addedData[$key], $originalData[$key]);
					}
				}
			}
	
			if ($to == -1) {
				$to = time();
			}
		}
					
			
		$tz = $statsModel->getTzOffset();
		if (count($originalData) == 0) {
			$shouldBeFirst =  ($from + $tz * 3600) * 1000;
			$shouldBeLast =  ($to + $tz * 3600) * 1000;
			$originalData[] = array($shouldBeFirst, 0);
			$originalData[] = array($shouldBeLast, 0);
		} elseif (is_array($originalData[0]) && count($originalData[0]) == 0) {
			foreach ($originalData as $key => $value) {
				$shouldBeFirst =  ($from + $tz * 3600) * 1000;
				$shouldBeLast =  ($to + $tz * 3600) * 1000;
				$originalData[$key][] = array($shouldBeFirst, 0);
				$originalData[$key][] = array($shouldBeLast, 0);
			}
		}
			
		$container->setData($originalData);
	}
}
