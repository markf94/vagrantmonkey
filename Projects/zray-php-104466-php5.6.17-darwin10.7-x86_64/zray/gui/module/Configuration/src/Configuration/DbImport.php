<?php

namespace Configuration;

use ZendServer\Exception;
use Zsd\Db\TasksMapper;
use ZendServer\Log\Log;
use Servers\Db\ServersAwareInterface;
use Zend\Db\Sql\Where;

class DbImport implements ServersAwareInterface {
	/**
	 * @var \Zend\Db\Adapter\Adapter
	 */
	private $adapter;
	/**
	 * @var Zsd\Db\TasksMapper
	 */
	private $tasksMapper;
	/**
	 * @var Servers\Db\Mapper
	 */
	private $serversMapper;
	
	/**
	 * @var DdMapper
	 */
	private $ddMapper;
	
	/**
	 * @var MapperExtensions
	 */
	private $extensionMapper;
	
	/**
	 * @return \Zend\Db\Adapter\Adapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * @return \Zsd\Db\TasksMapper $tasksMapper
	 */
	public function getTasksMapper() {
		return $this->tasksMapper;
	}

	/**
	 * @return \Servers\Db\Mapper $serversMapper
	 */
	public function getServersMapper() {
		return $this->serversMapper;
	}

	/**
	 * @return DdMapper
	 */
	public function getDdMapper() {
		return $this->ddMapper;
	}

	/**
	 * @return MapperExtensions
	 */
	public function getExtensionMapper() {
		return $this->extensionMapper;
	}

	/**
	 * @param \Configuration\MapperExtensions $extensionMapper
	 */
	public function setExtensionMapper($extensionMapper) {
		$this->extensionMapper = $extensionMapper;
	}

	/**
	 * @param \Configuration\DdMapper $ddMapper
	 */
	public function setDdMapper($ddMapper) {
		$this->ddMapper = $ddMapper;
	}

	/**
	 * @param \Servers\Db\Mapper $serversMapper
	 * @return DbImport
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
		return $this;
	}

	/**
	 * @param \Zsd\Db\TasksMapper $tasksMapper
	 * @return DbImport
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasksMapper = $tasksMapper;
		return $this;
	}

	/**
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 */
	public function setAdapter($adapter) {
		$this->adapter = $adapter;
	}
	
	public function importDatabase($sql) {
		$connection = $this->adapter->getDriver()->getConnection(); /* @var $connection \PDO */
		try{
			$connection->beginTransaction();
			$replaces = preg_split("#replace into#i", $sql);
			
			foreach ($replaces as $replace) {
				if ($replace) {
					$connection->execute("REPLACE INTO {$replace}");
				}
			}
			
			$this->postImportPass();
			$connection->commit();
			$this->getTasksMapper()->insertTasksServers($this->getServersMapper()->findAllServersIds(), TasksMapper::COMMAND_APPLY_BLUEPRINT_WITH_EXTENSIONS);
		} catch (\Exception $e) {
			$connection->rollback();
			throw new Exception($e->getMessage(), Exception::ERROR, $e);
		}
	}

	/**
	 * Adapt old package data to newer versions of ZS
	 */
	private function postImportPass() {
		/// specifically set the IS_ZEND_EXTENSION column value according to DD json data
		$displayMap = $this->getDdMapper()->getComponentsDisplayMap();
		foreach ($displayMap as $extension => $zemExtension) {
			$where = new Where();
			$where->equalTo('name', $extension);
			$affected = $this->getExtensionMapper()->getTableGateway()->update(array(MapperExtensions::IS_ZEND_COMPONENT => intval($zemExtension)), $where);
			if (! $affected) {
				Log::info("Extension '{$extension}' was not found: IS_ZEND_EXTENSION column value not set");
			}
		}
	}
}
