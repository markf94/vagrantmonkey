<?php

namespace Configuration\Task;

use Zsd\Db\TasksMapperAwareInterface;
use Zsd\Db\TasksMapper;
use Application\ConfigAwareInterface;
use Zend\Config\Config;

class ConfigurationPackage implements TasksMapperAwareInterface, ConfigAwareInterface {
	/**
	 * @var TasksMapper
	 */
	private $tasksMapper;
	
	/**
	 * @var Config
	 */
	private $config;
	/* (non-PHPdoc)
	 * @see \Zsd\Db\TasksMapperAwareInterface::setTasksMapper()
	 */
	public function setTasksMapper($tasksMapper) {
		/// do not overwrite an existing tasks mapper - we may be in transition to cluster and using a custom tasks mapper
		if (is_null($this->tasksMapper)) {
			$this->tasksMapper = $tasksMapper;
		}
		return $this;
	}

	/**
	 * @param string $snapshotName
	 * @return integer
	 */
	public function exportConfiguration($snapshotName = null) {
		$directivesBlacklist = isset($this->config->directivesBlacklist) ? $this->config->directivesBlacklist->toArray() : array();
		$extraData = array('snapshotType' => 0, 'directivesBlacklist' => $directivesBlacklist);
			
		if (! is_null($snapshotName) && $snapshotName) {
			$extraData['snapshotName'] = $snapshotName;
			$extraData['snapshotType'] = 1;
		}
		
		$taskId = $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_CONFIGURATION_EXPORT, $extraData);
		return $taskId;
	}
	
	/**
	 * @return TasksMapper
	 */
	public function getTasksMapper() {
		return $this->tasksMapper;
	}
	
	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::getAwareNamespace()
	 */
	public function getAwareNamespace() {
		return array('export');
	}

	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::setConfig()
	 */
	public function setConfig($config) {
		$this->config = $config;
	}


}

