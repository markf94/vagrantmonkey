<?php

namespace Configuration;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zsd\Db\TasksMapper;
use ZendServer\Set;
use ZendServer\Log\Log;
use ZendServer\Exception;
use Vhost\Reply\VhostOperationContainer;

class MapperReplies extends MapperAbstract {
	protected $setClass='\Configuration\ServerInfoReplyContainer';
	
	const ZSD_TASKS_SLEEP_DEFAULT = 15;
	const ZSD_TASKS_SEQUENCE = 'ZSD_TASKS_SEQUENCE';
	
	/**
	 * @var TasksMapper
	 */
	protected $tasksMapper;

	/**
	 *
	 * @param integer $task
	 * @return boolean
	 */
	public function isTaskComplete($task) {
		$resultSet = $this->tableGateway->select(array(self::ZSD_TASKS_SEQUENCE => $task));
	
		/* @var $resultSet \Zend\Db\ResultSet\ResultSet */
		return ($resultSet && count($resultSet->toArray()) > 0) ? true : false;
	}
	
	/**
	 *
	 * @param integer $task
	 * @return Set
	 */
	public function getExportTaskReply($taskId) {
		$this->setClass = 'Configuration\ReplyContainer';
		$result = $this->select(array(self::ZSD_TASKS_SEQUENCE => $taskId));
		$this->delete(array(self::ZSD_TASKS_SEQUENCE => $taskId));
		return $result;
	}
	
	/**
	 *
	 * @param integer $task
	 * @return Set
	 */
	public function getTaskReply($taskId) {
		return $this->select(array(self::ZSD_TASKS_SEQUENCE => $taskId));
	}

	/**
	 * @param integer $taskId
	 * @param number $maxSleep
	 */
	public function waitForTask($taskId, $maxSleep = self::ZSD_TASKS_SLEEP_DEFAULT) {
		$sleep = 0;			
		while($sleep < $maxSleep) {
			if ($this->isTaskComplete($taskId)) {
				return true;
			}
			$sleep++;
			sleep(1);
		}
		return false;
		
	}

	/**
	 * @param integer $taskId
	 * @throws \ZendServer\Exception
	 * @return VhostOperationContainer
	 */
	public function waitAndExtractReply($taskId) {
		$this->setClass = 'Vhost\Reply\VhostOperationContainer';
		if ($this->waitForTask($taskId)) {
			$serverInfo = $this->getTaskReply($taskId);
		} else {
			throw new Exception(_t("Task %s did not complete in a timely manner", array($taskId)));
		}
		/// remove the reply record
		$this->delete(array(self::ZSD_TASKS_SEQUENCE => $taskId));
		return $serverInfo->current();
	}
	
	public function getServerInfoWithRetry($serverId) {
		$taskId = $this->getTasksMapper()->insertTask($serverId, TasksMapper::COMMAND_GET_PHPINFO);
		try {
			if ($this->waitForTask($taskId)) {
				$serverInfo = $this->getTaskReply($taskId);
			} else {
				throw new \Exception(_t("Webserver took an unusually long time to respond. Plese try again")); 
			}
			$serverData = $serverInfo->current()->toArray();
		} catch (\Exception $e) {
			throw new \WebAPI\Exception($e->getMessage(), \WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		return $serverData;
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $tableGateway
	 */
	public function getTableGateway() {
		return $this->tableGateway;
	}
	
	/**
	 * @return TasksMapper
	 */
	public function getTasksMapper() {
		return $this->tasksMapper;
	}

	/**
	 * @param TasksMapper $tasksMapper
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasksMapper = $tasksMapper;
		return $this;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $tableGateway
	 * @return MapperReplies
	 */
	public function setTableGateway($tableGateway) {
		$this->tableGateway = $tableGateway;
		return $this;
	}
	
}