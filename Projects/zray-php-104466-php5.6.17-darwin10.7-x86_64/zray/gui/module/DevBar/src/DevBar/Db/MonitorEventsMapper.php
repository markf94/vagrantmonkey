<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract,
	ZendServer\Edition,
	Zend\Db\Sql\Predicate\Predicate,
	Configuration\MapperDirectives;
use ZendServer\Log\Log;

class MonitorEventsMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\MonitorEventsContainer';
	
	/**
	 * @param integer $requestId
	 * @return \ZendServer\Set
	 */
	public function getMonitorEvents($requestId) {
		if (empty($requestId)) {
			return null;
		}
		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		$select->where(array('request_id' => $requestId));
		
		return $this->selectWith($select);
	}
	
	public function removeDevBarRequests(array $ids) {
		$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
		Log::debug("Deleted $effected rows from devbar_monitor_events");
	}
}