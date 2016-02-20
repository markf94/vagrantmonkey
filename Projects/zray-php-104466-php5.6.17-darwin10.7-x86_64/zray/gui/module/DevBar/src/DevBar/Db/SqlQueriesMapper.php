<?php

namespace DevBar\Db;

use Zend\Db\Sql\Select,
	Configuration\MapperAbstract,
	ZendServer\Edition,
	Zend\Db\Sql\Predicate\Predicate,
	ZendServer\Log\Log,
	Configuration\MapperDirectives;

class SqlQueriesMapper extends MapperAbstract {
	
	protected $setClass = '\DevBar\SqlQueryContainer';
	
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
	 * @param integer $requestId
	 * @return \ZendServer\Set
	 */
	public function getQueries($requestId) {
		if (empty($requestId)) {
			return array();
		}
		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		
		$select->join('devbar_sql_statements', 'devbar_sql_statements.id = devbar_sql_queries.prepared_statement_id', array('prepared_statement'), \Zend\Db\Sql\Select::JOIN_LEFT);
		
		$select->where(array('request_id' => $requestId));
		
		return $this->selectWith($select);
	}
	

	public function getRequestSqlQueriesExtraData($ids) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->where(array("request_id IN (" . implode(",", $ids) . ")"));
		return $this->selectWith($select);
	}
	
	public function removeDevBarRequests(array $ids) {
	 	$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
	    Log::debug("Deleted $effected rows from devbar_sql_queries");
	}
	
}
