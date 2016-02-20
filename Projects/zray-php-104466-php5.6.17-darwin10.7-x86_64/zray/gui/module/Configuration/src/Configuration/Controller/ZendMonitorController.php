<?php

namespace Configuration\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	Zend\Form\Form,
	Zend\Form\Element,
	Configuration\Forms\MonitorCleanup,
	Configuration\Forms\MonitorDefaultEmail,
	Zend\Validator\GreaterThan,
	ZendServer\Configuration\Manager,
	Zend\Validator\Digits;

class ZendMonitorController extends ActionController {
	const EVENT_TRACING_MODE_OFF 	= '0';
	const EVENT_TRACING_MODE_ON 	= '1';
	const EVENT_TRACING_MODE_LATENT	= '2';
	
    public function indexAction() {
    	$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
    	
    	/* @var \Configuration\MapperDirectives */
    	$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
    	$directivesSet = $directivesMapper->selectSpecificDirectives(array('zend_monitor.event_tracing_mode', 
    																	   'zend_monitor_ui.expiration_days',
    																	   'zend_monitor.event_generate_trace_file',
    	                                                                   'zend.monitor_generate_unique_events',
    	                                                                   'zend_monitor.aggregate_by_route'
    																)); /* @var $eventTracingMode \Configuration\DirectiveContainer */
    	
    	$eventTracingMode = $this->getDirectiveValueFromSet($directivesSet, 'zend_monitor.event_tracing_mode');
		$eventTracingMode = $eventTracingMode ? \Codetracing\Mapper\Statuses::STATUS_STAND_BY : \Codetracing\Mapper\Statuses::STATUS_DISABLED;
    	$expirationDays = $this->getDirectiveValueFromSet($directivesSet, 'zend_monitor_ui.expiration_days');
    	$generateUniqueEvents = $this->getDirectiveValueFromSet($directivesSet, 'zend.monitor_generate_unique_events');
    	$aggregateByRoute = $this->getDirectiveValueFromSet($directivesSet, 'zend_monitor.aggregate_by_route');
    	
    	
    	$extensionsMapper = $this->getLocator('Configuration\MapperExtensions'); /* @var $extensionsMapper \Configuration\MapperExtensions */
    	$codetracingLoaded = $extensionsMapper->isExtensionLoaded('Zend Code Tracing');
		$monitoringLoaded = $extensionsMapper->isExtensionLoaded('Zend Monitor');
		
    	$defaultEmail = Module::config('monitor', 'defaultEmail');
    	$defaultCustomAction = Module::config('monitor', 'defaultCustomAction');
    	
    	$cleanupForm = new MonitorCleanup();
    	$cleanupForm->setData(array('deleteEventsOccur' => $expirationDays));
    	
    	$defaultEmailForm = new MonitorDefaultEmail();
    	$defaultEmailForm->setData(array('defaultEmail' => $defaultEmail,
    									 'defaultCustomAction' => $defaultCustomAction));
    	
    	if (! $this->isAclAllowed('data:useMonitorAction', 'email')) {
    		$defaultEmailForm->get('defaultEmail')->setAttribute('disabled', 'disabled');
    	}
    	 	
    	if (! $this->isAclAllowed('data:useCustomAction')) {
    		$defaultEmailForm->get('defaultCustomAction')->setAttribute('disabled', 'disabled');
    	}
    	
    	$this->setDisabledByEditionMessage($defaultEmailForm);
    	$manager = new Manager();
    	
		$urlInsightDirectivesValues = $directivesMapper->getDirectivesValues(array(
			'zend_url_insight.enable', 
			'zend_url_insight.zray_enable', 
			'zend_url_insight.zray_dumps_interval', 
		));
		$urlInsightSettingsFormData = array(
			'url_insight_mode' => $urlInsightDirectivesValues['zend_url_insight.enable'],
			'zray_snapshots_mode' => $urlInsightDirectivesValues['zend_url_insight.zray_enable'],
			'zray_snapshots_interval' => $urlInsightDirectivesValues['zend_url_insight.zray_dumps_interval'],
		);
		
		$urlInsightSettingsForm = new \Configuration\Forms\UrlInsightSettings();
		
		$monitorSettingsForm = new \Configuration\Forms\MonitorSettings();
		$monitorSettingsFormData = array(
			// monitoring
			'monitoring' => $monitoringLoaded ? '1' : '0', // has to be managed
			
			// events aggregation
			'events_aggregation' => ($aggregateByRoute == 1 && $generateUniqueEvents == 0) ? 'route' : 
				(($aggregateByRoute == 0 && $generateUniqueEvents == 0) ? 'url' : 'none'),
				
			// code tracing
			'code_tracing_for_events' => $eventTracingMode,
			
			// clean up
			'delete_events_occur' => $expirationDays,
			
			// events default
			'monitoring_rule_default_email' => $defaultEmail,
			'monitoring_rule_default_callback_url' => $defaultCustomAction,
		);
		
    	return array(
			'pageTitle' => 'Settings',
			'pageTitleDesc' => '',  /* Daniel */
			'eventTracingMode' => $eventTracingMode, 
			'expirationDays' => $expirationDays,
			'cleanupForm' => $cleanupForm, 
			'defaultEmailForm' => $defaultEmailForm, 
			'osType' => $manager->getOsType(), 
			'codetracingLoaded' => $codetracingLoaded,
			'generateUniqueEvents' => $generateUniqueEvents,
			'urlInsightSettingsForm' => $urlInsightSettingsForm,
			'urlInsightSettingsFormData' => $urlInsightSettingsFormData,
			'monitorSettingsForm' => $monitorSettingsForm,
			'monitorSettingsFormData' => $monitorSettingsFormData,
		);
    }
    
    private function setDisabledByEditionMessage(\Zend\Form\Form $form) {
    	// check if all elements in the form are disabled
    	$allDisabled = true;
    	foreach ($form->getElements() as $element) { /* @var $element Element */
    		if ($element->getAttribute('type') != 'submit' && ! $element->hasAttribute('disabled')) {
    			$allDisabled = false;
    		}
    	}
    	
    	$licenseType = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseType();
    	$licenseType = ucwords(strtolower($licenseType));
    	
    	// all form elements are disabled - show general message
    	if ($allDisabled) {
    		// add global message
    		$description = '';
    		if ($form->hasAttribute('description')) {
    			$description = $form->getAttribute('description');
    		}
    		$description .= '<div class="restart-server-note global-restart-server-note">Not available in this edition</div>';
    		$form->setAttribute('description', $description);
    		
    		// remove the submit button
    		foreach ($form->getElements() as $element) { /* @var $element Element */
    			if ($element->getAttribute('type') == 'submit') {
    				$form->remove($element->getName());
    			}
    		}
    	} else { // not all form element disabled - show specific element message
    		foreach ($form->getElements() as $element) { /* @var $element Element */
    			if ($element->hasAttribute('disabled')) {
    				$element->setAttribute('description', '<div class="restart-server-note">Not available in this edition</div>');
    			}
    		}
    	}
    	
    	return $form;
    }
    
    private function isCleanupParamsValid($params) {
    	$deleteEventsOccur = $params['deleteEventsOccur'];
    	
    	$digitsValidator = new Digits();
    	$graterThanValidator = new GreaterThan(array('min' => -1));
    	
    	return ($digitsValidator->isValid($deleteEventsOccur) &&
    			$graterThanValidator->isValid($deleteEventsOccur));
    }
    
    /**
     * @param \Configuration\DirectiveContainer $set
     * @param string $directiveName
     */
    private function getDirectiveValueFromSet($set, $directiveName) {
    	foreach ($set as $directive) { /* @var $directive \Configuration\DirectiveContainer */
    		if ($directive->getName() == $directiveName) {
    			if ( $directive->getFileValue() == "" ) {
    				return $directive->getDefaultValue();
    			} else {
    				return $directive->getFileValue();
    			}
    		}
    	}
    }
}