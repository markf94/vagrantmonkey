<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract,
	ZendServer\Edition,
	Zend\Db\Sql\Predicate\Predicate,
	ZendServer\Log\Log,
	Configuration\MapperDirectives;

class BacktraceMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\BacktraceContainer';
	
	/**
	 * @param integer/array $requestId
	 * @return \ZendServer\Set
	 */
	public function getBacktrace($id) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		$select->where(array('id' => $id));
		return $this->selectWith($select);
	}
	
	public function removeDevBarRequests($sqlQueriesExtraData, $logEntriesExtraData) {
		
	
		$backtracesIds = array();
		foreach ($sqlQueriesExtraData as $el) {
			$backtraceIds[] = $el->getBacktraceId();
		}
		foreach ($logEntriesExtraData as $el) {
			$backtraceIds[] = $el->getBacktraceId();
		}
		$effected = $this->getTableGateway()->delete(array("id IN (" . implode(",", $backtracesIds) . ")"));
		Log::debug("Deleted $effected rows from devbar_backtrace");
		
	}
	
}
