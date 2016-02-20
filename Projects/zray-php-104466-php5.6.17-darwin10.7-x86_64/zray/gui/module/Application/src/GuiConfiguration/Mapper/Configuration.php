<?php

namespace GuiConfiguration\Mapper;

use Zsd\Db\TasksMapper;
use Application\Module;
use ZendServer\Log\Log;
use Zsd\Db\TasksMapperAwareInterface;

class Configuration implements TasksMapperAwareInterface{
	/**
	 * @var TasksMapper
	 */
	private $tasksMapper;
	
	/**
	 * @param array $directives - array of directives consisting of keys=>values. keys might not include the "zend_gui" prefix
	 * @param integer $auditId - the auditId to be inserted
	 */
	public function setGuiDirectives(array $directives) {
		$directivesToSave = array();
		$directivesPrefix = Module::INI_PREFIX . '.';
		foreach($directives as $name => $value) {
			$name = preg_replace("/^({$directivesPrefix}){0,1}(.*)/", "{$directivesPrefix}\${2}", $name); // making sure we have directive starting with zend_gui. whether the prefix was passed or not
			$directivesToSave[] = array('name' => $name, 'value' => strval($value));
		}
	
		Log::info("Updating GUI directives with the following changes: " . print_r($directivesToSave, true));
	
		$this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_SAVE_AND_APPLY_BLUEPRINT, $directivesToSave);
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
	}

}