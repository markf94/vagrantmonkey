<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CapabilitiesLabels extends AbstractHelper {
	
	/**
	 * @return \Application\View\Helper\CapabilitiesLabels
	 */
	public function __invoke() {
		return $this;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getNegativeLabels() {
		return array(
				'route:ServersWebAPI' => _t('Cluster management capabilities will not be available'),
				'route:DevBarWebApi' => _t('Z-Ray will not be displayed'),
				'route:VhostWebAPI' => _t('Virtual Hosts cannot be modified'),
				'dataRentention:timelimit' => _t('Information lists will display limited information'),
				'auditTrail:timelimit' => _t('Only audit entries from the last two hours will be displayed'),
				'service:Authentication:extended' => _t('Extended authentication will be unavailable'),
				'service:accessWebAPI' => _t('WebAPI will not be available for integration'),
				'data:useCustomAction' => _t('Callback URLs will be not be called'),
				'data:useMonitorAction:custom' => _t('Events will not request callback URLs'),
				'data:useMonitorAction:email' => _t('Events will not trigger information emails'),
				'data:components:Zend Job Queue' => _t('Zend Job Queue system will be deactivated'),
				'data:components:Zend Page Cache' => _t('Zend Page Cache system will be deactivated'),
				'data:components:Zend Java Bridge' => _t('Zend Java Bridge system will be unavailable'),
				'data:useMultipleUsers' => _t('Zend Server will allow access only for the \'admin\' user'),
				'data:useMonitorProRuleTypes' => _t('Advanced monitoring rules will be unavailable for use'),
				'data:useEmailNotification' => _t('Notifications will not trigger email messages'),
				'data:collectEventsCodeTrace' => _t('Code tracing will not be created automatically by Monitor events')
			);
	}
	
	/**
	 * @return array
	 */
	public function getPositiveLabels() {
		return array(
				'route:ServersWebAPI' => _t('Cluster management capabilities will be available'),
				'route:DevBarWebApi' => _t('Z-Ray will be available'),
				'route:VhostWebAPI' => _t('Virtual Hosts can be modified'),
				'dataRentention:timelimit' => _t('Information lists will display all available information'),
				'auditTrail:timelimit' => _t('All audit entries are displayed'),
				'service:Authentication:extended' => _t('Extended authentication is available'),
				'service:accessWebAPI' => _t('All WebAPI integration actions are available'),
				'data:useCustomAction' => _t('Callback URLs will be called when set'),
				'data:useMonitorAction:custom' => _t('When created, events will request callback URLs'),
				'data:useMonitorAction:email' => _t('When created, events will trigger information emails'),
				'data:components:Zend Job Queue' => _t('Zend Job Queue system will be available'),
				'data:components:Zend Page Cache' => _t('Zend Page Cache system will be available'),
				'data:components:Zend Java Bridge' => _t('Zend Java Bridge system will be available'),
				'data:useMultipleUsers' => _t('Zend Server will allow access for all users'),
				'data:useMonitorProRuleTypes' => _t('Advanced monitoring rules will be available for use'),
				'data:useEmailNotification' => _t('Notifications will trigger email messages'),
				'data:collectEventsCodeTrace' => _t('Code tracing will be created by Monitor events if set'),
		);
	}
}

