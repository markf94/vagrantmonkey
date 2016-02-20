<?php

namespace Bootstrap\Mapper;

use ZendServer\Log\Log;
use GuiConfiguration\Mapper\Configuration;

class Reset {

	/**
	 * @var \GuiConfiguration\Mapper\Configuration
	 */
	private $guiConfiguration;
	
	public function resetBootstrap() {
		Log::debug("setting bootstrap completed to false");
		return $this->getGuiConfigurationMapper()->setGuiDirectives(array('completed' => "false"));
	}
	
	/**
	 * @return Configuration
	 */
	public function getGuiConfigurationMapper() {
		return $this->guiConfiguration;
	}

	/**
	 * @param \GuiConfiguration\Mapper\Configuration $guiConfiguration
	 */
	public function setGuiConfigurationMapper($guiConfiguration) {
		$this->guiConfiguration = $guiConfiguration;
	}

}

