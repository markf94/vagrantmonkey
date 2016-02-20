<?php
namespace MonitorRules\Controller;
use Zend\Json\Json,
	MonitorRules\Rule,
	Application\Module;


use ZendServer\Mvc\Controller\ActionController,
ZendServer\Log\Log;

class EditController extends ActionController {
    public function indexAction() {
    	$this->getLocator('Navigation')->findByLabel('Event Rules')->setActive(true);
    	$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
    	if (isset($_GET['id'])) {    	
    		$monitorRules = $mapper->findMonitorRulesByRuleId(array($_GET['id'])); /* @var $monitorRules \MonitorRules\Rule */
    		$monitorRulesOrig = $monitorRules;
    		$monitorRules = $monitorRules[$_GET['id']];
    	} else {
    		$monitorRulesOrig = array($monitorRules = new Rule(array(), -1));
    	}
    	
    	$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
    	$applications = $deploymentModel->getMasterApplications();
    	$applications->setHydrateClass('\Deployment\Application\Container');
    	
    	$appId = (isset($_GET['app'])) ? $_GET['app'] : -1;
    	$ruleId = (isset($_GET['id'])) ? $_GET['id'] : '';
    	
    	// in adding a new Global Rule - remove the rule types that are not relevant 
		if ($appId == -1 && !$ruleId) {
			$ruleTypes = $mapper->getRuleTypesForNewGlobalRule();
		} else {
	    	$ruleTypes = $mapper->getRuleTypes();
		}

		$defaultEmail = Module::config('monitor', 'defaultEmail');
		$defaultCustomAction = Module::config('monitor', 'defaultCustomAction');
		
		return array('ruleId'	=> $ruleId,
					 'appId' => $appId,
					 'monitorRules' => $monitorRules,
					 'monitorRulesOrig' => $monitorRulesOrig,
					 'monitorRuleTypes' => $ruleTypes,
					 'dictionaryRuleTypeToAttribute' => Json::encode($mapper->getDictionaryRuleTypeToAttribute()),
					 'attributeNames' => Json::encode($mapper->getAttributeNames()),
					 'applications' => $applications,
					 'defaultEmail' => $defaultEmail,
					 'defaultCustomAction' => $defaultCustomAction);
    }
}