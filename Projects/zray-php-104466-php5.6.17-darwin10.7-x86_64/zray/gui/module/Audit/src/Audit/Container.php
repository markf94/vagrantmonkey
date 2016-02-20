<?php
namespace Audit;

class Container {
	
	/**
	 * @var array
	 */
	protected $auditData = array();

	/**
	 * @var string
	 */
	protected $progress = '';

	/**
	 * @var string
	 */
	protected $outcome = '';
	
	
	/**
	 * @param array $auditData
	 */
	public function __construct(array $auditData) {				
		if (!$auditData) {
			return;
		}
		
		$this->setAuditId($auditData['AUDIT_ID']);
		$this->setUsername($auditData['USERNAME']);
		$this->setRequestInterface($auditData['REQUEST_INTERFACE']);
		$this->setRemoteAddr($auditData['REMOTE_ADDR']);
		$this->setAuditType($auditData['AUDIT_TYPE']);
		$this->setbaseUrl($auditData['BASE_URL']);
		$this->setCreationTime($auditData['CREATION_TIME']);
		$this->setextraData($auditData['EXTRA_DATA']);
		
		if (!isset($auditData['PROGRESS'])) {
			$auditData['PROGRESS']='';
		}
		$this->setProgress($auditData['PROGRESS']);	
	}
	
	public function toArray() {
		return $this->auditData + array('outcome' => $this->outcome);
	}
	
	public function toString() {
		return implode(',' , $this->auditData);
	}
	
	/**
	 * @return integer
	 */
	public function getAuditId() {
		return $this->auditData['auditId'];
	}
	/**
	 * @param number $AuditId
	 */
	public function setAuditId($auditId) {
		$this->auditData['auditId'] = $auditId;
	}

	/**
	 * @return the $username
	 */
	public function getUsername() {
		return $this->auditData['username'];
	}
	/**
	 * @param string $username
	 */
	protected function setUsername($username) {
		$this->auditData['username'] = $username;
	}

	/**
	 * @return the $requestInterface
	 */
	public function getRequestInterface() {
		return $this->auditData['requestInterface'];
	}
	/**
	 * @param string $requestInterface
	 */
	protected function setRequestInterface($requestInterface) {
		$this->auditData['requestInterface'] = $requestInterface;
	}

	/**
	 * @return the $remoteAddr
	 */
	public function getRemoteAddr() {
		return $this->auditData['remoteAddr'];
	}
	/**
	 * @param string $remoteAddr
	 */
	protected function setRemoteAddr($remoteAddr) {
		$this->auditData['remoteAddr'] = $remoteAddr;
	}

	/**
	 * @return the $auditType
	 */
	public function getAuditType() {
		return $this->auditData['auditType'];
	}
	/**
	 * @param number $auditType
	 */
	protected function setAuditType($auditType) {
		$this->auditData['auditType'] = $auditType;
	}

	/**
	 * @return the $creationTime
	 */
	public function getCreationTime() {
		return $this->auditData['creationTime'];
	}
	/**
	 * @param string $creationTime
	 */
	protected function setCreationTime($creationTime) {
		$this->auditData['creationTime'] = $creationTime;
	}

	/**
	 * @return the $baseUrl
	 */
	public function getbaseUrl() {
		return $this->auditData['baseUrl'];
	}
	/**
	 * @param string $baseUrl
	 */
	protected function setbaseUrl($baseUrl) {
		$this->auditData['baseUrl'] = $baseUrl;
	}

	public function getRawExtradata() {
		return $this->auditData['extraData'];
	}
	
	/**
	 * @return string
	 */
	public function getextraData() {
		return (array) json_decode($this->auditData['extraData'], true);
	}
	/**
	 * @param string $extraData
	 */
	protected function setextraData($extraData) {
		$this->auditData['extraData'] = $extraData;
	}
	
	/**
	 * @return the $progress
	 */
	public function getProgress() {
		return $this->progress;
	}
	/**
	 * @param string $progress
	 */
	protected function setProgress($progress) {
		$this->progress = $progress;
	}
	
	/**
	 * @return the $outcome
	 */
	public function getOutcome() {
		return $this->outcome;
	}
	/**
	 * @param string $outcome
	 */
	public function setOutcome($outcome) {
		$this->outcome = $outcome;
	}		
}