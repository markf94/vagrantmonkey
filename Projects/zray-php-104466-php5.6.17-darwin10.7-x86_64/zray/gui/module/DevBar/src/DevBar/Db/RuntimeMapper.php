<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract,
	ZendServer\Edition,
	Zend\Db\Sql\Predicate\Predicate,
	Configuration\MapperDirectives;
use ZendServer\Log\Log;
use ZendServer\Set;

class RuntimeMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\RuntimeContainer';
	
	/**
	 * @param integer $requestId
	 * @return \DevBar\RuntimeContainer
	 */
	public function getRuntime($requestId) {
		return $this->getRequestsRuntime(array($requestId))->current();
	}
	
	/**
	 * @param array $requestId
	 * @return \ZendServer\Set
	 */
	public function getRequestsRuntime(array $requestId) {
		if (! count($requestId)) {
			return new Set(array(), $this->setClass);
		}
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);

		$select->where(array('request_id' => $requestId));
		
		return $this->selectWith($select);
	}
	
	public function removeDevBarRequests(array $ids) {
		$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
		Log::debug("Deleted $effected rows from devbar_processing_breakdown");
	}
	
}
