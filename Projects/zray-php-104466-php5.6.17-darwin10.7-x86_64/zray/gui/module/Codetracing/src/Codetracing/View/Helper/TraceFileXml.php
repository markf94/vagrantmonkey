<?php
namespace Codetracing\View\Helper;

use Zend\View\Helper\AbstractHelper;

class TraceFileXml extends AbstractHelper {
	
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
		
		return <<<XML
<codeTrace>
	<id>{$trace->getId()}</id>
	<date>{$this->getView()->webapiDate($trace->getDate())}</date>
	<timestamp>{$trace->getDate()}</timestamp>
	<url>{$this->getView()->escapeHtml($url)}</url>
	<createdBy>{$trace->getReason()}</createdBy>
	<fileSize>{$trace->getTraceSize()}</fileSize>
	<applicationId>{$trace->getApplicationId()}</applicationId>
	<nodeId>{$trace->getNodeId()}</nodeId>
	<routeDetails>$routeStr</routeDetails>
</codeTrace>
XML;
	}

}

