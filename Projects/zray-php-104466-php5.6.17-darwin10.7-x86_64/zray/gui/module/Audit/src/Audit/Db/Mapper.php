<?php

namespace Audit\Db;

use Bootstrap\Exception;

use Zend\Db\Sql\Expression;

use Zend\Json\Json;

use Audit\AuditTypeGroupsInterface;
use Audit\AuditTypeInterface;

use Audit\Container;
use Audit\Dictionary;
use Audit\ProgressContainer;

use Application\Module;

use ZendServer\Log\Log,
ZendServer\Set,
Zend\Db\TableGateway\TableGateway,
\Configuration\MapperAbstract,
Zend\Db\Sql\Select,
Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Where;

class Mapper extends MapperAbstract implements AuditTypeInterface {
	
	const AUDIT_REQUEST_INTERFACE_UI = 'INTERFACE_UI';
	const AUDIT_REQUEST_INTERFACE_WEBAPI = 'INTERFACE_WEBAPI';
	const AUDIT_REQUEST_INTERFACE_FILE_SYSTEM = 'INTERFACE_FILE_SYSTEM';
	const AUDIT_REQUEST_INTERFACE_DEVBAR = 'INTERFACE_DEVBAR';
	
	protected $defaultField = 'AUDIT_ID';
	
	protected $setClass = '\Audit\Container';
	
	protected $tableColumns = array(
									'AUDIT_ID',
									'USERNAME',
									'REQUEST_INTERFACE',
									'REMOTE_ADDR',
									'AUDIT_TYPE',
									'BASE_URL',
									'CREATION_TIME',
									'EXTRA_DATA'
							);

	protected $auditTypeStrings = array();
	
	protected $auditTypes = array(
				0 => self::AUDIT_APPLICATION_DEPLOY,
				1 => self::AUDIT_APPLICATION_REMOVE,
				2 => self::AUDIT_APPLICATION_UPGRADE,
				3 => self::AUDIT_APPLICATION_ROLLBACK,
				4 => self::AUDIT_APPLICATION_REDEPLOY,
				5 => self::AUDIT_APPLICATION_REDEPLOY_ALL,
				6 => self::AUDIT_APPLICATION_DEFINE,
				7 => self::AUDIT_DIRECTIVES_MODIFIED,
				8 => self::AUDIT_EXTENSION_ENABLED,
				9 => self::AUDIT_EXTENSION_DISABLED,
				10 => self::AUDIT_RESTART_DAEMON,
				11 => self::AUDIT_RESTART_PHP,
				12 => self::AUDIT_GUI_AUTHENTICATION,
				13 => self::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS,
				14 => self::AUDIT_GUI_CHANGE_PASSWORD,
				15 => self::AUDIT_GUI_AUTHORIZATION,
				16 => self::AUDIT_GUI_AUTHENTICATION_LOGOUT,
				17 => self::AUDIT_GUI_AUDIT_SETTINGS_SAVE,
				18 => self::AUDIT_GUI_BOOTSTRAP_CREATEDB,
				19 => self::AUDIT_GUI_BOOTSTRAP_SAVELICENSE,
				20 => self::AUDIT_SERVER_JOIN,
				21 => self::AUDIT_SERVER_ADD,
				22 => self::AUDIT_SERVER_ENABLE,
				23 => self::AUDIT_SERVER_DISABLE,
				24 => self::AUDIT_SERVER_REMOVE,
				25 => self::AUDIT_SERVER_REMOVE_FORCE,
				26 => self::AUDIT_SERVER_RENAME,
				27 => self::AUDIT_SERVER_SETPASSWORD,
				28 => self::AUDIT_CODETRACING_CREATE,
				29 => self::AUDIT_CODETRACING_DELETE,
				30 => self::AUDIT_CODETRACING_DEVELOPER_ENABLE,
				31 => self::AUDIT_CODETRACING_DEVELOPER_DISABLE,
				32 => self::AUDIT_MONITOR_RULES_ENABLE,
				33 => self::AUDIT_MONITOR_RULES_DISABLE,
				34 => self::AUDIT_MONITOR_RULES_SAVE,
				35 => self::AUDIT_MONITOR_RULES_ADD,
				36 => self::AUDIT_MONITOR_RULES_REMOVE,
				37 => self::AUDIT_STUDIO_DEBUG,
				38 => self::AUDIT_STUDIO_PROFILE,
				39 => self::AUDIT_STUDIO_SOURCE,
				40 => self::AUDIT_STUDIO_DEBUG_MODE_START,
				41 => self::AUDIT_STUDIO_DEBUG_MODE_STOP,
				42 => self::AUDIT_CLEAR_OPTIMIZER_PLUS_CACHE,
				43 => self::AUDIT_CLEAR_DATA_CACHE_CACHE,
				44 => self::AUDIT_CLEAR_PAGE_CACHE_CACHE,
				45 => self::AUDIT_PAGE_CACHE_SAVE_RULE,
				46 => self::AUDIT_PAGE_CACHE_DELETE_RULES,
				47 => self::AUDIT_JOB_QUEUE_SAVE_RULE,
				48 => self::AUDIT_JOB_QUEUE_DELETE_RULES,
				49 => self::AUDIT_JOB_QUEUE_DELETE_JOBS,
				50 => self::AUDIT_JOB_QUEUE_REQUEUE_JOBS,
				51 => self::AUDIT_JOB_QUEUE_RESUME_RULES,
				52 => self::AUDIT_JOB_QUEUE_DISABLE_RULES,
				53 => self::AUDIT_JOB_QUEUE_RUN_NOW_RULE,
				54 => self::AUDIT_GET_PHPINFO,
				55 => self::AUDIT_WEBAPI_KEY_ADD,
				56 => self::AUDIT_WEBAPI_KEY_REMOVE,
				57 => self::AUDIT_GUI_SAVELICENSE,
				58 => self::AUDIT_CONFIGURATION_EXPORT,
				59 => self::AUDIT_CONFIGURATION_IMPORT,
				60 => self::AUDIT_CONFIGURATION_RESET,
				61 => self::AUDIT_CLEAR_STATISTICS,
				62 => self::AUDIT_RELOAD_CONFIGURATION,
				63 => self::AUDIT_LIBRARY_REMOVE,
				64 => self::AUDIT_LIBRARY_VERSION_REMOVE,
				65 => self::AUDIT_LIBRARY_REDEPLOY,
				66 => self::AUDIT_LIBRARY_DEPLOY,
				67 => self::AUDIT_VHOST_ADD,
				68 => self::AUDIT_VHOST_REMOVE,
				69 => self::AUDIT_LIBRARY_SET_DEFAULT,
				70 => self::AUDIT_VHOST_EDIT,
				71 => self::AUDIT_VHOST_RESCAN,
				72 => self::AUDIT_VHOST_REDEPLOY,
				73 => self::AUDIT_VHOST_ENABLE_DEPLOYMENT,
				74 => self::AUDIT_VHOST_DISABLE_DEPLOYMENT,
				75 => self::AUDIT_JOB_QUEUE_ADD_JOB,
				76 => self::AUDIT_DEVELOPER_TOKEN_ADD,
				77 => self::AUDIT_DEVELOPER_TOKEN_REMOVE,
				78 => self::AUDIT_CLEAR_URL_TRACKING,
				79 => self::AUDIT_DEVBAR_ACCESS_ELEVATE,
				80 => self::AUDIT_DEVBAR_ACCESS_DEMOTE,
				81 => self::AUDIT_DEVELOPER_TOKEN_EXPIRE,
				82 => self::AUDIT_UrlInsight_RULE_ADD,
				83 => self::AUDIT_UrlInsight_RULE_REMOVE,
				84 => self::AUDIT_GUI_CHANGE_SERVER_PROFILE,
        	    85 => self::AUDIT_PLUGIN_DEPLOY,
        	    86 => self::AUDIT_PLUGIN_REMOVE,
        	    87 => self::AUDIT_PLUGIN_UPGRADE,
        	    88 => self::AUDIT_PLUGIN_ROLLBACK,
        	    89 => self::AUDIT_PLUGIN_REDEPLOY,
        	    90 => self::AUDIT_PLUGIN_REDEPLOY_ALL,
        	    91 => self::AUDIT_PLUGIN_ENABLE,
        	    92 => self::AUDIT_PLUGIN_DISABLE,
	            93 => self::AUDIT_JOB_QUEUE_ADD_QUEUE,
	            94 => self::AUDIT_JOB_QUEUE_DELETE_QUEUE,
	            95 => self::AUDIT_JOB_QUEUE_UPDATE_QUEUE,
	            96 => self::AUDIT_DEBUGGER_EDITED,
	            97 => self::AUDIT_JOB_QUEUE_QUEUES_EXPORT,
	            98 => self::AUDIT_JOB_QUEUE_QUEUES_IMPORT,
	            99 => self::AUDIT_JOB_QUEUE_SUSPEND_QUEUE,
	            100 => self::AUDIT_JOB_QUEUE_ACTIVATE_QUEUE,
	            
	            101 => self::AUDIT_ZRAY_DELETE,
			);
	
	protected $auditGroups = array(
				AuditTypeGroupsInterface::AUDIT_GROUP_AUTHENTICATION => array(
						AuditTypeInterface::AUDIT_GUI_AUTHENTICATION,
						AuditTypeInterface::AUDIT_GUI_AUTHENTICATION_LOGOUT,
						AuditTypeInterface::AUDIT_GUI_CHANGE_PASSWORD,
						AuditTypeInterface::AUDIT_DEVBAR_ACCESS_ELEVATE,
						AuditTypeInterface::AUDIT_DEVBAR_ACCESS_DEMOTE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_AUTHORIZATION => array(
						AuditTypeInterface::AUDIT_GUI_AUTHORIZATION
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_BOOTSTRAP => array(
						AuditTypeInterface::AUDIT_GUI_BOOTSTRAP_CREATEDB,
						AuditTypeInterface::AUDIT_GUI_BOOTSTRAP_SAVELICENSE,
						AuditTypeInterface::AUDIT_GUI_CHANGE_SERVER_PROFILE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_CLEAR_CACHE => array(
						AuditTypeInterface::AUDIT_CLEAR_OPTIMIZER_PLUS_CACHE,
						AuditTypeInterface::AUDIT_CLEAR_DATA_CACHE_CACHE,
						AuditTypeInterface::AUDIT_CLEAR_PAGE_CACHE_CACHE,
						AuditTypeInterface::AUDIT_CLEAR_STATISTICS,
						AuditTypeInterface::AUDIT_CLEAR_URL_TRACKING,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_CLUSTER_MANAGEMENT => array(
						AuditTypeInterface::AUDIT_SERVER_JOIN,
						AuditTypeInterface::AUDIT_SERVER_ADD,
						AuditTypeInterface::AUDIT_SERVER_ENABLE,
						AuditTypeInterface::AUDIT_SERVER_DISABLE,
						AuditTypeInterface::AUDIT_SERVER_REMOVE,
						AuditTypeInterface::AUDIT_SERVER_REMOVE_FORCE,
						AuditTypeInterface::AUDIT_SERVER_RENAME,
						AuditTypeInterface::AUDIT_SERVER_SETPASSWORD,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_CODETRACING => array(
						AuditTypeInterface::AUDIT_CODETRACING_CREATE,
						AuditTypeInterface::AUDIT_CODETRACING_DELETE,
						AuditTypeInterface::AUDIT_CODETRACING_DEVELOPER_ENABLE,
						AuditTypeInterface::AUDIT_CODETRACING_DEVELOPER_DISABLE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_CONFIGURATION => array(
						AuditTypeInterface::AUDIT_DIRECTIVES_MODIFIED,
						AuditTypeInterface::AUDIT_EXTENSION_ENABLED,
						AuditTypeInterface::AUDIT_EXTENSION_DISABLED,
						AuditTypeInterface::AUDIT_CONFIGURATION_EXPORT,						
						AuditTypeInterface::AUDIT_CONFIGURATION_IMPORT,						
						AuditTypeInterface::AUDIT_CONFIGURATION_RESET,
						AuditTypeInterface::AUDIT_RELOAD_CONFIGURATION,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_DEPLOYMENT => array(
						AuditTypeInterface::AUDIT_APPLICATION_DEPLOY,
						AuditTypeInterface::AUDIT_APPLICATION_REMOVE,
						AuditTypeInterface::AUDIT_APPLICATION_UPGRADE,
						AuditTypeInterface::AUDIT_APPLICATION_ROLLBACK,
						AuditTypeInterface::AUDIT_APPLICATION_REDEPLOY,
						AuditTypeInterface::AUDIT_APPLICATION_REDEPLOY_ALL,
						AuditTypeInterface::AUDIT_APPLICATION_DEFINE,
						AuditTypeInterface::AUDIT_LIBRARY_DEPLOY,
						AuditTypeInterface::AUDIT_LIBRARY_REDEPLOY,
						AuditTypeInterface::AUDIT_LIBRARY_REMOVE,
						AuditTypeInterface::AUDIT_LIBRARY_VERSION_REMOVE,
    				    AuditTypeInterface::AUDIT_PLUGIN_DEPLOY,
    				    AuditTypeInterface::AUDIT_PLUGIN_REMOVE,
    				    AuditTypeInterface::AUDIT_PLUGIN_UPGRADE,
    				    AuditTypeInterface::AUDIT_PLUGIN_ROLLBACK,
    				    AuditTypeInterface::AUDIT_PLUGIN_REDEPLOY,
    				    AuditTypeInterface::AUDIT_PLUGIN_REDEPLOY_ALL,
    				    AuditTypeInterface::AUDIT_PLUGIN_ENABLE,
    				    AuditTypeInterface::AUDIT_PLUGIN_DISABLE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_JOBQUEUE_RULES => array(
						AuditTypeInterface::AUDIT_JOB_QUEUE_SAVE_RULE,
						AuditTypeInterface::AUDIT_JOB_QUEUE_DELETE_RULES,
						AuditTypeInterface::AUDIT_JOB_QUEUE_DELETE_JOBS,
						AuditTypeInterface::AUDIT_JOB_QUEUE_REQUEUE_JOBS,
						AuditTypeInterface::AUDIT_JOB_QUEUE_RESUME_RULES,
						AuditTypeInterface::AUDIT_JOB_QUEUE_DISABLE_RULES,
						AuditTypeInterface::AUDIT_JOB_QUEUE_RUN_NOW_RULE,
						AuditTypeInterface::AUDIT_JOB_QUEUE_ADD_JOB,
						AuditTypeInterface::AUDIT_JOB_QUEUE_ADD_QUEUE,
						AuditTypeInterface::AUDIT_JOB_QUEUE_DELETE_QUEUE,
						AuditTypeInterface::AUDIT_JOB_QUEUE_UPDATE_QUEUE,
						AuditTypeInterface::AUDIT_JOB_QUEUE_SUSPEND_QUEUE,
						AuditTypeInterface::AUDIT_JOB_QUEUE_ACTIVATE_QUEUE,
						AuditTypeInterface::AUDIT_JOB_QUEUE_QUEUES_EXPORT,
						AuditTypeInterface::AUDIT_JOB_QUEUE_QUEUES_IMPORT,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_LICENSE => array(
						AuditTypeInterface::AUDIT_GUI_SAVELICENSE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_MONITOR => array(
						AuditTypeInterface::AUDIT_MONITOR_RULES_ENABLE,
						AuditTypeInterface::AUDIT_MONITOR_RULES_DISABLE,
						AuditTypeInterface::AUDIT_MONITOR_RULES_SAVE,
						AuditTypeInterface::AUDIT_MONITOR_RULES_ADD,
						AuditTypeInterface::AUDIT_MONITOR_RULES_REMOVE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_PAGE_CACHE_RULES => array(
						AuditTypeInterface::AUDIT_PAGE_CACHE_SAVE_RULE,
						AuditTypeInterface::AUDIT_PAGE_CACHE_DELETE_RULES,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_PHPINFO => array(
						AuditTypeInterface::AUDIT_GET_PHPINFO,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_RESTART => array(
						AuditTypeInterface::AUDIT_RESTART_DAEMON,
						AuditTypeInterface::AUDIT_RESTART_PHP,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_SETTINGS_CHANGES => array(
						AuditTypeInterface::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS,
						AuditTypeInterface::AUDIT_GUI_AUDIT_SETTINGS_SAVE,
						AuditTypeInterface::AUDIT_DIRECTIVES_MODIFIED,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_STUDIO => array(
						AuditTypeInterface::AUDIT_STUDIO_DEBUG,
						AuditTypeInterface::AUDIT_STUDIO_PROFILE,
						AuditTypeInterface::AUDIT_STUDIO_SOURCE,
						AuditTypeInterface::AUDIT_STUDIO_DEBUG_MODE_START,
						AuditTypeInterface::AUDIT_STUDIO_DEBUG_MODE_STOP,
						AuditTypeInterface::AUDIT_DEBUGGER_EDITED,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_WEBAPI => array(
						AuditTypeInterface::AUDIT_WEBAPI_KEY_ADD,
						AuditTypeInterface::AUDIT_WEBAPI_KEY_REMOVE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_DEPLOYMENT_LIBRARY => array(
						AuditTypeInterface::AUDIT_LIBRARY_DEPLOY,
						AuditTypeInterface::AUDIT_LIBRARY_REMOVE,
						AuditTypeInterface::AUDIT_LIBRARY_VERSION_REMOVE,
						AuditTypeInterface::AUDIT_LIBRARY_REDEPLOY,
						AuditTypeInterface::AUDIT_LIBRARY_SET_DEFAULT,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_DEPLOYMENT_VHOST => array(
					AuditTypeInterface::AUDIT_VHOST_ADD,
					AuditTypeInterface::AUDIT_VHOST_EDIT,
					AuditTypeInterface::AUDIT_VHOST_REMOVE,
					AuditTypeInterface::AUDIT_VHOST_RESCAN,
					AuditTypeInterface::AUDIT_VHOST_REDEPLOY,
					AuditTypeInterface::AUDIT_VHOST_ENABLE_DEPLOYMENT,
					AuditTypeInterface::AUDIT_VHOST_DISABLE_DEPLOYMENT,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_DEVELOPER => array(
						AuditTypeInterface::AUDIT_DEVELOPER_TOKEN_ADD,
						AuditTypeInterface::AUDIT_DEVELOPER_TOKEN_REMOVE,
						AuditTypeInterface::AUDIT_DEVELOPER_TOKEN_EXPIRE,
						AuditTypeInterface::AUDIT_DEVBAR_ACCESS_ELEVATE,
						AuditTypeInterface::AUDIT_DEVBAR_ACCESS_DEMOTE,
				),
				AuditTypeGroupsInterface::AUDIT_GROUP_UrlInsight => array(
					AuditTypeInterface::AUDIT_UrlInsight_RULE_ADD,
					AuditTypeInterface::AUDIT_UrlInsight_RULE_REMOVE,
				),
        	    AuditTypeGroupsInterface::AUDIT_GROUP_ZRAY => array(
            	    AuditTypeInterface::AUDIT_ZRAY_DELETE,
        	    ),
			);
	
	protected $auditRequestInterfaces = array(
		0 => self::AUDIT_REQUEST_INTERFACE_UI,
		1 => self::AUDIT_REQUEST_INTERFACE_WEBAPI,
		2 => self::AUDIT_REQUEST_INTERFACE_FILE_SYSTEM,
		3 => self::AUDIT_REQUEST_INTERFACE_DEVBAR,
	);

	/**
	 * @var \Audit\Db\ProgressMapper
	 */
	protected $progressMapper;	
	
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findAllAuditMessages() {
		return $this->select();
	}

	/**
	 * @return \ZendServer\Set
	 */
	public function findAuditMessage($auditId) {
		return $this->select(array('AUDIT_ID'=>$auditId));
	}
		
	/**
	 * @param array $filters
	 * @return \ZendServer\Set
	 */
	public function findAuditMessagesFiltered($filters = array()) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->join('ZSD_AUDIT_PROGRESS', 'ZSD_AUDIT_PROGRESS.AUDIT_ID = ZSD_AUDIT.AUDIT_ID', array('PROGRESS'));
		$select->group('ZSD_AUDIT.AUDIT_ID');
		$filters = $this->getFilters($filters); /* @var $filters Where */
		$filters->isNotNull('PROGRESS');
		$select->where($filters);
		$select->order(array($this->getOrderByField('AUDIT_ID') => 'DESC'));
		return $this->selectWith($select);
	}
	
	/**
	 * @param integer $limit
	 * @param integer $offset
	 * @param string $order
	 * @param string $direction
	 * @param array $filters
	 * @return \ZendServer\Set
	 */	
	public function findAuditMessagesPaged($limit, $offset, $order='', $direction='ASC', $filters = array()) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$filters = $this->getFilters($filters);
		$select->join("ZSD_AUDIT_PROGRESS", "ZSD_AUDIT.AUDIT_ID = ZSD_AUDIT_PROGRESS.AUDIT_ID", array ("PROGRESS"), Select::JOIN_LEFT  );
		$select->where($filters);
		$select->limit(intval($limit));
		$select->offset(intval($offset));
		$select->group('ZSD_AUDIT.AUDIT_ID');
		$select->order(array('ZSD_AUDIT.' . $this->getOrderByField($order) => $direction));
		return $this->selectWith($select);
		
	}
	/**
	 * @param array $filters
	 * @return number
	 */
	public function countAuditMessages($filters = array()) {
	    $table = $this->getTableGateway()->getTable();
	    $select = new Select($table);
	    $filters = $this->getFilters($filters);
	    return $this->count($this->defaultField, $filters);
	}
		
	/**
	 * @param Container $message
	 * @return number
	 */
	public function addAuditMessage(Container $message) {
		$messageRecord = $message->toArray();
		$messageRecord['auditType'] = array_search($messageRecord['auditType'], $this->auditTypes);
		$messageRecord['requestInterface'] = array_search($messageRecord['requestInterface'], $this->auditRequestInterfaces);
		$messageRecord['extraData'] = Json::encode($messageRecord['extraData']);
		unset($messageRecord['progress']);
		unset($messageRecord['outcome']);
		
		Log::info("t:{$messageRecord['auditType']}, e:{$messageRecord['extraData']}");
		
		$this->getTableGateway()->insert(array_combine($this->tableColumns, $messageRecord));
		$auditId = $this->getTableGateway()->getLastInsertValue();
		return $auditId;
	}		

	/**
	 * @return \Audit\Db\ProgressMapper
	 */
	public function getProgressMapper() {
		return $this->progressMapper;
	}
	
	/**
	 * @param \Audit\Db\ProgressMapper $progressMapper
	 * @return \Audit\Db\Mapper
	 */
	public function setProgressMapper($progressMapper) {
		$this->progressMapper = $progressMapper;
		return $this;
	}
	
	/**
	 *
	 * @param \Zend\Db\ResultSet\ResultSet $resultSet
	 * @return Array
	 */
	protected function resultSetToArray($resultSet) {
		$responseData = $resultSet->toArray();
		$responseData = $this->addProgressData($responseData);
		
		foreach($responseData as $idx=>&$auditMessage) {
			if (isset($auditMessage['AUDIT_TYPE']) && is_numeric($auditMessage['AUDIT_TYPE'])) {
				$auditMessage['AUDIT_TYPE'] = $this->auditTypes[$auditMessage['AUDIT_TYPE']];
			}
			if (isset($auditMessage['REQUEST_INTERFACE']) && is_numeric($auditMessage['REQUEST_INTERFACE'])) {
				$auditMessage['REQUEST_INTERFACE'] = $this->getRequestInterfaceString($auditMessage['REQUEST_INTERFACE']);
			}
			if (isset($auditMessage['EXTRA_DATA']) && is_numeric($auditMessage['EXTRA_DATA'])) {
				$auditMessage['EXTRA_DATA'] = Json::decode($auditMessage['EXTRA_DATA']);
			}
		}
	
		return $responseData;
	}
	
	protected function addProgressData(array $auditData) {		
		$auditIds = array();
		$auditDataWithKeys = array();
		foreach ($auditData as $auditMessage) {
			$auditIds[] = $auditMessage['AUDIT_ID'];
			$auditDataWithKeys[$auditMessage['AUDIT_ID']] = $auditMessage;
		}
		
		if (!$auditIds) {
			return $auditIds;
		}
		
		$progressDataWithKeys = $this->getProgressMapper()->findMessagesProgressData($auditIds);
		
		foreach ($progressDataWithKeys as $auditId => $progressData) {
			$auditDataWithKeys[$auditId]['PROGRESS'] = $progressData;
		}

		return array_values($auditDataWithKeys); // back to numeric keys
	}
	
	/**
	 * 
	 * @param integer $requestInterface
	 * @throws \ZendServer\Exception
	 * @return string
	 */
	protected function getRequestInterfaceString($requestInterface) {
		if (isset($this->auditRequestInterfaces[$requestInterface])) {
			return $this->auditRequestInterfaces[$requestInterface];
		}
	
		throw new \ZendServer\Exception("Unkown audit request interface {$requestInterface}");
	}	
	
	protected function getOrderByField($orderByField='') {		
		if ($orderByField) {
			return strtoupper($orderByField);
		}
		
		return $this->defaultField;
	}
	
	/**
	 * 
	 * @param array $filters
	 * @return \Zend\Db\Sql\Where
	 */
	protected function getFilters(array $filters = array()) {
	    $select = new Select();
	    if (isset($filters['from'])) {
	        $select->where(array('ZSD_AUDIT.CREATION_TIME >= ?' => $filters['from']));
	    }
	    if (isset($filters['to'])) {
	        $select->where(array('ZSD_AUDIT.CREATION_TIME <= ?' => $filters['to']));
	    }
	    if (isset($filters['auditGroups']) && is_array($filters['auditGroups'])) {
	        $auditTypeTranslated = $this->getAuditTypesTranslated($filters['auditGroups']);
	        if ($auditTypeTranslated) {
	        	$select->where('AUDIT_TYPE IN (' . implode(',', $auditTypeTranslated) . ')');
	        }
	    }
	   
	    if (isset($filters['freeText']) && $filters['freeText']) {
	         // Search also in auditType strings
	         $auditDictionary = new Dictionary();
	         $auditTypeStrings = $auditDictionary->getAuditTypeStrings();
	         $auditTypes = array();
	         foreach ($auditTypeStrings as $key => $value) {
	             if (stripos($value, $filters['freeText']) !== false) {
	             	 $auditTypes[] = array_search($key, $this->auditTypes);
	             }
	         }
			$select->where
					->nest()
					->like('ZSD_AUDIT.EXTRA_DATA',"%{$filters['freeText']}%")
					->or
					->like('USERNAME',"%{$filters['freeText']}%")
					->unnest();
	         if ($auditTypes) {
	         	$select->where(array('AUDIT_TYPE IN (' . implode(',', $auditTypes) . ')'), PredicateSet::OP_OR);
	         }
	    }

	    
	    if (isset($filters['outcome']) && is_array($filters['outcome'])) {
	    	$select->where($this->getOutcomeTranslated($filters['outcome'][0]));
	    }
	    
	    return $select->where;
	}
	
	protected function getOutcomeTranslated($key) {
		
		// fix the progress key: take both started and requested, no success/failed
		if ($key == 0) {
			return (array('not exists(select ZSD_AUDIT.audit_id from ZSD_AUDIT_PROGRESS where ZSD_AUDIT_PROGRESS.audit_id = ZSD_AUDIT.audit_id AND (progress = 2 OR progress = 3))'));
		} elseif ($key == 3) { // failed
			return (array('exists(select ZSD_AUDIT.audit_id from ZSD_AUDIT_PROGRESS where ZSD_AUDIT_PROGRESS.audit_id = ZSD_AUDIT.audit_id AND progress = 3)'));
		} elseif($key == 2) { // success
			return (array(	'not exists(select ZSD_AUDIT.audit_id from ZSD_AUDIT_PROGRESS where ZSD_AUDIT_PROGRESS.audit_id = ZSD_AUDIT.audit_id AND progress = 3)',
							'exists(select ZSD_AUDIT.audit_id from ZSD_AUDIT_PROGRESS where ZSD_AUDIT_PROGRESS.audit_id = ZSD_AUDIT.audit_id AND progress = 2)'));
		} else {
			throw new Exception('Unknown outcome constant');
		}
		 
	}
	/**
	 * @param array $auditGroups
	 * @return array
	 */
	protected function getAuditTypesTranslated(array $auditGroups) {
	    $auditTypes = array();
	    foreach ($auditGroups as $auditGroup) {
	    	if (isset($this->auditGroups[$auditGroup])) {
	    		$auditGroupTypes = $this->auditGroups[$auditGroup];
	    		foreach ($auditGroupTypes as $auditTypeConstant) {
			        $key = array_search($auditTypeConstant, $this->auditTypes);
			        if ($key !== false) {
			            $auditTypes[] = $key;
			        }
	    		}
	    	}
	    }
	    return $auditTypes;
	}
}
