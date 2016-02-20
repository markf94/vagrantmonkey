<?php

namespace Codetracing;
use Zend\Db\Sql\Expression;

use Deployment\IdentityApplicationsAwareInterface;

use Deployment\IdentityFilterInterface;

use Zend\XmlRpc\Value\Integer;


use Zend\Db\Sql\Predicate\Like;

use Zend\Db\Table\Select;

use Zend\Db\Sql\Predicate\PredicateSet,
Zend\Db\Sql\Where
;

use Configuration\MapperAbstract;
use ZendServer\Set;

class TraceFilesMapper extends MapperAbstract implements IdentityApplicationsAwareInterface {

	protected $setClass='\Codetracing\TraceFileContainer';
	
	/**
	 * @var IdentityFilterInterface
	 */
	private $identityFilter;
	
	const TRACE_ID = 'trace_id';
	const FILEPATH = 'filepath';
	const HOST = 'host';
	const ORIGINAL_URL = 'originating_url';	
	const FINAL_URL = 'final_url';
	const TRACE_SIZE = 'trace_size';
	const REASON = 'reason';
	const TRACE_TIME = 'trace_time';
	const NODE_ID = 'node_id';
	const APP_ID = 'app_id';
	
	/**
	 * 
	 * @param array $filters
	 * @param int $limit
	 * @param int $offset
	 * @param string $orderBy
	 * @param string $direction
	 * @return Ambigous <\Zend\Db\ResultSet\ResultSet, \ZendServer\Set>
	 */
	public function selectAllFileTraces(array $filters = array(), $limit = 20, $offset = 0, $orderBy = self::TRACE_TIME, $direction = 'Desc') {
		$limit = intval($limit); // MySQL does not like getting the value in quotes. this might happen as this value might be received from the ini file
		$offset = intval($offset); // MySQL does not like getting the value in quotes. this might happen when paging
		$where = $this->getWhere($filters);
		$select = new \Zend\Db\Sql\Select($this->getTableGateway()->getTable());
	    $select->where($where);
	    $select->limit($limit);
	    $select->offset($offset);
	    $select->order($orderBy . ' ' . $direction);
	    return $this->selectWith($select);
	}
    
	/**
	 * 
	 * @param array $filters
	 */
	public function getTraceCount(array $filters = array()) {
		$count = new \Zend\Db\Sql\Select($this->getTableGateway()->getTable());
		$count->columns(array('count' => new Expression('COUNT(*)')));
		$where = $this->getWhere($filters);
		$count->where($where);
		$sql = $count->getSqlString($this->getTableGateway()->getAdapter()->getPlatform());
	    $result = $this->getTableGateway()->getAdapter()->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
	    $row = $result->toArray();
	    return $row[0]['count'];
	}

	/**
	 * @param array $traceFileIds
	 * @return Set
	 */
	public function findCodetracesByIds(array $traceFileIds) {
		$where = new Where();
		$where->in(self::TRACE_ID, $traceFileIds);
		$where->in(self::APP_ID, current($this->filterIdentityApplications()));
		
		$resultArray = $this->select($where, false);
		
		$returnSet = array();
		foreach ($resultArray as $row) {
			$returnSet[$row[self::TRACE_ID]] = $row;
		}
		
		return new Set($returnSet, $this->setClass);
	}

	/**
	 * @param string $traceFileId
	 * @return \Codetracing\TraceFileContainer
	 */
	public function findCodetraceById($traceFileId) {
		return $this->select(array('trace_id = \''. $traceFileId .'\' AND ' . 
				self::APP_ID .' IN ('. implode(',', current($this->filterIdentityApplications())) .')')
			)->current()->toArray();
	}
	
	/**
	 * @param array $traceFileIds
	 * @return integer
	 */
	public function deleteByTraceIds(array $traceFileIds) {
		return $this->getTableGateway()->delete('trace_id IN (\''. implode('\',\'', $traceFileIds) .'\') AND ' . 
				self::APP_ID .' IN ('. implode(',', current($this->filterIdentityApplications())) .')');
	}

	/* (non-PHPdoc)
	 * @see \Deployment\IdentityApplicationsAwareInterface::setIdentityFilter()
	 */
	public function setIdentityFilter(IdentityFilterInterface $filter) {
		$this->identityFilter = $filter;
		return $this;
	}
	
	/**
	 * @param integer $applicationIds
	 * @param boolean $emptyIsAll
	 * @return integer
	 */
	protected function filterIdentityApplications($params = array()) {
		$this->identityFilter->setAddGlobalAppId(true);
		$applications = isset($params['applications']) ? $params['applications'] : array();
		$params['applications'] = $this->identityFilter->filterAppIds($applications,true);
		return $params;
	}
	
	/**
	 * 
	 * @param array $filters
	 * @return \Zend\Db\Sql\Where
	 */
	protected function getWhere(array $filters = array()) {
		$filters = $this->filterIdentityApplications($filters);
		$where = new Where();
		if (isset($filters['type']) && $filters['type'] != '-1') {
			$type = $filters['type'];
			$where->equalTo(self::REASON, $type);
		}
		 
		if (isset($filters['applications']) && is_array($filters['applications'])) {
			$applications = (array) $filters['applications'];
			$where->in(self::APP_ID, $applications);
		}
		 
		if (isset($filters['freetext']) && $filters['freetext']) {
			$freetext = $filters['freetext'];
			$where->like(self::ORIGINAL_URL, "%$freetext%");
			$where->orPredicate(new Like(self::TRACE_ID, "%$freetext%"));
		}
		 
		return $where;
	}
}
