<?php

namespace StudioIntegration;

use Zend\Config\Config;
use ZendServer\Log\Log;

class Mapper {
	
	/**
	 * @var Config
	 */
	private $moduleConfiguration;
	
	public function getConfiguration() {
		$configuration = Configuration::getInstance();
		$configuration->setConfiguration($this->config('studioHost'), $this->config('studioPort'), $this->config('studioUseSsl'), $this->config('studioBreakOnFirstLine'), $this->config('studioUseRemote'));
		$configuration->setAutoDetect($this->config('studioAutoDetection'));
		$configuration->setBrowserDetect($this->config('studioAutoDetectionEnabled'));
		$configuration->setTimeout($this->config('studioClientTimeout'));
		$configuration->setAutoDetectionPort($this->config('studioAutoDetectionPort'));
		return $configuration;
	}
	
	/**
	 * @param string $param
	 * @return array/string $moduleConfiguration
	 */
	public function config($param = null) {
		if (is_null($param)) {
			return $this->moduleConfiguration;
		}
		if (isset($this->moduleConfiguration[$param])) {
			return $this->moduleConfiguration[$param];
		} else {
			return '';
		}
	}

	/**
	 * @param array $moduleConfiguration
	 * @return Mapper
	 */
	public function setModuleConfiguration($moduleConfiguration) {
		$this->moduleConfiguration = $moduleConfiguration;
		return $this;
	}

}

