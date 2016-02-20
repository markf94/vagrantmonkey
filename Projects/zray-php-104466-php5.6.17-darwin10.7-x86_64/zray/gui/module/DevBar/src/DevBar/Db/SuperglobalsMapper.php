<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract;
use DevBar\SuperGlobalContainer;
use ZendServer\Log\Log;

class SuperglobalsMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\SuperGlobalContainer';
	
	/**
	 * @param integer $requestId
	 * @return array
	 */
	public function getSuperglobals($requestId) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->order('id', 'sg_name');
		$select->where(array('request_id' => $requestId));
		
		$globalsRowset = $this->selectWith($select, false, true);
		
		$globalsMap = array();
		
		foreach ($globalsRowset as $superglobal) {
			$globalsMap[$superglobal['sg_name']][]= new SuperGlobalContainer($superglobal->getArrayCopy());
		}
		
		return $globalsMap;
	}
	

	public function removeDevBarRequests(array $ids) {
		$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
		Log::debug("Deleted $effected rows from devbar_superglobals_data");
	}
}
