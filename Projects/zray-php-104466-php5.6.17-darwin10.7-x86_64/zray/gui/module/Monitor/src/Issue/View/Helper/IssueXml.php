<?php
namespace Issue\View\Helper;

use Zend\View\Helper\AbstractHelper,
Issue\Container,
Deployment\Application\InfoContainer,
EventsGroup\View\Helper\EventsGroupXml;

/**
 * @uses \Issue\View\Helper\IssueSeverity
 * @uses \Issue\View\Helper\IssueStatus
 * @uses \WebApi\View\Helper\WebapiDate
 * @uses \ZendServer\View\Helper\PhpErrorType
 */
class IssueXml extends AbstractHelper {
	/**
	 * @param integer $severity
	 * @return string
	 */
	public function __invoke(Container $issue, InfoContainer $application = null, $eventsData = null ) {
		if ($eventsData instanceof \ZendServer\Set) {
			$eventsData = $eventsData->current();
		}
		
		$codetracing = $issue->hasTrace() ? 'true' : 'false';
		$errorString = $eventsData?"<![CDATA[{$eventsData->getErrorString()}]]>" : '';
		$errorType = $eventsData? $this->getView()->phpErrorType($eventsData->getErrorType()) : '';
		
		if (is_null($application)) {
			$applicationName = '';
			$applicationId = 0;
		} else {
			$applicationName = $this->getView()->escapehtml($application->getUserApplicationName());
			$applicationId = $application->getApplicationId();
		}
		
		$mvcData = $issue->getMvcData();
		$issueArray['routeDetails'] = array();
		foreach ($mvcData as $key => $value) {
			$issueArray['routeDetails'][] = $value;
		} 
		$routeDetails = implode(" | ", $issueArray['routeDetails']);
		
		return <<<XML
			<issue>
				<id>{$this->getView()->escapeHtml($issue->getId())}</id>
				<eventType>{$this->getView()->escapeHtml($issue->getEventType())}</eventType>
				<rule><![CDATA[{$issue->getRuleName()}]]></rule>
				<count>{$this->getView()->escapeHtml($issue->getCount())}</count>
				<lastOccurance>{$this->getView()->webapiDate($issue->getLastOccurance())}</lastOccurance>
				<lastOccuranceTimestamp>{$issue->getLastOccurance()}</lastOccuranceTimestamp>
				<severity>{$this->getView()->issueSeverity($issue->getSeverity())}</severity>
				<status>{$this->getView()->issueStatus($issue->getStatus())}</status>
				<hasCodetracing>{$codetracing}</hasCodetracing>
				<codeTracingEventGroupId>{$this->getView()->escapeHtml($issue->getCodeTracingEventGroupId())}</codeTracingEventGroupId>				
				<ruleId>{$this->getView()->escapeHtml($issue->getRuleId())}</ruleId>						
				<generalDetails>
					<url><![CDATA[{$issue->getUrl()}]]></url>
					<baseUrl><![CDATA[{$issue->getBaseUrl()}]]></baseUrl>
					<sourceFile><![CDATA[{$issue->getFilename()}]]></sourceFile>
					<sourceLine>{$this->getView()->escapeHtml($issue->getLine())}</sourceLine>
					<function><![CDATA[{$issue->getFunction()}]]></function>
					<customEventClass><![CDATA[{$issue->getCustomEventClass()}]]></customEventClass>
					<aggregationHint>{$this->getView()->escapeHtml($issue->getAggregationHint())}</aggregationHint>
					<errorString>{$errorString}</errorString>
					<errorType>{$errorType}</errorType>
				</generalDetails>
				<whatHappenedDetails>{$this->getView()->EventsGroupXml($issue->getMaxEventGroup())}</whatHappenedDetails>			
				<appName>{$applicationName}</appName>
				<appId>{$applicationId}</appId>
				<routeDetails>
					{$routeDetails}
				</routeDetails>
			</issue>
XML;
	}
	
	/**
	 * @param \Issue\Container $issue
	 * @return string
	 */
	private function routeDetails(Container $issue) {
		$routeDetails = '';
		if ($issue->hasMvcData()) {
			foreach ($issue->getMvcData() as $key => $detail) {
				$routeDetails .= <<<ROUTEDETAILS
					<routeDetail>
						<key>{$key}</key>
						<value>{$detail}</value>
					</routeDetail>
ROUTEDETAILS;
			}
		}
		return $routeDetails;
	}
}

