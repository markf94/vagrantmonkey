<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\RequestContainer;

class RequestInfoXml extends AbstractHelper {
	
	public function __invoke(RequestContainer $request) {
		$isPrimary = $request->isPrimaryPage() ? 'Yes' : 'No';
		return <<<XML
		<requestInfo>
			<id>{$request->getId()}</id>
			<pageId>{$request->getPageId()}</pageId>
			<url><![CDATA[{$request->getUrl()}]]></url>
			<httpResponse>{$request->getStatusCode()}</httpResponse>
			<method>{$request->getMethod()}</method>
			<runTime>{$request->getRunTime()}</runTime>
			<startTime>{$this->getView()->webapidate($request->getStartTime()/1000)}</startTime>
			<startTimeTimestamp>{$request->getStartTime()}</startTimeTimestamp>
	    	<isPrimaryPage>{$isPrimary}</isPrimaryPage>
	    	<memoryPeak>{$request->getPeakMemory()}</memoryPeak>
	    	<memoryLimit>{$request->getMemoryLimit()}</memoryLimit>
		</requestInfo>
XML;
	}
}