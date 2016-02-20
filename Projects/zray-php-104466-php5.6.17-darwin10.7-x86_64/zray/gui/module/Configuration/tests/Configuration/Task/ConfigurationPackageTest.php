<?php

namespace Configuration\Task;

use ZendServer\PHPUnit\TestCase;
use Audit\Container;
use Zsd\Db\TasksMapper;
use Zend\Config\Config;

require_once 'tests/bootstrap.php';

class ConfigurationPackageTest extends TestCase
{
	public function testExportConfiguration() {
		
		$directivesBlacklist = array('directive1');
		
		$tasksMapper = $this->getMock('Zsd\Db\TasksMapper');
		$tasksMapper->expects($this->any())->method('insertTask')
			->with(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_CONFIGURATION_EXPORT, $extraData = array('snapshotType' => 0, 'directivesBlacklist' => $directivesBlacklist))
			->will($this->returnValue(1001));
		
		$configuration = new ConfigurationPackage();
		$configuration->setTasksMapper($tasksMapper);
		$configuration->setConfig(new Config(array('directivesBlacklist' => $directivesBlacklist)));
		
		self::assertEquals(1001, $configuration->exportConfiguration());
	}
	
	public function testExportConfigurationEmptyNameConsideredNull() {
		
		$directivesBlacklist = array('directive1');
		
		$tasksMapper = $this->getMock('Zsd\Db\TasksMapper');
		$tasksMapper->expects($this->once())->method('insertTask')
			->with(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_CONFIGURATION_EXPORT, $extraData = array('snapshotType' => 0, 'directivesBlacklist' => $directivesBlacklist))
			->will($this->returnValue(1001));
		
		$configuration = new ConfigurationPackage();
		$configuration->setTasksMapper($tasksMapper);
		$configuration->setConfig(new Config(array('directivesBlacklist' => $directivesBlacklist)));
		
		self::assertEquals(1001, $configuration->exportConfiguration(''));
	}
	
	public function testExportConfigurationNamed() {
		
		$directivesBlacklist = array('directive1');
		
		$tasksMapper = $this->getMock('Zsd\Db\TasksMapper');
		$tasksMapper->expects($this->once())->method('insertTask')
			->with(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_CONFIGURATION_EXPORT, array('snapshotType' => 1, 'directivesBlacklist' => $directivesBlacklist, 'snapshotName' => 'SystemBoot'))
			->will($this->returnValue(1001));
		
		$configuration = new ConfigurationPackage();
		$configuration->setTasksMapper($tasksMapper);
		$configuration->setConfig(new Config(array('directivesBlacklist' => $directivesBlacklist)));
		
		self::assertEquals(1001, $configuration->exportConfiguration('SystemBoot'));
	}
}

