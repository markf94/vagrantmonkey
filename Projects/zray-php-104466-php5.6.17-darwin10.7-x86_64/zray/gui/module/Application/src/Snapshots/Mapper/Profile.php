<?php

namespace Snapshots\Mapper;

use ZendServer\Log\Log;
use ZendServer\Exception;
use GuiConfiguration\Mapper\Configuration;
use Configuration\MapperDirectives;
use Zend\Config\Config;

class Profile {

	/**
	 * @var array
	 */
	private $profiles;
	
	/**
	 * @var Configuration
	 */
	private $guiConfigurationMapper;
	/**
	 * @var MapperDirectives
	 */
	private $directivesMapper;
	
	/**
	 * @var string
	 */
	private $phpversion;
	
	public function activateProfile($profile) {
		
		if (isset($this->profiles[$profile])) {
			Log::info("will set {$profile} directives");
			$directives = array();
			foreach ($this->profiles[$profile] as $key=>$sectionData) { /* @var $sectionData \Zend\Config\Config */				
				if ($key === 'GUI') {					
					$this->getGuiConfigurationMapper()->setGuiDirectives($sectionData);
				} elseif ($key === 'PHP_53' && version_compare($this->getPhpversion(), '5.4.0', '<')) {
					$directives = array_merge($directives, $sectionData);
				} elseif ($key === 'PHP_54' && version_compare($this->getPhpversion(), '5.4.0', '>=')) {
					$directives = array_merge($directives, $sectionData);
				} elseif (in_array($key, array('ZEND', 'PHP_ALL'))) {
					$directives = array_merge($directives, $sectionData);
				}
			}
			
			if (count($directives) > 0) {
				$this->getDirectivesMapper()->setDirectives($directives);
			}
		} else {
			throw new Exception(_t('Profile "%s" does not exist', array($profile)));
		}
	}

	
	
	/**
	 * @return array
	 */
	public function getProfiles() {
		return $this->profiles;
	}

	/**
	 * @return Configuration
	 */
	public function getGuiConfigurationMapper() {
		return $this->guiConfigurationMapper;
	}

	/**
	 * @return MapperDirectives
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}

	/**
	 * @return string
	 */
	public function getPhpversion() {
		if (is_null($this->phpversion)) {
			$this->phpversion = phpversion();
		}
		return $this->phpversion;
	}

	/**
	 * @param string $phpversion
	 */
	public function setPhpversion($phpversion) {
		$this->phpversion = $phpversion;
	}

	/**
	 * @param \GuiConfiguration\Mapper\Configuration $guiConfigurationMapper
	 */
	public function setGuiConfigurationMapper($guiConfigurationMapper) {
		$this->guiConfigurationMapper = $guiConfigurationMapper;
	}

	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
	}

	/**
	 * @param Config|array $profiles
	 */
	public function setProfiles($profiles) {
		if ($profiles instanceof Config) {
			$profiles = $profiles->toArray();
		}
		$this->profiles = $profiles;
	}

}

