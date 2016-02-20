<?php
namespace Audit;

use Zend\Json\Json;

class ProgressContainer {
	/**
	 * @var integer
	 */
	private $id;
	/**
	 * @var integer
	 */
	private $auditId;
	/**
	 * @var integer
	 */
	private $nodeId;
	/**
	 * @var string
	 */
	private $nodeIp;
	/**
	 * @var string
	 */
	private $nodeName;
	/**
	 * @var integer
	 */
	private $creationTime;
	/**
	 * @var integer
	 */
	private $progress;
	/**
	 * @var string
	 */
	private $extraData = '[]';
	
	
	/**
	 * @param array $auditProgressData
	 */
	public function __construct(array $auditProgressData=array()) {
		if (!$auditProgressData) {
			return;
		}
	
		$this->setId($auditProgressData['AUDIT_PROGRESS_ID']);
		$this->setAuditId($auditProgressData['AUDIT_ID']);
		$this->setNodeId($auditProgressData['NODE_ID']);
		$this->setNodeIp($auditProgressData['NODE_IP']);
		$this->setNodeName($auditProgressData['NODE_NAME']);
		$this->setCreationTime($auditProgressData['CREATION_TIME']);
		$this->setProgress($auditProgressData['PROGRESS']);
		$this->setExtraData($auditProgressData['EXTRA_DATA']);
	}	
	
	/**
	 * @return number $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return number $auditId
	 */
	public function getAuditId() {
		return $this->auditId;
	}

	/**
	 * @return number $nodeId
	 */
	public function getNodeId() {
		return $this->nodeId;
	}

	/**
	 * @return string $nodeIp
	 */
	public function getNodeIp() {
		return $this->nodeIp;
	}

	/**
	 * @return string $nodeName
	 */
	public function getNodeName() {
		return $this->nodeName;
	}

	/**
	 * @return number $creationTime
	 */
	public function getCreationTime() {
		return $this->creationTime;
	}

	/**
	 * @return number $progress
	 */
	public function getProgress() {
		return $this->progress;
	}

	/**
	 * @return array $extraData
	 */
	public function getExtraData() {
		return $this->extraData;
	}

	/**
	 * @param number $id
	 * @return ProgressContainer
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param number $auditId
	 * @return ProgressContainer
	 */
	public function setAuditId($auditId) {
		$this->auditId = $auditId;
		return $this;
	}
	
	/**
	 * @param number $nodeId
	 * @return ProgressContainer
	 */
	public function setNodeId($nodeId) {
		$this->nodeId = $nodeId;
		return $this;
	}

	/**
	 * @param string $nodeIp
	 * @return ProgressContainer
	 */
	public function setNodeIp($nodeIp) {
		$this->nodeIp = $nodeIp;
		return $this;
	}

	/**
	 * @param string $nodeName
	 * @return ProgressContainer
	 */
	public function setNodeName($nodeName) {
		$this->nodeName = $nodeName;
		return $this;
	}

	/**
	 * @param number $creationTime
	 * @return ProgressContainer
	 */
	public function setCreationTime($creationTime) {
		$this->creationTime = $creationTime;
		return $this;
	}

	/**
	 * @param number $progress
	 * @return ProgressContainer
	 */
	public function setProgress($progress) {
		$this->progress = $progress;
		return $this;
	}

	/**
	 * @param array $extraData
	 * @return ProgressContainer
	 */
	public function setExtraData($extraData) {
		$this->extraData = $extraData;
		return $this;
	}

	public function toArray() {
		return get_object_vars($this);
	}
	
}