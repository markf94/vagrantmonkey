<?php

namespace Vhost\Filter;

use \ZendServer\Exception;

class Dictionary {	

	// Sort
	const COLUMN_ID = 'id';
	const COLUMN_NAME = 'name';
	const COLUMN_PORT = 'port';
	const COLUMN_LAST_UPDATED = 'last_updated';
	const COLUMN_SSL = 'is_ssl';
	const COLUMN_OWNER = 'owner';
	
	const FILTER_COLUMN_SSL = 'ssl';
	const FILTER_COLUMN_TYPE = 'type';
	const FILTER_COLUMN_DEPLOYMENT = 'deployment';
	const FILTER_COLUMN_PORT = 'port';
	
	const SSL_ENABLED = 'ssl_enabled';
	const SSL_DISABLED = 'ssl_disabled';
	
	const TYPE_SYSTEM_DEFINED = 'system_defined';
	const TYPE_ZS_DEFINED = 'zs_defined';
	
	const DEPLOYMENT_DISABLED = 'deployment_enabled';
	const DEPLOYMENT_ENABLED = 'deployment_disabled';
	
	const FILTER_COLUMN_FREE_TEXT = 'freeText';
	
	/**
	 * @return array
	 */
	public function getSSLDictionaryForFiltering() {
		return array(
			self::SSL_ENABLED => 'SSL enabled',
			self::SSL_DISABLED => 'SSL disabled',
		);
	}
	
	/**
	 * @return array
	 */
	public function getTypeDictionaryForFiltering() {
		return array(
			self::TYPE_SYSTEM_DEFINED => 'System defined',
			self::TYPE_ZS_DEFINED => 'Zend Server defined',
		);
	}
	
	/**
	 * @return array
	 */
	public function getDeploymentDictionaryForFiltering() {
		return array(
				self::DEPLOYMENT_ENABLED => 'Deployment enabled',
				self::DEPLOYMENT_DISABLED => 'Deployment disabled',
		);
	}
		
	/**
	 * @return array
	 */
	public function getFilterColumns() {
		return array(
				self::FILTER_COLUMN_SSL			=> self::FILTER_COLUMN_SSL,
				self::FILTER_COLUMN_TYPE		=> self::FILTER_COLUMN_TYPE,
				self::FILTER_COLUMN_DEPLOYMENT	=> self::FILTER_COLUMN_DEPLOYMENT,
				self::FILTER_COLUMN_FREE_TEXT	=> self::FILTER_COLUMN_FREE_TEXT,
				self::FILTER_COLUMN_PORT		=> self::FILTER_COLUMN_PORT,
		);
	}
	
	private $sslToDbPriority = array ( // sadly, the priorities kept in the DB are reversed that actual values!
			self::SSL_DISABLED => '0',
			self::SSL_ENABLED => '1',
	);
	
	private $typeToDbPriority = array ( // sadly, the priorities kept in the DB are reversed that actual values!
			self::TYPE_SYSTEM_DEFINED => '0',
			self::TYPE_ZS_DEFINED => '1',
	);
	
	private $deploymentToDbPriority = array ( // sadly, the priorities kept in the DB are reversed that actual values!
			self::DEPLOYMENT_ENABLED => array('1', '2'),
			self::DEPLOYMENT_DISABLED => array('0'),
	);	
	
	public function sslToDbValues(array $ssls) {
		$res = array();
		foreach ($ssls as $ssl) {
			$res[] = $this->sslToDbPriority[$this->getSslConstant($ssl)];
		}
	
		return $res;
	}
	
	public function getSslConstant($ssl) {
		if (!$this->isKnownSsl($ssl)) {
			throw new Exception(_t("ssl '%s' is not a known ssl type: '%s'", array($ssl, implode(',', array_keys($this->getSSLDictionaryForFiltering())))));
		}
		
		return $ssl;
	}
	
	public function isKnownSsl($ssl) {
		return array_key_exists($ssl, $this->getSSLDictionaryForFiltering());
	}
	
	public function typeToDbValues(array $types) {
		$res = array();
		foreach ($types as $type) {
			$res[] = $this->typeToDbPriority[$this->getTypeConstant($type)];
		}
	
		return $res;
	}
	
	public function getTypeConstant($type) {
		if (!$this->isKnownType($type)) {
			throw new Exception(_t("Type '%s' is not a known virtual host type: '%s'", array($type, implode(',', array_keys($this->getTypeDictionaryForFiltering())))));
		}
	
		return $type;
	}
	
	public function isKnownType($type) {
		return array_key_exists($type, $this->getTypeDictionaryForFiltering());
	}
	
	public function deploymentToDbValues(array $deployments) {
		$res = array();
		foreach ($deployments as $deployment) {
			$res[] = $this->deploymentToDbPriority[$this->getDeploymentConstant($deployment)];
		}
	
		return $res;
	}
	
	public function getDeploymentConstant($deployment) {
		if (!$this->isKnownDeployment($deployment)) {
			throw new Exception(_t("deployment '%s' is not a known deployment type: '%s'", array($deployment, implode(',', array_keys($this->getDeploymentDictionaryForFiltering())))));
		}
	
		return $deployment;
	}
	
	public function isKnownDeployment($deployment) {
		return array_key_exists($deployment, $this->getDeploymentDictionaryForFiltering());
	}

	public function getJQTimeRange() {
		return array (
				'all' => _t ( 'All' ),
				'day' => _t ( '24 Hours' ),
				'week' => _t ( 'Week' ),
				'month' => _t ( 'Month' ),
		);
	}
	
	public function getTimeRanges() {
		$timeRangesArray = array('all' => array());
		$timeRangesArray['month'] = array(date('m/d/Y H:i', strtotime('-1 month')), date('m/d/Y H:i'), strtotime('-1 month'), time());
		$timeRangesArray['week'] = array(date('m/d/Y H:i', time() - 7*24*60*60) , date('m/d/Y H:i'), time() - 7*24*60*60, time());
		$timeRangesArray['day'] = array(date('m/d/Y H:i', time() - 24*60*60), date('m/d/Y H:i'), time() - 24*60*60, time());
		
		return $timeRangesArray;
	}
	
}