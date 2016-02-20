<?php
namespace StudioIntegration\Client;


class Debug extends StudioClient {

	public function __construct($filename, $line) {
		parent::__construct();
		// Timeout of 1 hour, let the user debug the script for a long time
		$this->setOptions(array('timeout' => 3600));
		$this->addDebuggerParam('debug_stop',		1);
		$this->addDebuggerParam('debug_file_bp',	$filename);
		$this->addDebuggerParam('debug_line_bp',	$line);
	}
}