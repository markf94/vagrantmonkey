<?php

namespace Vhost\Mapper;

use Vhost\Entity\Vhost as VhostEntity;
use Configuration\MapperReplies;
use Servers\Db\Mapper as ServersMapper;
use ZendServer;
use Vhost\Reply\VhostOperationContainer;
use ZendServer\Log\Log;


abstract class AbstractVhostAction {
	/**
	 * @var ServersMapper
	 */
	private $serversMapper;
	/**
	 * @var Vhost
	 */
	private $vhostMapper;
	/**
	 * @var MapperReplies
	 */
	private $repliesMapper;
	/**
	 * @var string
	 */
	private $vhostName;
	/**
	 * @var integer
	 */
	private $port;
	/**
	 * @var string
	 */
	private $template;
	/**
	 * @var boolean
	 */
	private $ssl = false;
	
	/**
	 * @var string
	 */
	private $sslCertificateChainPath;
	/**
	 * @var string
	 */
	private $sslCertificateKeyPath;
	/**
	 * @var string
	 */
	private $sslCertificatePath;
	/**
	 * @var string
	 */
	private $sslAppName;

	/**
	 * @var boolean
	 */
	private $forceCreate;
	
	/**
	 * @throws Exception|ZendServer\Exception
	 * @return VhostEntity
	 */
	public function setVhost() {
		$this->checkServersParticipation();
		$vhostMapper = $this->getVhostMapper();
		$taskId = $this->modifyVhost();
	
		$reply = $this->getRepliesMapper()->waitAndExtractReply($taskId);
		if (! $reply->isSuccess()) {
			if ($reply->getSuccessCode() == VhostOperationContainer::REPLY_ERROR) {
				throw new Exception($reply->getMessage(), Exception::APACHE_CONFIGURATION_INVALID);
			}
			if ($reply->getSuccessCode() == VhostOperationContainer::REPLY_FILE_NOT_FOUND) {
				throw new Exception(_t('Required file was not found: %s', array($reply->getMessage())), Exception::APACHE_CONFIGURATION_INVALID);
			}
			throw new Exception(_t('Name and port changes are not allowed'), Exception::VHOST_OPERATION_FAILED);
		}
	
		$vhostsResult = $this->postOperationCheck();
		
		return $vhostsResult;
	}
	
	/**
	 * @return boolean
	 * @throws Exception
	 */
	protected function postOperationCheck() {
		/// retrieves the latest vhost ... not exactly atomic but reply does not contain the created vhostid
		$vhostsResult = $this->getVhostMapper()->getNewVhost();
		if (is_null($vhostsResult)) {
			throw new Exception(_t("Post operation check failed, the virtual host was not set"), Exception::VHOST_OPERATION_FAILED);
		}
		return $vhostsResult;
	}
	
	/**
	 * @return Vhost
	 */
	public function getVhostMapper() {
		return $this->vhostMapper;
	}
	
	/**
	 * @return string
	 */
	public function getVhostName() {
		return $this->vhostName;
	}
	
	/**
	 * @return integer
	 */
	public function getPort() {
		if (is_null($this->port)) {
			$this->setPort($this->getVhostMapper()->getDefaultServerVhost()->getPort());
		}
		return intval($this->port);
	}
	
	/**
	 * @return string
	 */
	public function getTemplate() {
		if (is_null($this->template)) {
			if ($this->getSsl()) {
				$this->setTemplate($this->getVhostMapper()->getSSLSchemaContent());
			} else {
				$this->setTemplate($this->getVhostMapper()->getSchemaContent());
			}
		}
		return $this->template;
	}
	
	/**
	 * @return MapperReplies
	 */
	public function getRepliesMapper() {
		return $this->repliesMapper;
	}
	
	/**
	 * @return ServersMapper
	 */
	public function getServersMapper() {
		return $this->serversMapper;
	}
	
	/**
	 * @return the $ssl
	 */
	public function getSsl() {
		return $this->ssl;
	}

	/**
	 * @return the $sslCertificateChainPath
	 */
	public function getSslCertificateChainPath() {
		return $this->sslCertificateChainPath;
	}
	
	/**
	 * @return the $sslCertificateKeyPath
	 */
	public function getSslCertificateKeyPath() {
		return $this->sslCertificateKeyPath;
	}

	/**
	 * @return the $sslCertificatePath
	 */
	public function getSslCertificatePath() {
		return $this->sslCertificatePath;
	}
	
	/**
	 * @return the $sslAppName
	 */
	public function getSslAppName() {
		return $this->sslAppName;
	}

	/**
	 * @return boolean
	 */
	public function isForceCreate() {
		return $this->forceCreate;
	}
	
	/**
	 * @param string $sslCertificateKeyPath
	 */
	public function setSslCertificateChainPath($sslCertificateChainPath) {
		$this->sslCertificateChainPath = $sslCertificateChainPath;
	}
	
	/**
	 * @param string $sslAppName
	 */
	public function setSslAppName($sslAppName) {
		$this->sslAppName = $sslAppName;
	}
	
	/**
	 * @param string $sslCertificateKeyPath
	 */
	public function setSslCertificateKeyPath($sslCertificateKeyPath) {
		$this->sslCertificateKeyPath = $sslCertificateKeyPath;
	}

	/**
	 * @param string $sslCertificatePath
	 */
	public function setSslCertificatePath($sslCertificatePath) {
		$this->sslCertificatePath = $sslCertificatePath;
	}

	/**
	 * @param boolean $ssl
	 */
	public function setSsl($ssl) {
		$this->ssl = $ssl;
	}

	/**
	 * @param ServersMapper $serversMapper
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
	}
	
	/**
	 * @param \Configuration\MapperReplies $repliesMapper
	 */
	public function setRepliesMapper($repliesMapper) {
		$this->repliesMapper = $repliesMapper;
	}
	
	/**
	 * @param string $vhostName
	 */
	public function setVhostName($vhostName) {
		$this->vhostName = $vhostName;
	}
	
	/**
	 * @param number $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}
	
	/**
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}
	
	/**
	 * @param \Vhost\Mapper\Vhost $vhostMapper
	 */
	public function setVhostMapper($vhostMapper) {
		$this->vhostMapper = $vhostMapper;
	}
	
	/**
	 * @param boolean $forceCreate
	 */
	public function setForceCreate($forceCreate) {
		$this->forceCreate = $forceCreate;
	}
	/**
	 * @return number
	 */
	abstract protected function modifyVhost();
	
	private function checkServersParticipation() {
		if (0 == $this->getServersMapper()->findRespondingServers()->count()) {
			throw new ZendServer\Exception(_t('No responding servers found, or no ZSD available to handle this task'));
		}
	}
}

