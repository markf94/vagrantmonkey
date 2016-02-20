<?php

namespace Audit\Controller\Plugin;
use Users\Identity;

use Users\IdentityAwareInterface;

use Zend\EventManager\EventInterface;

use Zend\Mvc\InjectApplicationEventInterface;

use Zend\Mvc\ApplicationInterface;

use ZendServer\Log\Log;
use ZendServer\Exception;

use Audit\Db\Mapper;
use Audit\Db\ProgressMapper;

use Application\Module;

use Audit\ProgressContainer;

use Zend\Json\Json;

use Audit\Container;
use Servers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Audit\ExtraData\ParserInterface;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\DirectivesFileConnector;
use Application\Db\Connector;

class AuditMessage extends AbstractPlugin implements InjectApplicationEventInterface, IdentityAwareInterface
{
	/**
	 * @var \Audit\Db\Mapper
	 */
	private $auditMapper;
	/**
	 * @var \Zend\Authentication\AuthenticationService
	 */
	private $authService;
	
	/**
	 * @var \Users\Identity
	 */
	private $identity;
	
	/**
	 * @var string
	 */
	private $remoteAddr;
	
	/**
	 * @var \Zend\EventManager\Event
	 */
	private $event;
	
	/**
	 * @var Container
	 */
	private $message;
	
	/**
	 * @var boolean
	 */
	private $leap = false;
	
	/**
	 * @param string $type
	 * @param string $progress
	 * @param array $extraData
	 * @param string $baseUrl
	 * @return AuditMessage|Audit\Container
	 */
	public function __invoke($type = null, $progress = ProgressMapper::AUDIT_NO_PROGRESS, $extraData = array(), $baseUrl = '') {
		
		// don't create audit messages for ZRay standalone
		if (isZrayStandaloneEnv()) {
			return $this;
		}
		
		if (is_null($type)) {
			return $this;
		}
		
		if (! is_null($this->message)) {
			return $this->message;
		}
		
		try {
			$identity = $this->getIdentity();
			$remoteAddr = $this->getRemoteAddr();
	
			$event = $this->getEvent(); /* @var $event \Zend\Mvc\MvcEvent */
			
			$requestInterface = (!$event->getParam('webapi_ui')) && $event->getParam('webapi') ? Mapper::AUDIT_REQUEST_INTERFACE_WEBAPI : Mapper::AUDIT_REQUEST_INTERFACE_UI;
			if ($event->getParam('devbar', false)) {
				$requestInterface = Mapper::AUDIT_REQUEST_INTERFACE_DEVBAR;
			}
			
			if ($extraData instanceof ParserInterface) {
				$extraData = $extraData->toArray();
			}
			
			$message = new Container(array(
				'AUDIT_ID' => null,
				'USERNAME' => $identity->getUsername(),
				'REQUEST_INTERFACE' => $requestInterface,
				'REMOTE_ADDR' => $remoteAddr,
				'AUDIT_TYPE' => $type,
				'BASE_URL' => $baseUrl,
				'CREATION_TIME' => time(),
				'EXTRA_DATA' => $extraData,
			));
			
			if (! $this->auditMapper instanceof \Audit\Db\Mapper) {
				throw new Exception("auditMapper not available. bootstrap phaze?");
			}
			
			$auditId = $this->auditMapper->addAuditMessage($message);
			$message->setAuditId($auditId);
			if (! $event->getParam('auditId', false)) {
				/// store away if no auditId is assigned to this event yet
				$event->setParam('auditId', $auditId);
			} elseif (! in_array($progress, array(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY))) {
				/// Audit entry was already created but progress state that was passed is not final
				Log::warn('Duplicate audit entry must be created with a final progress state');
			}
			
		} catch (\Exception $e) {
			Log::err("Failed writing audit entry for type {$type}, {$progress}");
			Log::debug($e);
			return new Container(array());
		}

		if ($progress != ProgressMapper::AUDIT_NO_PROGRESS) {
			$plugin = $this->isLeap() ? 'AuditMessageProgressClusterLeap' : 'AuditMessageProgress';
			$this->getController()->$plugin($progress, $extraData, $auditId);
		}
		
		$this->message = $message;
		return $message;
	}
	
	/**
	 * @param \Audit\Db\Mapper $auditMapper
	 * @return AuditMessage
	 */
	public function setAuditMapper(\Audit\Db\Mapper $auditMapper) {
		$this->auditMapper = $auditMapper;
		return $this;
	}
	
	/**
	 * @param Identity $identity
	 * @return AuditMessage
	 */
	public function setIdentity(Identity $identity) {
		$this->identity = $identity;
		return $this;
	}

	/**
	 * @param string $remoteAddr
	 * @return AuditMessage
	 */
	public function setRemoteAddr($remoteAddr) {
		$this->remoteAddr = $remoteAddr;
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

	/* (non-PHPdoc)
	 * @see \Zend\Mvc\InjectApplicationEventInterface::setEvent()
	 */
	public function setEvent(EventInterface $event) {
		$this->event = $event;
		return $this;
	}
	
	/**
	 * @return \User\Identity
	 */
	private function getIdentity() {
		return $this->identity;
	}
	/**
	 * @return string $remoteAddr
	 */
	private function getRemoteAddr() {
		if(is_null($this->remoteAddr)) {
			$serverParams = $this->getController()->getRequest()->getServer();
			$this->remoteAddr = $serverParams['REMOTE_ADDR'];
		}
		return $this->remoteAddr;
	}
	/**
	 * @return \Audit\Db\Mapper
	 */
	public function getAuditMapper() {
		return $this->auditMapper;
	}
	/**
	 * @return Container
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @return boolean
	 */
	public function isLeap() {
		return $this->leap;
	}

	/**
	 * @param boolean $leap
	 */
	public function setLeap($leap = true) {
		$this->leap = $leap;
		if ($leap) {
			$this->message = null;
		}
	}
	/**
	 * @param \Audit\Container $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}



}
