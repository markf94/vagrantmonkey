<?php

namespace MonitorRules\Model;

use Zsd\Db\TasksMapperAwareInterface;
use Zsd\Db\TasksMapper;
use ZendServer\Log\Log;

class Tasks implements TasksMapperAwareInterface {
	/**
	 * @var TasksMapper
	 */
	private $tasksMapper;
	
	/**
	 * @param array $ids
	 */
	public function syncMonitorRulesChanges(array $ids) {
		Log::debug(__FUNCTION__ . " on servers ".implode(',', $ids));
		$this->getTasksMapper()->insertTasksServers($ids, TasksMapper::COMMAND_MONITOR_RULES_UPDATED);
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
	}

}