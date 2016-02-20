<?php

namespace Audit\Controller\Plugin;
use ZendServer\Exception;

use ZendServer\Edition;

use ZendServer\Log\Log;

use Audit\Db\Mapper;

use Application\Module;

use Audit\ProgressContainer;

use Zend\Json\Json;

use Audit\Container;
use Servers;
use Audit\Db\ProgressMapper;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Application\Db\DirectivesFileConnector;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\Connector;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractController;

class AuditMessageProgress extends AbstractPlugin implements InjectApplicationEventInterface
{
	/**
	 * @var \Audit\Db\ProgressMapper
	 */
	private $auditProgressMapper;
	/**
	 * @var \Servers\Configuration\Mapper
	 */
	private $serversMapper;
	/**
	 * @var \Servers\Db\Mapper
	 */
	private $serversDbMapper;
	
	/**
	 * @var MvcEvent
	 */
	private $event;
	
	public function __invoke($progress = null, $extraData = array(), $overrideAuditId = null) {
		
		// don't create audit messages for ZRay standalone
		if (isZrayStandaloneEnv()) {
			return $this;
		}
		
		if (is_null($progress)) {
			return $this;
		}
		
		$auditId = 0;
		if (is_null($overrideAuditId)) {
			if ($this->getController() instanceof AbstractController
				&& $this->getController()->auditMessage() instanceof AuditMessage
				&& $this->getController()->auditMessage()->getMessage() instanceof Container
			) {
				
				$auditId = $this->getController()->auditMessage()->getMessage()->getAuditId();
			} else {
				return new ProgressContainer();
			}
		} else {
			$auditId = $overrideAuditId;
		}

		try {
			try {
				$nodeId = $this->serversMapper->getServerNodeId();
				$serverData = $this->serversDbMapper->findServerById($nodeId)->toArray();
				$server = new Servers\Container($serverData, $nodeId);
			} catch (Exception $e) {
				$edition = new Edition();
				if ($edition->isClusterManager()) {
					$server = new Servers\Container(array(
								'NODE_ID' => 0,
								'NODE_IP' => $this->getController()->getRequest()->getServer('REMOTE_ADDR'),
								'NODE_NAME' => _t('Cluster Manager'),
							), 0);
				} else {
					throw $e;
				}
			}
			
			$progressMessage = new ProgressContainer();
			$progressMessage->setAuditId($auditId);
			$progressMessage->setProgress($progress);
			$progressMessage->setNodeId($server->getNodeId());
			$progressMessage->setNodeIp($server->getNodeIp());
			$progressMessage->setNodeName($server->getNodeName());
			$progressMessage->setCreationTime(time());
			$progressMessage->setExtraData($extraData);
			$this->auditProgressMapper->addAuditProgress($progressMessage);
			
		} catch (\Exception $e) {
			Log::warn("Failed writing progress entry for auditId {$auditId}, {$progress}");
			Log::debug($e);
			return new ProgressContainer();
		}
		
		return $progressMessage;
	}
	
	/**
	 * @return \Audit\Db\ProgressMapper
	 */
	public function getAuditProgressMapper() {
		return $this->auditProgressMapper;
	}

	/**
	 * @return \Servers\Db\Mapper
	 */
	public function getServersDbMapper() {
		return $this->serversDbMapper;
	}

	/**
	 * @param \Servers\Mapper $serversMapper
	 * @return AuditMessage
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
		return $this;
	}
	
	/**
	 * @param \Servers\Db\Mapper $serversDbMapper
	 * @return AuditMessage
	 */
	public function setServersDbMapper($serversDbMapper) {
		$this->serversDbMapper = $serversDbMapper;
		return $this;
	}
	/**
	 * @param \Audit\Db\ProgressMapper $auditProgressMapper
	 * @return AuditMessage
	 */
	public function setAuditProgressMapper($auditProgressMapper) {
		$this->auditProgressMapper = $auditProgressMapper;
		return $this;
	}
	/* (non-PHPdoc)
	 * @see \Zend\Mvc\InjectApplicationEventInterface::setEvent()
	 */
	public function setEvent(\Zend\EventManager\EventInterface $event) {
		$this->event = $event;
		return $this;
	}

	/* (non-PHPdoc)
	 * @see \Zend\Mvc\InjectApplicationEventInterface::getEvent()
	 */
	public function getEvent() {
		if (is_null($this->event)) {
			$this->event = $this->getController()->getEvent();
		}
		return $this->event;
	}





}