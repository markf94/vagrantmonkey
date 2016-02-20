<?php
namespace Issue\View\Helper;

use Zend\View\Helper\AbstractHelper,
Issue\Container,
ZendServer\Log\Log,
Deployment\Application\InfoContainer;
use Zend\Json\Json;

/**
 * @uses \Issue\View\Helper\IssueSeverity
 * @uses \Issue\View\Helper\IssueStatus
 * @uses \WebApi\View\Helper\WebapiDate
 * @uses \ZendServer\View\Helper\PhpErrorType
 */
class IssueJson extends AbstractHelper {
	/**
	 * @param Issue\Container $issue
	 * @param Deployment\Application\InfoContainer $application
	 * @return string
	 */
	public function __invoke(Container $issue, InfoContainer $application = null, $eventsData = null ) {
		if ($eventsData instanceof \ZendServer\Set) {
			$eventsData = $eventsData->current();
		}
		
		$issueArray = array(
				'id' => $issue->getId(),
		        'eventType' => $issue->getEventType(),
				'rule' => $issue->getRuleName(),
				'count' => $issue->getCount(),
				'lastOccurance' => $issue->getLastOccurance(),
				'lastOccuranceTimestamp' => (integer)$issue->getLastOccurance(),
				'severity' => $issue->getSeverity(),
				'status' => $issue->getStatus(),
				'hasCodetracing' => $issue->hasTrace(),
				'codeTracingEventGroupId' => $issue->getCodeTracingEventGroupId(),
				'ruleId' => $issue->getRuleId(),
				'generalDetails' => array(
						'url' => $issue->getUrl(),
						'baseUrl' => $issue->getBaseUrl(),
						'sourceFile' => $issue->getFilename(),
						'sourceLine' => $issue->getLine(),
						'function' => $issue->getFunction(),
				        'customEventClass' => $issue->getCustomEventClass(),
						'aggregationHint' => $issue->getAggregationHint(),
						'errorString' => (! is_null($eventsData))?$eventsData->getErrorString():"",
						'errorType' => (! is_null($eventsData))?$this->getView()->phpErrorType($eventsData->getErrorType()):""),
				'whatHappenedDetails' => Json::decode($this->getView()->EventsGroupJson($issue->getMaxEventGroup()))
		);
		
		$mvcData = $issue->getMvcData();
		$issueArray['routeDetails'] = array();
		foreach ($mvcData as $key => $value) {
		  $issueArray['routeDetails'][] = $value;
		} 
		$issueArray['routeDetails'] = implode(" | ", $issueArray['routeDetails']);
		
		$issueArray['lastOccurance'] = $this->getView()->webapiDate($issue->getLastOccurance());
		$issueArray['severity'] = $this->getView()->issueSeverity($issue->getSeverity());
		$issueArray['status'] = $this->getView()->issueStatus($issue->getStatus());
		
		if (is_null($application)) {
			$issueArray['appName'] = '';
			$issueArray['appId'] = 0;
		} else {
			$issueArray['appName'] = $application->getUserApplicationName();
			$issueArray['appId'] = $application->getApplicationId();
		}
				
		return $this->getView()->json($issueArray, array());
	}
}

