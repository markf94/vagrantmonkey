<?php
namespace DevBar;

use ZendServer\Log\Log;
class AccessTokenContainer {
	/**
	 * @var array
	 */
	protected $accessToken;
	
	/**
	 * @param array $accessToken
	 */
	public function __construct(array $accessToken, $key=null) {
		$this->accessToken = $accessToken;
	}
	
	public function toArray() {
		return $this->accessToken;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->accessToken['id']) ? $this->accessToken['id'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getToken() {
		return (isset($this->accessToken['token']) ? $this->accessToken['token'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getAllowedHosts() {
		return (isset($this->accessToken['allowed_hosts']) ? $this->accessToken['allowed_hosts'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getBaseUrl() {
		return (isset($this->accessToken['base_url']) ? $this->accessToken['base_url'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getTtl() {
		return (isset($this->accessToken['ttl']) ? $this->accessToken['ttl'] : 0);
	}
	
	/**
	 * @return string
	 */
	public function getTitle() {
		return (isset($this->accessToken['name']) ? $this->accessToken['name'] : 0);
	}
	
	/**
	 * @return boolean
	 */
	public function getActions() {
	    return ((isset($this->accessToken['run_actions']) && $this->accessToken['run_actions'] == 1) ? true : false);
	}
	
	/**
	 * @return boolean
	 */
	public function getInject() {
	    return ((isset($this->accessToken['inject']) && $this->accessToken['inject'] == 1) ? true : false);
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getToken();
	}
}