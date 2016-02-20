<?php
namespace Servers\Db;

use Zsd\Db\TasksMapperAwareInterface;
use Zsd\Db\TasksMapper;
use ZendServer\Log\Log;
class Tasks implements TasksMapperAwareInterface {
	/**
	 * @var TasksMapper
	 */
	private $tasksMapper;
	
	/**
	 * @param string $serverName
	 * @param string $nodeIp
	 * @param string $dbHost
	 * @param integer $dbPort
	 * @param string $dbUsername
	 * @param string $dbPassword
	 * @param string $dbName
	 */
	public function serverAddToCluster($serverName, $nodeIp, $dbHost, $dbPort, $dbUsername, $dbPassword, $dbName, $extraAuditDetails=array()) {
		Log::info("adding server '$serverName' to the cluster");
		
		$this->tasksMapper->insertTask(0, TasksMapper::COMMAND_JOIN_CLUSTER, array(
					'node_name' => $serverName,
					'node_ip' => $nodeIp,
					'database_host' => $dbHost,
					'database_port' => (string)$dbPort,
					'database_name' => $dbName,
					'user' => $dbUsername,
					'password' => $dbPassword
			) + $extraAuditDetails);
	}
	
	/**
	 * @param integer $serverId
	 */
	public function serverEnable($serverId) {
		Log::info("enable server '$serverId'");
		$this->tasksMapper->insertTask($serverId, TasksMapper::COMMAND_ENABLE_SERVER);
	}
	
	/**
	 * @param integer $serverId
	 */
	public function serverDisable($serverId) {
		Log::info("disable server '$serverId'");
		$this->tasksMapper->insertTask($serverId, TasksMapper::COMMAND_DISABLE_SERVER);
	}
	
	/**
	 * @param integer $serverId
	 */
	public function serverRemove($serverId) {
		Log::info("remove server '$serverId'");
		$this->tasksMapper->insertTask($serverId, TasksMapper::COMMAND_DISCONNECT_FROM_CLUSTER);
	}
	
	/**
	 * @param integer $serverId
	 */
	public function serverForceRemove($serverId) {
		Log::info("remove server '$serverId'");
		$this->tasksMapper->removeServerTasks($serverId);
		$this->tasksMapper->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_FORCE_DISCONNECT_FROM_CLUSTER, array('node_id' => $serverId));
	}
	
	/**
	 * @param array $serversIds
	 */
	public function restartPhpSelective($serversIds) {
		Log::info('Starting selective restart');
		$this->tasksMapper->insertTasksServers($serversIds, TasksMapper::COMMAND_RESTART_SERVER, array(TasksMapper::RESTART_TYPE_SELECTIVE));
	}
	
	/**
	 * @param array $serversIds
	 */
	public function restartPhpFull($serversIds) {
		Log::info('Starting full restart');
		$this->tasksMapper->insertTasksServers($serversIds, TasksMapper::COMMAND_RESTART_SERVER, array(TasksMapper::RESTART_TYPE_FULL));
	}
	
	/**
	 * @param array $serversIds
	 */
	public function restartDaemon($serversIds, $daemon) {
		$task = $this->getTasksMapper()->getTaskDaemonName($daemon);
		$this->tasksMapper->insertTasksServers($serversIds, $task, array(TasksMapper::RESTART_TYPE_FULL));
	}
	
	/**
	 * @return TasksMapper
	 */
	public function getTasksMapper() {
		return $this->tasksMapper;
	}
	
	/* (non-PHPdoc)
	 * @see \Zsd\Db\TasksMapperAwareInterface::setTasksMapper()
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasksMapper = $tasksMapper;
	}
	

}
