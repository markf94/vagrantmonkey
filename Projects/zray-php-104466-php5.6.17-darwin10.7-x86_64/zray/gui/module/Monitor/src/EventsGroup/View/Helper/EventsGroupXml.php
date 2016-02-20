<?php
namespace EventsGroup\View\Helper;

use EventsGroup\Container;

use Zend\View\Helper\AbstractHelper;

class EventsGroupXml extends AbstractHelper {
	/**
	 * @param \EventsGroup\Container $eventsGroup
	 * @return string
	 */
	public function __invoke(Container $eventsGroup) {
		$hasCodetracing = $eventsGroup->hasCodetracing() ? 'true' : 'false';
		return <<<XML
				<eventsGroupId>{$eventsGroup->getEventsGroupId()}</eventsGroupId>
				<eventsCount>{$eventsGroup->getEventsCount()}</eventsCount>
				<startTime>{$this->view->webapiDate($eventsGroup->getstartTime())}</startTime>
				<startTimeTimestamp>{$eventsGroup->getstartTime()}</startTimeTimestamp>
				<serverId>{$eventsGroup->getServerId()}</serverId>
				<class>{$eventsGroup->getClass()}</class>
				<hasCodetracing>{$hasCodetracing}</hasCodetracing>
				<userData><![CDATA[{$eventsGroup->getUserData()}]]></userData>
				<javaBacktrace><![CDATA[{$eventsGroup->getJavaBacktrace()}]]></javaBacktrace>
				<execTime>{$eventsGroup->getExecTime()}</execTime>
				<avgExecTime>{$eventsGroup->getAvgExecTime()}</avgExecTime>
				<relExecTime>{$eventsGroup->getRelExecTime()}</relExecTime>
				<memUsage>{$eventsGroup->getMemUsage()}</memUsage>
				<avgMemUsage>{$eventsGroup->getAvgMemUsage()}</avgMemUsage>
				<relMemUsage>{$eventsGroup->getRelMemUsage()}</relMemUsage>
				<avgOutputSize>{$eventsGroup->getAvgOutputSize()}</avgOutputSize>
				<relOutputSize>{$eventsGroup->getRelOutputSize()}</relOutputSize>
				<load>{$eventsGroup->getLoad()}</load>
XML;
	}
}

