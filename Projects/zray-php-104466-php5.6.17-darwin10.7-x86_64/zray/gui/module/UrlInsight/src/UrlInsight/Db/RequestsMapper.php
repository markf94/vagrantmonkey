<?php

namespace UrlInsight\Db;

use Zend\Db\Sql\Select,
	Zend\Db\Sql\Expression,
	Configuration\MapperAbstract;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;

class RequestsMapper extends MapperAbstract {
	
	const UrlInsight_RESOURCES_DICTIONARY = 'urlinsight_resources_dictionary'; 
	const UrlInsight_OTHER_URLS_RESOURCE_ID = 1; // resource id of "all the other requests" 
	
	protected $setClass = '\UrlInsight\RequestContainer';
	
	/**
	 * @param integer/array $ids
	 * @return \ZendServer\Set
	 */
	public function getRequests($ids = array(), $limit = 0, $offset = 0, $order = '', $fromTimestamp = 0) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);

		$requestsTable = $this->getTableName();
		$resourcesDictionaryTable = self::UrlInsight_RESOURCES_DICTIONARY; 
		
		$select = new Select($requestsTable);
		$select->join($resourcesDictionaryTable, "{$requestsTable}.resource_id = {$resourcesDictionaryTable}.id", array('resource_string'), Select::JOIN_LEFT);

		// from timestamp
		$tsCond = new Predicate();
		$tsCond->greaterThanOrEqualTo($requestsTable.'.from_time', $fromTimestamp);
		$select->where($tsCond);
		
		
		if (count($ids) > 0) {
			$select->where(array($requestsTable.'.resource_id' => $ids));
		}
		
		if ($limit > 0) {
			$select->limit(intval($limit));
			$select->offset(intval($offset));
		}
		
		if (!empty($order)) {
		    $select->order($order);
		}
		
		return $this->selectWith($select);
	}
	
	/**
	 * 
	 * @param array $params can contain {
	 * 		ids: 123,
	 * 		limit: 10,
	 * 		offset: 1,
	 *		applicationId: 30,
	 *		filter: 2, // 1 to 3
	 * 		period: 24, // in hours
	 * }
	 * @return Ambigous <\ZendServer\Set, multitype:, NULL, \Zend\Db\ResultSet\ResultSetInterface, \Zend\Db\ResultSet\ResultSet, multitype:NULL multitype: Ambigous <\ArrayObject, multitype:, \Zend\Db\ResultSet\mixed, unknown> >
	 */
	public function getUrls($params) {
		$ids = @$params['ids'] ?: array();
		$limit = @$params['limit'] ?: 0;
		$offset = @$params['offset'] ?: 0;
		$applicationId = isset($params['applicationId']) && is_numeric($params['applicationId']) ? intval($params['applicationId']) : 0;
		$filter = @$params['filter'] ?: 1;
		$period = @$params['period'] ?: 0;
		
		$requestsTable = $this->getTableName();
		$resourcesDictionaryTable = self::UrlInsight_RESOURCES_DICTIONARY;
	
		$select = new Select($requestsTable);
		//$select->from($table)
		$select->columns(array(
				'resource_id',
				'app_id',
				'from_time' => new Expression('min(from_time)'),
				'until_time' => new Expression('max(until_time)'),
				'samples' => new Expression('sum(samples)'),
				'min_time' => new Expression('min(min_time)'),
				'max_time' => new Expression('max(max_time)'),
				'avg_time' => new Expression('(sum(avg_time * samples) / sum(samples))'),
				'time_consumption' => new Expression('sum(avg_time * samples)'), // used for order only. the data is calculated afterwards
				'max_memory' => new Expression('max(max_memory)'),
				'avg_memory' => new Expression('(sum(avg_memory * samples) / sum(samples))'),
		));
		$select->join($resourcesDictionaryTable, "{$requestsTable}.resource_id = {$resourcesDictionaryTable}.id and {$requestsTable}.app_id = {$resourcesDictionaryTable}.app_id", array('resource_string', 'resource_string_example'), Select::JOIN_INNER);
	
		// limit to specific URLs
		if (count($ids) > 0) {
			$select->where(array($requestsTable.'.resource_id' => $ids));
		}

		// limit to specific application id
		if ($applicationId !== 0) {
			$select->where(array($requestsTable.'.app_id' => $applicationId));
		}

		// limit to specific date
		if ($period && $period > 0) {
			// translate to seconds
			$period = $period * 60 * 60;
		
			$wherePeriod = new Predicate();
			$wherePeriod->greaterThan($requestsTable.'.from_time', time() - $period);
			$select->where($wherePeriod);
		}
		
		$select->group($requestsTable.'.resource_id');
		$select->order($this->getOrderStringByFilter($filter));
		if ($limit > 0) {
			$select->limit(intval($limit));
			$select->offset(intval($offset));
			
			// exclude "others" links
			$excludeOthers = new Predicate();
			$excludeOthers->notEqualTo($requestsTable.'.resource_id', self::UrlInsight_OTHER_URLS_RESOURCE_ID);
		    $select->where($excludeOthers);
		}
	
		return $this->selectWith($select);
	}
	
	protected function getOrderStringByFilter($filterNumber) {
		$orderStrings = array(
			// most time consuming
			1 => 'time_consumption desc',
			// slowest response time
			2 => 'avg_time desc',
			// most popular
			3 => 'samples desc',
		);
		
		return @$orderStrings[$filterNumber] ?: array_shift($orderStrings); // default is the first one
	}
}