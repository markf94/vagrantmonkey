<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\LogEntryContainer;

class LogEntriesJson extends AbstractHelper {
	
	public function __invoke($logEntries) {
		$entries = array();
		foreach ($logEntries as $logEntry) {
			$entries[] = $this->logEntryInfo($logEntry);
		}

		return $this->getView()->json($entries);
	}
	
	private function logEntryInfo(LogEntryContainer $logEntry) {
		return array(
			'id' => $logEntry->getId(),
			'requestId' => $logEntry->getRequestId(),
			'created' => $this->getView()->webapidate($logEntry->getTimestamp()),
			'createdTimestamp' => $logEntry->getTimestamp(),
			'type' => $logEntry->getType(),
			'message' => $logEntry->getMessage(),
			'filename' => $logEntry->getFilename(),
			'line' => $logEntry->getLine(),
			'silenced' => $logEntry->getSilenced(),
			'backtraceId' => $logEntry->getBacktraceId(),
			'sequenceId' => $logEntry->getSequenceId(),
		);
	}
}