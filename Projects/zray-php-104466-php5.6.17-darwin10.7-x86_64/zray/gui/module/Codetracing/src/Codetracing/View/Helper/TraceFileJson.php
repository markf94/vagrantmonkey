<?php
namespace Codetracing\View\Helper;

use Codetracing\TraceFileContainer;

use Zend\View\Helper\AbstractHelper;

class TraceFileJson extends AbstractHelper {
		
	/**
	 * @param ExtensionContainer
	 * @return string
	 */
	public function __invoke(\Codetracing\TraceFileContainer $trace) {
		
		$route = $trace->getRouteDetails();
		if (isset($route['controller'])) {
			$routeStr = $route['controller'] . " | " . $route['action'];
		} else {
			$routeStr = "";
		}
		
		
		try {
			$url = $this->getView()->formatTargetUrl($trace->getUrl());
		} catch (\Exception $e) {
			$url = $trace->getUrl();
		}
		
		$traceArray = array(
			'id' 		=> $trace->getId(),
			'date' 		=> $this->getView()->webapiDate($trace->getDate()),
			'timestamp'	=> $trace->getDate(),
			'url' 		=> $url,
			'host'		=> $trace->getHost(),
			'createdBy'	=> $trace->getReason(),
			'filesize' 	=> $trace->getTraceSize(),
			'applicationId'	=> $trace->getApplicationId(),
			'nodeId' => $trace->getNodeId(),
			'routeDetails' => $routeStr,
			'rowId' => $trace->getRowId(),
		);
		
		return $this->getView()->json($traceArray);
	}
}

