<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper,
JobQueue\Model\RecurringJob;
use \ZendServer\Log\Log;

class RuleXml extends AbstractHelper {
	
	/**
	 * @param \JobQueue\Model\RecurringJob $rule
	 * @return string
	 */
	public function __invoke($rule, $statuses, $priorites, $applications=array()) { //@todo - to be completed

		$status = strtolower($statuses[$rule['status']]);
		$priority = strtolower($priorites[$rule['priority']]);
		$persistent = $rule['persistent']?"yes":"no";
		
		$applicationName = "";
		foreach($applications as $app) {
			
			if ($app->getApplicationId() == $rule['app_id']) {
				$applicationName = $this->getView()->escapehtml($app->getUserApplicationName());
				break;
			}
		}
		
		if (isset($rule['last_run']) && $rule['last_run']) {
			$lastRun = $this->getView()->strtotimeAddTZOffset($rule['last_run']) ;
		} else {
			$lastRun = "";
		}
		$nextRun = isset($rule['next_run']) ? $this->getView()->strtotimeAddTZOffset($rule['next_run']) : '';
		
		$httpHeaders = isset($rule['http_headers']) ? $rule['http_headers'] : '';
		
		$queueStatus = '';
		if (isset($rule['queue_status'])) {
			$queueStatus = '<queueStatus>'.$rule['queue_status'].'</queueStatus>';
		}
		
		return <<<RULEXML
	    <rule>
			<id>{$rule['id']}</id>
			<type>{$rule['type']}</type>
			<queueId><![CDATA[{$rule['queue_id']}]]></queueId>
			<queueName><![CDATA[{$rule['queue_name']}]]></queueName>
			{$queueStatus}
			<status>{$status}</status>
			<priority>{$priority}</priority>
			<persistent>$persistent</persistent>
			<script><![CDATA[{$rule['script']}]]></script>
			<name><![CDATA[{$rule['name']}]]></name>
			<last_run>{$rule['last_run']}</last_run>
			<last_runTimestamp>{$lastRun}</last_runTimestamp>
			<next_run>{$rule['next_run']}</next_run>
			<next_runTimestamp>{$nextRun}</next_runTimestamp>
			<http_headers><![CDATA[{$httpHeaders}]]></http_headers>
			<vars><![CDATA[{$rule['vars']}]]></vars>
			<applicationId><![CDATA[{$rule['app_id']}]]></applicationId>
			<application><![CDATA[{$applicationName}]]></application>
		</rule>
		
RULEXML;
	}
}

