<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\ExceptionsContainer;

class ExceptionsXml extends AbstractHelper {
	
	public function __invoke($exceptions) {
		$entries = array();
		foreach ($exceptions as $exception) {
			$entries[] = $this->exception($exception);
		}

		return implode(PHP_EOL, $entries);
	}
	
	private function exception(ExceptionsContainer $exception) {
		return <<<XML
<exception>
	<id>{$exception->getId()}</id>
	<requestId>{$exception->getRequestId()}</requestId>
	<text><![CDATA[{$exception->getExceptionText()}]]></text>
	<code><![CDATA[{$exception->getExceptionCode()}]]></code>
	<className><![CDATA[{$exception->getExceptionClass()}]]></className>
	<filename><![CDATA[{$exception->getFileName()}]]></filename>
	<line>{$exception->getLineNumber()}</line>
	<backtraceId>{$exception->getBacktraceId()}</backtraceId>
	<sequenceId>{$exception->getSequenceId()}</sequenceId>
	<createdAt>{$this->getView()->webapidate($exception->getCreatedAt())}</createdAt>
	<createdAtTimestamp>{$exception->getCreatedAt()}</createdAtTimestamp>
</exception>
XML;
	}
}