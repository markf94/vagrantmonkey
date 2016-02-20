<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\RequestContainer;

class RequestInfoJson extends AbstractHelper {
	
	public function __invoke(RequestContainer $request) {
	    return $this->getView()->json(array(
	        'id' => $request->getId(),
	    	'pageId' => $request->getPageId(),
		    'url' => $request->getUrl(),
		    'httpResponse' => $request->getStatusCode(),
			'method' => $request->getMethod(),
		    'runTime' => $request->getRunTime(),
	    	'startTime' => $this->getView()->webapidate($request->getStartTime()/1000),
	    	'startTimeTimestamp' => $request->getStartTime(),
	    	'isPrimaryPage' => $request->isPrimaryPage(),
	    	'memoryPeak' => $request->getPeakMemory(),
	    	'memoryLimit' => $request->getMemoryLimit(),
		));
	}
}