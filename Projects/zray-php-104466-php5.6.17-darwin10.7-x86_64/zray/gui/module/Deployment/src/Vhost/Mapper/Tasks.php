<?php

namespace Vhost\Mapper;

use Zsd\Db\TasksMapper;
use Zsd\Db\TasksMapperAwareInterface;
use Servers\Db\Mapper as ServersMapper;
use Servers\Db\ServersAwareInterface;
use Zsd\ZsdHealthChecker;
use ZendServer\Exception;
use Zend\Mvc\MvcEvent;

class Tasks implements TasksMapperAwareInterface {
	/**
	 * @var TasksMapper
	 */
	private $tasksMapper;
	
	/**
	 * @var ZsdHealthChecker
	 */
	private $zsdHealth;
	
	/**
	 * @var MvcEvent
	 */
	private $event;
	
	/**
	 * @param string $certificateFile
	 * @param string $keyFile
	 * @param string $chainFile
	 * @throws Exception
	 * @return number
	 */
	public function validateSslFiles($certificateFile = '', $keyFile = '', $chainFile = '', $appName = '') {
		if ($this->getZsdHealth()->checkZsdHealth(true) === false) { /// may return null in certain cases (?!)
			throw new Exception(_t('ZSD is not available to perform this action'));
		}
		
		$files = array('ssl_certificate' => $certificateFile, 'ssl_certificate_key' => $keyFile, 'ssl_certificate_chain' => $chainFile, 'ssl_app_name' => $appName);
		
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_SSL_VALID,
				array($files));
	}
	
	/**
	 * @param string $vhostName
	 * @param integer $port
	 * @param string $template
	 * @param boolean $ssl
	 * @param string $certificateFile
	 * @param string $keyFile
	 * @param string $chainFile
	 * @return number
	 */
	public function validateTemplate($vhostName, $port, $template, $ssl, $certificateFile = '', $keyFile = '', $chainFile = '', $appName = '') {
		if ($this->getZsdHealth()->checkZsdHealth(true) === false) { /// may return null in certain cases (?!)
			throw new Exception(_t('ZSD is not available to perform this action'));
		}

		$properties = array('name' => $vhostName, 'port' => $port, 'template' => $template, 'ssl_support' => $ssl, 'ssl_certificate' => $certificateFile, 'ssl_certificate_key' => $keyFile, 'ssl_certificate_chain' => $chainFile, 'ssl_app_name' => $appName);
		
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_TEMPLATE_VALID,
				array($properties));
	}
	
	/**
	 * @param array $extraData
	 * @return number
	 */
	public function addVhostTask($extraData = array()) {
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_INSERT, array($extraData));
	}
	
	/**
	 * 
	 * @param integer $auditId
	 * @param array $extraData
	 * @return number
	 */
	public function editVhostTask($extraData = array()) {
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_EDIT, array($extraData));
	}
	
	/**
	 *
	 * @param string $vhostIds
	 * @param array $extraData
	 * @return number
	 */
	public function removeVhostTask($vhostIds) {
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_REMOVE, $vhostIds);
	}
	
	/**
	 * @param string $vhostId
	 * @return number
	 */
	public function redeployVhostTask($vhostId) {
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_REDEPLOY, array((int) $vhostId));
	}
	
	/**
	 * @param string $vhostId
	 * @return number
	 */
	public function unmanageVhostTask($vhostId) {
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_UNMANAGE, array(array('vhostId' => (int) $vhostId)));
	}
	
	/**
	 * @param string $vhostId
	 * @return number
	 */
	public function manageVhostTask($vhostId, $updateBlueprint) {
		return $this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_VHOST_MANAGE, array(array('vhostId' => (int) $vhostId, 'updateBlueprint' => (boolean)$updateBlueprint)));
	}
	
	/**
	 * @return TasksMapper
	 */
	public function getTasksMapper() {
		return $this->tasksMapper;
	}

	/**
	 * @return ZsdHealthChecker
	 */
	public function getZsdHealth() {
		return $this->zsdHealth;
	}

	/**
	 * @param \Zsd\ZsdHealthChecker $zsdHealth
	 */
	public function setZsdHealth($zsdHealth) {
		$this->zsdHealth = $zsdHealth;
	}

	/**
	 * @param \Zsd\Db\TasksMapper $tasksMapper
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasksMapper = $tasksMapper;
	}
}

