<?php
namespace MonitorRules\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Zend\Stdlib\Parameters,
	ZendServer\Log\Log;

class IndexController extends ActionController {
    public function indexAction() {
        $directivesMapper = $this->getLocator('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
        $eventTraceMode = $directivesMapper->getDirectiveValue('zend_monitor.event_tracing_mode');
        $eventGenerateTraceFile = $directivesMapper->getDirectiveValue('zend_monitor.event_generate_trace_file');
        
        $extensionsMapper = $this->getLocator('Configuration\MapperExtensions'); /* @var $extensionsMapper \Configuration\MapperExtensions */
       
        $deploymentModel = $this->getLocator('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
        $applications = $deploymentModel->getMasterApplications();
        $applications->setHydrateClass('\Deployment\Application\Container');
		/* @var $mapper \MonitorRules\Model\Mapper */
		$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); 
        
        $acl = $this->getLocator('ZendServerAcl'); /* @var $acl \ZendServer\Permissions\AclQuery */
        $allowGlobal = $acl->isAllowed('data:globalApplication');
        $ruleTypes = array();
        foreach ($mapper->getRuleTypes() as $ruleType) { /* @var $ruleType \MonitorRules\Type */
        	$ruleTypes[$ruleType->getId()] = array('name' => $ruleType->getName(), 'enabled' => $ruleType->isEnabled(), 'supported' => $this->isAclAllowed('data:useMonitorProRuleTypes'));
        }
        
        $mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
        $rules = $mapper->findAllMonitorRules();
        
        $rulesCount = array();      
        foreach ($rules as $rule) { /* @var $rule \MonitorRules\Rule */
			// skip jobqueue rules
			if (stripos($rule->getName(), 'job ') !== false) continue;
        	// count the rule by its application id
        	if (isset($rulesCount[$rule->getAppId()])) {
        		$rulesCount[$rule->getAppId()]++;
        	} else {
        		$rulesCount[$rule->getAppId()] = 1;
        	}
        }
        
        if (isset($rulesCount['-1'])) {
        	$rulesCount['Global'] = $rulesCount['-1'];
        	unset($rulesCount['-1']);
        }
        
        return array('pageTitle' => 'Event Rules',
					 'pageTitleDesc' => '',  /* Daniel */
					 'applications' => $applications,
        			 'monitorRuleTypes' => $ruleTypes,
                     'allowGlobal' => $allowGlobal,
        			 'rulesCount' => $rulesCount,
        			'eventTraceMode' => $eventTraceMode,
        			'codetracingLoaded' => $extensionsMapper->isExtensionLoaded('Zend Code Tracing')
            );
    }
    
    public function exportAction() {
    	$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
    	$request->setQuery(new Parameters($request->getQuery()->toArray()));
    	$monitorRulesXml = $this->forward()->dispatch('MonitorRulesWebApi-1_7', array('action' => 'monitorExportRules'));
    	/** @var $renderer \Zend\View\Renderer\PhpRenderer */
    	$resolver = $this->getLocator('ViewTemplatePathStackWebAPI'); /* @var $resolver \Zend\View\Resolver\TemplatePathStack */
    	$defaultSuffix = $resolver->getDefaultSuffix();
    	
    	$resolver->setDefaultSuffix('pxml.phtml');
    	$paths = $resolver->getPaths();
    	
    	$renderer = $this->getLocator('Zend\View\Renderer\PhpRenderer'); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
    	$renderer->setResolver($resolver);
    	$monitorRules = $renderer->render($monitorRulesXml);
    	/* @var $response \Zend\Http\PhpEnvironment\Response */
    	$response = $this->getResponse();
    	$response->setContent($monitorRules);
    		
    	return $this->getResponse();
    }
}