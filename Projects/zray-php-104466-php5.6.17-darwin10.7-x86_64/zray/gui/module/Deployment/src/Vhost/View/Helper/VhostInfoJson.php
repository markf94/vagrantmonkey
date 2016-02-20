<?php
namespace Vhost\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Vhost\VhostNodeContainer;
use Vhost\Entity\Vhost;
use Messages\Db\MessageMapper;

class VhostInfoJson extends AbstractHelper {

	/**
	 * @param Vhost $vhost
	 * @param array $vhostsNodes
	 * @return string
	 */
	public function __invoke(Vhost $vhost, array $vhostsNodes = array()) {
		$servers = array();
		if (isset($vhostsNodes[$vhost->getId()])) {
			$servers = $this->getServers($vhostsNodes[$vhost->getId()]);
		}
		
		$status = $this->convertStatus($this->getVhostStatus($vhost, $vhostsNodes));
		
		$vhostInfo = array(
			'id' => $vhost->getId(),
			'name' => $vhost->getName(),
			'port' => $vhost->getPort(),
			'status' => $status,
			'default' => $vhost->isDefault(),
			'zendDefined' => $vhost->isZendDefined(),
			'zendManaged' => $vhost->isManagedByZend(),
			'ssl' => $vhost->isSsl(),
			'created' => $this->getView()->webapidate($vhost->getCreatedAt()),
			'lastUpdated' => $this->getView()->webapidate($vhost->getLastUpdated()),
			'createdTimestamp' => $vhost->getCreatedAt(),
			'lastUpdatedTimestamp' => $vhost->getLastUpdated(),
			'servers' => $servers,
		);
		
		return $vhostInfo;
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
				Vhost::STATUS_UNKNOWN			=> 'Ok',
		);
	
		return (isset($strings[$status])) ? $strings[$status] : 'Error';
	}
	
	/**
	 * @param Vhost $vhost
	 * @param VhostNodeContainer $vhostsNodes
	 * @return integer
	 */
	private function getVhostStatus($vhost, $vhostsNodes) {
		
		if (! isset($vhostsNodes[$vhost->getId()])) {
			return Vhost::STATUS_ERROR;
		}
		
		if (0 == count($vhostsNodes[$vhost->getId()])) { /// No responding servers
			return Vhost::STATUS_UNKNOWN;
		}

		$status = array();
		foreach ($vhostsNodes[$vhost->getId()] as $server) { /* @var $server VhostNodeContainer */
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
		$servers = array();
		foreach ($vhostsNodes as $vhostsNode) {
			$servers[] = $this->getServer($vhostsNode);
		}
	
		return $servers;
	}
	
	/**
	 * @param VhostNodeContainer $vhostsNode
	 * @return string
	 */
	private function getServer(VhostNodeContainer $vhostsNode) {
		return array(
			'id' => $vhostsNode->getNodeId(),
			'status' => $this->convertStatus($vhostsNode->getStatus()),
			'name' => $vhostsNode->getName(),
			'lastMessage' => $this->lastMessage($vhostsNode->getLastMessage()),
		);
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
