<?php

namespace UrlInsight\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract;

class ZraySnapshotsMapper extends MapperAbstract {
	
	/**
	 * Get zray snapshots by 
	 * @param unknown $resourceId
	 * @param number $limit
	 * @return NULL|Ambigous <\ZendServer\Set, multitype:, NULL, \Zend\Db\ResultSet\ResultSetInterface, \Zend\Db\ResultSet\ResultSet, multitype:NULL multitype: Ambigous <\ArrayObject, multitype:, \Zend\Db\ResultSet\mixed, unknown> >
	 */
	public function getZraySnapshots($resourceId, $limit = 5) {
		
		// check parameters
		if (!is_numeric($resourceId) || !($resourceId > 0)) return null;
		if (!is_numeric($limit)) $limit = 5;
		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->columns(array('zray_request_id', 'zray_request_time', 'resource_id'))
			->where(array('resource_id' => $resourceId))
			->order('zray_request_time desc')
			->limit(intval($limit));
		
		return $this->selectWith($select);		
	}
}