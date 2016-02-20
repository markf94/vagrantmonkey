<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\FunctionStatsContainer;

class FunctionsStatsXml extends AbstractHelper {
	
	public function __invoke($functions) {
		$entries = array();
		foreach ($functions as $function) {
			$entries[] = $this->functionElement($function);
		}

		return implode(PHP_EOL, $entries);
	}
	
	private function functionElement(FunctionStatsContainer $function) {
		return <<<XML
<functionStats>
	<id>{$function->getId()}</id>
	<requestId>{$function->getRequestId()}</requestId>
	<functionName><![CDATA[{$function->getFunctionName()}]]></functionName>
	<functionScope><![CDATA[{$function->getFunctionScope()}]]></functionScope>
	<functionFull><![CDATA[{$this->functionFullName($function->getFunctionScope(), $function->getFunctionName())}]]></functionFull>
	<timesCalled>{$function->getTimesCalled()}</timesCalled>
	<timeExclusive>{$function->getTimeExclusive()}</timeExclusive>
	<timeInclusive>{$function->getTimeInclusive()}</timeInclusive>
	<filename><![CDATA[{$function->getFilename()}]]></filename>
	<line>{$function->getLine()}</line>
	<isInternal>{$function->getIsInternal()}</isInternal>
</functionStats>
XML;
	}
	
	/**
	 * @param string $scope
	 * @param string $name
	 * @return string
	 */
	private function functionFullName($scope, $name) {
		if ($name == '{main}') {
			return '{main}';
		} else if ($scope) {
			return "$scope::$name()";
		} else {
			return "$name()";
		}
	}
}