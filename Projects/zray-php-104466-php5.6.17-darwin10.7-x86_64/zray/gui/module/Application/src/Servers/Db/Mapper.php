<?php

namespace Servers\Db;

use Application\Module;

use ZendServer\Log\Log,
ZendServer\Set,
Zend\Db\Sql\Select,
Zend\Db\TableGateway\TableGateway,
Configuration\MapperAbstract,
Servers\View\Helper\ServerStatus,
ZendServer\Ini\IniReader,
ZendServer\Exception;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Adapter;
use ZendServer\Edition;
use Configuration\MapperDirectives;
use Zend\Db\Sql\Where;

class Mapper extends MapperAbstract {
	
	const ZSD_DEFAULT_HEARTBEAT_INTERVAL = 3;
	
	protected $setClass = '\Servers\Container';
	
	protected $systemStatus;
	
	/**
	 * @var Edition
	 */
	protected $edition;
	
	/**
	 * @var MapperDirectives
	 */
	protected $directivesMapper;
	
	public static function getDummyNodeRecord() {
		return array(
			"NODE_ID" => 0,
			"NODE_NAME" => gethostname(),
			"JTIME" => 1441263938.524,
			"NODE_IP" => '',
			"STATUS_CODE" => 0,
			"REASON_STRING" => '',
			"IS_DELETED" =>	0,
			"LAST_UPDATED" => strtotime('11 Aug 1980 11:00:00'), // dummy date
			"SERVER_FLAGS" => 0,
		);
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findAllServers($params = array()) {
		// since Z-Ray standalone doesn't have a cluster (yet) the response is static
		if (isZrayStandaloneEnv()) {
			return new \ZendServer\Set(array(
				self::getDummyNodeRecord(),
			), '\Servers\Container');
		}
		
		$orderBy = 'NODE_NAME';
		if (isset($params['order'])) {
			$orderBy = strtoupper($params['order']);
		}
		$order = 'ASC';
		if (isset($params['direction'])) {
			$order = strtoupper($params['direction']);
		}
		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->order(array(strtoupper($orderBy) => $order));
		
		return $this->selectWith($select);
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function countAllServers() {
		return $this->count();
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function countActiveServers() {
		$where = new Where();
		$where->equalTo('IS_DELETED', '0');
		return $this->count('*', $where);
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findRestartingServers() {
		return $this->select(array('STATUS_CODE = ' . ServerStatus::STATUS_SERVER_RESTARTING));
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findRestartRequiredServers() {
		return $this->select(array('STATUS_CODE = ' . ServerStatus::STATUS_RESTART_REQUIRED));
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findServersById(array $ids, $params = array()) {
		// since Z-Ray standalone doesn't have a cluster (yet) the response is static
		if (isZrayStandaloneEnv()) {
			return new \ZendServer\Set(array(
				self::getDummyNodeRecord(),
			), '\Servers\Container');
		}
		
		$orderBy = 'NODE_NAME';
		if (isset($params['order'])) {
			$orderBy = strtoupper($params['order']);
		}
		$order = 'ASC';
		if (isset($params['direction'])) {
			$order = strtoupper($params['direction']);
		}
		
		$searchIdArray = implode(',', $ids);

		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->where(array('NODE_ID in (' . $searchIdArray . ')'));
		$select->order(array(strtoupper($orderBy) => $order));
		
		return $this->selectWith($select);
	}
	
	/**
	 * @return \Servers\Container
	 */
	public function findServerById($id) {
		return $this->select(array("NODE_ID = $id"))->current();
	}
	
	/**
	 * @return \Servers\Container
	 */
	public function findServerByName($name) {
		return $this->select(array('NODE_NAME' => $name))->current();
	}
		
	/**
	 * @return string
	 */
	public function getTimestamp() {
		if (Module::isSingleServer()) {
			return time(); // for some reason, running the timestamp query on sqlite causes sqlite locks during bootstrap - probably the query below is not comitted properly
		}

		$timestamp = current($this->tableGateway->getAdapter()->query('SELECT CURRENT_TIMESTAMP')->execute()->current());
		if ($timestamp) {
			return strtotime($timestamp);
		} else {
			Log::warn('Unable to determine current database timestamp - mysql db is down?');
			return time();
		}
	}
	
	public function getDatabaseSchemaName() {
		$config = new IniReader();
		$iniFile = getCfgVar('zend.conf_dir').DIRECTORY_SEPARATOR.'zend_database.ini';
		$dbDirectives = $config->fromFile($iniFile, false);// flat reading, important for windows
		
		return $dbDirectives['zend.database.name'];
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findRespondingServers($removeStale=true, $removeDisabled=true, $params = array()) {
		return $this->filterNonRespondingServers($this->findAllServers($params), $removeStale, $removeDisabled);
	}

	/**
	 * @return \ZendServer\Set
	 */
	public function findRespondingServersByIds(array $ids, $removeStale=true, $removeDisabled=true, $params = array()) {
		return $this->filterNonRespondingServers($this->findServersById($ids, $params), $removeStale, $removeDisabled);		
	}
		
	/**
	 * @return Array
	 */
	public function findAllServersIds() {
		$servers = $this->findAllServers();
		return array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());
	}

	/**
	 * @return Array
	 */
	public function findAllServersNamesByIds() {
		$serversNamesByIds = array();
		$servers = $this->findAllServers();
		foreach ($servers as $server) { /* @var $server \Servers\Container */
			$serversNamesByIds[$server->getNodeId()] = $server->getNodeName();
		}
		
		return $serversNamesByIds;
	}

	/**
	 * @param integer $serverId
	 * @throws Exception
	 * @return string
	 */
	public function findServerNameById($serverId) {
		$serversNamesByIds = $this->findAllServersNamesByIds();
		if (!isset($serversNamesByIds[$serverId])) {
			throw new Exception("Could not find serverId '{$serverId}'");
		}
	
		return $serversNamesByIds[$serverId];
	}
		
	/**
	 * @return Boolean
	 */
	public function isNodeIdExists($serversId) {
		return in_array($serversId, $this->findAllServersIds());
	}
		
	/**
	 * @return Boolean
	 */	
	public function isNodeNameExists($serversName) {
		return in_array($serversName, $this->findAllServersNamesByIds());
	}
		
	/**
	 * @return Array
	 */
	public function findRespondingServersIds($removeStale=true, $removeDisabled=true, $params = array()) {
		// for standalone return only the current node id
		if (isZrayStandaloneEnv()) {
			return array(0);
		}
		
		$servers = $this->findRespondingServers($removeStale, $removeDisabled, $params);
		return array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());
	}
			
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $tableGateway
	 */
	public function getTableGateway() {
		return $this->tableGateway;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $tableGateway
	 * @return \Servers\Db\Mapper
	 */
	public function setTableGateway($tableGateway) {
		$this->tableGateway = $tableGateway;
		return $this;
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function changeServerNameById($id, $name) {
		$this->tableGateway->update(array('NODE_NAME' => $name), "NODE_ID = $id");
		
		return new Set($this->tableGateway->select(array('NODE_ID in (' . $id . ')'))->toArray());
	}	
	
	public function setIsDeleted($id, $value) {
		$this->tableGateway->update(array('IS_DELETED' => $value), "NODE_ID = $id");
	}

	public function getSystemStatus() {
		if ($this->systemStatus) return $this->systemStatus; // as when running 1.3 we will have call from 1.2 and 1.3, we "cache" the response
		$this->systemStatus = ServerStatus::STATUS_OK;
		foreach ($this->findAllServers() as $server) {/* @var $server \Servers\Container */
			if ($server->isPendingRestart()) {
				return $this->systemStatus = ServerStatus::STATUS_RESTART_REQUIRED; // as restarting might solve errors, we will show this status even if some servers are in errors
			} elseif($server->isStatusError()) {
				$this->systemStatus = ServerStatus::STATUS_ERROR;
			}
		}
	
		return $this->systemStatus;
	}
	
	public function getZsdLastUpdated($nodeId) {
		$select = new Select();
		$select->columns(array('LAST_UPDATED'));
		$select->from('ZSD_TIMESTAMP');
	
		$predicate = new Predicate();
		$predicate->equalTo('NODE_ID', $nodeId);
		
		$select->where(array($predicate));
		$query = $select->getSqlString($this->getTableGateway()->getAdapter()->getPlatform());
		$result = $this->getTableGateway()->getAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
		
		$result = $result->toArray();
		if (count($result) > 0) {
			return $result[0]['LAST_UPDATED'];
		}

		return null;
	}
	
	/**
	 * @return MapperDirectives
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}

	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
		return $this;
	}

	/**
	 *
	 * @param \Zend\Db\ResultSet\ResultSet $resultSet
	 * @return Array
	 */
	protected function resultSetToArray($resultSet) {
		$mappedArray = array();
		foreach ($resultSet as $resultRow) { /* @var $resultRow \ArrayObject */
			$mappedArray[$resultRow['NODE_ID']]= $resultRow->getArrayCopy();
		}
		return $mappedArray; // placeholder where inheriting mappers can play with the ResultSet->toArray conversion
	}
	
	private function filterNonRespondingServers($allServers, $removeStale=true, $removeDisabled=true) {
		if (! $this->getEdition()->isCluster()) {
			return $allServers;
		}
		$isLockedPart = array();
		$liveServers = array();
		foreach ($allServers as $server) {/* @var $server \Servers\Container */
			$serverId = $server->getNodeId();
			if ($server->isDeleted()) {
				log::debug("server {$serverId} seems to have been deleted - will not show any data about it");
				continue;
			}
			
			if ($removeDisabled && $server->isDisabled()) {
				log::debug("server {$serverId} seems to be disabled - will not show any data about it");
				continue;
			}
				
			$liveServers[$serverId] = $server->toArray();
		}
		
		if (count($liveServers) == 0) {
			return new Set(array(), 'Servers\Container');
		}
		
		$interval = $this->getDirectivesMapper()->getDirectiveValue('zend_server_daemon.keep_alive_interval');
		$interval = $interval ? $interval+2 : self::ZSD_DEFAULT_HEARTBEAT_INTERVAL; // add two seconds to any value
		
		$select = new Select();
		$select->columns(array('NODE_ID', 'ALIVE' => new Expression("LAST_UPDATED >= (UNIX_TIMESTAMP(NOW()) - {$interval})")));
		$select->from('ZSD_TIMESTAMP');
		$predicate = new Predicate();
		$predicate->in('NODE_ID', array_keys($liveServers));
		$select->where(array($predicate));
		$query = $select->getSqlString($this->getTableGateway()->getAdapter()->getPlatform());
		$result = $this->getTableGateway()->getAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);

		if ($result->count()) {
			$result = $result->toArray();
			// turn result into an id map
			$heartbeats = array_combine(array_map(function($item){
				return $item['NODE_ID'];
			}, $result),array_values($result));
		} else {
			$heartbeats = array();
		}
		
		
		$notResponding = array();
		$serversList = array();
		$liveServers = new Set($liveServers, 'Servers\Container');
		
		foreach ($liveServers as $serverId => $server) {
			if (isset($heartbeats[$serverId]) && ($heartbeats[$serverId]['ALIVE'] == 1)) { /// has a heartbeat and is alive
				$serversList[$serverId] = $server->toArray();
			} else { /// server has no heartbeat or is not alive
				$notResponding[] = $serverId;
				if ($removeStale) {
					continue;
				} else {
					$server->setStatusCode(ServerStatus::STATUS_NOT_RESPONDING);
					$serversList[$serverId] = $server->toArray();
				}
			}
		}

		if ($notResponding) Log::info("serverIds " . implode(',', $notResponding) ." does not seem to be responding (executed query: '{$query}' returned '1')");
		return new Set($serversList, 'Servers\Container');
	}
	
	/**
	 * @return Edition
	 */
	public function getEdition() {
		if (! $this->edition instanceof Edition) {
			$this->edition = new Edition();
		}
		return $this->edition;
	}

	/**
	 * @param \ZendServer\Edition $edition
	 * @return Mapper
	 */
	public function setEdition($edition) {
		$this->edition = $edition;
		return $this;
	}

}
