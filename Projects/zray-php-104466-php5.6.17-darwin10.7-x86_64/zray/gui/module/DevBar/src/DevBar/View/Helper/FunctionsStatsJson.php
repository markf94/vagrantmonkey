<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\LogEntryContainer;
use DevBar\functionStatsContainer;

class FunctionsStatsJson extends AbstractHelper {
	
	public function __invoke($functions) {
		$entries = array();
		foreach ($functions as $function) {
			$entries[] = $this->functionInfo($function);
		}

		return $this->getView()->json($entries);
	}
	
	private function functionInfo(functionStatsContainer $function) {
		return array(
			'id' => $function->getId(),
			'requestId' => $function->getRequestId(),
			'functionName' => $function->getFunctionName(),
			'functionScope' => $function->getFunctionScope(),
			'functionFull' => $this->functionFullName($function->getFunctionScope(), $function->getFunctionName()),
			'timesCalled' => intval($function->getTimesCalled()),
			'timeExclusive' => intval($function->getTimeExclusive()),
			'timeInclusive' => intval($function->getTimeInclusive()),
			'filename' => $function->getFilename(),
			'line' => $function->getLine(),
			'isInternal' => $function->getIsInternal(),
		);
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