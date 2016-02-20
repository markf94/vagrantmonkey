<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class JobJson extends AbstractHelper {
	
	/**
	 * @param array $job
	 * @param \ZendServer\Set $applications
	 * @return string
	 */
	public function __invoke($job, $applications) {
		
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
				$applicationName = $app->getUserApplicationName();
				break;
			}
		}
		
		$creationTimeTimestamp = $this->getView()->strtotimeAddTZOffset($job["creation_time"]);
		$endTimeTimestamp = $this->getView()->strtotimeAddTZOffset($job["end_time"]);
		$scheduleTimeTimestamp = $this->getView()->strtotimeAddTZOffset($scheduleTime);
		
	    return $this->getView()->json(array(
			"id" => $job["id"],
			"type" => $job["type"],
			"nodeId" => $job["node_id"],
			"nodeName" => (isset($job["node_name"]) ? $job["node_name"] : ''),
			"queueId" => $job["queue_id"],
			"queueName" => $job["queue_name"],
			"queueStatus" => $job["queue_status"],
			"status" => $job["status"],
			"priority" => $job["priority"],
			"persistent" => $job["persistent"],
			"script" => $job["script"],
			"name" => $name,
			"creationTime" => $this->getView()->webapidate(strtotime($job["creation_time"])),
			"creationTimeTimestamp" => $creationTimeTimestamp,
			"startTime" => $startTime,
			"startTimeTimestamp" => $startTimeTimestamp,
			"endTime" => $this->getView()->webapidate(strtotime($job["end_time"])),
			"endTimeTimestamp" => strtotime($endTimeTimestamp),
			"schedule" => $schedule,
			"scheduleTime" => $this->getView()->webapidate(strtotime($scheduleTime)),
			"scheduleTimeTimestamp" => $scheduleTimeTimestamp,
			"scheduleId" => $scheduleId,
			"applicationId" => $job["app_id"],
	   		"application" => $applicationName
	    ));
	}
}

