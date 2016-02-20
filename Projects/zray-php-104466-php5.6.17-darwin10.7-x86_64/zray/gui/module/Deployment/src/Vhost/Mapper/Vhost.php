<?php

namespace Vhost\Mapper;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

use Application\Db\Adapter\AdapterAwareInterface;
use Application\Db\Connector;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;
use Vhost\Entity\Vhost as VhostEntity;
use Vhost\Entity\VhostNode as VhostNodeEntity;
use ZendServer\Set;
use ZendServer\FS\FS;
use Vhost\StdLib\Hydrator\VhostApplications;
use Zend\Db\ResultSet\ResultSet;
use Deployment\Model;
use ZendServer\Exception;
use Configuration\MapperDirectives;
use Zend\Uri\UriFactory;
use Servers\Db\ServersAwareInterface;
use Servers\Db\Mapper as ServersMapper;
use Messages\Db\MessageMapper;
use Messages\MessageContainer;
use Vhost\VhostNodeContainer;
use Zend\Db\Sql\Predicate\Predicate;
use ZendServer\Log\Log;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class Vhost implements AdapterAwareInterface, ServersAwareInterface {
	const VHOSTS_TABLE = 'ZSD_VHOSTS';
	const VHOSTS_NODES_TABLE = 'ZSD_VHOSTS_NODES';
	const VHOST_MAX_RESULTS = 500;
	
	/**
	 * @var Tasks
	 */
	private $vhostTasks;
	
	/**
	 * @var Adapter
	 */
	private $dbAdapter;
	
	/**
     * @var Mapper
     */
    private $serversMapper;

    /**
     * @var Model
     */
    private $deploymentMapper;
    
    /**
     * @var VhostApplications
     */
    private $vhostHydrator;
    
    /**
     * @var boolean
     */
    private $vhostsManaged = true;
    
    /**
     * @var MapperDirectives
     */
    private $directivesMapper;
    
    /**
     * @var MessageMapper
     */
    private $messagesMapper;
    
    /**
     * @param VhostEntity $vhost
     * @return number
     */
    public function vhostStatus(VhostEntity $vhost) {

    	$servers = $this->getFullVhostNodes(array($vhost->getId()));
    	
    	$status = array();
    	foreach ($servers[$vhost->getId()] as $server) { /* @var $server VhostNodeContainer */
    		$status[$server->getStatus()] = $server->getStatus();
    	}
    	
    	// have only one status - ok or error
    	if (count($status) == 1) {
    		return current($status);
    	}
    	
    	// There is one member with status modified
    	if (isset($status[VhostEntity::STATUS_MODIFIED])) {
    		return VhostEntity::STATUS_MODIFIED;
    	}
    	
    	// the vhost deployment should be enabled but its not
    	if (isset($status[VhostEntity::STATUS_DEPLOYMENT_NOT_ENABLED])) {
    		return VhostEntity::STATUS_DEPLOYMENT_NOT_ENABLED;
    	}
    	
    	// have pending restart
    	if (isset($status[VhostEntity::STATUS_PENDING_RESTART])) {
    		return VhostEntity::STATUS_PENDING_RESTART;
    	}
    	
    	// have create error
    	if (isset($status[VhostEntity::STATUS_CREATE_ERROR])) {
    		return VhostEntity::STATUS_CREATE_ERROR;
    	}
    	
    	// have mixed ok and error status
    	return VhostEntity::STATUS_WARNING;
    }
    
    /**
     * @param string $baseUrl
     * @throws \WebAPI\Exception
     * @return VhostEntity
     */
    public function createVhostFromURL($baseUrl) {
    	///disassemble $baseUrl to retrieve the vhost
    	$baseUrl = trim($baseUrl, '/');
    	$baseUrlUri = UriFactory::factory($baseUrl);
   		// use default template
   		$template = $this->getSchemaContent();
   		$host = strtolower($baseUrlUri->getHost());
   		$taskId = $this->insertVhost($host, $baseUrlUri->getPort(), $template);
   		$this->getVhostTasks()->getTasksMapper()->waitForTasksComplete(array(), array($taskId));
  		$vhostHost = "{$host}:{$baseUrlUri->getPort()}";
   		return $this->getVhostByName($vhostHost);
    }
    
    public function vhostFromURL($baseUrl) {
    	$baseUrl = trim($baseUrl, '/');
    	$baseUrlUri = UriFactory::factory($baseUrl);
    	$vhostHost = "{$baseUrlUri->getHost()}:{$baseUrlUri->getPort()}";
    	
    	$validator = new \Vhost\Validator\VhostValidForDeploy();
    	$validator->setVhostMapper($this);
    	if (! $validator->isValid($vhostHost)) {
    		throw new \WebAPI\Exception(_t('Requested Vhost is invalid: %s', array(current($validator->getMessages()))), \WebAPI\Exception::VIRTUAL_HOST_INVALID);
    	}
    	
    	return $this->getVhostByName($vhostHost);
    }
    
    /**
     * @throws Exception
     * @return VhostEntity
     */
    public function getDefaultServerVhost() {
    	$sql = new Sql($this->getDbAdapter());
    	$select = $sql->select(self::VHOSTS_TABLE);
   		$select->where(array('IS_DEFAULT' => 1));
   		
   		$statement = $sql->prepareStatementForSqlObject($select);
   		$result = $statement->execute();
   		
   		
   		if ($result instanceof ResultInterface && $result->isQueryResult()) {
	   		if (1 != $result->count()) {
	   			throw new Exception(_t('System has %s default vhosts, cannot determine which one is the default server', array($result->count())));
	   		}
   			$resultSet = new HydratingResultSet(new ReflectionHydrator(), new VhostEntity());
   			$resultSet->initialize($result);
   		
   			return $resultSet->current();
   		} else {
   			throw new Exception(_t('Cannot determine a default server: no vhosts found'));
   		}
    }
    
	/**
	 * @param integer $id
	 * @return VhostEntity
	 * @throws \Zend\Db\Exception
	 */
	public function getVhostById($id) {
		$resultSet = $this->getVhosts(array($id));
		if ($resultSet->count() == 1) {
			return $resultSet->current();
		}
		
		return new VhostEntity();
	}
	
	/**
	 * @param array $vhosts
	 * @param array $filter
	 * @return number
	 */
	public function countVhosts($filter = array()) {
		
		$sql = new Sql($this->getDbAdapter());
		$select = $sql->select(self::VHOSTS_TABLE);
		$select->columns(array('counter' => new Expression('COUNT(*)')));
		$select->where($this->parseFilters($filter));
		
		$stmt = $sql->prepareStatementForSqlObject($select);
		$result = $stmt->execute();
		$row = $result->current();
		return (integer)$row['counter'];
	}
	
	/**
	 * @param array $vhosts
	 * @return ResultSet
	 * @throws \Zend\Db\Exception
	 */
	public function getVhosts($vhosts = array(), $filter = array(), $limit = 0, $offset = 0, $order = null, $direction = null) {
		$sql = new Sql($this->getDbAdapter());
		$select = $sql->select(self::VHOSTS_TABLE);
	
		$limit = min($limit, self::VHOST_MAX_RESULTS);
		if ($limit > 0) {
			$select->limit(intval($limit));
			/// Use offset only if limit is provided
			if ($offset > 0) {
				$select->offset(intval($offset));
			}
		}
	
		if (! is_null($order)) {
			if (! is_null($direction)) {
				$select->order($order . ' ' . $direction);
			} else {
				$select->order($order);
			}
		}
		
		$where = $this->parseFilters($filter);
		$select->where($where);
		
		if (count($vhosts) > 0) {
			$select->where(array('id' => $vhosts));
		}
		
		$statement = $sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();
	
		if ($result instanceof ResultInterface && $result->isQueryResult()) {
			$resultSet = new HydratingResultSet($this->getVhostHydrator(), new VhostEntity());
			$resultSet->initialize($result);
			return $resultSet;
		}
	
		return new ResultSet();
	}

	/**
	 * @return \Vhost\Entity\Vhost
	 */
	public function getNewVhost() {
		$sql = new Sql($this->getDbAdapter());
		$select = $sql->select(self::VHOSTS_TABLE);
		$select->limit(1);
		$select->order('ID DESC');
		
		$statement = $sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();
		
		if ($result instanceof ResultInterface && $result->isQueryResult()) {
			$resultSet = new HydratingResultSet($this->getVhostHydrator(), new VhostEntity());
			$resultSet->initialize($result);
			
			return $resultSet->current();
		}
		
		return new VhostEntity();
	}
	
	/**
	 * @param string $vhostName
	 * @return vhostEntity
	 */
	public function getVhostByName($vhostName) {
		$vhost = $this->getVhostsByNames(array($vhostName))->current();
		return $vhost === false ? null : $vhost;
	}
	
	/**
	 * @return Set
	 */
	public function getVhostsByNames($names = array()) {
		$sql = new Sql($this->getDbAdapter());
		$select = $sql->select(self::VHOSTS_TABLE);

		$wherePredicates = array();
		foreach ($names as $name) {
			$vhostName = '';
			$vhostPort = '';
			
			$exploded = explode(':', $name);
			if (count($exploded) == 1) {
				$vhostName = $name;
			} else {
				$vhostPort = array_pop($exploded);
				$vhostName = implode(':', $exploded);
			}
			
			$wherePredicate = new Where();
			$wherePredicate->equalTo('NAME', $vhostName);
			if (! empty($vhostPort)) {
				$wherePredicate->equalTo('PORT', $vhostPort);
			}
			
			$wherePredicates[] = $wherePredicate;
		}
		
		$select->where($wherePredicates, 'OR');
		
		$statement = $sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();
		
		if ($result instanceof ResultInterface && $result->isQueryResult()) {
			$resultSet = new HydratingResultSet($this->getVhostHydrator(), new VhostEntity());
			$resultSet->initialize($result);
			return $resultSet;
		}
	
		return new ResultSet();
	}
	
	/**
	 * @param integer $vhostId
	 * @return ResultInterface
	 */
	public function getSingleVhostNodes($vhostId) {
		return $this->getVhostNodes(array($vhostId));
	}
	
	/**
	 * @param array $vhostsIds
	 * @return ResultInterface
	 */
	public function getVhostPorts() {
		$sql = new Sql($this->getDbAdapter());
		$select = $sql->select(self::VHOSTS_TABLE);
		$select->group('port');
		
		$statement = $sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();
	
		if ($result instanceof ResultInterface && $result->isQueryResult()) {
			$resultSet = new HydratingResultSet(new ReflectionHydrator(), new VhostEntity());
			$resultSet->initialize($result);
	
			return $resultSet;
		}
	
		return null;
	}
	
	/**
	 * @param array $vhostsIds
	 * @return ResultInterface
	 */
	public function getVhostNodes($vhostsIds = array()) {
		$sql = new Sql($this->getDbAdapter());
		$select = $sql->select(self::VHOSTS_NODES_TABLE);
		$select->join('ZSD_NODES', self::VHOSTS_NODES_TABLE . '.node_id = ZSD_NODES.node_id', array('NAME' => 'NODE_NAME'));
		if (count($vhostsIds) > 0) {
			$select->where(array('VHOST_ID' => $vhostsIds));
		}
		
		$statement = $sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();
		
		if ($result instanceof ResultInterface && $result->isQueryResult()) {
			$resultSet = new HydratingResultSet(new ReflectionHydrator(), new VhostNodeEntity());
			$resultSet->initialize($result);
				
			return $resultSet;
		}
		
		return null;
	}
	
	/**
	 * @param array $vhostsIds
	 * @return array
	 */
	public function getFullVhostNodes($vhostsIds) {
        $serversMapper = $this->getServersMapper();
		$servers = $serversMapper->findRespondingServers();
		$serversIds = array();
		foreach ($servers as $server) { /* @var $server \Servers\Container */
			$serversIds[$server->getNodeId()] = $server->getNodeName();
		}
	
		$vhostsNodes = array();
		foreach ($vhostsIds as $vhostsId) {
			$vhostsNodes[$vhostsId] = array();
		}
	
		$vhostsNodesResult = $this->getVhostNodes($vhostsIds);
		foreach ($vhostsNodesResult as $vhostsNode) {
			if (isset($serversIds[$vhostsNode->getNodeId()])) {
				$vhostsNodes[$vhostsNode->getVhostId()][] = new \Vhost\VhostNodeContainer($vhostsNode);
			}
		}
	
		// detect missing servers vhost nodes and put error vhost node istead
		foreach ($vhostsNodes as $vhostId => $vhostNodes) {
			$vhostsServers = array();
			foreach ($vhostNodes as $vhostNode) { /* @var $vhostNode \Vhost\VhostNodeContainer */
				$vhostsServers[$vhostNode->getNodeId()] = $vhostNode->getNodeId();
			}
			
			$diff = array_diff_key($serversIds, $vhostsServers);

			if (0 < count($diff)) {
				$messages = array();
				$messagesSet = $this->getMessagesMapper()->findAllVhostMessages(array_keys($diff));
				foreach ($messagesSet as $message) { /* @var $message MessageContainer */
					$messages[$message->getMessageNodeId()] = $message;
				}
				
				foreach ($diff as $diffId => $serverName) {
					$missingServer = $this->createNotExistsVhostNode($vhostId, $diffId);
					$missingServer->setName($serverName);
					if (isset($messages[$diffId])) {
						$serverMessage = $messages[$diffId];
						$severity = VhostNodeContainer::STATUS_ERROR;
						$missingServer->setStatus($severity);
						$missingServer->setLastMessage(array('type' => $serverMessage->getMessageType(), 'details' => $serverMessage->getMessageDetails()));
					}
					$vhostsNodes[$vhostId][] = $missingServer;
				}
			}
		}
	
		return $vhostsNodes;
	}
	
	/**
	 * Reads the schema SSL file content according to the webserver type
	 * @return string
	 */
	public function getSSLSchemaContent() {
		return $this->getSchemaFileContent('-ssl');
	}
	
	/**
	 * Reads the schema file content according to the webserver type
	 * @return string
	 */
	public function getSchemaContent() {
		return $this->getSchemaFileContent();
	}
	
	/**
	 * Reads the schema file content according to the webserver type
	 * @param \Configuration\MapperDirectives $directivesMapper
	 * @return string
	 */
	public function getManageTemplate() {
		// get the webserverType
		$webserverType = $this->getDirectivesMapper()->selectSpecificDirectives(array('zend.webserver_type'))->current()->getFileValue();
			
		// get vhost schema file according to webserver type
		$schemaFile = '';
		
		switch ($webserverType) {
			case 'nginx':
				$template = '
server {
&nbsp;&nbsp;&nbsp;listen 80;
&nbsp;&nbsp;&nbsp;root /var/www;
&nbsp;&nbsp;&nbsp;<%INCLUDE_LINE%>
}					
';
				break;
			default: // apache
				$template = '
&lt;VirtualHost *:80&gt;
&nbsp;&nbsp;&nbsp;DocumentRoot /var/www
&nbsp;&nbsp;&nbsp;...							
&nbsp;&nbsp;&nbsp;<%INCLUDE_LINE%>
&lt;/VirtualHost&gt;';
				break;
		}
			
		return $template;
	}
	
	/**
	 * @param integer $auditId
	 * @param integer $vhostId
	 * @param string $template
	 * @return number
	 */
	public function editVhost ($vhostId, $template, $certFile = '', $certKeyFile = '', $certChainFile = '', $sslAppName = '', $force = false) {
		$params = array(
			'vhostId' => $vhostId,
			'template' => $template, 
			'ssl_certificate' => (string)$certFile, 
			'ssl_certificate_key' => (string)$certKeyFile, 
			'ssl_certificate_chain' => (string)$certChainFile, 
			'ssl_app_name' => (string)$sslAppName, 
			'forceCreate' => (boolean)$force
		);
		return $this->getVhostTasks()->editVhostTask($params);
	}
	
	/**
	 * @param string $vhostName
	 * @param integer $port
	 * @param string $template
	 * @param boolean $ssl
	 * @param string $certFile
	 * @param string $certKeyFile
	 * @param boolean $force
	 * @return number
	 */
	public function insertVhost($vhostName, $port, $template, $ssl = false, $certFile = '', $certKeyFile = '', $certChainFile = '', $sslAppName = '', $force = false) {
		return $this->getVhostTasks()->addVhostTask(array('name' => $vhostName, 'port' => $port, 'template' => $template, 'ssl_support' => (boolean)$ssl, 'ssl_certificate' => (string)$certFile, 'ssl_certificate_key' => (string)$certKeyFile, 'ssl_certificate_chain' => (string)$certChainFile, 'ssl_app_name' => (string)$sslAppName, 'forceCreate' => (boolean)$force));
	}
	
	/**
	 * @param array $vhostIds
	 */
	public function removeVhosts(array $vhostIds) {
		$this->getVhostTasks()->removeVhostTask($vhostIds);
	}
	
	/**
	 * @param integer $vhostId
	 */
	public function redeployVhost($vhostId) {
		$this->getVhostTasks()->redeployVhostTask($vhostId);
	}
	
	/**
	 * @param integer $vhostId
	 */
	public function manageVhost($vhostId, $applyImmediately) {
		$this->getVhostTasks()->manageVhostTask($vhostId, $applyImmediately);
	}
	
	/**
	 * @param integer $vhostId
	 */
	public function unmanageVhost($vhostId) {
		$this->getVhostTasks()->unmanageVhostTask($vhostId);
	}
	
	/**
	 * @return Tasks
	 */
	public function getVhostTasks() {
		if (is_null($this->vhostTasks)) {
			throw new \ZendServer\Exception('vhostTasks class is not available');
		}
		return $this->vhostTasks;
	}

	/**
	 * @return Adapter
	 */
	public function getDbAdapter() {
		return $this->dbAdapter;
	}

	/**
	 * @return MessageMapper
	 */
	public function getMessagesMapper() {
		return $this->messagesMapper;
	}

	/**
	 * @param \Messages\Db\MessageMapper $messagesMapper
	 */
	public function setMessagesMapper($messagesMapper) {
		$this->messagesMapper = $messagesMapper;
	}

	/**
	 * @param \Vhost\Mapper\Tasks $vhostTasks
	 */
	public function setVhostTasks($vhostTasks) {
		$this->vhostTasks = $vhostTasks;
	}
	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\AdapterAwareInterface::setDbAdapter()
	 */
	public function setDbAdapter(\Zend\Db\Adapter\Adapter $adapter) {
		$this->dbAdapter = $adapter;
	}
	/* (non-PHPdoc)
	 * @see \Application\Db\Adapter\AdapterAwareInterface::getAdapterDb()
	 */
	public function getAdapterDb() {
		return Connector::DB_CONTEXT_ZSD;
	}

    /**
     * @param ServersMapper $serversMapper
     */
    public function setServersMapper($serversMapper)
    {
        $this->serversMapper = $serversMapper;
    }

    /**
     * @return ServersMapper
     */
    public function getServersMapper()
    {
        return $this->serversMapper;
	}

	/**
	 * @return VhostApplications
	 */
	public function getVhostHydrator() {
		if (is_null($this->vhostHydrator)) {
			$this->vhostHydrator = new VhostApplications();
			$this->vhostHydrator->setDeploymentMapper($this->getDeploymentMapper());
		}
		return $this->vhostHydrator;
	}
	
	/**
	 * @return Model
	 */
	public function getDeploymentMapper() {
		return $this->deploymentMapper;
	}
	
	/**
	 * @param \Deployment\Model $deploymentMapper
	 */
	public function setDeploymentMapper($deploymentMapper) {
		$this->deploymentMapper = $deploymentMapper;
	}
	
	/**
	 * @param integer $vhostId
	 * @param integer $nodeId
	 * @return \Vhost\VhostNodeContainer
	 */
	private function createNotExistsVhostNode($vhostId, $nodeId) {
		$vhostNode = new \Vhost\VhostNodeContainer(new \Vhost\Entity\VhostNode());
		$vhostNode->setStatus(\Vhost\VhostNodeContainer::STATUS_ERROR)
		->setId(0)
		->setVhostId($vhostId)
		->setNodeId($nodeId)
		->setLastMessage(_t('Vhost does not exist on the server')); 
	
		return $vhostNode;
	}
	/**
	 * @return boolean
	 */
	public function isVhostsManaged() {
		return $this->vhostsManaged;
	}

	/**
	 * @param boolean $vhostsManaged
	 */
	public function setVhostsManaged($vhostsManaged) {
		$this->vhostsManaged = $vhostsManaged;
	}
	
	/**
	 * @return MapperDirectives
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}

	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
	}


	/**
	 * @param string $suffix
	 * @return string
	 */
	private function getSchemaFileContent($suffix = '') {
		// get the webserverType
		$webserverType = $this->getDirectivesMapper()->selectSpecificDirectives(array('zend.webserver_type'))->current()->getFileValue();
			
		// get vhost schema file according to webserver type
		$schemaFile = '';
	
		switch ($webserverType) {
			case 'nginx':
				$schemaFile = "vhost-nginx{$suffix}.tpl";
				break;
			default: // apache
				$schemaFile = "vhost{$suffix}.tpl";
				break;
		}
			
		// read schema file content
		$installDir = getCfgVar('zend.install_dir');
		$schemaPath = FS::createPath($installDir, 'share', $schemaFile);
		$schemaFileObj = FS::getFileObject($schemaPath);
		return $schemaFileObj->readAll();
	}

	/**
	 * @return array
	 */
	public function getSortColumnsDictionary() {
		return array(
				'id',
				'name',
				'port',
				'last_updated',
				'owner',
		);
	}

	/**
	 * @param array $filter
	 * @param Where $where
	 * @return Where
	 */
	private function parseFilters(array $filter, Where $where = null) {
		if (is_null($where)) {
			$where = new Where();
		}
	
		if (isset($filter['ssl'])) {
			$where->equalTo('is_ssl', $filter['ssl'][0]);
		}
		
		if (isset($filter['type'])) {
			$where->equalTo('owner', $filter['type'][0]);
		}
		
		if (isset($filter['deployment'])) {
			$where->in('owner', $filter['deployment'][0]);
		}
		
		if (isset($filter['port'])) {
			$where->in('port', $filter['port']);
		}
		
		if (isset($filter['freeText']) && $filter['freeText']) {
			$freeText = $filter['freeText'];
			$predicate = new Predicate(null, Predicate::OP_OR);
			$predicate->like('name', "%$freeText%");
			$where->addPredicate($predicate);
		}
		
		return $where;
	}

}