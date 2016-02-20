<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract,
	ZendServer\Edition,
	Zend\Db\Sql\Predicate\Predicate,
	Configuration\MapperDirectives;

use ZendServer\Log\Log;

class LogEntriesMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\LogEntryContainer';
	
	/**
	 * @param integer $requestId
	 * @return \ZendServer\Set
	 */
	public function getEntries($requestId) {
		if (empty($requestId)) {
			return array();
		}
		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		$select->where(array('request_id' => $requestId));
		
		return $this->selectWith($select);
	}
	

	public function removeDevBarRequests(array $ids) {
		$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
		Log::debug("Deleted $effected rows from devbar_log_entries");
	}
	

	public function getRequestLogEntriesExtraData($ids) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->where(array("request_id IN (" . implode(",", $ids) . ")"));
		return $this->selectWith($select);
	}
	
}
