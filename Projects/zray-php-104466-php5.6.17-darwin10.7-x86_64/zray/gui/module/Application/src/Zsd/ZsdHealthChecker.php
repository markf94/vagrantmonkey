<?php

namespace Zsd;

use ZendServer\Log\Log;

use ZendServer\Edition;
use ZendServer\EditionAwareInterface;
use Servers\Db\ServersAwareInterface;


class ZsdHealthChecker implements EditionAwareInterface, ServersAwareInterface {
	
	const THRESHOLD_DELTA = 10; // how long in seconds we allow the zsd timestmap to be stale, before reporting that ZSD is down. ZSD updates the timestamp at least once every 1 seconds
	
	/**
	 * @var Servers\Db\Mapper
	 */
	private $serversMapper;
	
	/**
	 * @var SessionStorage
	 */
	private $sessionStorage = null;
	
	/**
	 * @var Edition
	 */
	private $edition;
	
	/**
	 * @param boolean $zsdIsDown
	 * @return void|boolean
	 */
	public function checkZsdHealth($zsdIsDown=false) {
		
	    // remove all disabled not keep the non-alive servers
		$serverIds = $this->getServersMapper()->findRespondingServersIds(false, true);
		// run for all server and check if they are alive - have a zsd timestamp
		foreach ($serverIds as $id) {
    		$currentZsdTimeStamp = $this->getServersMapper()->getZsdLastUpdated($id);
    		if (is_null($currentZsdTimeStamp)) {
    			Log::warn("ZSD of $id HealthCheck - ZSD seems to be down");
    			return false;
    		}
		}
		
		$edition = $this->getEdition();
		$currentZsdTimeStamp = $this->getServersMapper()->getZsdLastUpdated($edition->getServerId());
		$currentPhpTimeStamp = time();
		
		$previousZsdTimeStamp = $this->getSessionStorage()->getZsdTimeStamp();
		$previousPhpTimeStamp = $this->getSessionStorage()->getPhpTimeStamp();
		
		if (! $previousZsdTimeStamp) {
			log::info("ZSD HealthCheck - timestamps are missing - newly created system?");
			$this->getSessionStorage()->setZsdTimeStamp($currentZsdTimeStamp);
			$this->getSessionStorage()->setPhpTimeStamp($currentPhpTimeStamp);
			return;
		}
		
		if ($zsdIsDown === false && ($currentPhpTimeStamp - $previousPhpTimeStamp) <= self::THRESHOLD_DELTA) {
			Log::debug("ZSD HealthCheck - will not check ZSD timestamps as within the threshold");
			return;
		}
		
		$this->getSessionStorage()->setPhpTimeStamp($currentPhpTimeStamp);
		if ($currentZsdTimeStamp === $previousZsdTimeStamp) {
			Log::warn("ZSD HealthCheck - ZSD seems to be down");
			return false;
		} else {
			$this->getSessionStorage()->setZsdTimeStamp($currentZsdTimeStamp);
			Log::debug("ZSD HealthCheck - ZSD is up and running");
			return true;
		}
	}
	
	/**
	 * @return \Servers\Db\Mapper
	 */
	public function getServersMapper() {
		return $this->serversMapper;
	}
	/**
	 * @param \Zsd\Servers\Db\Mapper $serversMapper
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
		return $this;
	}

	/**
	 * @return \Zsd\SessionStorage
	 */
	public function getSessionStorage() {
		if ($this->sessionStorage) return $this->sessionStorage;
				
		return $this->sessionStorage = new SessionStorage;
	}
	
	/**
	 * @param \Zsd\SessionStorage $sessionStorage
	 */
	public function setSessionStorage($sessionStorage) {
		$this->sessionStorage = $sessionStorage;
	}
	/* (non-PHPdoc)
	 * @see \ZendServer\EditionAwareInterface::setEdition()
	 */
	public function setEdition($edition) {
		$this->edition = $edition;
	}


	/**
	 * @return \ZendServer\Edition
	 */
	public function getEdition() {
		return $this->edition;
	}

	
}
