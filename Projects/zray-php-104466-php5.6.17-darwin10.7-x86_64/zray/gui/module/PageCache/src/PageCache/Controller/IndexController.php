<?php

namespace PageCache\Controller;

use Zend\View\Model\ViewModel;
use ZendServer\Log\Log;

use ZendServer\Mvc\Controller\ActionController,
	Zend\Stdlib\Parameters,
	Application\Module;

class IndexController extends ActionController
{
    public function IndexAction() {
    	if (! $this->isAclAllowedEdition('route:PageCacheWebApi')) {
    		$viewModel = new ViewModel();
    		$viewModel->setTemplate('page-cache/index/index-marketing');
    		return $viewModel;
    	}
    	
    	$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
    	$applications = $deploymentModel->getMasterApplications();
    	$applications->setHydrateClass('\Deployment\Application\Container');
    	
    	$mapper = $this->getLocator()->get('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */

    	$rules = $mapper->getRules();
    	
    	$rulesCount = array();
    	foreach ($rules as $rule) { /* @var $rule \PageCache\Rule */
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
    	
    	$isAllowedToCreateKey = $this->isAclAllowed('route:PageCacheEditRule', 'pagecacheSaveApplicationRule');
    	$isAllowedToClearCache = $this->isAclAllowed('route:CacheWebApi', 'cacheClear');
    	$isAllowedToDeleteRules = $this->isAclAllowed('route:PageCacheWebApi', 'pagecacheDeleteRules');
    	$isAllowedToClearRulesCache = $this->isAclAllowed('route:PageCacheWebApi', 'pagecacheClearRulesCache');
    	$isAllowedToExportRules = $this->isAclAllowed('route:PageCacheWebApi', 'pagecacheExportRules');
    	
    	return array('pageTitle' => 'Page Cache',
					 'pageTitleDesc' => '',  /* Daniel */
					 'rules' => $rules,
    				 'applications' => $applications,
    	             'matchTypeDictionary' => json_encode($mapper->getMatchTypeDictionary()),
    				 'isAllowedToCreateKey' => $isAllowedToCreateKey,
    				 'isAllowedToClearCache' => $isAllowedToClearCache,
    				 'isAllowedToDeleteRules' => $isAllowedToDeleteRules,
    				 'isAllowedToClearRulesCache' => $isAllowedToClearRulesCache,
    				 'isAllowedToExportRules' => $isAllowedToExportRules,
    	             'rulesCount' => $rulesCount,
    				 );
    }
    
    public function exportAction() {
    	$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
    	$request->setQuery(new Parameters($request->getQuery()->toArray()));
    	$pcRulesXml = $this->forward()->dispatch('PageCacheWebApi-1_3', array('action' => 'pagecacheExportRules'));
    	/** @var $renderer \Zend\View\Renderer\PhpRenderer */
    	$resolver = $this->getLocator('ViewTemplatePathStackWebAPI'); /* @var $resolver \Zend\View\Resolver\TemplatePathStack */
    	$defaultSuffix = $resolver->getDefaultSuffix();
    	 
    	$resolver->setDefaultSuffix('pxml.phtml');
    	$paths = $resolver->getPaths();
    	 
    	$renderer = $this->getLocator('Zend\View\Renderer\PhpRenderer'); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
    	$renderer->setResolver($resolver);
    	$pcRules = $renderer->render($pcRulesXml);
    	/* @var $response \Zend\Http\PhpEnvironment\Response */
    	$response = $this->getResponse();
    	$response->setContent($pcRules);
    
    	return $this->getResponse();
    }
}
