<?php

namespace Zsd\Db;

interface TasksMapperAwareInterface {
	/**
	 * @param \Zsd\Db\TasksMapper $tasksMapper
	 */
	public function setTasksMapper($tasksMapper);
}