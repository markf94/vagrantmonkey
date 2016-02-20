<?php
namespace Deployment\Application;
use Deployment\Model;

use ZendServer\Container\Structure,
Deployment;

use ZendServer\Log\Log;

class InfoContainer {
	
	/**
	 * @var integer
	 */
	private $applicationId;
	/**
	 * @var string
	 */
	private $userApplicationName;
	/**
	 * @var string
	 */
	private $applicationName;
	/**
	 * @var integer
	 */
	private $applicationStatus;
	/**
	 * @var integer
	 */
	private $applicationHealthStatus;
	/**
	 * @var string
	 */
	private $baseUrl;
	
	public function __construct(array $appInfo, $key) {
		$this->applicationId = $key;
		$this->userApplicationName = isset($appInfo['userApplicationName']) ? $appInfo['userApplicationName'] : '';
		$this->applicationName = isset($appInfo['applicationName']) ? $appInfo['applicationName'] : '';
		$this->baseUrl = isset($appInfo['baseUrl']) ? $appInfo['baseUrl'] : '';
		
		if (isset($appInfo['applicationStatus'])) {
		    $this->applicationStatus = Model::convertApplicationStatus($appInfo['applicationStatus']);
		} else {
			$this->applicationStatus = Model::STATUS_UNKNOWN;
		}
		
		if (isset($appInfo['applicationHealthStatus'])) {
			$this->applicationHealthStatus = Model::convertApplicationHealthStatus($appInfo['applicationHealthStatus']);
		} else {
			$this->applicationHealthStatus = Model::HEALTH_UNKNOWN;
		}
	}
	
	/**
	 * @return string
	 */
	public function getUserApplicationName() {
		return $this->userApplicationName;
	}

	/**
	 * @return string
	 */
	public function getApplicationName() {
		return $this->applicationName;
	}

	/**
	 * @return integer
	 */
	public function getApplicationStatus() {
		return $this->applicationStatus;
	}

	/**
	 * @return integer
	 */
	public function getApplicationHealthStatus() {
		return $this->applicationHealthStatus;
	}
	/**
	 * @return integer
	 */
	public function getApplicationId() {
		return $this->applicationId;
	}

	public function getBaseUrl() {		
		$defaultServer = \Application\Module::config('deployment', 'defaultServer');
		
		if (($port = parse_url($this->baseUrl, PHP_URL_PORT)) && parse_url($defaultServer, PHP_URL_PORT)) { // we don't want to create a url with port appearing twice
			$this->baseUrl = str_replace(":{$port}", '', $this->baseUrl); // will use the port of the $defaultServer only			
		}
		
		return str_replace('<default-server>', $defaultServer, $this->baseUrl);
	}
}

