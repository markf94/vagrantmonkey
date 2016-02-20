<?php

namespace PageCache\Model;

use Zsd\Db\TasksMapper;
use Zsd\Db\TasksMapperAwareInterface;
use ZendServer\Log\Log;
class Tasks implements TasksMapperAwareInterface {
	/**
	 * @var TasksMapper
	 */
	private $tasksMapper;
	

	/**
	 * @param array $ids
	 */
	public function syncPageCacheRulesChanges(array $ids) {
		Log::debug(__FUNCTION__ . " on servers ".implode(',', $ids));
		$this->getTasksMapper()->insertTasksServers($ids, TasksMapper::COMMAND_PAGECACHE_RULES_UPDATED);
	}

	/**
	 * @param integer $auditId
	 */
	public function clearCache($extraData = array()) {
		$this->tasksMapper->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_CLEAR_PAGECACHE_CACHE, $extraData);
	}
	
	/**
	 * @return TasksMapper
	 */
	public function getTasksMapper() {
		return $this->tasksMapper;
	}
	
	/**
	 * @param \Zsd\Db\TasksMapper $tasksMapper
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasksMapper = $tasksMapper;
		return $this;
	}
}