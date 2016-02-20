<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract,
	DevBar\Filter\Dictionary,
	ZendServer\Edition,
	Zend\Db\Sql\Predicate\Predicate,
	ZendServer\Log\Log,
	Zend\Db\Sql\Predicate\Like,
	Configuration\MapperDirectives;

class RequestsMapper extends MapperAbstract {
	
	const COLUMN_ID 			= 'id';
	const COLUMN_PAGE_ID 		= 'page_id';
	const COLUMN_URL_ID			= 'url_id';
	const COLUMN_STATUS_CODE	= 'status_code';
	const COLUMN_METHOD			= 'method';
	const COLUMN_START_TIME		= 'start_time';
	const COLUMN_RUN_TIME		= 'request_time';
	const COLUMN_URL			= 'url';
	
	protected $setClass = '\DevBar\RequestContainer';
	
	protected $systemStatus;
	
	/**
	 * @var Edition
	 */
	protected $edition;
	
	/**
	 * @var MapperDirectives
	 */
	protected $directivesMapper;
	
	/**
	 * @param string $pageId
	 * @return \DevBar\RequestContainer
	 */
	public function getFirstRequests($pageId) {
		

		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		$select->join('devbar_requests_urls', 'devbar_requests.url_id = devbar_requests_urls.id', array('url'));
		
		$select->where(array($table . '.' . self::COLUMN_PAGE_ID => $pageId, 'is_primary_page' => '1'));
		$select->limit(1);
		
		return $this->selectWith($select)->current();
	}
	
	/**
	 * @param integer $id
	 * @return \DevBar\RequestContainer
	 */
	public function getRequest($id) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		$select->join('devbar_requests_urls', 'devbar_requests.url_id = devbar_requests_urls.id', array('url'));
		
		$select->where(array($table . '.' . self::COLUMN_ID => $id));
		
		return $this->selectWith($select)->current();
	}
	
	/**
	 * @param string $pageId
	 * @param string $lastId
	 * @param integer $limit
	 * @param array $columns
	 * @return \ZendServer\Set
	 */
	public function getRequests($pageId, $lastId = '', $limit = 0, $columns = array()) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);

		if (count($columns) > 0) {
			$select->columns($columns);
		}
		
		$select->join('devbar_requests_urls', 'devbar_requests.url_id = devbar_requests_urls.id', array('url'));
		
		$select->where(array($table . '.' . self::COLUMN_PAGE_ID => $pageId));
		
		if (! empty($lastId)) {
			$lastIdPredicate = new Predicate();
			$lastIdPredicate->greaterThan($table . '.' . self::COLUMN_ID, $lastId);
			$select->where($lastIdPredicate);
		}
		
		if ($limit > 0) {
			$select->limit(intval($limit));
		}
		
		return $this->selectWith($select);
	}
	
	/**
	 * @brief return the names of the columns from `devbar_requests` and `devbar_requests_urls`
	 * @return array
	 */
	public function getDevbarRequestColumnNames() {
		$metadata = new \Zend\Db\Metadata\Metadata($this->getTableGateway()->getAdapter());
		$devbarRequestsColumnNames = $metadata->getColumnNames('devbar_requests');
		
		$metadata = new \Zend\Db\Metadata\Metadata($this->getTableGateway()->getAdapter());
		$devbarRequestUrlsColumnNames = $metadata->getColumnNames('devbar_requests_urls');
		
		return array_merge($devbarRequestsColumnNames, $devbarRequestUrlsColumnNames);
	}
	
	public function removeRequests($ids) {
	    // devbar_requests
	    $effected = $this->delete(array("id IN (" . implode(",", $ids) . ")"));
	    Log::debug("Deleted $effected rows from devbar_requests");
	}
	
	public function getRequestExtraIdsForRemove($ids) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->where(array("id IN (" . implode(",", $ids) . ")"));
		return $this->selectWith($select);
	}
	
	/**
	 * @brief same as `getRequestsFromTimestamp` but returns a number
	 * @param int $fromTimestamp 
	 * @param int $limit 
	 * @param int $offset 
	 * @param string $order 
	 * @param string $direction 
	 * @param array $filters 
	 * @return int
	 */
	public function getRequestsCountFromTimestamp($fromTimestamp, $limit = 0, $offset = 0, $order = null, $direction = null, $filters = array()) {
		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->join('devbar_requests_urls', 'devbar_requests.url_id = devbar_requests_urls.id', array('url'));
		
		$select->columns(array(
			'total' => new \Zend\Db\Sql\Expression('count(*)'),
		));
		
		$condition = new Predicate(null, Predicate::OP_AND);
		if (!isset($filters['from']) && !isset($filters['to'])) {
			// default start time for devbar requests
			$condition->greaterThan($table . '.' . self::COLUMN_START_TIME, $fromTimestamp);
		}
		
		if (!empty($filters)) {
			$this->getFilters($condition, $filters);
		}
		
		$select->where($condition);
		
		$result = $this->selectWith($select)->toArray();
		if ($result) {
			$result = current($result);
			if ($result && isset($result['total'])) {
				return intval($result['total']);
			}
		}
		
		return 0;
	}
	
	public function getRequestsFromTimestamp($fromTimestamp, $limit = 0, $offset = 0, $order = null, $direction = null, $filters = array()) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		$select->join('devbar_requests_urls', 'devbar_requests.url_id = devbar_requests_urls.id', array('url'));
		
		$condition = new Predicate(null, Predicate::OP_AND);
		
		if (!isset($filters['from']) && !isset($filters['to'])) {
			// default start time for devbar requests
			$condition->greaterThan($table . '.' . self::COLUMN_START_TIME, $fromTimestamp);
		}
		
		if (!empty($filters)) {
			$this->getFilters($condition, $filters);
		}
		
		$select->where($condition);
		
		if ($limit > 0) {
			$select->limit(intval($limit));
		}
		
		if ($offset > 0) {
			$select->offset(intval($offset));
		}
		
		if (!is_null($order)) {
			
			// validate order direction
			if (is_null($direction) || !in_array(strtolower($direction), array('asc', 'desc'))) {
				$direction = 'asc';
			}
			
			// add "devbar_requests" to the `order by` parameter (if it's not there)
			if (strtolower($order) != 'url' && !preg_match('%^devbar_requests%i', $order)) {
				$order = 'devbar_requests.'.$order;
			} elseif (strtolower($order) == 'url') {
				$order = 'devbar_requests_urls.'.$order;
			}
			$select->order(array($order => $direction));
		}
		
		//Log::err('sql is:' .$select->getSqlString());
		return $this->selectWith($select);
	}
	
	/**
	 *
	 * @param array $filters
	 * @return \Zend\Db\Sql\Where
	 */
	protected function getFilters(Predicate &$condition, array $filters = array()) {
		$table = $this->getTableGateway()->getTable();
		
		if (isset($filters['from'])) {
			$condition->greaterThanOrEqualTo($table . '.' . self::COLUMN_START_TIME, ($filters['from'] * 1000)); // milliseconds
		}
		if (isset($filters['to'])) {
			$condition->lessThanOrEqualTo($table . '.' . self::COLUMN_START_TIME, ($filters['to'] * 1000)); // // milliseconds
		}
		if (isset($filters['method']) && is_array($filters['method']) && isset($filters['method'][0])) {
			$method = $filters['method'][0];
			if (in_array($method, array('GET','POST','CLI'))) {
		   		$condition->equalTo($table . '.' . self::COLUMN_METHOD, $method);
			}
		}
		if (isset($filters['response']) && is_array($filters['response'])) { // status code : 2xx, 4xx, 5xx...
			
			// just only one response status is gotten
			if (count($filters['response']) == 1 && isset($filters['response'][0])) {
				$responseDigit = substr($filters['response'][0], 0, 1); // 2xx->2, 4xx->4
				if (!$responseDigit) return;
				$condition->like(self::COLUMN_STATUS_CODE, "%{$responseDigit}%");
			} else if (count($filters['response']) > 1) { // more than 1
				
				$responseDigit = substr($filters['response'][0], 0, 1); // 2xx->2, 4xx->4
				if (!$responseDigit) return;
				$condition->like(self::COLUMN_STATUS_CODE, "%{$responseDigit}%");
				
				for ($i=1; $i<count($filters['response']); $i++) {
					$responseDigit = substr($filters['response'][$i], 0, 1); // 2xx->2, 4xx->4
					if (!$responseDigit) return;
					$condition->orPredicate(new Like(self::COLUMN_STATUS_CODE, "%$responseDigit%"));
				}
			}
		}
		
		// search text currenly is looking for the text in url OR in status fields
		if (isset ( $filters ['freeText'] ) && !empty($filters['freeText'])) {
			$condition->like('devbar_requests_urls.url', "%{$filters['freeText']}%");
			$condition->orPredicate(new Like(self::COLUMN_STATUS_CODE, "%{$filters['freeText']}%"));
		}
		
	}
	
	/**
	 * Get request id to page id array map
	 * @param array|integer $requestIds
	 * @return array
	 */
	public function getPageIDsByRequestIds($requestIds) {
		if (is_numeric($requestIds)) $requestIds = array($requestIds);
		if (!count($requestIds)) return array();

		// get pageIds by requestIds
		$table = $this->getTableGateway()->getTable();
		
		$select = new Select($table);
		$select->columns(array('id', 'page_id'));
		$select->where(array('id' => $requestIds));
		$resultSet = $this->selectWith($select);
	
		// create map reqId to pageId
		$requestIdToPageIdMap = array();
		foreach ($resultSet as $row) {
			$requestIdToPageIdMap[$row->getId()] = $row->getPageId();
		}
		
		return $requestIdToPageIdMap;
	}
	
	/**
	 * Bring the last request from `devbar_requests` table
	 * now used by the ZrayLive page
	 * @return null | \Zend\Db\ResultSet\ResultSetInterface
	 */
	public function getLastRequest() {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->order('start_time desc')->limit(1);
		$resultSet = $this->selectWith($select);
		$resultSetArray = $resultSet->toArray();
		if (!empty($resultSetArray)) {
			return $resultSet->current();
		}
		
		return null; 
	}
}
