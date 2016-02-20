<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class RuleJson extends AbstractHelper {
	
	/**
	 * @param array $job
	 * @return string
	 */
	public function __invoke($rule, $statuses, $priorites, $applications) {
		
			$status = strtolower($statuses[$rule['status']]);
			$priority = strtolower($priorites[$rule['priority']]);
			$persistent = $rule['persistent']?"yes":"no";
		
			$applicationName = "";
			foreach($applications as $app) {
				if ($app->getApplicationId() == $rule['app_id']) {
					$applicationName = $app->getUserApplicationName();
					break;
				}
			}
			
			if (isset($rule['last_run']) && $rule['last_run']) {
				$lastRun = $this->getView()->strtotimeAddTZOffset($rule['last_run']) ;
			} else {
				$lastRun = "";
			}
			$nextRun = isset($rule['next_run']) ? $this->getView()->strtotimeAddTZOffset($rule['next_run']) : '';
			
			$data = array(
				"id" => $rule['id'],
				"type" => $rule['type'],
				"queueId" => $rule['queue_id'],
				"queueName" => $rule['queue_name'],
				"status" => $status,
				"priority" => $priority,
				"persistent" => $persistent,
				"script" => $rule['script'],
				"name" => $rule['name'],
				"last_run" => isset($rule['last_run']) ? $rule['last_run'] : '',
				"last_runTimestamp" => isset($rule['last_run']) ? $lastRun : '',
				"next_run" => $rule['next_run'],
				"next_runTimestamp" => $nextRun,
				"http_headers" => isset($rule['http_headers']) ? $rule['http_headers'] : '',
				"vars" => $rule['vars'],
				"applicationId" => $rule['app_id'],
				"application" => $applicationName
			);
			if (isset($rule['queue_status'])) {
				$data['queueStatus'] = $rule['queue_status'];
			}
			return $this->getView()->json($data);
	}
	
	
}

