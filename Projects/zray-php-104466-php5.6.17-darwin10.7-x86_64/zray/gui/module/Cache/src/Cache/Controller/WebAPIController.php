<?php

namespace Cache\Controller;

use ZendServer\Mvc\Controller\WebAPIActionController,
	Configuration\DdMapper,
	ZendServer\Set,
	ZendServer\Log\Log,
	ZendServer\Exception as ZSException,
	Audit\Db\Mapper as auditMapper,
	Audit\Db\ProgressMapper;
use Zsd\Db\TasksMapper;
use Configuration\Controller\ZendComponentsController;
use WebAPI\Exception;
use Zend\Json\Json;

class WebAPIController extends WebAPIActionController
{
	public function datacacheClearAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array());
		$keys = $this->validateArrayNonEmpty($params['keys'], 'keys');
		
		
		$taskId = array(TasksMapper::COMMAND_CLEAR_DATACACHE_DISK_CACHE_NAMESPACE, TasksMapper::COMMAND_CLEAR_DATACACHE_SHM_CACHE_NAMESPACE);
		
		$keysFormatted = array();
		foreach ($keys as $key) {
			if (is_string($key)) {
				if (0 < preg_match('/^(?P<namespace>[^:]+)::(?P<key>.+)$/', $key, $matches)) {
					$keysFormatted[] = array('namespace' => $matches['namespace'], 'key' => $matches['key']);
				} elseif (0 < preg_match('/^(?P<namespace>[^:]+)::$/', $key, $matches)) {
					$keysFormatted[] = array('namespace' => $matches['namespace'], 'key' => '');
				} else {
					$keysFormatted[] = array('namespace' => '', 'key' => $key);
				}
			}
		}
		
		try {
			if (0 < count($keysFormatted)) {
				$audit = $this->auditMessage(auditMapper::AUDIT_CLEAR_DATA_CACHE_CACHE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $keysFormatted); /* @var $audit \Audit\Container */
				$tasksMapper = $this->getLocator()->get('Zsd\Db\TasksMapper'); /* @var $tasksMapper \Zsd\Db\TasksMapper */
				
				foreach ($taskId as $task) {
					$tasksMapper->insertTask(TasksMapper::DUMMY_NODE_ID, $task, $keysFormatted);
				}
			} elseif (0 < count($keys)) {
				throw new Exception('Bad formatted keys');
			}
		} catch (\Exception $e) {
			throw new Exception ( vsprintf ( 'Keys passed to clear were invalid: %s', array (
					Json::encode ($keys) 
			) ), Exception::INVALID_PARAMETER );
		}
		return $this->acceptableviewmodelselector ()->setTemplate ( 'cache/web-api/cache-clear' );
	}
	
	/**
	 *
	 * @throws WebAPI\Exception
	 */
	public function cacheClearAction() {
		$this->isMethodPost ();
		
		$auditTypeArray = array (
				ZendComponentsController::COMPONENT_ZEND_OPCACHE => auditMapper::AUDIT_CLEAR_OPTIMIZER_PLUS_CACHE,
				ZendComponentsController::COMPONENT_ZEND_OPTIMIZER => auditMapper::AUDIT_CLEAR_OPTIMIZER_PLUS_CACHE,
				ZendComponentsController::COMPONENT_ZEND_DATA_CACHE => auditMapper::AUDIT_CLEAR_DATA_CACHE_CACHE,
				ZendComponentsController::COMPONENT_ZEND_PAGE_CACHE => auditMapper::AUDIT_CLEAR_PAGE_CACHE_CACHE, 
				ZendComponentsController::COMPONENT_ZEND_URL_TRACKING => auditMapper::AUDIT_CLEAR_URL_TRACKING,
		);
		
		$params = $this->getParameters ();
		$this->validateMandatoryParameters ( $params, array (
				'component' 
		) );
		$this->validateAllowedValues ( $params ['component'], 'component', array (
				ZendComponentsController::COMPONENT_ZEND_OPCACHE,
				ZendComponentsController::COMPONENT_ZEND_OPTIMIZER,
				ZendComponentsController::COMPONENT_ZEND_DATA_CACHE,
				ZendComponentsController::COMPONENT_ZEND_PAGE_CACHE, 
				ZendComponentsController::COMPONENT_ZEND_URL_TRACKING, 
		) );
		$audit = $this->auditMessage ( $auditTypeArray [$params ['component']], ProgressMapper::AUDIT_PROGRESS_REQUESTED ); /* @var $audit \Audit\Container */
		$tasksMapper = $this->getLocator ()->get ( 'Zsd\Db\TasksMapper' ); /* @var $tasksMapper \Zsd\Db\TasksMapper */
		
		
		if ($params ['component'] == ZendComponentsController::COMPONENT_ZEND_PAGE_CACHE) {
			$tasks = $this->getLocator ()->get ( 'PageCache\Model\Tasks' );
			$tasks->clearCache ();
		} else {
			$taskId = '';
			switch ($params ['component']) {
				case ZendComponentsController::COMPONENT_ZEND_OPCACHE :
				case ZendComponentsController::COMPONENT_ZEND_OPTIMIZER :
					$taskId = TasksMapper::COMMAND_CLEAR_OPTIMIZER_PLUS_CACHE;
					break;
				case ZendComponentsController::COMPONENT_ZEND_DATA_CACHE :
					$taskId = array (
							TasksMapper::COMMAND_CLEAR_DATACACHE_DISK_CACHE,
							TasksMapper::COMMAND_CLEAR_DATACACHE_SHM_CACHE 
					);
					break;
				case ZendComponentsController::COMPONENT_ZEND_URL_TRACKING :
					$taskId = TasksMapper::COMMAND_CLEAR_URL_TRACKING;
					break;
				default :
					throw new Exception ( "No clear action exists for this component {$params['component']}");
					
			}
			
			if (is_array($taskId)) {
				foreach ($taskId as $task) {
					$tasksMapper->insertTask(TasksMapper::DUMMY_NODE_ID, $task);
				}
			} else {
				$tasksMapper->insertTask(TasksMapper::DUMMY_NODE_ID, $taskId);
			}
		}
		
	
		return array();
	}
}