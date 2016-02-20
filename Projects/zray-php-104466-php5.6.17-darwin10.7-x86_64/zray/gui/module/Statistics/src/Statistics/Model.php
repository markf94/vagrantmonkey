<?php
namespace Statistics;

use Deployment\IdentityApplicationsAwareInterface;
use Deployment\IdentityFilterException;
use Deployment\IdentityFilterInterface;
use ZendServer\Log\Log;

use Zend\Db\Adapter\Driver\Pdo\Pdo;

use Application\Module as AppModule;

use Zend\Db\Sql\Select;

use Zend\Db\Sql\Expression;

use Zend\Db\Adapter\Adapter;

class Model implements IdentityApplicationsAwareInterface {
	const STATS_EVENTS_PIE 					= 500;
	const TYPE_AVG_PROC_TIME 				= 501;
	const ENOUGH_STATISTICS_ROWS			= 60;
	const TYPE_NUMBER_OF_EVENTS_LAYERED 	= 502;
	const STATS_BROWSERS_PIE 				= 503;
	const STATS_OS_PIE 						= 504;
	const STATS_MOBILE_OS_PIE 				= 505;
	const STATS_REQUESTS_MAP 				= 506;
	const TYPE_TREND_MOBILE_USAGE_LAYERED 	= 507;
	const TYPE_MOBILE_AVG_PROC_TIME 		= 508;
	
	/**
	 * @var \Zend\Db\Adapter\Adapter
	 */
	private $adapter;
	
	private $tz;
	
	private $timezoneOffset;

    /**
     * @var IdentityFilterInterface
     */
    private $identityFilter;

	public function hasEnoughStatistics() {
		$select = new Select('stats_daily');
		$select->columns(array('rowCount' => new Expression('COUNT(*)')));
		$sql = $select->getSqlString($this->getAdapter()->getPlatform());
		$query = $this->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
		if ($query->current()->rowCount > self::ENOUGH_STATISTICS_ROWS) {
			return true;
		}
		return false;
	}
	
	/**
	 * @param integer $type
	 * @return array
	 */
	public function getMultipleChartData($type) {
		$multiTypes = array(
			self::TYPE_NUMBER_OF_EVENTS_LAYERED => array(
				'types' => array(
					'Notice' 	=> ZEND_STATS_TYPE_MON_NUM_OF_INFO_EVENTS,
					'Critical' 	=> ZEND_STATS_TYPE_MON_NUM_OF_SEVERE_EVENTS,
					'Warning'	=> ZEND_STATS_TYPE_MON_NUM_OF_WARNING_EVENTS,
				),
				'subType' => false,
			)
		);
					
		if ($type == 'all') {
			return $multiTypes;
		}
		
		if (isset($multiTypes[$type])) {
			return $multiTypes[$type];
		}
		return null;
	}
	
	/**
	 * @param array $data
	 * @param string $type
	 * @param string $subType
	 * @return \Statistics\Container
	 */
	public function getContainer($data, $type, $subType = -1) {
		$container = new Container($data);
		
		switch ($type) {
			case ZEND_STATS_TYPE_AVG_MEMORY_USAGE:
				$container->setTitle('Avg. Memory Usage')
				->setYTitle('Memory usage')
				->setName('Memory Usage')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('mb');
				break;
			case ZEND_STATS_TYPE_AVG_CPU_USAGE:
				$container->setTitle('CPU Usage')
				->setYTitle('CPU usage')
				->setName('CPU Usage')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('%');
				break;
			case ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME:
				$container->setTitle('Avg. Response Time')
				->setYTitle('Processing time')
				->setName('PHP')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('ms');
				break;
			case ZEND_STATS_TYPE_OS_DISTRIBUTION:
				$container->setTitle('Requests Per Second')
				->setYTitle('Request / sec')
				->setName('Requests')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND:
				$container->setTitle('Requests Per Second')
				->setYTitle('Request / sec')
				->setName('Requests')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_AVG_DATABASE_TIME:
				$container->setTitle('Requests Per Second - db')
				->setYTitle('Request / sec')
				->setName('Database')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_MON_NUM_OF_EVENTS:
				$container->setTitle('Number of Events')
				->setYTitle('Events / sec')
				->setName('Events')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_ACTIVE_SESSIONS:
				$container->setTitle('Active Users')
				->setYTitle('Sessions / sec')
				->setName('Sessions')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setYAxisType(\Statistics\Container::YAXIS_INTEGER)
				->setValueType('');
				break;
			// TODO: check if can remove this - duplicate
			case ZEND_STATS_TYPE_MON_NUM_OF_EVENTS:
				$this->getMonitorNumOfEvents($container, $subType);
				break;
								
			case ZEND_STATS_TYPE_OPLUS_HITS:
				$container->setTitle('O+ Hits')
				->setYTitle('O+ Hits')
				->setName('O+ Hits')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_OPLUS_MISSES:
				$container->setTitle('O+ Misses')
				->setYTitle('O+ Misses')
				->setName('O+ Misses')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			
			case ZEND_STATS_TYPE_PC_HITS:
				$container->setTitle('Page Cache Hits')
				->setYTitle('Page Cache Hits')
				->setName('Page Cache Hits')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_PC_MISSES:
				$container->setTitle('Page Cache Misses')
				->setYTitle('Page Cache Misses')
				->setName('Page Cache Misses')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
				
			case ZEND_STATS_TYPE_DC_DISK_HITS:
				$container->setTitle('Data Cache Disk Hits')
				->setYTitle('Data Cache Disk Hits')
				->setName('Data Cache Disk Hits')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_DC_DISK_MISSES:
				$container->setTitle('Data Cache Disk Misses')
				->setYTitle('Data Cache Disk Misses')
				->setName('Data Cache Disk Misses')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
				
			case ZEND_STATS_TYPE_DC_SHM_HITS:
				$container->setTitle('Data Cache SHM Hits')
				->setYTitle('Data Cache SHM Hits')
				->setName('Data Cache SHM Hits')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_DC_SHM_MISSES:
				$container->setTitle('Data Cache SHM Misses')
				->setYTitle('Data Cache SHM Misses')
				->setName('Data Cache SHM Misses')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('');
				break;
			case ZEND_STATS_TYPE_DC_SHM_UTILITZATION:
				$container->setTitle('Data Cache SHM Utilization')
				->setYTitle('Data Cache SHM Utilization')
				->setName('Data Cache SHM Utilization')
				->setChartType(\Statistics\Container::TYPE_LINE)
				->setValueType('mb');
				break;
		}
		$container->setCounterId($type);
		return $container;
		
		//ZEND_STATS_TYPE_AVG_CPU_USAGE, ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME
	}
	
	public function getTzOffset() {
		return $this->tz;
	}
	
	/**
	 * @return \Statistics\Container
	 */
	public function createContainer($data = array()) {
		return new Container($data);
	}
	
	public function getMapStatistics($type, $subType = null, $applicationIds = array(), $from, $to, $server) {
        if (1 <= count($applicationIds)) {
            $this->identityFilter->setAddGlobalAppId(false);
        }
        $applicationIds = $this->filterIdentityApplications($applicationIds);
        if (! $applicationIds) {
            return array();
        }
		$appsList = implode(',',$applicationIds);
		
		// pick a source table
		$table = $this->getTableByOffset($from);
		
		$db = $this->getAdapter();
		
		$UntilTimeQuery = '';
		if ($from != -1) {
			$UntilTimeQuery .= "and from_time >= '{$from}' ";
		}
		if ($to != -1) {
			$UntilTimeQuery .= "and until_time <= '{$to}' ";
		}
		
		$query = "select code, sum(counter_value) as total
				from {$table} as S
				join stats_geo_dictionary as D
				on S.entry_sub_type_id = D.id
				where entry_type_id = " . ZEND_STATS_TYPE_GEO_DISTRIBUTION . " {$UntilTimeQuery} ";
			
		$query .= " and app_id in({$appsList})";
					
		if ($server > 0) {
		$query .= " and node_id = '{$server}'";
		}
			
		$query .= ' group by name';
			
		Log::debug('Statistics query for type '.$type.' (pie) is');
		Log::debug($query);
						
		$results = $db->query($query)->execute(); /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
		
		$data = array();
		foreach ($results as $result) {
			$data[] = array('id' => $result['code'], 'value' => intval($result['total']));
		}
		
		return $data;
	}
	
	/**
	 * @param string $type
	 * @param string $subType
	 * @param array $applicationIds
	 * @param string $from
	 * @param string $to
	 * @return \Statistics\Container
	 */
	public function getPieStatistics($type, $subType = null, $applicationIds = array(), $from, $to, $server) {
		if (!defined('ZM_TYPE_FUNCTION_ERROR') && !defined('ZM_TYPE_ZEND_ERROR')) {
			throw new \Exception(_t('Failed to get statistics. The Monitor UI component is not loaded'));
		}
		$eventsPieContainer = $this->createContainer();
		$eventsPieContainer->setTitle('Events\' Breakdown')
			->setName('EventsPie')
			->setChartType(\Statistics\Container::TYPE_PIE)
			->setValueType('%')
			->setCounterId(\Statistics\Model::STATS_EVENTS_PIE);

        if (1 <= count($applicationIds)) {
            $this->identityFilter->setAddGlobalAppId(false);
        }

        $applicationIds = $this->filterIdentityApplications($applicationIds);

        // Application Ids must be explicitly stated
        if (! $applicationIds) {
            $eventsPieContainer->setData(array());
			return $eventsPieContainer;
		}
		
		$appsList = implode(',',$applicationIds);
		
		// pick a source table
		$table = $this->getTableByOffset($from);
		
		if ($type == self::STATS_EVENTS_PIE) {
			$types = array(
				'Performance' => array(
					ZM_TYPE_FUNCTION_SLOW_EXEC,
					ZM_TYPE_REQUEST_SLOW_EXEC,
					ZM_TYPE_REQUEST_RELATIVE_SLOW_EXEC,
					ZM_TYPE_JQ_DAEMON_HIGH_CONCURRENCY_LEVEL
				),
				'Errors' => array(
					ZM_TYPE_FUNCTION_ERROR,
					ZM_TYPE_ZEND_ERROR,
					ZM_TYPE_JAVA_EXCEPTION,
					ZM_TYPE_JQ_JOB_EXEC_ERROR,
					ZM_TYPE_TRACER_FILE_WRITE_FAIL,
					ZM_TYPE_ZSM_RESTART_FAILED,
					ZM_TYPE_JQ_JOB_LOGICAL_FAILURE,
					ZM_TYPE_CUSTOM
				),
				'Resources' => array(
					ZM_TYPE_REQUEST_LARGE_MEM_USAGE,
					ZM_TYPE_REQUEST_RELATIVE_LARGE_MEM_USAGE,
					ZM_TYPE_REQUEST_RELATIVE_LARGE_OUT_SIZE,
					ZM_TYPE_JQ_JOB_EXEC_DELAY
				),
			);
			
			$db = $this->getAdapter();
			
// 			if ($timeFrame !== 'e') {
// 				$offset = $this->getTimeOffset($timeFrame);
				
// 				/// pick a source table
// 				$table = $this->getTableByOffset($offset);
				
// 				$untilTime = ' and until_time >= ' . $offset;
// 			} else {
// 				$untilTime = '';
// 				$table = 'stats_monthly';
// 			}
			
			//$from = strtotime($from);
			//$to = strtotime($to);
			
			//$untilTime = ' and until_time >= ' . $offset;
			
			$data = array();
			$totalResults = 0;
						
			$UntilTimeQuery = '';
			if ($from != -1) {
				$UntilTimeQuery .= "and from_time >= '{$from}' ";
			}
			if ($to != -1) {
				$UntilTimeQuery .= "and until_time <= '{$to}' ";
			}
			
			foreach ($types as $typeName => $typeData) {
				$query = "select sum(counter_value) as total
					from {$table}
					where entry_type_id IN (" . ZEND_STATS_TYPE_MON_NUM_OF_INFO_EVENTS . ", " . ZEND_STATS_TYPE_MON_NUM_OF_WARNING_EVENTS . ", " . ZEND_STATS_TYPE_MON_NUM_OF_SEVERE_EVENTS . ")  
					{$UntilTimeQuery}and entry_sub_type_id IN (" . implode(', ', $typeData) . ")";

				$query .= " and app_id in({$appsList})";
				
				if ($server > 0) {
					$query .= " and node_id = '{$server}'";
				}
				
				Log::debug('Statistics query for type '.$type.' (pie) is');
				Log::debug($query);
				
				$results = $db->query($query)->execute(); /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
				foreach ($results as $result) {
					$data[] = array($typeName, (int) $result['total']);
					$totalResults += (int) $result['total'];
				}
			}
			\ZendServer\Log\Log::debug("Retrieved " . count($data) . " statistics records");
			
			
			if ($totalResults == 0) {
				$data = array();	
			}
			
			$eventsPieContainer->setData($data);
		} elseif ($type == self::STATS_BROWSERS_PIE) {
		
			$db = $this->getAdapter();
			
			$UntilTimeQuery = '';
			if ($from != -1) {
				$UntilTimeQuery .= "and from_time >= '{$from}' ";
			}
			if ($to != -1) {
				$UntilTimeQuery .= "and until_time <= '{$to}' ";
			}
			
			$query = "select name, sum(counter_value) as total
				from {$table} as S
				join stats_browsers_dictionary as D
				on S.entry_sub_type_id = D.id
				where entry_type_id = " . ZEND_STATS_TYPE_BROWSERS_DISTRIBUTION . " {$UntilTimeQuery} ";
			
			$query .= " and app_id in({$appsList})";
			
			if ($server > 0) {
				$query .= " and node_id = '{$server}'";
			}
			
			$query .= ' group by name';
			
			Log::debug('Statistics query for type '.$type.' (pie) is');
			Log::debug($query);
			
			$results = $db->query($query)->execute(); /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
			
			$resArray = array();
			foreach($results as $row) {
				$resArray[] = $row;
			}

			$data = array_map(function($item){
				return array($item['name'], intval($item['total']));
			}, $resArray);
			
			$eventsPieContainer->setTitle('Browsers Distribution')->
								setName('BrowsersPie')->
								setData($data);	
		} elseif ($type == self::STATS_OS_PIE || $type == self::STATS_MOBILE_OS_PIE) {
			$db = $this->getAdapter();
			
			$UntilTimeQuery = '';
			
			if ($from != -1) {
				$UntilTimeQuery .= "and from_time >= '{$from}' ";
			}
			if ($to != -1) {
				$UntilTimeQuery .= "and until_time <= '{$to}' ";
			}
			
			$mobile = '';
			if ($type == self::STATS_MOBILE_OS_PIE) {
				$mobile = 'mobile = 1 and';
			}
			
			$query = "select name, sum(counter_value) as total
				from {$table} as S
				join stats_os_dictionary as D
				on S.entry_sub_type_id = D.id
				where {$mobile} entry_type_id = " . ZEND_STATS_TYPE_OS_DISTRIBUTION . " {$UntilTimeQuery} ";
			
			$query .= " and app_id in({$appsList})";
			
			if ($server > 0) {
				$query .= " and node_id = '{$server}'";
			}
			
			$query .= ' group by name';
			
			Log::debug('Statistics query for type '.$type.' (pie) is');
			Log::debug($query);
			
			$results = $db->query($query)->execute(); /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
			
			$resArray = array();
			foreach($results as $row) {
				$resArray[] = $row;
			}

			$data = array_map(function($item) {
				return array($item['name'], intval($item['total']));
			}, $resArray);

			if ($type == self::STATS_OS_PIE) {
				$eventsPieContainer->setTitle('OS Distribution')->setName('OsPie');
			} elseif ($type == self::STATS_MOBILE_OS_PIE) {
				$eventsPieContainer->setTitle('Mobile OS Distribution')->setName('MobileOsPie');
			}
			
			$eventsPieContainer->setData($data);
		}
		
		return $eventsPieContainer;
	}
	
	/**
	 * @param string $type
	 * @param string $subType
	 * @param array $applicationId
	 * @param string $from
	 * @param string $to
	 * @return \Statistics\Container
	 */
	public function getStatistics($type, $subType = null, $applicationIds = array(), $from, $to, $server) {
		//$dbPath = get_cfg_var('zend.data_dir') . '/db/statistics.db';
		
		$db = $this->getAdapter();
		
		$multiTypes = $this->getMultipleChartData('all');
		
		if (1 <= count($applicationIds)) {
			$this->identityFilter->setAddGlobalAppId(false);
		}
		
		if ($type == self::TYPE_AVG_PROC_TIME) {
			$entryTypeId = 'entry_type_id IN("' . ZEND_STATS_TYPE_OUTPUT_SEND_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_OUTPUT_SEND_TIME . '", "' . ZEND_STATS_TYPE_AVG_DATABASE_TIME . '", "' . ZEND_STATS_TYPE_AVG_NETWORK_TIME . '", "' . ZEND_STATS_TYPE_AVG_LOCAL_TIME . '", "' . ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_AVG_DATABASE_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_AVG_NETWORK_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_AVG_LOCAL_TIME . '", "' . ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME . '")';
		} elseif ($type == self::TYPE_MOBILE_AVG_PROC_TIME) {
			$entryTypeId = 'entry_type_id IN("' . ZEND_STATS_TYPE_OUTPUT_SEND_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_OUTPUT_SEND_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_AVG_DATABASE_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_AVG_NETWORK_TIME . '", "' . ZEND_STATS_TYPE_MOBILE_AVG_LOCAL_TIME . '", "' . ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME . '")';
		} elseif (isset($multiTypes[$type])) {
			$entryTypeId = 'entry_type_id IN("' . implode('", "', $multiTypes[$type]['types']) . '")';
		} elseif ($type == ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND) { // requests per second is num of requests devided to the timeframe
			$entryTypeId = "entry_type_id = " . ZEND_STATS_TYPE_NUM_REQUESTS;
		} elseif ($type == self::TYPE_TREND_MOBILE_USAGE_LAYERED) {
			$entryTypeId = "entry_type_id = " . ZEND_STATS_TYPE_OS_DISTRIBUTION;
		} else {
			$entryTypeId = "entry_type_id = {$type}";
		}
		
		
		/// pick a source table
		$table = $this->getTableByOffset($from);
		
		// in case of num of
		$devider = '';
		if ($type == ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND || $type == ZEND_STATS_TYPE_OS_DISTRIBUTION) {
			if ($table == 'stats_daily') {
				$devider = '/60';
			} elseif ($table == 'stats_weekly') {
				$devider = '/3600';
			} else {
				$devider = '/86400';
			}
		}

		// get the interval between points according to the table
		if ($table == 'stats_daily') {
			$interval = 60;
		} elseif ($table == 'stats_weekly') {
			$interval = 3660;
		} else {
			$interval = 86400;
		}
		
		$UntilTimeQuery = '';
		if ($from != -1) {
			$UntilTimeQuery .= "and from_time >= '{$from}' ";
		}
		if ($to != -1) {
			$UntilTimeQuery .= "and until_time <= '{$to}' ";
		}
		
		$nodeIdQuery = '';
		if ($server > 0) {
			$nodeIdQuery = "and node_id = '{$server}' ";
		}

		$joinMobile = '';
		$whereMobile = '';
		if ($type == ZEND_STATS_TYPE_OS_DISTRIBUTION) {
			$joinMobile = 'join stats_os_dictionary as D on S.entry_sub_type_id = D.id';
			$whereMobile = 'and mobile = 1 ';
		} elseif ($type == self::TYPE_TREND_MOBILE_USAGE_LAYERED) {
			$joinMobile = 'join stats_os_dictionary as D on S.entry_sub_type_id = D.id';
		}
		
		$query = "select *, sum(counter_value){$devider} as total_sum, (sum(counter_value * samples) / sum(samples)){$devider} as total_avg
			  	from {$table} as S {$joinMobile}
			  	where {$entryTypeId} {$whereMobile} {$UntilTimeQuery} {$nodeIdQuery} ";
		
		
		//$db = new Sqlite(array('dbname' => $dbPath));
// 		if ($timeFrame !== 'e') {
// 			$offset = $this->getTimeOffset($timeFrame);

// 			/// pick a source table
// 			$table = $this->getTableByOffset($offset);
			
// 			// in case of num of 
// 			$devider = '';
// 			if ($type == ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND) {
// 				if ($table == 'stats_daily') {
// 					$devider = '/60';
// 				} elseif ($table == 'stats_weekly') {
// 					$devider = '/3600';
// 				} else {
// 					$devider = '/86400';
// 				}
// 			}
			
// 			$query = "select *, sum(counter_value){$devider} as total_sum, (sum(counter_value * samples) / sum(samples)){$devider} as total_avg
// 					  from {$table} 
// 					  where {$entryTypeId} and from_time >= {$from} and until_time <= {$to} ";
// 		} else {
// 			// in case of num of
// 			$devider = '';
// 			if ($type == ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND) {
// 				$devider = '/86400';
// 			}
			
// 			$query = "select *, sum(counter_value){$devider} as total_sum, (sum(counter_value * samples) / sum(samples)){$devider} as total_avg
// 					  from stats_monthly
// 					  where {$entryTypeId}";
// 		}
		
		if (! is_null($subType)) {
			$query .= ' and entry_sub_type_id = ' . $subType;
		}

		// Application Ids must be explicitly stated

        $applicationIds = $this->filterIdentityApplications($applicationIds);
		if (! $applicationIds) {
			return $this->getContainer(array(), $type, $subType);
		}
		
		$appsList = implode(',',$applicationIds);
		$query .= " and app_id in({$appsList})";
		
		// in case of multitype we need to figure out if we group by sub_type or not
		if (isset($multiTypes[$type]) && ! $multiTypes[$type]['subType']) {
			$query .= " group by entry_type_id, from_time
					   order by from_time asc";
		} elseif ($type == self::TYPE_TREND_MOBILE_USAGE_LAYERED) {
			$query .= " group by entry_type_id, mobile, from_time
					   order by from_time asc";
		} elseif ($type == ZEND_STATS_TYPE_OS_DISTRIBUTION) {
			$query .= " group by entry_type_id, from_time
					   order by from_time asc";
		} else {
			$query .= " group by entry_type_id, entry_sub_type_id, from_time
					   order by from_time asc";
		}
		
		Log::debug('Statistics query for type '.$type.' (pie) is');
		Log::debug($query);
		
		$results = $db->query($query)->execute(); /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
		
		// this section handles the data break-down in order to process it
		if ($type == self::TYPE_AVG_PROC_TIME) {
			// we need specific handler for TYPE_AVG_PROC_TIME since the value of ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME is determined by its own value minus the sum of all others
			$seperatedTypes = array(ZEND_STATS_TYPE_OUTPUT_SEND_TIME => array(),
									ZEND_STATS_TYPE_AVG_DATABASE_TIME => array(),
									ZEND_STATS_TYPE_AVG_NETWORK_TIME => array(),
									ZEND_STATS_TYPE_AVG_LOCAL_TIME => array(),
									ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME => array(),
					
									// add mobile types
									ZEND_STATS_TYPE_MOBILE_AVG_DATABASE_TIME => array(),
									ZEND_STATS_TYPE_MOBILE_AVG_NETWORK_TIME => array(),
									ZEND_STATS_TYPE_MOBILE_AVG_LOCAL_TIME => array(),
									ZEND_STATS_TYPE_MOBILE_OUTPUT_SEND_TIME => array(),
									ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME => array()
					);
			
			foreach ($results as $result) {
				$seperatedTypes[$result['entry_type_id']][$result['from_time']] = $result;
			}
			
			$mobileStats = array(ZEND_STATS_TYPE_MOBILE_OUTPUT_SEND_TIME => ZEND_STATS_TYPE_OUTPUT_SEND_TIME,
								 ZEND_STATS_TYPE_MOBILE_AVG_DATABASE_TIME => ZEND_STATS_TYPE_AVG_DATABASE_TIME,
								 ZEND_STATS_TYPE_MOBILE_AVG_NETWORK_TIME => ZEND_STATS_TYPE_AVG_NETWORK_TIME,
								 ZEND_STATS_TYPE_MOBILE_AVG_LOCAL_TIME => ZEND_STATS_TYPE_AVG_LOCAL_TIME,
								 ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME => ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME);
			
			foreach ($mobileStats as $mobileStat => $mobileStatAlt) {
				foreach ($seperatedTypes[$mobileStat] as $fromTime => $row) {
					if (isset($seperatedTypes[$mobileStatAlt][$fromTime])) {
						$totalResults = $seperatedTypes[$mobileStatAlt][$fromTime]['total_avg'] * $seperatedTypes[$mobileStatAlt][$fromTime]['samples'];
						$totalRowResults = $row['total_avg'] * $row['samples'];
						$totalSamples = $seperatedTypes[$mobileStatAlt][$fromTime]['samples'] + $row['samples'];
						
						$seperatedTypes[$mobileStatAlt][$fromTime]['total_avg'] = ($totalResults + $totalRowResults) / $totalSamples;
						$seperatedTypes[$mobileStatAlt][$fromTime]['total_sum'] += $row['total_sum'];
						$seperatedTypes[$mobileStatAlt][$fromTime]['counter_value'] += $row['counter_value'];
						$seperatedTypes[$mobileStatAlt][$fromTime]['samples'] += $row['samples'];
					} else {
						$seperatedTypes[$mobileStatAlt][$fromTime] = $row;
					}
				}
				unset($seperatedTypes[$mobileStat]);
			}

			// sort the results by timestamp
			foreach ($seperatedTypes as $key => $seperatedType) {
				ksort($seperatedTypes[$key]);
			}
			
			foreach ($seperatedTypes[ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME] as $key => $seperatedType) {
				$reduce = 0;
				if (isset($seperatedTypes[ZEND_STATS_TYPE_AVG_DATABASE_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_AVG_DATABASE_TIME][$key]['total_avg'];
				}
				if (isset($seperatedTypes[ZEND_STATS_TYPE_OUTPUT_SEND_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_OUTPUT_SEND_TIME][$key]['total_avg'];
				}
				if (isset($seperatedTypes[ZEND_STATS_TYPE_AVG_NETWORK_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_AVG_NETWORK_TIME][$key]['total_avg'];
				}
				if (isset($seperatedTypes[ZEND_STATS_TYPE_AVG_LOCAL_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_AVG_LOCAL_TIME][$key]['total_avg'];
				}
				
				$seperatedTypes[ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME][$key]['total_avg'] = max(0, $seperatedTypes[ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME][$key]['total_avg'] - $reduce);
			}
			
			$resultsByType = array();
			foreach ($seperatedTypes as $seperatedType) {
				$resultsByType[] = $this->processStatisticsResult($seperatedType, $type, $from, $to, $interval);
			}
			
			return $this->getContainer($resultsByType, $type, $subType);
		} elseif ($type == self::TYPE_MOBILE_AVG_PROC_TIME) {
			// we need specific handler for TYPE_MOBILE_AVG_PROC_TIME since the value of ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME is determined by its own value minus the sum of all others
			$seperatedTypes = array(ZEND_STATS_TYPE_MOBILE_OUTPUT_SEND_TIME => array(),
									ZEND_STATS_TYPE_MOBILE_AVG_DATABASE_TIME => array(),
									ZEND_STATS_TYPE_MOBILE_AVG_NETWORK_TIME => array(),
									ZEND_STATS_TYPE_MOBILE_AVG_LOCAL_TIME => array(),
									ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME => array());
			foreach ($results as $result) {
				$seperatedTypes[$result['entry_type_id']][] = $result;
			}
			
			foreach ($seperatedTypes[ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME] as $key => $seperatedType) {
				$reduce = 0;
				if (isset($seperatedTypes[ZEND_STATS_TYPE_MOBILE_AVG_DATABASE_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_MOBILE_AVG_DATABASE_TIME][$key]['total_avg'];
				}
				if (isset($seperatedTypes[ZEND_STATS_TYPE_MOBILE_OUTPUT_SEND_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_MOBILE_OUTPUT_SEND_TIME][$key]['total_avg'];
				}
				if (isset($seperatedTypes[ZEND_STATS_TYPE_MOBILE_AVG_NETWORK_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_MOBILE_AVG_NETWORK_TIME][$key]['total_avg'];
				}
				if (isset($seperatedTypes[ZEND_STATS_TYPE_MOBILE_AVG_LOCAL_TIME][$key]['total_avg'])) {
					$reduce += $seperatedTypes[ZEND_STATS_TYPE_MOBILE_AVG_LOCAL_TIME][$key]['total_avg'];
				}
				
				$seperatedTypes[ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME][$key]['total_avg'] = max(0, $seperatedTypes[ZEND_STATS_TYPE_AVG_MOBILE_REQUEST_PROCESSING_TIME][$key]['total_avg'] - $reduce);
			}
			
			$resultsByType = array();
			foreach ($seperatedTypes as $seperatedType) {
				$resultsByType[] = $this->processStatisticsResult($seperatedType, $type, $from, $to, $interval);
			}
			
			return $this->getContainer($resultsByType, $type, $subType);
		} elseif ($type == self::TYPE_TREND_MOBILE_USAGE_LAYERED) {
			$seperatedTypes = array('0' => array(), '1' => array());
			foreach ($results as $result) {
				$seperatedTypes[$result['mobile']][] = array('from_time' => $result['from_time'], 'total_sum' => $result['total_sum']);
			}
			
			$resultsByType = array();
			foreach ($seperatedTypes as $seperatedType) {
				$resultsByType[] = $this->processStatisticsResult($seperatedType, $type, $from, $to, $interval);
			}
				
			// collect all timestamps in the data
			$allTimestamps = array();
			foreach ($resultsByType as $resultByType) {
				foreach ($resultByType as $row) {
					$timestamp = '' . $row[0];
					$allTimestamps[$timestamp] = 0;
				}
			}
			ksort($allTimestamps);
				
			// add values for timestamp that does not exists
			$newResultsByType = array();
			foreach ($resultsByType as $resultByTypeKey => $resultByType) {
				$newResultsByType[$resultByTypeKey] = $allTimestamps;
				foreach ($resultByType as $row) {
					$timestamp = '' . $row[0];
					$newResultsByType[$resultByTypeKey][$timestamp] = $row[1];
				}
			}
				
			$newCleanResults = array();
			foreach ($newResultsByType as $resultByTypeKey => $resultByType) {
				$newCleanResults[$resultByTypeKey] = array();
				foreach ($resultByType as $timestamp => $val) {
					// add the point itself
					$newCleanResults[$resultByTypeKey][] = array((float) $timestamp, $val);
				}
			}
				
			return $this->getContainer($newCleanResults, $type, $subType);
		} elseif (isset($multiTypes[$type])) {
			$seperatedTypes = array();
			foreach ($multiTypes[$type]['types'] as $multiType) {
				$seperatedTypes[$multiType] = array();
			}
			
			foreach ($results as $result) {
				$seperatedTypes[$result['entry_type_id']][] = $result;
			}

			$resultsByType = array();
			foreach ($seperatedTypes as $seperatedType) {
				$resultsByType[] = $this->processStatisticsResult($seperatedType, $type, $from, $to, $interval);
			}
			
			// collect all timestamps in the data
			$allTimestamps = array();
			foreach ($resultsByType as $resultByType) {
				foreach ($resultByType as $row) {
					$timestamp = '' . $row[0];
					$allTimestamps[$timestamp] = 0; 
				}
			}
			ksort($allTimestamps);
			
			// add values for timestamp that does not exists 
			$newResultsByType = array();
			foreach ($resultsByType as $resultByTypeKey => $resultByType) {
				$newResultsByType[$resultByTypeKey] = $allTimestamps;
				foreach ($resultByType as $row) {
					$timestamp = '' . $row[0];
					$newResultsByType[$resultByTypeKey][$timestamp] = $row[1];
				}
			}
			
			$newCleanResults = array();
			foreach ($newResultsByType as $resultByTypeKey => $resultByType) {
				$newCleanResults[$resultByTypeKey] = array();
				foreach ($resultByType as $timestamp => $val) {
					$newCleanResults[$resultByTypeKey][] = array((float) $timestamp, $val);
				}
			}
			
			return $this->getContainer($newCleanResults, $type, $subType);
		} else {
			$fixedValues = $this->processStatisticsResult($results, $type, $from, $to, $interval);
			return $this->getContainer($fixedValues, $type, $subType);
		}
	}
	
	public function clearDb() {		
		foreach ($this->getDelStatement() as $query) {
			$this->getAdapter()->query($query)->execute();
		}
	}
	
	private function processStatisticsResult($statResults, $type, $from, $to, $interval) {
		if (count($statResults) == 0) return array();

		$values = array();
		$interval *= 1000;

		foreach ($statResults as $result) {
			if ($type == ZEND_STATS_TYPE_AVG_CPU_USAGE || $type == ZEND_STATS_TYPE_AVG_MEMORY_USAGE || $type == self::TYPE_AVG_PROC_TIME || $type == self::TYPE_MOBILE_AVG_PROC_TIME || $type == ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME) {
				$counterValue = $result['total_avg'];
			} else {
				$counterValue = $result['total_sum'];
			}
				
			if ($type == ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND || $type == ZEND_STATS_TYPE_OS_DISTRIBUTION) {
				$val = (float) round($counterValue, 2);
			} else {
				$val = round($counterValue);
			}
			
			$values[] = array(($result['from_time'] + $this->tz * 3600) * 1000, $val);
		}
				
		if (count($values) > 0) {
			$lastValue = $values[count($values) - 1];
			$shouldBeLast =  ($to + $this->tz * 3600) * 1000;
			$diff = ($shouldBeLast - $lastValue[0]) / $interval;
			
			if ($diff > 2) {
				$values[] = array($lastValue[0] + $interval, 0);
			} else {
				// add one point with the last value, to display the graph until the end
				$values[] = array($lastValue[0] + $interval, $lastValue[1]);
			}
		}

		// This part adds 0 values on gaps
		$fixedValues = array();
		foreach ($values as $key => $value) {
			if ($key > 0) {
				$diff = ($value[0] - $values[$key - 1][0]) / $interval;
				if ($diff > 2) {
					// add "0" after the previous point
					$fixedValues[] = array($values[$key - 1][0] + $interval, 0);
					// add "0" before the current point
					$fixedValues[] = array($values[$key][0] - $interval, 0);
				}
				$fixedValues[] = $value;
			} else {
				// check if the first value is not in the beginning of the graph, 
				// then add "0" before the point
				// (calculate the number of intervals from the beginning of the graph to the first point)
				$diff = ($value[0] - (($from + ($this->tz * 3600)) * 1000)) / $interval;
				if ($diff > 1) {
					$fixedValues[] = array($value[0] - $interval, 0);
				}

				$fixedValues[] = $value;
			}
		}
		return $fixedValues;
	}
	
	public function sortTimestamps($a, $b) {
		if ($a[0] == $b[0]) {
			return 0;
		}
		return ($a[0] < $b[0]) ? -1 : 1;
	}
	
	private function getTheTimezoneOffset($tz) {
		$dt = new \DateTime(null, new \DateTimeZone($tz));
    	return $dt->getOffset()/60/60;
	}

	/**
	 * @return \Zend\Db\Adapter\Adapter $adapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}
	
	/**
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @return \Statistics\Model
	 */
	public function setAdapter($adapter) {
		$this->adapter = $adapter;
		return $this;
	}
	
	/**
	 * 
	 * @param integer $offset
	 * @param integer $timestamp
	 * @return string
	 */
	public function getTableByOffset($offset) {
		$tz = @date_default_timezone_get();
		$this->tz = $this->getTheTimezoneOffset($tz);
		
		$timestamp = $this->getDbTimestamp();
		$now = strtotime($timestamp);
	
		$frame = $now - $offset;
		
		// reduce timezone offset
		$frame -= 3600 * $this->tz;
		
		if ($frame <= (60*60*24)+15) {
			$table = 'stats_daily';
		} elseif ($frame > (60*60*24) && $frame <= (60*60*24*14)) {
			$table = 'stats_weekly';
		} else {
			$table = 'stats_monthly';
		}
	
		return $table;
	}
	
	private function getTables() {
		return array('stats_daily', 'stats_monthly', 'stats_weekly');
	}
	
	private function getDelStatement() {
		$query = array();
		foreach ($this->getTables() as $table) {
			$query[] = "DELETE from {$table};";
		}
	
		return $query;
	}
		
	private function getDbTimestamp() {
		$db = $this->getAdapter();
		return current($db->query('SELECT CURRENT_TIMESTAMP')->execute()->current());
	}
	
	private function getTimeOffset($timeFrame) {
		$offset = strtotime('now');
		$timePieces = explode(' ', $timeFrame);
		foreach ($timePieces as $piece) {
			$piece = preg_replace_callback('#([[:digit:]]+)([ymdh])#', function($matches){
				$quantifier = intval($matches[1]);
				switch ($matches[2]) {
					case 'd':
						$operand = 'days';
						break;
					case 'm':
						$operand = 'months';
						break;
					case 'y':
						$operand = 'years';
						break;
					case 'h':
					default:
						$operand = 'hours';
				}
				return "$quantifier $operand";
			}, trim($piece));
			$offset = strtotime("-$piece",$offset);
		}
		
		return $offset;
	}
	 
	private function getTimezoneOffset($remote_tz, $origin_tz = null) {
		if($origin_tz === null) {
			if(!is_string($origin_tz = date_default_timezone_get())) {
				return false; // A UTC timestamp was returned -- bail out!
			}
		}
		$origin_dtz = new \DateTimeZone($origin_tz);
		$remote_dtz = new \DateTimeZone($remote_tz);
		$origin_dt = new \DateTime("now", $origin_dtz);
		$remote_dt = new \DateTime("now", $remote_dtz);
		$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
		return $offset;
	}


    /**
     * @param IdentityFilterInterface $filter
     * @return
     */
    public function setIdentityFilter(IdentityFilterInterface $filter)
    {
        $this->identityFilter = $filter;
        return $this;
    }
	
	

	/**
	 * @param string $container
	 * @param string $subType
	 */
	private function getMonitorNumOfEvents($container, $subType) {
		switch ($subType) {
			case ZM_TYPE_REQUEST_SLOW_EXEC:
				$container->setTitle('Number of slow execution requests')
				->setYTitle('Requests')
				->setValueType('');
		}
	}


    /**
     * @param integer $applicationIds
     * @param boolean $emptyIsAll
     * @return array
     */
    private function filterIdentityApplications($applicationIds) {
        try {
            if (!$applicationIds) {
                return $this->identityFilter->filterAppIds(array(), true);
            }
            return $this->identityFilter->filterAppIds($applicationIds);
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return array();
            }
        }
    }
}

