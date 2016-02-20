<?php
namespace Vhost;

use Vhost\Entity\VhostNode;

class VhostNodeContainer {
	const STATUS_OK 					= 0;
	const STATUS_ERROR 					= 1;
	const STATUS_PENDING_RESTART		= 2;
	const STATUS_WARNING 				= 3;
	const STATUS_DEPLOYMENT_NOT_ENABLED = 4;
	const STATUS_CREATE_ERROR			= 5;
	
	protected $id;
	protected $vhostId;
	protected $nodeId;
	protected $status;
	protected $lastMessage;
	protected $name;

	public function __construct(VhostNode $vhostNode) {
		$this->id = $vhostNode->getId();
		$this->vhostId = $vhostNode->getVhostId();
		$this->nodeId = $vhostNode->getNodeId();
		$this->status = $vhostNode->getStatus();
		$this->lastMessage = $vhostNode->getStatusMessage();
		$this->name	= $vhostNode->getName();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getVhostId() {
		return $this->vhostId;
	}
	
	public function getNodeId() {
		return $this->nodeId;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getLastMessage() {
		return $this->lastMessage;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	public function setVhostId($vhostId) {
		$this->vhostId = $vhostId;
		return $this;
	}
	
	public function setNodeId($nodeId) {
		$this->nodeId = $nodeId;
		return $this;
	}
	
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}
	
	public function setLastMessage($lastMessage) {
		$this->lastMessage = $lastMessage;
		return $this;
	}
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	

}
