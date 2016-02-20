<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;
use DevBar\LogEntryContainer;

class LogEntriesXml extends AbstractHelper {
	
	public function __invoke($logEntries) {
		$entries = array();
		foreach ($logEntries as $logEntry) {
			$entries[] = $this->LogEntry($logEntry);
		}

		return implode(PHP_EOL, $entries);
	}
	
	private function LogEntry(LogEntryContainer $logEntry) {
		return <<<XML
<logEntry>
	<id>{$logEntry->getId()}</id>
	<requestId>{$logEntry->getRequestId()}</requestId>
	<created>{$this->getView()->webapidate($logEntry->getTimestamp())}</created>
	<createdTimestamp>{$logEntry->getTimestamp()}</createdTimestamp>
	<type>{$logEntry->getType()}</type>
	<message><![CDATA[{$logEntry->getMessage()}]]></message>
	<filename><![CDATA[{$logEntry->getFilename()}]]></filename>
	<line>{$logEntry->getLine()}</line>
	<silenced>{$logEntry->getSilenced()}</silenced>
	<backtraceId>{$logEntry->getBacktraceId()}</backtraceId>
	<sequenceId>{$logEntry->getSequenceId()}</sequenceId>
</logEntry>
XML;
	}
}