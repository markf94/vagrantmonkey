<?php

namespace Notifications\Db;

use Notifications\NotificationContainer;

use ZendServer\Log\Log,
Configuration\MapperAbstract,
\Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Servers\Db\Mapper;
use Servers\Db\ServersAwareInterface;
use ZendServer\Edition;

class NotificationsMapper extends MapperAbstract implements ServersAwareInterface {
	
	protected $setClass = '\Notifications\NotificationContainer';
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	protected $notificationsActionsGateway;

	/**
	 * @var Mapper
	 */
	protected $serversMapper;
	
	/**
	 * @return Set[MessageContainer]
	 */
	public function findAllNotifications() {
		$date = new \DateTime();
		$showAt = $date->getTimestamp();
		
		return $this->select('SHOW_AT <= "' . $showAt . '"');
	}
	
	/**
	 * @return Set[MessageContainer]
	 */
	public function findAllNotificationsWithNames() {
		$date = new \DateTime();
		$showAt = $date->getTimestamp();
		$notificationsTable = $this->getTableName();
		$notificationsActionsTable = $this->getNotificationsActionsGateway()->getTable(); 
		
		$select = new Select($notificationsTable);
		$select->join($notificationsActionsTable, "{$notificationsTable}.TYPE = {$notificationsActionsTable}.TYPE", array('NAME'), Select::JOIN_LEFT);
		$select->where('SHOW_AT <= "' . $showAt . '"');
		$select->order('ID desc');
		return $this->getTableGateway()->selectWith($select);
	}
	
	/**
	 * @return Set[MessageContainer]
	 */
	public function getNotificationByType($type) {
		return $this->select('type = "' . $type . '"');
	}
	
	/**
	 * @return array
	 */
	public function findMissingServers() {
		
		$select = new Select($this->getTableGateway()->getTable());
		$select->columns(array('NODE_ID' => new Expression('DISTINCT NODE_ID')));
		
		$result = $this->selectWith($select,false,true); /* @var $result \Zend\Db\ResultSet\ResultSet */
		if (0 == $result->count()) {
			return array();
		}
		
		$notificationServerIds = array();
		foreach ($result as $row) {
			$notificationServerIds[] = $row['NODE_ID'];
		}
		return array_diff($notificationServerIds, $this->getServersMapper()->findAllServersIds());
	}
	
	/**
	 * @param array $missingServers
	 * @return number
	 */
	public function cleanNotificationsForMissingServers(array $missingServers) {
		if (0 < count($missingServers)) {
			$where = new Where();
			$where->in('NODE_ID', $missingServers);
			$this->delete($where);
		}
		return count($missingServers);
	}
	
	/**
	 * @param integer $type
	 */
	public function deleteByType($type) {
	    $select = $this->select('type = "' . $type . '"');
	    $selectArray = $select->toArray();
	    if (!empty($selectArray)) {
	        return $this->delete('TYPE = "' . $type . '"');
	    }
	}

	/**
	 * @param array $types
	 */
	public function deleteByTypes(array $types) {
		$where = $this->getSqlInStatement('TYPE', $types);
		return $this->delete($where);
	}
		
	/**
	 * @param integer $type
	 */
	public function insertNotification($type, $extraData = null) {
		$notifications = $this->getNotificationByType($type);
		if (count($notifications) > 0) { // insert only if not exists
			return;
		}
		
		$date = new \DateTime();
		$showAt = $date->getTimestamp();
		
		$edition = new Edition();
		
		$data = array('SEVERITY' => 2, 'CREATION_TIME' => $showAt, 'TYPE' => $type, 'REPEATS' => 0, 'SHOW_AT' => $showAt, 'NODE_ID' => $edition->getServerId());
		if (! is_null($extraData)) {
			$data['EXTRA_DATA'] = json_encode($extraData);
		}
		$row = $this->getTableGateway()->insert($data);
	}
	
	public function updateNotifiedFlag($type) {
	    return $this->update(array('NOTIFIED' => 1), array('TYPE' => $type));
	}
	
	public function getNotifiedFlag($type) {
	    $notifications = $this->select(array('TYPE' => $type, 'NOTIFIED' => 1));
	    return $notifications->toArray();
	}
	
	/**
	 * @param integer $type
	 * @param integer $minutesDelta
	 * @return number
	 */
	public function insertFilter($type, $minutesDelta) {
		$date = new \DateTime();
		$date->modify('+' . $minutesDelta . ' minutes');
		$showAt = $date->getTimestamp();
	
		return $this->update(array('SHOW_AT' => $showAt), array('TYPE' => $type));
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway
	 */
	public function getNotificationsActionsGateway() {
		return $this->notificationsActionsGateway;
	}
	
	/**
	 * @return \Servers\Db\Mapper
	 */
	public function getServersMapper() {
		return $this->serversMapper;
	}

	/**
	 * @param \Servers\Db\Mapper $serversMapper
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $notificationsActionsGateway
	 */
	public function setNotificationsActionsGateway($notificationsActionsGateway) {
		$this->notificationsActionsGateway = $notificationsActionsGateway;
	}
}
