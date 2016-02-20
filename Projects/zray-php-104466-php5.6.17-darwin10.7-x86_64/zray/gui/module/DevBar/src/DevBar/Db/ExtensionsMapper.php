<?php

namespace DevBar\Db;

use Configuration\MapperAbstract;
use ZendServer\Set;
use ZendServer\Log\Log;

class ExtensionsMapper extends MapperAbstract {
	
	protected $setClass = 'DevBar\ExtensionContainer';
	
	/**
	 * @param integer $requestId
	 * @return array
	 */
	public function findRequestDataTypesMap($requestId) {
		$select = $this->getTableGateway()->getSql()->select();
		$select->columns(array('namespace', 'data_type'));
		$select->group(array('namespace', 'data_type'));
		$select->where(array('request_id' => $requestId));
		$select->order('id asc');
		
		$resultSet = $this->selectWith($select, false, true);
		
		$datatypeMap = array();
		foreach($resultSet as $customDataRecord) {
			$datatypeMap[$customDataRecord['namespace']][] = $customDataRecord['data_type'];
		}
		
		return $datatypeMap;
	}
	
	/**
	 * @param integer $requestId
	 * @return Set
	 */
	public function findCustomDataForRequestId($requestId, $limit = 0) {
	    $select = $this->getTableGateway()->getSql()->select();
	    $select->where(array('request_id' => $requestId));
	    
	    if ($limit > 0) {
	       $select->limit(intval($limit));
	    }
	    
	    return $this->selectWith($select);
	}


	public function removeDevBarRequests(array $ids) {
		$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
		Log::debug("Deleted $effected rows from devbar_user_data");
	}
}
