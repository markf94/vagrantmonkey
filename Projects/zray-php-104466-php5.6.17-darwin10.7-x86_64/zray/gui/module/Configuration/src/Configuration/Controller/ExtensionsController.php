<?php

namespace Configuration\Controller;

use ZendServer\Mvc\Controller\ActionController,
Zend\Stdlib\Parameters;

class ExtensionsController extends ActionController {
	
    public function phpExtensionsAction() {    	
    	$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
    	$request->setQuery(new Parameters(array('type' => 'PHP') + $request->getQuery()->toArray()));
    	
    	$acl = $this->getLocator('ZendServerAcl'); /* @var $acl \Zend\Acl\Acl */
    	$isAllowedToSaveDirectives = $acl->isAllowed('route:ConfigurationWebApi', 'configurationStoreDirectives');
    	$isAllowedToEnable = $acl->isAllowed('route:ConfigurationWebApi', 'configurationExtensionsOn');
    	$isAllowedToDisable = $acl->isAllowed('route:ConfigurationWebApi', 'configurationExtensionsOff');
    	
    	$extensionsView = $this->forward()->dispatch('ConfigurationWebApi-1_3', array('action' => 'configurationExtensionsList')); /* @var $extensionsView \Zend\View\Model\ViewModel */
    	$extensionsView->setTemplate('configuration/extensions/php-extensions');// Restoring original route
    	$extensionsView->setVariable('isAllowedToSaveDirectives', $isAllowedToSaveDirectives);
    	$extensionsView->setVariable('isAllowedToEnable', $isAllowedToEnable);
    	$extensionsView->setVariable('isAllowedToDisable', $isAllowedToDisable);
    	
    	$extensionsView->setVariable('pageTitle', 'Extensions');
		$extensionsView->setVariable('pageTitleDesc', ''); /* Daniel */
    	return $extensionsView;
    }    
    
}
