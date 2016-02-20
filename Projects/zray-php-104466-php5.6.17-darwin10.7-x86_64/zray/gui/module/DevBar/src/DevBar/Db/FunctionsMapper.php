<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract,
	ZendServer\Edition,
	Zend\Db\Sql\Predicate\Predicate,
	Configuration\MapperDirectives;

use ZendServer\Log\Log;

class FunctionsMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\FunctionStatsContainer';
	
	/**
	 * @param integer/array $requestId
	 * @return \ZendServer\Set
	 */
	public function getFunctions($requestId) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		if (is_array($requestId)) {
			$retVal = array();
			
			if (!empty($requestId)) {
				$select->where(array('request_id' => $requestId));
				
				// group results by request id
				$result = $this->selectWith($select);
				if ($result) foreach ($result as $row) {
					if (!isset($retVal[$row->getRequestId()])) {
						$retVal[$row->getRequestId()] = array();
					}
					
					$retVal[$row->getRequestId()][] = $row;
				}
			}
			
			return $retVal;
		} else {
			$select->where(array('request_id' => $requestId));
			return $this->selectWith($select);
		}
		
	}
	

	public function removeDevBarRequests(array $ids) {
		$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
		Log::debug("Deleted $effected rows from devbar_functions_stats");
	}
}
