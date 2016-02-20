<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract;
use DevBar\SuperGlobalContainer;
use ZendServer\Log\Log;

class ExceptionsMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\ExceptionsContainer';
	
	/**
	 * @param integer $requestId
	 * @return array
	 */
	public function getExceptions($requestId) {
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
		Log::debug("Deleted $effected rows from devbar_exceptions");
	}
}
