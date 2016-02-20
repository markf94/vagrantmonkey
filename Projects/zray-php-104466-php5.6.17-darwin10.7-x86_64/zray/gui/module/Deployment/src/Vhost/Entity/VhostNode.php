<?php
namespace Vhost\Entity;

class VhostNode {
	protected $ID;
	protected $VHOST_ID;
	protected $NODE_ID;
	protected $STATUS;
	protected $STATUS_MESSAGE;
	protected $NAME;

	public function getId() {
		return $this->ID;
	}
	
	public function getName() {
		return $this->NAME;
	}
	
	public function getVhostId() {
		return $this->VHOST_ID;
	}
	
	public function getNodeId() {
		return $this->NODE_ID;
	}
	
	public function getStatus() {
		return $this->STATUS;
	}
	
	public function getStatusMessage() {
		return $this->STATUS_MESSAGE;
	}
}
