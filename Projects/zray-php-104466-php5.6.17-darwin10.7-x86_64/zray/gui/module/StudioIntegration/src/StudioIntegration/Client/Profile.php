<?php
namespace StudioIntegration\Client;


class Profile extends StudioClient {

	public function __construct() {
		parent::__construct();
		// Timeout of 5 minutes for very slow requests or suspensions in the IDE (like in the case of fatal errors)
		$this->setOptions(array('timeout' => 300));
		$this->addDebuggerParam('debug_coverage',	1);
		$this->addDebuggerParam('start_profile',	1);
	}
}