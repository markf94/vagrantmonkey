<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;
use \ZendServer\Log\Log;

class JobXml extends AbstractHelper {
	
	/**
	 * @param array $job
	 * @return string
	 */
	public function __invoke($job, $applications, $statuses) {
		
		$name = isset($job['name']) ? $job['name'] : '';
	
		$schedule = isset($job['schedule']) ? $job['schedule'] : '';
		$scheduleTime = isset($job['schedule_time']) ? $job['schedule_time'] : '';
		$scheduleId = isset($job['schedule_rule_id']) ? $job['schedule_rule_id'] : '';
		
		if (isset($job["start_time"]) && $job["start_time"]) {
			$startTimeTimestamp = $this->getView()->strtotimeAddTZOffset($job["start_time"]);
			$startTime = $this->getView()->webapidate($startTimeTimestamp);
		} else if ($scheduleTime) {
			$startTimeTimestamp = $this->getView()->strtotimeAddTZOffset($scheduleTime);
			$startTime = $this->getView()->webapidate($startTimeTimestamp);
		} else {
			$startTimeTimestamp = '';
			$startTime = "";
		}
		
		$applicationName = "";
		foreach($applications as $app) {
			if ($app->getApplicationId() == $job['app_id']) {
				$applicationName = $this->getView()->escapehtml($app->getUserApplicationName());
				break;
			}
		} 

		$creationTimeTimestamp = $this->getView()->strtotimeAddTZOffset($job["creation_time"]);
		$endTimeTimestamp = $this->getView()->strtotimeAddTZOffset($job["end_time"]);
		$scheduleTimeTimestamp = $this->getView()->strtotimeAddTZOffset($scheduleTime);
		
		$status = strtolower($statuses[$job['status']]);
		
		$nodeName = isset($job['node_name']) ? $job['node_name'] : '';
		
		return <<<JOBXML
	    <job>
			<id>{$job['id']}</id>
			<type>{$job['type']}</type>
			<nodeId>{$job['node_id']}</nodeId>
			<nodeName>{$nodeName}</nodeName>
			<queueId><![CDATA[{$job['queue_id']}]]></queueId>
			<queueName><![CDATA[{$job['queue_name']}]]></queueName>
			<queueStatus><![CDATA[{$job['queue_status']}]]></queueStatus>
			<status>{$status}</status>
			<priority>{$job['priority']}</priority>
			<persistent>{$job['persistent']}</persistent>
			<script><![CDATA[{$job['script']}]]></script>
			<name><![CDATA[{$name}]]></name>
			<creationTime>{$this->getView()->webapidate(strtotime($job['creation_time']))}</creationTime>
			<creationTimeTimestamp>{$creationTimeTimestamp}</creationTimeTimestamp>
			<startTime>{$startTime}</startTime>
			<startTimeTimestamp>{$startTimeTimestamp}</startTimeTimestamp>
			<endTime>{$this->getView()->webapidate(strtotime($job['end_time']))}</endTime>
			<endTimeTimestamp>{$endTimeTimestamp}</endTimeTimestamp>
			<schedule>{$schedule}</schedule>
			<scheduleTime>{$this->getView()->webapidate(strtotime($scheduleTime))}</scheduleTime>
			<scheduleTimeTimestamp>{$scheduleTimeTimestamp}</scheduleTimeTimestamp>
			<scheduleId>{$scheduleId}</scheduleId>
			<applicationId>{$job['app_id']}</applicationId>
			<application>{$applicationName}</application>
		</job>
JOBXML;
	}
}

