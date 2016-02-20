<?php

namespace Configuration\Controller;

use ZendServer\Mvc\Controller\ActionController,
Application\Module;
use Zend\View\Model\ViewModel;

class ZendComponentsController extends ActionController {
	
	const COMPONENT_ZEND_OPCACHE = 'Zend OPcache';
	const COMPONENT_ZEND_OPTIMIZER = 'Zend Optimizer+';
	const COMPONENT_ZEND_DATA_CACHE = 'Zend Data Cache';
	const COMPONENT_ZEND_PAGE_CACHE = 'Zend Page Cache';
	const COMPONENT_ZEND_URL_TRACKING = 'Zend Server Z-Ray';
	
	public function indexAction() {	
	    $acl = $this->getLocator('ZendServerAcl'); /* @var $acl \Zend\Acl\Acl */
	    $isAllowedToSaveDirectives = $acl->isAllowed('route:ConfigurationWebApi', 'configurationStoreDirectives');
	    $isAllowedToEnable = $acl->isAllowed('route:ConfigurationWebApi', 'configurationExtensionsOn');
	    $isAllowedToDisable = $acl->isAllowed('route:ConfigurationWebApi', 'configurationExtensionsOff');
	    
	    $componentsView = new ViewModel();
		$componentsView->setVariable('isAllowedToSaveDirectives', $isAllowedToSaveDirectives);
		$componentsView->setVariable('isAllowedToEnable', $isAllowedToEnable);
		$componentsView->setVariable('isAllowedToDisable', $isAllowedToDisable);

    	$componentsView->setVariable('pageTitle', 'Components');
		$componentsView->setVariable('pageTitleDesc', ''); /* Daniel */
		return $componentsView;
	}
}

