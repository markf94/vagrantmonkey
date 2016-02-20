<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\BacktraceContainer;

class UrlInsightUrlXml extends AbstractHelper {
	
	public function __invoke(\UrlInsight\RequestContainer $request) {
		return <<<XML
<url>
	<resourceId>{$request->getResourceId()}</resourceId>
	<appId>{$request->getAppId()}</appId>
	<url><![CDATA[{$request->getUrl()}]]></url>
	<urlTooltip><![CDATA[{$request->getUrlTooltip()}]]></urlTooltip>
	<urlExample><![CDATA[{$request->getUrlExample()}]]></urlExample>
	<samples>{$request->getSamples()}</samples>
	<minTime>{$request->getMinTime()}</minTime>
	<maxTime>{$request->getMaxTime()}</maxTime>
	<avgTime>{$request->getAvgTime()}</avgTime>
	<maxMemory>{$request->getMaxMemory()}</maxMemory>
	<avgMemory>{$request->getAvgMemory()}</avgMemory>
	<fromTime>{$request->getFromTime()}</fromTime>
	<untilTime>{$request->getUntilTime()}</untilTime>
</url>
XML;
	}
}