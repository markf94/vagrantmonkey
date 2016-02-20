<?php

namespace Codetracing\Mapper;

use Zsd\Db\TasksMapperAwareInterface;
use Zsd\Db\TasksMapper;

class Tasks implements TasksMapperAwareInterface {
	/**
	 * @var TasksMapper
	 */
	private $tasks;

	/**
	 * @param array $traceIds
	 * @return integer
	 */
	public function deleteTracesByIds(array $traceIds = array()) {
		return $this->getTasks()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_CODETRACING_DELETE, $traceIds);
	}
	
	/**
	 * @return TasksMapper
	 */
	public function getTasks() {
		return $this->tasks;
	}

	/*
	 * (non-PHPdoc)
	 * @see \Zsd\Db\TasksMapperAwareInterface::setTasksMapper()
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasks = $tasksMapper;
	}
}