<?php
namespace StudioIntegration\Client;

class Source extends StudioClient {

	public function __construct($filename, $line) {
		parent::__construct();
		$this->addDebuggerParam('get_file_content',	$filename);
		$this->addDebuggerParam('line_number',		$line);
	}
}