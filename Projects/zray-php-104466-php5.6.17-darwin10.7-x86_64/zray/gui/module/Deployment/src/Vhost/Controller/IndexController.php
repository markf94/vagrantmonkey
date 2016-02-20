<?php

namespace Vhost\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Vhost\Form\Vhost;
use ZendServer\Configuration\Manager;
use Deployment\Validator\VirtualHostPort;
use ZendServer\FS\FS;
use Application\Module;

class IndexController extends ActionController
{
    public function indexAction() {
    	$output = $this->forward()->dispatch('VhostWebAPI-1_6', array('action' => 'vhostGetStatus'));
    	$output->setTemplate('vhost/index/index.phtml');
    	
    	$deploymentModel = $this->getLocator()->get('Deployment\Model');
    	$output->setVariable('supportedByWebserver', $deploymentModel->isDeploySupportedByWebserver());
    	$vhostForm = new Vhost('vhost-form');
    	
    	$schemaContent = $this->getVhostMapper()->getSchemaContent();
    	$vhostForm->prepareElements();
    	
    	if (FS::isAix()) {
    		$vhostForm->setData(array('port' => '10080'));
    	} elseif (FS::isMac()) {
    	    $vhostForm->setData(array('port' => '10088'));
    	}
    	
    	$output->setVariable('defaultTemplate', $schemaContent);
    	$output->setVariable('defaultSslTemplate', $this->getVhostMapper()->getSSLSchemaContent());
    	$output->setVariable('vhostForm', $vhostForm);
    	
    	$manageTemplate = $this->getVhostMapper()->getManageTemplate();
    	$output->setVariable('manageTemplate', $manageTemplate);
    	
    	$system = new Manager();
    	$output->setVariable('defaultServerPort', $system->getDefaultListenPort());
    	$output->setVariable('useCertAppName', FS::isAix());
    	
    	$notificationsMapper = $this->getLocator()->get('Notifications\Db\NotificationsMapper');
    	$notifications = $notificationsMapper->getNotificationByType(\Notifications\NotificationContainer::TYPE_VHOST_CONFIG_FAILED);
    	if ($notifications->count() > 0) {
    		$currNotification = $notifications->current();
    		$extraData = $currNotification->getExtraData();
    		$output->setVariable('generalError', $extraData[0]);
    	}
    	
    	$vhostPortValidator = new VirtualHostPort();
    	$output->setVariable('reservedPorts', $vhostPortValidator->getBlockedPorts());
    	$output->setVariable('perPage', Module::config('list', 'resultsPerPage'));
    	
    	$vhostDictionary = $this->getLocator()->get('Vhost\Filter\Dictionary'); /* @var $vhostDictionary \Vhost\Filter\Dictionary */
    	
    	$portsResult = $this->getVhostMapper()->getVhostPorts();
    	$ports = array();
    	foreach ($portsResult as $port) {
    		$ports[$port->getPort()] = $port->getPort();
    	}
    	
    	$output->setVariable('internalFilters', array(
    		'ssl' => array('name' => 'ssl', 'label' => _t('SSL Status'), 'options' => $vhostDictionary->getSSLDictionaryForFiltering()),
    		'type' => array('name' => 'type', 'label' => _t('Type'), 'options' => $vhostDictionary->getTypeDictionaryForFiltering()),
    		'deployment' => array('name' => 'deployment', 'label' => _t('Deployment'), 'options' => $vhostDictionary->getDeploymentDictionaryForFiltering()),
    		'port' => array('name' => 'port', 'label' => _t('Port'), 'options' => $ports),
    	));
    	$output->setVariable('externalFilters', array());
    	
    	$mapper = $this->getLocator('ZendServer\Filter\Mapper');
    	$existingFilters = array();
    	foreach ($mapper->getByType('vhost') as $filter) { /* @var $filter \ZendServer\Filter\Filter */
    		$existingFilters[$filter->getName()] = array('id' => $filter->getId(),
    				'name' => $filter->getName(), 'custom' => $filter->getCustom(), 'data' => $filter->getData());
    	}
    	 
    	$output->setVariable('existingFilters', $existingFilters);
    	
    	$output->setVariable('pageTitle', 'Virtual Hosts');
		$output->setVariable('pageTitleDesc', ''); /* Daniel */
    	return $output;
    }    
}
