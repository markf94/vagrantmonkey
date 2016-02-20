<?php

namespace Configuration\Controller;

use ZendServer\Mvc\Controller\ActionController;

use ZendServer\Exception;

use ZendServer\Configuration\Manager;

class LicenseController extends ActionController
{
    public function indexAction() {
    	$licenseMapper = $this->getLocator('Configuration\License\ZemUtilsWrapper'); /* @var $licenseMapper \Configuration\License\ZemUtilsWrapper */
    	$licenseInfo = $licenseMapper->getLicenseInfo();
    	$edition = $licenseMapper->getLicenseType();
    	$serversMapper = $this->getLocator('Servers\Db\Mapper');/* @var $serversMapper \Servers\Db\Mapper */
    	$currentNumberOfServers = $serversMapper->countAllServers();
    	
    	$capabilitiesMap = $this->capabilitiesList()->getCapabilitiesList();
    	$changesMatrix = $this->capabilitiesList()->getChangesMatrix();
    	
    	$isEvaluation = $licenseInfo->isEvaluation();
    	$manager = new Manager();
    	
    	$extraParams = \Application\Module::config('license', 'zend_gui', 'extra');
    	
    	return array('pageTitle' => 'License',
					 'pageTitleDesc' => '',  /* Daniel */
					
    				'license' => $licenseInfo,
    				'osType' => $manager->getOsType(),
    				'edition' => $edition,
    				'daysToExpired' => $licenseMapper->getLicenseExpirationDaysNum(),
    				'numberOfServers' => $currentNumberOfServers,
    				'licenseChangeEffects' => $changesMatrix,
    				'capabilitiesMap' => $capabilitiesMap,
    				'extraParams' => $extraParams,
    				'isEvaluation' => $isEvaluation,
    			);
	}
}