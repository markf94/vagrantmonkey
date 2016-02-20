<?php

namespace EventsGroup\Db;

use Audit\Container;
use Zend\Json\Json;
use Audit\AuditTypeInterface;
use Application\Module;
use ZendServer\Log\Log, ZendServer\Set, Zend\Db\TableGateway\TableGateway, Configuration\MapperAbstract, Zend\Db\Sql\Select, Zend\Db\Sql\Predicate\PredicateSet;
use Issue\Container as IssueContainer;
use Zend\Db\Sql\Where;

class Mapper extends MapperAbstract {
	private $orderTranslated = array (
			'name' => 'issues.rule_name',
			'date' => 'events.event_id',
			'severity' => 'severity' 
	);
	public function getRequestSummary($requestUid) {
		
		$res = zend_monitor_ui_get_request_summary_2($requestUid);
				
		return $res;
	}
	
	/**
	 * @param string $traceId
	 * @return \ZendServer\Set
	 */
	public function getEventGroupByTraceFile($traceId) {
		$where = new Where();
		$where->like('tracer_dump_file', "%{$traceId}");
		return $this->select($where);
	}
	
	public function getEventsGroup($groupId) {
	
		Log::debug ( __FUNCTION__ . " with group $groupId" );
	
		$select = new \Zend\Db\Sql\Select ();
		$select->from ( $this->getTableGateway ()->getTable () );
		$select->join ( "event_actions", $this->getTableGateway ()->getTable () . ".event_id = event_actions.event_id", array (
				"email_action" => "email",
				"url_action" => "url"
		), Select::JOIN_LEFT );
		$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array ());
		$select->columns ( array (
				'cluster_issue_id',
				"event_id",
				"app_id",
				"node_id",
				'repeats',
				'tracer_dump_file',
				'first_timestamp',
				'last_timestamp',
				'node_id',
				'request_id',
				'script_id',
				'extra_data',
				'event_type',
		)
		);
		$select->where ( array (
				"events.event_id = ?" => $groupId,
		) );		
	
		$result = $this->selectWith ( $select );
	
		$result = $this->addEventGroupsAttributesToResult($result);
			
		return new \EventsGroup\Container(array_pop($result));
	}
	
	public function getEventsGroups($issueId, $limit = 0, $offset = 0) {
				
		Log::debug ( __FUNCTION__ . " with issue $issueId" );
		
		$select = new \Zend\Db\Sql\Select ();
		$select->from ( $this->getTableGateway ()->getTable () );
		$select->join ( "event_actions", $this->getTableGateway ()->getTable () . ".event_id = event_actions.event_id", array (
				"email_action" => "email",
				"url_action" => "url" 
		), Select::JOIN_LEFT );
		$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array ());
		$select->join ( "issues", "events.issue_id = issues.id", array ());
		$select->columns ( array (
				'cluster_issue_id',
				"event_id",
				"app_id",
				"node_id",
				'repeats',
				'tracer_dump_file',
				'first_timestamp',
				'last_timestamp',
				'node_id',
				'request_id',
				'script_id',
				'extra_data',
				'event_type',
		)
		 );
		$select->where ( array (
				"events.cluster_issue_id = ?" => $issueId,
				"issues.status != ?" => ZM_STATUS_DELETED
		) );
		
		if ($limit > 0) {
			$select->limit ( intval($limit));
			$select->offset( intval($offset) );
		}
		
		$select->order("events.event_id DESC");
		
		$result = $this->selectWith ( $select );
		
		$result = $this->addEventGroupsAttributesToResult($result);
		
		return new Set ( $result, 'EventsGroup\Container' );
	}
	
	public function getEventGroupData($eventId) {
		
		Log::debug ( __FUNCTION__ . " with group $eventId");
		
		$datas = $this->getEventGroupsData(array($eventId));
		
		foreach ($datas->toArray() as $key => $data) {
			if ($eventId == $key) {
				return new \EventsGroup\DataContainer($data);
			}
		}
		
		return new \EventsGroup\DataContainer(array());
	}
	
	public function getEventGroupsData($eventIds) {
		Log::debug ( __FUNCTION__ . " with groups " . implode(",", $eventIds));
		
		$list = zend_monitor_get_event_groups_data ($eventIds); 

		$issueIds = array();
		foreach ($list as $key => $data) {
			$issueIds[] = $data[ZM_DATA_ISSUE_ID];
		}
		
		if ($issueIds) {
			$mvcData = $this->getEventsMvcData($issueIds);
			
			foreach ($list as $key => $data) {
				$data[ZM_DATA_REQUEST_COMPONENTS] = $mvcData[$data[ZM_DATA_ISSUE_ID]];
				$list[$key] = $data;
			}
		} else {
			$list = array();
		}	
		
		return new Set($list, 'EventsGroup\DataContainer');
	}
	
	
	public function getEventsMvcData($issueIds) {
		Log::debug (__FUNCTION__ . " with issues " . implode(",", $issueIds));
		
		if (!$issueIds) {
			return array();	
		}
		
		foreach ($issueIds as $key => $val) {
			$issueIds[$key] = (int) $val;
		}
		
		$result = $this->getTableGateway()->getAdapter()->query(
				"select distinct cluster_issue_id, comp_name, comp_value from request_components where cluster_issue_id IN (" . implode(",", $issueIds) . ")"
				, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->toArray();
				
		$data = array();
		foreach ($issueIds as $id) {
			$data[$id] = array();
		}
		
		foreach ($result as $comp) {
			$values = array_values($comp);
			$data[$values[0]][$values[1]] = $values[2];
		}
		return $data;				
	}
	
	/**
	 *
	 * @param array $issueIds        	
	 * @throws Exception
	 */
	public function getIssuesLastEventGroupData(array $issueIds = array()) {
		Log::debug (__FUNCTION__ . " with issues " . implode(",", $issueIds));
		
		if ($issueIds) {
		$select = new \Zend\Db\Sql\Select ();
			$select->from ( $this->getTableGateway ()->getTable () );
			$select->columns ( array (
					'cluster_issue_id',
					"event_id",
					"app_id",
					"node_id",
					'repeats',
					'tracer_dump_file',
					'first_timestamp',
					'last_timestamp',
					'node_id',
					'request_id',
					'script_id',
					'extra_data',
					'event_type',
			)
			 );
			$select->join ( "matched_rules", "events.event_id = matched_rules.event_id", array ());
			
			$select->where ( array (
					'cluster_issue_id IN (' . implode ( ",", $issueIds ) . ")" 
			) );
			$select->order ( "event_id DESC" );
			$select->group ( 'cluster_issue_id' );
			
			$result = $this->selectWith ( $select );
			
			$result = $this->addEventGroupsAttributesToResult($result);
		} else {
			$result = array();
		}
				
		
		return new Set ( $result, 'EventsGroup\Container' );
	}
	
	public function addEventGroupsAttributesToResult($result) {
		
		$groups = array();
		foreach ($result as $key => $value) {
			$groups[] = (int)$value['event_id'];
		}
		Log::debug("Fetching event groups attributes for groups " . implode(",", $groups));
		
		$attributes = zend_monitor_get_event_groups_attributes($groups);
		
		foreach ($result as $key => $value) {
			$groupId = (int)$value['event_id'];
			if (isset($attributes[$groupId])) {
				$value[ZM_DATA_ATTR] = $attributes[$groupId];
			} else {
				Log::warn("Cannot find event group attributes for group $groupId (issue " . $value['cluster_issue_id'] . ")");
			}
			$result[$key] = $value;
		}
		
		return $result;		
	}
}
