<?php
namespace Vhost\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Vhost\VhostNodeContainer;
use Vhost\Entity\Vhost;
use Messages\Db\MessageMapper;

class VhostInfoXml extends AbstractHelper {
	
	/**
	 * @param Vhost $vhost
	 * @param VhostNodeContainer $vhostsNodes
	 * @return string
	 */
	public function __invoke(Vhost $vhost, array $vhostsNodes = array()) {
		$serversArray = array();
		$servers = '';
		if (isset($vhostsNodes[$vhost->getId()])) {
			$servers = $this->getServers($vhostsNodes[$vhost->getId()]);
		}
		
		$status = $this->convertStatus($this->getVhostStatus($vhost, $vhostsNodes));
		
		$zendDefined = ($vhost->isZendDefined()) ? 'true' : 'false';
		$zendManaged = ($vhost->isManagedByZend()) ? 'true' : 'false';
		$isDefault = ($vhost->isDefault()) ? 'true' : 'false';
		
		$fullName = $vhost->getName() . ':' . $vhost->getPort();
		$isSsl = $vhost->isSsl() ? 'true' : 'false';
		
		return <<<XML
	<vhostInfo>
		<id>{$vhost->getId()}</id>
		<name>{$vhost->getName()}</name>
		<port>{$vhost->getPort()}</port>
		<status>{$status}</status>
		<default>{$isDefault}</default>
		<zendDefined>{$zendDefined}</zendDefined>
		<zendManaged>{$zendManaged}</zendManaged>
		<ssl>{$isSsl}</ssl>
		<created>{$this->getView()->webapidate($vhost->getCreatedAt())}</created>
		<lastUpdated>{$this->getView()->webapidate($vhost->getLastUpdated())}</lastUpdated>
		<createdTimestamp>{$vhost->getCreatedAt()}</createdTimestamp>
		<lastUpdatedTimestamp>{$vhost->getLastUpdated()}</lastUpdatedTimestamp>
		<servers>{$servers}
		</servers>
	</vhostInfo>
XML;
	}
	
	/**
	 * @param integer $status
	 * @return string
	 */
	private function convertStatus($status) {
		$strings = array(
				Vhost::STATUS_OK 				=> 'Ok',
				Vhost::STATUS_ERROR 			=> 'Error',
				Vhost::STATUS_MODIFIED 			=> 'Modified',
				Vhost::STATUS_WARNING 			=> 'Warning',
				Vhost::STATUS_PENDING_RESTART	=> 'PendingRestart',
				Vhost::STATUS_CREATE_ERROR		=> 'CreateError',
				Vhost::STATUS_DEPLOYMENT_NOT_ENABLED => 'DeploymentNotEnabled',
				Vhost::STATUS_UNKNOWN			=> 'Ok'
		);
	
		return (isset($strings[$status])) ? $strings[$status] : 'Error';
	}
	
	/**
	 * @param Vhost $vhost
	 * @param VhostNode $vhostsNodes
	 * @return integer
	 */
	private function getVhostStatus($vhost, $vhostsNodes) {
		if (! isset($vhostsNodes[$vhost->getId()])) {
			return Vhost::STATUS_ERROR;
		}

		$status = array();
		foreach ($vhostsNodes[$vhost->getId()] as $server) { /* @var $server VhostNode */
			$status[$server->getStatus()] = $server->getStatus();
		}
	
		// have only one status - ok or error
		if (count($status) == 1) {
			return current($status);
		}
	
		// There is one member with status modified
		if (isset($status[Vhost::STATUS_MODIFIED])) {
			return Vhost::STATUS_MODIFIED;
		}
		
		// the vhost deployment should be enabled but its not
		if (isset($status[Vhost::STATUS_DEPLOYMENT_NOT_ENABLED])) {
			return Vhost::STATUS_DEPLOYMENT_NOT_ENABLED;
		}
		
		// have pending restart
		if (isset($status[Vhost::STATUS_PENDING_RESTART])) {
			return Vhost::STATUS_PENDING_RESTART;
		}
		
		// have mixed ok and error status
		return Vhost::STATUS_WARNING;
	}

	/**
	 * @param array $vhostsNodes
	 * @return string
	 */
	private function getServers(array $vhostsNodes = array()) {
		$servers = '';
		foreach ($vhostsNodes as $vhostsNode) {
			$servers .= PHP_EOL . $this->getServer($vhostsNode);
		}
		
		return $servers;
	}
	
	/**
	 * @param VhostNode $vhostsNode
	 * @return string
	 */
	private function getServer(VhostNodeContainer $vhostsNode) {
		$status = $this->convertStatus($vhostsNode->getStatus());
		
		return <<<XML
			<vhostServer>
				<id>{$vhostsNode->getNodeId()}</id>
  				<status>{$status}</status>
  				<name>{$vhostsNode->getName()}</name>
  				<lastMessage><![CDATA[{$this->lastMessage($vhostsNode->getLastMessage())}]]></lastMessage>
			</vhostServer>
XML;
	}
	
	/**
	 * @param string $lastMessage
	 * @return string
	 */
	private function lastMessage($lastMessage) {
		if (is_array($lastMessage) && isset($lastMessage['type'])) {
			$errorMessage = isset($lastMessage['details']['message']) ? $lastMessage['details']['message'] : '';
			switch ($lastMessage['type']) {
				case MessageMapper::TYPE_VHOST_ADDED:
					return _t('Vhost was not added to this server: %s', array($errorMessage));
					break;
				case MessageMapper::TYPE_VHOST_REDEPLOYED:
					return _t('Vhost was not redeployed to this server: %s', array($errorMessage));
					break;
				case MessageMapper::TYPE_VHOST_MODIFIED:
				case MessageMapper::TYPE_VHOST_REMOVED:
				case MessageMapper::TYPE_VHOST_WRONG_OWNER:
				default:
					return $lastMessage;
			}
		} else {
			return $lastMessage;
		}
	}
}