<?php

namespace WebAPI\Controller;

use Users\Identity,
	Users\IdentityAwareInterface,
	ZendServer\Mvc\Controller\ActionController,
	WebAPI\Forms\AddWebApiKey,
	Zend\View\Model\ViewModel,
	Application\Module;

class ApiKeysController extends ActionController implements IdentityAwareInterface
{
	/**
	 * @var Identity
	 */
	private $identity;
	
    /* (non-PHPdoc)
	 * @see \Users\IdentityAwareInterface::setIdentity()
	 */
	public function setIdentity(Identity $identity) {
		$this->identity = $identity;
	}
		
	public function indexAction() {
		$isAllowedToManageKeys = $this->isAclAllowed('route:KeysWebAPI', 'apiKeysAddKey');
		
		if (! $this->isAclAllowed('data:useWebApiKeys')) {
			$viewModel = new ViewModel();
			$viewModel->setTemplate('web-api/api-keys/index-marketing');
			
			$key = $this->getWebapiMapper()->findKeyByName('ZendStudio');
			$viewModel->setVariable('key', $key);
			$viewModel->setVariable('isAllowedToManageKeys', $isAllowedToManageKeys);			
		
			return $viewModel;
		}
		
    	if (! $isAllowedToManageKeys) {
    		$key = $this->getWebapiMapper()->findKeyByName($this->identity->getUsername());
    		
    		$viewModel = new ViewModel();
    		$viewModel->setTemplate('web-api/api-keys/index-dev');
    		
    		$viewModel->setVariable('key', $key);
    		
    		return $viewModel;
    	}

    	if (! $this->isAclAllowed('route:KeysWebAPI', 'apiKeysGetList')) {
    		return $viewModel;
    	}
    	
    	$apiKeysView = $this->forward()->dispatch('KeysWebAPI-1_3', array('action' => 'apiKeysGetList')); /* @var $apiKeysView \Zend\View\Model\ViewModel */
    	$apiKeysView->setTemplate('web-api/api-keys/index');// Restoring original route
    	
    	$form = new AddWebApiKey();
    	
    	$usersMapper = $this->getLocator('Users\Db\Mapper'); /* @var $usersMapper \Users\Db\Mapper */
    	$users = $usersMapper->getUsers();
    	$allUsers = array();
    	foreach ($users as $user) {
    		$allUsers[$user['NAME']] = $user['ID'];
    	}
    	$form->setUsers($allUsers);
    	
    	$apiKeysView->setVariable('form', $form);
    	
    	$authConfig = Module::config('authentication');
    	$isSimpleAuth = $authConfig->simple;

    	$usersList = $this->getUsersMapper()->getActiveUsers()->toArray();
    	$users = array();
    	foreach ($usersList as $user) {
    		$users[] = $user['NAME'];
    	}
    	
    	$apiKeysView->setVariable('users', $users);
    	$apiKeysView->setVariable('isSimpleAuth', $isSimpleAuth);
    	$apiKeysView->setVariable('pageTitle', 'Web API Keys');
		$apiKeysView->setVariable('pageTitleDesc', ''); /* Daniel */
    	return $apiKeysView;
    	
	}
	
}
