<?php 

require_once dirname(__FILE__) . '/PendingDeployment/Interface.php';

class ZendDeployment_PendingDeployment implements ZendDeployment_PendingDeployment_Interface
{
	
	private $_package;
	private $_baseUrl;
	private $_userParams;
	private $_zendParams;
	private $_id;
	private $_name;
	
	public function __construct()
	{
		$this->_baseUrl = "";
	}
	
	public function setDeploymentPackage($package) {
		$this->_package = $package;
	}
		
	/* (non-PHPdoc)
	 * @see ZendDeployment_PendingDeployment_Interface::getDeploymentPackage()
	 */
	public function getDeploymentPackage() {
		return $this->_package;
	}
	
	public function setBaseUtl($baseUrl) {
		$this->_baseUrl = $baseUrl;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PendingDeployment_Interface::getBaseUrl()
	 */
	public function getBaseUrl() {
		return $this->_baseUrl;
	}
	
	public function setUserParams($userParams) {
		$this->_userParams = $userParams;
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PendingDeployment_Interface::getUserParams()
	 */
	public function getUserParams() {
		return $this->_userParams;
	}

	public function setZendParams($zendParams) {
		$this->_zendParams = $zendParams;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PendingDeployment_Interface::getZendParams()
	 */
	public function getZendParams() {
		return $this->_zendParams;		
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PendingDeployment_Interface::isNull()
	 */
	public function isNull() {
		return ($this->_baseUrl == ""); 		
	}
	
	
	public function setId($id) {
		$this->_id = $id;
	}
	
	public function setName($name) {
		$this->_name = $name;
	}
	
	public function getName() {
	    return $this->_name;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PendingDeployment_Interface::getId()
	 */
	public function getId() {
		return $this->_id;		
	}
	
	
	
}