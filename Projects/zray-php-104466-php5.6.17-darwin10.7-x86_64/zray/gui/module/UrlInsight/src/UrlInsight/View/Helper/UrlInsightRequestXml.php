<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\BacktraceContainer;

class UrlInsightRequestXml extends AbstractHelper {
	
	public function __invoke(\UrlInsight\RequestContainer $request) {
		return <<<XML
<request>
	<id>{$request->getId()}</id>
	<url><![CDATA[{$request->getUrl()}]]></url>
	<urlTooltip><![CDATA[{$request->getUrlTooltip()}]]></urlTooltip>
	<samples>{$request->getSamples()}</samples>
	<minTime>{$request->getMinTime()}</minTime>
	<maxTime>{$request->getMaxTime()}</maxTime>
	<avgTime>{$request->getAvgTime()}</avgTime>
	<maxMemory>{$request->getMaxMemory()}</maxMemory>
	<avgMemory>{$request->getAvgMemory()}</avgMemory>
	<fromTime>{$request->getFromTime()}</fromTime>
	<untilTime>{$request->getUntilTime()}</untilTime>
</request>
XML;
	}
}