<?php

namespace Zsd\Db;

use ZendServer\Set,
ZendServer\Log\Log,
ZendServer\FS\FS,
Zend\Json\Json,
Zend\Db\TableGateway\TableGateway,
ZendServer\Exception;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
use Audit\Controller\Plugin\AuditMessage;
use Audit\Controller\Plugin\InjectAuditMessageInterface;
use Audit\Container;
use ZendServer\Edition;

class TasksMapper implements InjectAuditMessageInterface {
	
	const DUMMY_NODE_ID = -1;
	const DUMMY_TASK_ID = -1;
	const DUMMY_AUDIT_ID = -1;
	
	//////////////////////////////////////////////////////////////////
	// ZSD coommands
	//////////////////////////////////////////////////////////////////
	
	// restart Zend Server (all daemons + apache)
	const COMMAND_RESTART_SERVER           = 0;

	// Remove the current node from the cluster and move to "singleserver" mode
	const COMMAND_DISCONNECT_FROM_CLUSTER  = 1;
	
	// Copy the blueprint from the database to the INI files
	const COMMAND_SAVE_AND_APPLY_BLUEPRINT = 2;
	
	// join to cluster
	const COMMAND_JOIN_CLUSTER             = 3;
	
	// enable an extension. The extension name is defined
	// inside the extra-info (json)
	const COMMAND_ENABLE_EXTENSION         = 4;
	
	// disable an extension. The extension name is defined
	// inside the extra-info (json)
	const COMMAND_DISABLE_EXTENSION        = 5;
	
	// Disable the current server
	const COMMAND_DISABLE_SERVER           = 6;
	
	// Enable the current server
	const COMMAND_ENABLE_SERVER            = 7;
	
	// An internal task that is passed by the "removed" server to the other servers in the
	// cluster notifying about its removal
	const COMMAND_CLUSTER_MEMBER_REMOVED   = 8;
	
	// applies blueprint data to FS against a certain node
	const COMMAND_APPLY_BLUEPRINT          = 12;

	// Force remove a server. The node id is defined
	// inside the extra-info (json)
	const COMMAND_FORCE_DISCONNECT_FROM_CLUSTER = 13;	
	
	const COMMAND_RESTART_PHP = 15;// Restart the webserver	
	const COMMAND_RESTART_MONITOR_NODE = 16;// Restart Monitor Node	
	const COMMAND_RESTART_SCD = 17;// Restart Session Clustering	
	const COMMAND_RESTART_JQD = 18;// Restart Job Queue Daemon	
	const COMMAND_RESTART_ZDD = 19;// Restart Deployment Daemon	
	const COMMAND_RESTART_JB = 29;// Restart JB
	
	// Monitor rules were updated in the database
	const COMMAND_MONITOR_RULES_UPDATED = 20;
	
	const COMMAND_CLEAR_OPTIMIZER_PLUS_CACHE = 21;
	const COMMAND_CLEAR_DATACACHE_DISK_CACHE = 22;
	const COMMAND_CLEAR_DATACACHE_SHM_CACHE = 23;
	const COMMAND_CLEAR_PAGECACHE_CACHE = 24;
	
	const COMMAND_CLEAR_DATACACHE_DISK_CACHE_NAMESPACE = 56;
	const COMMAND_CLEAR_DATACACHE_SHM_CACHE_NAMESPACE = 57;
	
	const COMMAND_PAGECACHE_RULES_UPDATED = 26;// Page Cache rules were updated in the database
	
	const COMMAND_GET_PHPINFO = 28;	
	
	const COMMAND_APPS_LIST_UPDATED = 39;
	
	// applies blueprint data to FS against a certain node - including extension data
	const COMMAND_APPLY_BLUEPRINT_WITH_EXTENSIONS          = 40;
	
	const COMMAND_LICENSE_UPDATED = 41;
	
	const COMMAND_RELOAD_CONFIGURATION = 42;
	
	const COMMAND_VHOST_INSERT 	= 43;
	const COMMAND_VHOST_REMOVE	= 44;
	const COMMAND_VHOST_EDIT	= 45;
	const COMMAND_VHOST_TEMPLATE_VALID	= 48;
	const COMMAND_VHOST_REDEPLOY		= 49;
	const COMMAND_VHOST_MANAGE			= 50;
	const COMMAND_VHOST_UNMANAGE		= 52;
	const COMMAND_VHOST_SSL_VALID		= 54;
	
	const COMMAND_CONFIGURATION_EXPORT	= 47;
	
	const COMMAND_CODETRACING_DELETE	= 58;
	
	const COMMAND_CLEAR_URL_TRACKING		= 60;
	
	const COMMAND_UPDATED_ACCESS_TOKENS		= 61;
	
	const COMMAND_URL_INSIGHT_RULE_INSERT	= 62;
	const COMMAND_URL_INSIGHT_RULE_REMOVE	= 63;
	
	const COMMAND_MONITOR_RESET_CACHE		= 64;
	
	// same as restart but engine's different implementatiokn
	// used for changing debugger (studioIntegration module)
	const COMMAND_STOP_START_SERVER			= 65;
	
	const RESTART_TYPE_FULL = 0;
	const RESTART_TYPE_SELECTIVE = 1;
	
	/**
	 * @var AuditMessage
	 */
	private $auditMessage;
	
	/**
	 * @var Edition
	 */
	private $edition;
	
	protected $daemonsToCommands = array(
			'jb' 			=> self::COMMAND_RESTART_JB,
			'jqd' 			=> self::COMMAND_RESTART_JQD,
			'monitor_node'	=> self::COMMAND_RESTART_MONITOR_NODE,
			'scd' 			=> self::COMMAND_RESTART_SCD,
			'zdd' 			=> self::COMMAND_RESTART_ZDD,			
			);
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $tableGateway;
	
	public function __construct(TableGateway $tableGateway = null) {
		$this->tableGateway = $tableGateway;
	}
	
	/**
	 * @param integer $serverId
	 * @param integer $taskId
	 * @param mixed $extraData
	 * @return integer
	 */
	public function insertTask($serverId, $taskId, $extraData = array()) {
		if (is_object($extraData) || is_array($extraData)) {
			$extraData = Json::encode($extraData);
		}
		
		$auditId = $this->getAuditId();
		
		$this->tableGateway->insert(
				array(	'NODE_ID'	=> $serverId,
						'TASK_ID' 	=> $taskId,
						'AUDIT_ID' => $auditId,
						'EXTRA_DATA' => $extraData,
				));
		Log::debug("Insert task s:{$serverId}, t:{$taskId}, a:{$auditId}, ex:{$extraData}");
		return $this->tableGateway->getLastInsertValue();
	}
	
	/**
	 * @param array $serverIds
	 * @param integer $taskId
	 * @param mixed $extraData
	 */
	public function insertTasksServers($serverIds, $taskId, $extraData = array()) {
		$dbConnection = $this->getTableGateway()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
		
		if (is_object($extraData) || is_array($extraData)) {
			$extraData = Json::encode($extraData);
		}
		
		try {
			$dbConnection->beginTransaction();
			foreach($serverIds as $serverId) {
				$this->insertTask($serverId, $taskId, $extraData);
			}
			$dbConnection->commit();
		} catch (Exception $ex) {
			$dbConnection->rollback();
			Log::err("Cannot send task {$taskId}: {$ex->getMessage()}");
		}
	}
	
	/**
	 * @throws Exception
	 * @return string
	 */
	public function getTaskDaemonName($daemonName) {
		if (!isset($this->daemonsToCommands[$daemonName])) {
			throw new Exception("Failed to restart- unknown daemon '{$daemonName}'");
		}
		return $this->daemonsToCommands[$daemonName];
	}
	
	/**
	 * @param integer $serverId
	 */
	public function removeServerTasks($serverId) {
		Log::debug("removing server '$serverId' current tasks");
		$this->tableGateway->delete(array('NODE_ID' => $serverId));
	}

	/**
	 * @param array $servers
	 * @return array
	 */
	function tasksPerServerComplete(array $servers) {
		if (count($servers) > 0) {
			$resultSet = $this->tableGateway->select('NODE_ID IN (' . implode(',', $servers). ')');
			$resultSet = $resultSet->toArray();
			$result = array_combine($servers, array_fill(0, count($servers), true));
			foreach ($resultSet as $taskRow) {
				$result[$taskRow['NODE_ID']] = false;
			}
			return $result ? $result : array();
		}
		return array();
	}
	
	/**
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function findAllTasks() {
		return $this->getTableGateway()->select();
	}
	
	/**
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function findAllTasksOfConnectedServers() {
		$select = new Select();
		$on = new Expression('ZSD_NODES.NODE_ID = ZSD_TASKS.NODE_ID AND ZSD_NODES.IS_DELETED = ?', '0');
		$select->join('ZSD_NODES', $on, array());
		$select->from($this->getTableGateway()->getTable());
		return $this->getTableGateway()->selectWith($select);
	}
	
	/**
	 * 
	 * @param array $servers
	 * @param array $tasks
	 * @return boolean
	 */
	public function tasksComplete($servers = array(), $tasks = array()) {
	    $select = new Select($this->tableGateway->getTable());
	    $select->columns(array('counter' => new \Zend\Db\Sql\Expression('COUNT(*)')));

	    if (count($servers) > 0) {
	    	$select->where->in('NODE_ID', $servers);
	    }
	    
	    if (count($tasks) > 0) {
	    	$select->where->in('ZSD_TASKS_SEQUENCE', $tasks);
	    }

	    if ($this->getEdition()->isClusterServer()) {
	    	$select->where->notEqualTo('NODE_ID', 0);
	    }
	    
	    $resultSet = $this->tableGateway->selectWith($select);
	    $current = $resultSet->current();
	    if ($current instanceof \ArrayObject) {
		    return 0 < intval(current($current->getArrayCopy())) ? false : true;
	    } else {
	    	return false;
	    }
	}
	
	public function waitForTasksComplete($servers = array(), $tasks = array(), $sleep=1) {
		$max_time = ini_get('max_execution_time');
		$initTime = $_SERVER["REQUEST_TIME"];
		
		while (($timePassed = round(time() - $initTime)) < $max_time - ($sleep+1)) { // will stop the check, once we're about to reach the max_execution_time limit
			if ($this->tasksComplete($servers, $tasks)) {
				Log::info("All tasks were completed after {$timePassed} seconds)");
				return true;
			}

			Log::debug("waiting for TasksComplete ({$timePassed} seconds passed)");
			sleep($sleep);
		}
		
		throw new Exception("tasks have not been completed!");
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $tableGateway
	 */
	public function getTableGateway() {
		return $this->tableGateway;
	}

	
	/**
	 * @return \ZendServer\Edition
	 */
	public function getEdition() {
		if (is_null($this->edition)) {
			$this->edition = new Edition();
		}
		return $this->edition;
	}

	/**
	 * @param \ZendServer\Edition $edition
	 */
	public function setEdition($edition) {
		$this->edition = $edition;
	}

	/**
	 * @param \Audit\Controller\Plugin\AuditMessage $auditMessage
	 */
	public function setAuditMessage($auditMessage) {
		$this->auditMessage = $auditMessage;
		return $this;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $tableGateway
	 * @return TasksMapper
	 */
	public function setTableGateway($tableGateway) {
		$this->tableGateway = $tableGateway;
		return $this;
	}

	/**
	 * @return \Audit\Controller\Plugin\AuditMessage
	 */
	public function getAuditMessage() {
		return $this->auditMessage;
	}
	
	/**
	 * @return integer
	 * @throws Exception
	 */
	private function getAuditId() {
		if($this->getAuditMessage() instanceof AuditMessage && $this->getAuditMessage()->getMessage() instanceof Container) {
			$auditId = $this->getAuditMessage()->getMessage()->getAuditId();
			if (is_null($auditId)) {
				$auditId = self::DUMMY_AUDIT_ID;
			}
			return $auditId;
		} else {
			Log::notice('No audit entry created for this task');
			return self::DUMMY_AUDIT_ID;
		}
	}	

}
