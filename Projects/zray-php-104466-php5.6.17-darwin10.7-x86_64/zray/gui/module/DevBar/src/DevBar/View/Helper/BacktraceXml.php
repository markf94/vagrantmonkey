<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\BacktraceContainer;

class BacktraceXml extends AbstractHelper {
	
	public function __invoke(BacktraceContainer $backtrace) {
		$json = json_decode($backtrace->getBacktrace(), true);
		
		$res = array();
		foreach ($json as $trace) {
			$res[] = $this->backtraceElement($trace);
		}

		return implode(PHP_EOL, $res);
	}
	
	private function backtraceElement(array $trace) {
		$file = $trace['file'];
		if (empty($file)) {
			$file = '<builtin>';
		}
		return <<<XML
<trace>
	<name><![CDATA[{$trace['name']}]]></name>
	<scope><![CDATA[{$trace['scope']}]]></scope>
	<file><![CDATA[{$file}]]></file>
	<cline>{$trace['cline']}</cline>
	<args>{$this->args($trace['args'])}</args>
</trace>
XML;
	}
	
	private function args($args) {
		$argsXml = array();
		foreach($args as $arg) {
			$argsXml[] = "<arg><![CDATA[{$arg}]]></arg>";
		}
		return implode(PHP_EOL, $argsXml);
	}
}