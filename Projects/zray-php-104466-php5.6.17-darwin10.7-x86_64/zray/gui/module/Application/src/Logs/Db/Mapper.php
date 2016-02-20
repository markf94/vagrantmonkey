<?php

namespace Logs\Db;

use Configuration\MapperAbstract;

class Mapper extends MapperAbstract {
	
	/**
	 * @var \Configuration\MapperDirectives
	 */
	private $directivesMapper;
	
	/**
	 * @return array
	 */
	public function findAllEnabledLogFiles() {
		$logFilesSet = $this->select(array('enabled' => 1));
		// resolve directives' dependencies
		$logFiles = array();
		foreach ($logFilesSet as $logFile) {
			if ($logFile['DIRECTIVE']) {
				$directiveValue = $this->getDirectivesMapper()->getDirectiveValue($logFile['DIRECTIVE']);
				$logFiles[$logFile['NAME']] = trim($directiveValue,'"');
			} else {
				$logFiles[$logFile['NAME']] = $logFile['FILEPATH'];
			}
		}
		
		ksort($logFiles);
		return $logFiles;
	}
	
	/**
	 * @return \Configuration\MapperDirectives $directivesMapper
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}

	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 * @return Mapper
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
		return $this;
	}

	
	
}

