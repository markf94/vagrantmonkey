<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;

class UrlInsightUrlJson extends AbstractHelper {
	
	public function __invoke(\UrlInsight\RequestContainer $request) {
		$info = array(
			'resourceId' => $request->getResourceId(),
			'appId' => $request->getAppId(),
			'url' => $request->getUrl(), 
			'urlTooltip' => $request->getUrlTooltip(), 
			'urlExample' => $request->getUrlExample(), 
			'samples' => $request->getSamples(),
			'minTime' => $request->getMinTime(),
			'maxTime' => $request->getMaxTime(),
			'avgTime' => $request->getAvgTime(),
			'maxMemory' => $request->getMaxMemory(),
			'avgMemory' => $request->getAvgMemory(),
			'fromTime' => $request->getFromTime(),
			'untilTime' => $request->getUntilTime(),
		);
		
		return $this->getView()->json($info);
	}
}