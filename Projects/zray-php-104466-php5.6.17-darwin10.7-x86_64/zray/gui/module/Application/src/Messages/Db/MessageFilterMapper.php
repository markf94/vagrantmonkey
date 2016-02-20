<?php

namespace Messages\Db;

use ZendServer\Log\Log,
ZendServer\Set,
Configuration\MapperAbstract,
Zend\Db\TableGateway\TableGateway,
Messages\MessageContainer,
Messages\Db\MessageMapper,
\Zend\Db\Sql\Select;

class MessageFilterMapper extends MapperAbstract {
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	protected $messagesGateway;

	/**
	 * @return Set[MessageContainer]
	 */
	public function findFilteredMessages() {	
		$this->cleanMessages(); // first clean the db
			
		$filteredMessagesTable = $this->getTableName();
		$messagesTable = $this->getMessagesGateway()->getTable();
		
		$select = new Select($messagesTable);
		$select->join($filteredMessagesTable, "{$messagesTable}.SUB_TYPE = {$filteredMessagesTable}.FILTER_TYPE", array(
				'SHOW_AT',				 
		), Select::JOIN_LEFT);
		$select->where('SHOW_AT IS NULL');
		$select->order(array('MSG_ID' => 'ASC'));
		
		try {
			$resultSet = $this->getMessagesGateway()->selectWith($select); /* @var $rowset \Zend\Db\ResultSet\ResultSet */
		}
		catch (\Exception $e) {
			throw new \ZendServer\Exception("query failed with the following error: " . $e->getMessage());
		}
		
		return new Set($resultSet->toArray(), 'MessageContainer');
		return $resultSet->toArray();
	}	

	public function cleanMessages() {
		$filterIdsToRemove = array_map(function($filter) {return $filter['FILTER_ID'];}, $this->select('SHOW_AT < ' . time()));
		
		return $this->delete(array("FILTER_ID IN (" . implode(",", $filterIdsToRemove) . ")"));
	}
	
	public function insertFilter($filterType, $minutesDelta) {
		$timestamp = time();
		$date = new \DateTime($timestamp);
		$date->setTimestamp($timestamp);
		$date->modify("+{$minutesDelta} minutes");
		$show_at = $date->getTimestamp();
		
		if (in_array($filterType, $this->findFilterTypes())) {
			return $this->update(array('SHOW_AT' => $show_at), array('FILTER_TYPE' => $filterType));			
		}else {
			return $this->insert(array('FILTER_TYPE' => $filterType, 'SHOW_AT' => $show_at));
		}
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway
	 */
	public function getMessagesGateway() {
		return $this->messagesGateway;
	}
	
	/**
	 * @param \Zend\Db\TableGateway\TableGateway $messagesGateway
	 */
	public function setMessagesGateway($messagesGateway) {
		$this->messagesGateway = $messagesGateway;
	}	
	

	protected function findFilterTypes() {
		$select = new Select($this->getTableName());
		$select->columns(array('FILTER_TYPE'));
	
		return array_map(function($filter) {return $filter['FILTER_TYPE'];}, $this->selectWith($select));
	}

}
