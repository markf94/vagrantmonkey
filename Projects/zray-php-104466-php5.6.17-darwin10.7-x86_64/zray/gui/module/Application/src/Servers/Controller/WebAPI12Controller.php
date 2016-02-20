<?php
namespace Servers\Controller;

use Audit\AuditTypeInterface,
	WebAPI\Mvc\View\Http\ExceptionStrategy,
	WebAPI\Exception,
	ZendServer\Mvc\Controller\WebAPIActionController,
	ZendServer\Log\Log,
	Zend\Stdlib\Parameters,
	Zend\View\Model\ViewModel,
	\Servers\Container,
	Servers\View\Helper\ServerStatus,
	WebAPI,
ZendServer\Set;
use Messages\Db\MessageMapper;
use Audit\Db\Mapper;
use Audit\Db\ProgressMapper;
use Notifications\NotificationContainer;

class WebAPI12Controller extends WebAPIActionController
{
	
	/**
	 * Restart PHP on all servers or on specified servers in the cluster.
	 * A 202 response in this case does not always indicate a
	 * successful restart of all servers, and the user is advised to check the server(s) status again
	 * after a few seconds using the clusterGetServerStatus command.
	 *
	 *
	 * @throws WebAPI\Exception
	 */
	public function restartPhpAction() {
		$this->getRequest()->getPost()->set('force', 'TRUE');
		return $this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'restartPhp'));
	}
	
	public function clusterAddServerAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('serverName', 'serverUrl', 'guiPassword'));
		
		$this->validateString($params['serverName'], 'serverName');
		$this->validateString($params['serverUrl'], 'serverUrl');
		$this->validateString($params['guiPassword'], 'guiPassword');
		
		if ($this->getServersMapper()->isNodeNameExists($params['serverName'])) {
			throw new WebAPI\Exception(_t('This server name already exists in the cluster'), WebAPI\Exception::INVALID_SERVER_RESPONSE);
		}
		
		$uri = new \Zend\Uri\Uri($params['serverUrl']);
		
		$this->getRequest()->setPost(new Parameters(array('serverIp' => $uri->getHost(), 'serverName' => $params['serverName'])));
		$serverView = $this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'clusterAddServer')); /* @var $serverView \Zend\View\Model\ViewModel */
		
		$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
		$serverStatus = $serversMapper->findServerByName($params['serverName']);
		// waiting for maximum 1 min
		$maxSleepTime = 60; $sleepCounter = 0;
		while(($sleepCounter < $maxSleepTime) && (empty($serverStatus) || in_array($serverStatus->getStatusCode(), array (ServerStatus::STATUS_SERVER_RESTARTING, ServerStatus::STATUS_RESTART_REQUIRED)))) {
			sleep(1);
			$sleepCounter++;
			$serverStatus = $serversMapper->findServerByName($params['serverName']);
		}
		
		$serverView->setVariables(array('server' => $serverStatus));
		return $serverView;
	}
	
	public function clusterDisableServerAction() {
		$params = $this->getParameters();
		$this->isMethodPost();
		$this->validateMandatoryParameters($params, array('serverId'));
		$this->validateInteger($params['serverId'], 'serverId');
		$serversSet = $this->getServersMapper()->findServersById(array($params['serverId']));
		$serverData = $serversSet->current();
		$oldStatus = $serverData->getStatusCode();
		$serverView = $this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'clusterDisableServer')); /* @var $serverView \Zend\View\Model\ViewModel */
		sleep(1);
		$serverData = $this->getServersMapper()->findServersById(array($params['serverId']))->current();
		$serverView->setVariables(array('server' => $serverData));
		$serverView->setTemplate('servers/web-api/1x3/cluster-enable-server');
		return $serverView;
	}
	/*
	 * Disable the cluster member
	*/
	public function clusterEnableServerAction() {
		$params = $this->getParameters();
		$this->isMethodPost();
		$this->validateMandatoryParameters($params, array('serverId'));
		$this->validateInteger($params['serverId'], 'serverId');
		$serversSet = $this->getServersMapper()->findServersById(array($params['serverId']));
		$serverData = $serversSet->current();
		$oldStatus = $serverData->getStatusCode();
		$serverView = $this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'clusterEnableServer')); /* @var $serverView \Zend\View\Model\ViewModel */
		$serverData = $this->getServersMapper()->findServersById(array($params['serverId']))->current();
		while($serverData->getStatusCode() == $oldStatus && in_array($serverData->getStatusCode(), array (ServerStatus::STATUS_DISABLED, ServerStatus::STATUS_DISABLING_SERVER))) {
			sleep(1);
			$serverData = $this->getServersMapper()->findServersById(array($params['serverId']))->current();
		}		
		$serverView->setVariables(array('server' => $serverData));
		$serverView->setTemplate('servers/web-api/1x3/cluster-enable-server');
		return $serverView;
	}
		
	/**
	 * Remove a server from the cluster
	 */
	public function clusterRemoveServerAction() {
		$params = $this->getParameters(array('force' => 'false'));
		$this->isMethodPost();
		$this->validateMandatoryParameters($params, array('serverId'));
		$this->validateInteger($params['serverId'], 'serverId');
		$this->validateBoolean($params['force'], 'force');
		if (strtoupper($params['force']) == 'TRUE') {
			$serverView = $this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'clusterForceRemoveServer')); /* @var $serverView \Zend\View\Model\ViewModel */
		} else {
			$serverView = $this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'clusterRemoveServer')); /* @var $serverView \Zend\View\Model\ViewModel */
		}
		
		$serversSet = $this->getServersMapper()->findServersById(array($params['serverId']));
		$serverData = $serversSet->current();
		if ($serversSet->count() == 0) {
			$serverData = new Container(array(),'');
			$serverData->setNodeId($params['serverId']);
			$serverData->setStatusCode(ServerStatus::STATUS_NOT_EXIST);
		}
		$serverView->setVariables(array('server' => $serverData));
		$serverView->setTemplate('servers/web-api/1x3/cluster-enable-server');
		return $serverView;
	}
	
	public function clusterGetServerStatusAction() {
		$params = $this->getParameters(array('servers' => array(), 'order' => 'NODE_NAME', 'direction' => 'ASC'));
		
		$serversIds = $this->getServersIds($params['servers']);
	
		$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
	
		// no servers provided, retreive all servers
		if (count($serversIds) === 0) {
			$serversSet = $serversMapper->findRespondingServers(false, false, $params);
		} else { // get only required servers
			$serversSet = $serversMapper->findRespondingServersByIds($serversIds, false, false, $params);
		}
	
		$serversSet = $this->addMessageData($serversSet);
		
		if (strtoupper($params['order']) === 'STATUS_CODE') {
			$serversSet = $this->sortServersByStatus($serversSet, $params['direction'] === 'ASC');
		}
		
		return array('servers' => $serversSet);
	}
	
	protected function sortServersByStatus($serversSet, $sortAsc=true) {
		$serversArray = array();
		foreach ($serversSet as $server) {/* @var $server \Servers\Container */
			$serversArray[] = $server->toArray();
		}
		
		usort($serversArray, $this->usortStatus('STATUS_CODE', $sortAsc));
		
		return new Set($serversArray, 'Servers\Container');;
	}

	
	protected function usortStatus($key, $sortAsc) {
		return function ($a, $b) use ($key, $sortAsc) {
			if ($sortAsc) {
				return $a[$key] > $b[$key];
			} else {
				return $a[$key] < $b[$key];
			}
			
		};		
	}

	/**
	 *
	 * @param array $serversIds
	 * @return array
	 */
	protected function getServersIds(array $serversIds) {
		$serversIds = $this->validateServersIds($serversIds);
		if ($serversIds) {
			return $serversIds;
		}
			
		$servers = $this->getServersMapper()->findAllServers();
	
		return array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());
	}

	protected function addMessageData($servers) {
		$newServers = array();
		$messages = $this->getMessagesMapper()->findServersMessages(array_keys($servers->toArray()));
		$messagesPerServer = array();
		foreach ($messages as $message) { /* @var $message \Messages\MessageContainer */
			$serverId = $message->getMessageNodeId();
			$messagesPerServer[$serverId][] = $message;
		}
	
		foreach ($servers as $idx=>$server) { /* @var $server \Servers\Container */
			if ($server->isStatusError() && $this->hasMismatchMessages($messagesPerServer[$idx])) {
				$server->setStatusCode(ServerStatus::STATUS_WARNING); // node has error status, but there aren't any error messages (usually implies server is mismatched)
			}
				
			$newServers[] = $server->toArray() + array('MESSAGES' => isset($messagesPerServer[$idx]) ? $messagesPerServer[$idx] : array());
		}
	
		return new Set($newServers, '\Servers\Container');
	}

	protected function hasMismatchMessages($messages) {
		foreach($messages as $message) {/* @var $message \Messages\MessageContainer */
			if ($message->getMessageType() == MessageMapper::TYPE_MISSMATCH) {
				return true;
			}
		}
	
		return false;
	}
	
	protected function isServerWithErrorMessages($messages) {
		foreach($messages as $message) {/* @var $message \Messages\MessageContainer */
			if ($message->isError()) {
				return true;
			}
		}
	
		return false;
	}
	
	/**
	 *
	 * @param array $serversIds
	 * @throws WebAPI\Exception
	 */
	protected function validateServersIds($serversIds) {
		$serversIds = $this->validateArray($serversIds, 'servers');
		foreach($serversIds as $key => $serverId) {
			$this->validateInteger($serverId, "servers[$key]");
		}
	
		return $serversIds;
	}
}