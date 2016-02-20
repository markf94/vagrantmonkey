<?php

namespace Issue\Db;

use Deployment\IdentityApplicationsAwareInterface;
use Deployment\IdentityFilterException;
use Deployment\IdentityFilterInterface;
use Zend\Db\Sql\Predicate\Predicate;

use Zend\Db\Sql\Expression;
use Audit\Container;
use Zend\Json\Json;
use Audit\AuditTypeInterface;
use Application\Module;
use ZendServer\Log\Log, ZendServer\Set, Zend\Db\TableGateway\TableGateway, Configuration\MapperAbstract, Zend\Db\Sql\Select, Zend\Db\Sql\Predicate\PredicateSet;
use Issue\Container as IssueContainer;
use Zend\Db\Adapter\Platform\Mysql as MysqlPlatform;
use ZendServer\Exception;
use Zend\Db\Sql\Where;
use MonitorUi\Wrapper;

class Mapper extends MapperAbstract implements IdentityApplicationsAwareInterface {
	

	/**
	 * @var Wrapper
	 */
	private $wrapper;
	
	private $orderTranslated = array (
			'name' => 'issues.rule_name',
			'date' => 'events_last_timestamp',
			'severity' => 'events.severity',
			'id' => 'issues.cluster_issue_id',
	        'repeats' => 'repeats',
	);

    /**
     * @var IdentityFilterInterface
     */
    private $identityFilter;
    /**
     * @param IdentityFilterInterface $filter
     */
    public function setIdentityFilter(IdentityFilterInterface $filter)
    {
        $this->identityFilter = $filter;
    }

	public function getRequestSummary($requestUid) {
	}
	
	/**
	 *
	 * @param array $issueIds        	
	 * @throws Exception
	 */
	public function getIssuesLastEventGroupData(array $issueIds = array()) {
		Log::debug ( __FILE__ );
		
		$select = new \Zend\Db\Sql\Select();
		$select->from ( $this->getTableGateway ()->getTable () );
		$select->columns ( array (
				'events.cluster_issue_id' 
		)
		 );
		$select->join ( "issues", "issues.cluster_issue_id = events.cluster_issue_id", array (
				"event_id",
				'tracer_dump_file' 
		), Select::JOIN_LEFT  );
		
		$select->order ( "events.event_id DESC" );
		$select->group ( 'issues.cluster_issue_id' );
		
		$result = $this->selectWith ( $select );
		
		return $result;
	}
	public function applyCommonColumns($select) {
		$select->columns ( array (
				'cluster_issue_id' => new \Zend\Db\Sql\Expression ( 'events.cluster_issue_id'),
				'rule_name' => new \Zend\Db\Sql\Expression ( 'issues.rule_name'),
				'event_type' => new \Zend\Db\Sql\Expression ( 'issues.event_type'),
				'agg_hint' => new \Zend\Db\Sql\Expression ( 'issues.agg_hint'),
				'full_url' => new \Zend\Db\Sql\Expression ( 'issues.full_url'),
				'status' => new \Zend\Db\Sql\Expression ( 'issues.status'),
				'file_name' => new \Zend\Db\Sql\Expression ( 'issues.file_name'),
				'function_name' => new \Zend\Db\Sql\Expression ( 'issues.function_name'),
				'line' => new \Zend\Db\Sql\Expression ( 'issues.line'),
				'severity' => new \Zend\Db\Sql\Expression ( 'issues.severity'),
				'app_id' => new \Zend\Db\Sql\Expression ( 'events.app_id' ),
				'rule_id' => new \Zend\Db\Sql\Expression ( 'matched_rules.id'),
				'events_last_timestamp' => new \Zend\Db\Sql\Expression ( 'max(events.last_timestamp)' ),
				'last_timestamp' => new \Zend\Db\Sql\Expression ( 'max(events.last_timestamp)' ),
				'first_timestamp' => new \Zend\Db\Sql\Expression ( 'min(events.first_timestamp)' ),
				'repeats' => new \Zend\Db\Sql\Expression ( 'sum(events.repeats)' ),
				'max_event_id' => new \Zend\Db\Sql\Expression ( 'max(events.event_id)' ),
				'tracer_dump_file' => new \Zend\Db\Sql\Expression ( 'max(events.tracer_dump_file)' )  // just
		                                                                                   // need
		                                                                                   // to
		                                                                                   // see
		                                                                                   // if
		                                                                                   // there
		                                                                                   // are
		                                                                                   // any
		                                                                                   // tracer
		 ));
		$select->group('events.cluster_issue_id');
		return $select;
	}
	
	private function getOrderTranslated($orderby) {
		
		if (isset($this->orderTranslated [$orderby])) {
			return $this->orderTranslated [$orderby];
		} else {
			throw \Exception("Unknown order by - $orderby");
		}
	}
	
	public function getIssues(array $params, $limit, $offset, $orderby, $direction) {
		if ($orderby == "severity" && $offset == 0) {
			$result = $this->getIssuesBySeverityOrder ( $params, $limit, $direction );
			$result = $this->addRequestComponentsToResultSet($result);
		} else {
			
			$issues = $this->getRelevantIssueIds($params, $limit, $offset, $orderby, $direction);
			if ($issues) {
			
			    $result = array();
			    $issuesArray = array_chunk($issues, 900); // limit 1000
			    foreach ($issuesArray as $chunk) {
    				$select = new \Zend\Db\Sql\Select ();
    				
    				$select->from ( $this->getTableGateway ()->getTable () );
    				$select->join ( "issues", "issues.id = events.issue_id", array ());
    				$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array (), Select::JOIN_LEFT );
    				// $select->join ( "request_components", "issues.cluster_issue_id = request_components.cluster_issue_id", array(), Select::JOIN_LEFT );
    				
    				$select = $this->applyCommonColumns ( $select );
    				
    				$select->where(array(
    						'issues.cluster_issue_id IN (' . implode(",", $chunk) . ")"
    						, "issues.status != " . ZM_STATUS_DELETED));
    					
    				$select->order ( array (
    						$this->getOrderTranslated($orderby) => $direction 
    				) );
    				
    				$selectResult = $this->selectWith ( $select );
    				$selectResult = $this->addRequestComponentsToResultSet($selectResult);
    				$result = array_merge($selectResult, $result);
			    }
			} else {
				$result = array();
			}
		}
		
		return new Set ( $result, 'Issue\Container' );
	}
	
	private function getRelevantIssueIds(array $params, $limit, $offset, $orderby, $direction) {
		$select = new \Zend\Db\Sql\Select ();
		$select->from ( $this->getTableGateway ()->getTable () );
		$select->join ( "issues", "issues.id = events.issue_id", array (), Select::JOIN_INNER );
		$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array (), Select::JOIN_LEFT );
		
		$select->columns(array(   'repeats' => 'repeats',
		                          'cluster_issue_id' => new Expression("events.cluster_issue_id"),
		                          'events_last_timestamp' => new \Zend\Db\Sql\Expression ( 'max(events.last_timestamp)')));
        try {
            $this->applyParams ( $select, $params );
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return array();
            }
        }
        if ($limit) {
			$select->limit ( intval($limit) );
        }
        if ($offset) {
			$select->offset ( intval($offset) );
        }
        
		$select->order ( array (
				$this->orderTranslated [$orderby] => $direction
		) );
		
		$select->group('issues.cluster_issue_id');
		
		$result = $this->selectWith($select);
		
		$list = array();
		foreach ($result as $arr) {
		    $arrayData = array_pop($arr);
			$list[] = $arr['cluster_issue_id'];
		}
		
		Log::debug(__FUNCTION__ . " returned " . count($result) . " issues");
		return $list;
	}
	
	public function getRelevantMvc(array $issues) {
	    $result = array();
	    if ($issues) {
	       	$result = $this->getTableGateway()->getAdapter()->query(
				"select distinct cluster_issue_id, comp_name, comp_value from request_components where cluster_issue_id IN (" . implode(",", $issues) . ")"
				, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->toArray();
	       	
		}
		
		$mvcArrayById = array();
		foreach ($result as $issueMvc) {
		    $mvcArrayById[$issueMvc['cluster_issue_id']][$issueMvc['comp_name']] = $issueMvc['comp_value'];
		}
		
	    Log::debug(__FUNCTION__ . " returned " . count($mvcArrayById) . " mvc issues");
	    return $mvcArrayById;
	}
	
	public function getIssuesBySeverityOrder(array $params, $limit, $direction) {
		
		Log::debug ( __FUNCTION__ . " with limit $limit");
		
		$severities = array (
				ZM_SEVERITY_SEVERE,
				ZM_SEVERITY_NORMAL,
				ZM_SEVERITY_INFO 
		);
		if ($direction == "ASC") {
			$severities = array_reverse ( $severities );
		}
		
		$result = array ();
		
		foreach ( $severities as $severity ) {
			$select = new \Zend\Db\Sql\Select ();
			$select->from ( $this->getTableGateway ()->getTable () );
			$select->join ( "issues", "issues.id = events.issue_id", array (), Select::JOIN_LEFT );
			$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array (), Select::JOIN_LEFT );
			
			$select = $this->applyCommonColumns ( $select );

            try {
                $this->applyParams ( $select, $params );
            } catch (IdentityFilterException $ex) {
                if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                    return array();
                }
            }

			$select->where ( array (
					"issues.severity = $severity" 
			) );
			
			if ($limit) {
				$select->limit ( intval($limit) );
			}
			$select->offset (0);
			
			$select->order ( "events.cluster_issue_id DESC" );

			$result = array_merge($result, $this->selectWith($select));
			
			Log::debug(__FUNCTION__ . " Found " . count($result) . " issues");
			$limit -= count ( $result );
			if ($limit <= 0) {
				break;
			}
		}
		
		$result = $this->addRequestComponentsToResultSet($result);
		
		return $result;
	}
	public function getIssuesCount(array $params) {
		$select = new \Zend\Db\Sql\Select ();
		$select->from ( $this->getTableGateway ()->getTable () );
		$select->join ( "issues", "issues.id = events.issue_id", array (), Select::JOIN_LEFT  );
		$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array (), Select::JOIN_LEFT );
		
		$select->columns ( array (
				'total' => new \Zend\Db\Sql\Expression ( 'count(distinct (issues.cluster_issue_id))' ) 
		) );
		try {
            $this->applyParams ( $select, $params );
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return 0;
            }
        }
		$result = $this->selectWith ( $select );
		return $result [0] ['total'];
	}
	/**
	 * @param integer $issueId
	 * @throws Exception
	 * @return \Issue\Container
	 */
	public function getIssue($issueId) {
		
		Log::debug(__FUNCTION__ . " with issue $issueId");
		
		$select = new Select ();
		$select->from ( $this->getTableGateway ()->getTable () );
		$select->where ( array (
				"issues.cluster_issue_id = ?" => $issueId,
				"issues.status != " . ZM_STATUS_DELETED
		) );
		$select->join ( "issues", "issues.id = events.issue_id", array (), Select::JOIN_LEFT );
		$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array (), Select::JOIN_LEFT );
		$select = $this->applyCommonColumns ( $select );
		
		$result = $this->selectWith ( $select );
		if (! $result[0]['cluster_issue_id']) {
			throw new Exception(_t('Issue not found'));
		}
		
		$issue = $result [0];
		
		$result = $this->addRequestComponentsToResultSet(array($issue));
		
		$moreIssueDetails = $this->getWrapper()->getIssueData($issueId);
		if (isset($moreIssueDetails[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES])) {
			$issue[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES] = $moreIssueDetails[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES];
		}
		return new IssueContainer ($issue);
	}

	/**
	 * @return Wrapper
	 */
	public function getWrapper() {
		if (is_null($this->wrapper)) {
			$this->wrapper = new Wrapper();
		}
		return $this->wrapper;
	}
	
	/**
	 * @param \MonitorUi\Wrapper $wrapper
	 */
	public function setWrapper($wrapper) {
		$this->wrapper = $wrapper;
	}
	
    /**
     *
     * @param Select $query
     * @param array $params
     * @throws IdentityFilterException
     */
    private function applyParams($query, $params) {
		
        $where = new Where();
        $where->equalTo('issues.status', ZM_STATUS_NEW);

        if (isset ($params ['ruleNames']) && !empty($params ['ruleNames'])) {
            $where->in('matched_rules.id', $params ['ruleNames']);
        }

        if (isset ( $params ['eventTypes'] ) && !empty($params ['eventTypes'])) {
            $eventTypes = $this->applyEventTypes($params['eventTypes']);
            $where->in('issues.event_type', $eventTypes);
        }

        if (isset ( $params ['severities'] ) && !empty($params ['severities'])) {
            $severities[] = current($params['severities']);
            $where->in('issues.severity', $severities);
        }

        if (isset ( $params ['applicationIds'] ) && !empty($params ['applicationIds'])) {
            $this->identityFilter->setAddGlobalAppId(false);
        } else {
            $params['applicationIds'] = array();
            $this->identityFilter->setAddGlobalAppId(true);
        }
        
        if (isset ( $params ['aggKeys'] ) && is_array($params ['aggKeys']) && !empty($params ['aggKeys'])) {
        	$where->in('issues.agg_key', $params ['aggKeys']);
        }
        
        if (isset ( $params ['fullUrl'] ) && !empty($params ['fullUrl'])) {
        	$where->equalTo('issues.full_url', $params ['fullUrl']);
        }
        
        $params['applicationIds'] = $this->identityFilter->filterAppIds($params['applicationIds'],true);
        $where->in('events.app_id', $params ['applicationIds']);

        if (isset ( $params ['from'] ) && !empty($params['from'])) {
            $from = (int) $params ['from'];
            $dates = new Predicate();
            $dates->greaterThanOrEqualTo('events.first_timestamp', $from)
                ->or
                ->greaterThanOrEqualTo('events.last_timestamp', $from);
            $where->andPredicate($dates);
        }
        if (isset ( $params ['to'] ) && !empty($params ['to'])) {
            $to = (int) $params['to'];
            $dates = new Predicate();
            $dates->lessThanOrEqualTo('events.first_timestamp', $to)
                ->or
                ->lessThanOrEqualTo('events.last_timestamp', $to);
            $where->andPredicate($dates);
        }

        if (isset ( $params ['freeText'] ) && !empty($params ['freeText'])) {
            $freeText = new Predicate();
            $freeText->like('issues.rule_name', "%{$params ['freeText']}%")
                ->or
                ->like('issues.function_name', "%{$params ['freeText']}%")
                ->or
                ->like('issues.full_url', "%{$params ['freeText']}%");

			// @TODO: add search in the routing: comp_name and comp_value
			
            $where->andPredicate($freeText);
        }
        
        $query->where($where);
        return $query;
    }

	/**
	 * @brief Add request components data to the result set
	 * @param Set|array $resultSet 
	 * @return  
	 */
	protected function addRequestComponentsToResultSet($resultSet) {
		if (empty($resultSet)) {
			return $resultSet;
		}

		// gather IDs
		$clusterIssueIds = array();
		foreach ($resultSet as $row) {
			$clusterIssueIds[] = $row['cluster_issue_id'];
		}

		$select = new \Zend\Db\Sql\Select('request_components');
		$select->columns(array(
			'cluster_issue_id',
			'comp_name' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(comp_name)'),
			'comp_value' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(comp_value)'),
		));
		$select->where(array('cluster_issue_id' => array_unique($clusterIssueIds)));
		$select->group('cluster_issue_id');

		$tableGateway = new \Zend\Db\TableGateway\TableGateway('request_components', $this->getTableGateway()->getAdapter());
		$result = $tableGateway->selectWith($select);
		if (!$result) {
			return $resultSet;
		}

		$result = $result->toArray();

		if (empty($result)) {
			return $resultSet;
		}

		// reorder the result
		$compRecords = array();
		foreach ($result as $row) {
			$compRecords[$row['cluster_issue_id']] = $row;
		}

		$newResultSet = array();
		foreach ($resultSet as $row) {
			$clusterIssueId = $row['cluster_issue_id'];
			$newResultSet[] = array_merge($row, array(
				'comp_name' => isset($compRecords[$clusterIssueId]) && isset($compRecords[$clusterIssueId]['comp_name']) ? $compRecords[$clusterIssueId]['comp_name'] : '',
				'comp_value' => isset($compRecords[$clusterIssueId]) && isset($compRecords[$clusterIssueId]['comp_value']) ? $compRecords[$clusterIssueId]['comp_value'] : '',
			));
		}

		return $newResultSet;
	}

    private function applyEventTypes($eventTypesParams) {
		$eventTypes = array();
		foreach ($eventTypesParams as $evType) {
			if (is_numeric($evType)) {// integer, already translated to const
				$eventTypes [] = $evType;
			} else {
				switch ($evType) {
					case \Issue\Filter\Dictionary::TYPE_FUNCTION_ERROR :
						$eventTypes [] = ZM_TYPE_FUNCTION_ERROR;
						break;
					case \Issue\Filter\Dictionary::TYPE_PHP_ERROR :
						$eventTypes [] = ZM_TYPE_ZEND_ERROR;
						break;
					case \Issue\Filter\Dictionary::TYPE_JAVA_EXCEPTION :
						$eventTypes [] = ZM_TYPE_JAVA_EXCEPTION;
						break;
					case \Issue\Filter\Dictionary::TYPE_JQ_JOB_EXECUTION_ERROR :
						$eventTypes [] = ZM_TYPE_JQ_JOB_EXEC_ERROR;
						break;
					case \Issue\Filter\Dictionary::TYPE_TRACER_WRITE_FILE_FAIL :
						$eventTypes [] = ZM_TYPE_TRACER_FILE_WRITE_FAIL;
						break;
					case \Issue\Filter\Dictionary::TYPE_JQ_JOB_LOGICAL_FAILURE :
						$eventTypes [] = ZM_TYPE_JQ_JOB_LOGICAL_FAILURE;
						break;
					case \Issue\Filter\Dictionary::TYPE_JQ_JOB_EXECUTION_DELAY :
						$eventTypes [] = ZM_TYPE_JQ_JOB_EXEC_DELAY;
						break;
					case \Issue\Filter\Dictionary::TYPE_CUSTOM :
						$eventTypes [] = ZM_TYPE_CUSTOM;
						break;
					case \Issue\Filter\Dictionary::TYPE_SLOW_FUNCTION :
						$eventTypes [] = ZM_TYPE_FUNCTION_SLOW_EXEC;
						break;
					case \Issue\Filter\Dictionary::TYPE_SLOW_SCRIPT :
						$eventTypes [] = ZM_TYPE_REQUEST_SLOW_EXEC;
						$eventTypes [] = ZM_TYPE_REQUEST_RELATIVE_SLOW_EXEC;
						break;
					case \Issue\Filter\Dictionary::TYPE_OUTPUT_SIZE :
						$eventTypes [] = ZM_TYPE_REQUEST_RELATIVE_LARGE_OUT_SIZE;
						break;
					case \Issue\Filter\Dictionary::TYPE_MEMORY_USAGE :
						$eventTypes [] = ZM_TYPE_REQUEST_LARGE_MEM_USAGE;
						$eventTypes [] = ZM_TYPE_REQUEST_RELATIVE_LARGE_MEM_USAGE;
						break;
					default: // integer, already translated to const
						$eventTypes [] = $evType;
				};
			}
		}
		return $eventTypes;
	}
}
