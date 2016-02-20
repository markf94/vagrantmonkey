<?php

namespace WebAPI\Controller;

use Audit\Db\ProgressMapper;

use Audit\AuditTypeInterface;

use Deployment\Exception,
	ZendServer\Mvc\Controller\WebAPIActionController,
	WebAPI\Forms\AddWebApiKey,
	Users\Identity,
	Users\IdentityAwareInterface;
use ZendServer\Log\Log;

class WebAPIKeysController extends WebAPIActionController implements IdentityAwareInterface
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
	
    public function apiKeysGetListAction() {
    	$this->isMethodGet();
		
		$params = $this->getParameters(array(
			'order' => 'id', 
			'direction' => 'DESC'
		));

    	$keys = $this->getWebapiMapper()->findKeys($params['order'],$params['direction']);
		
		
    	return array('keys' => $keys);
    }
    
    public function apiKeysRemoveKeyAction() {
    	$this->isMethodPost();
    	
    	$params = $this->getParameters();
    	$this->validateMandatoryParameters($params, array('ids')); 
    	$this->validateArray($params['ids'], 'ids');

    	// Unable user to remove system webAPI key 'zend-zsd'
    	$keys = $this->getWebapiMapper()->findAllKeys();
    	foreach($keys as $key) {
    		if (in_array($key->getId(), $params['ids']) && $key->getName() == 'zend-zsd') {
    			throw new \WebAPI\Exception(_t("Failed to remove the system WebAPI key"), \WebAPI\Exception::WEBAPI_KEY_REMOVE_FAILED); 
    		}
    	}
    	$this->getWebapiMapper()->deleteKeysById($params['ids']);
    	$keys = $this->getWebapiMapper()->findAllKeys();
    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_REMOVE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $params->toArray());
    	return array('keys' => $keys);
    }
    
    public function apiKeysAddKeyAction() {
    	$this->isMethodPost();
    
    	$params = $this->getParameters(array('hash' => ''));
    	$this->validateMandatoryParameters($params, array('name', 'username'));
    	
    	$this->validateString($params['name'], 'name');
    	$this->validateString($params['username'], 'username');
    	$hash = $this->validateString($params['hash'], 'hash');
    	
    	$form = new AddWebApiKey();
    	$form->setData($params);
    	if (! $form->isValid()) {
    		$errors = array();
    		foreach ($form->getMessages() as $field => $errorMessages) {
    			if (!$errorMessages) continue;
    			foreach ($errorMessages as $errorMessage) {
    				$errors[] = "$field: $errorMessage";
    			}
    		}
    		
    		throw new \WebAPI\Exception(implode(',', $errors), \WebAPI\Exception::INVALID_PARAMETER);
    	}
    	
    	unset($params['hash']);
    	
    	if ($this->getWebapiMapper()->findKeyByName($params['name'])->getUsername()) {
	    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_ADD, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, $params->toArray());
    		throw new \WebAPI\Exception(_t('This key name already exists'), \WebAPI\Exception::INVALID_PARAMETER);
    	}
    	
    	$this->getWebapiMapper()->addKey($params['name'], $params['username'], $hash);
    	if ($hash) {
    		Log::info('Hash override by user parameter');
    	}
    	
    	$key = $this->getWebapiMapper()->findKeyByName($params['name']);
    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_ADD, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $params->toArray());
    	return array('key' => $key);
    }
    
    /**
     * @internal Not documented since its ui only web-api
     */
    public function apiKeysEnableKeyAction() {
    	$this->isMethodPost();
    	
    	$params = $this->getParameters();
    	$this->validateMandatoryParameters($params, array('password'));    	

    	$this->setIdentity($this->identity);
    	$username = $this->identity->getUsername();
    	
    	$identity = $this->Authentication()->getIdentity();
    	if (! $this->Authentication()->authenticateOnly($identity->getIdentity(), $params['password'])){
	    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_ADD, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('bounduser' => $username));
    		throw new \WebAPI\Exception("The current password for user {$identity->getIdentity()} is incorrect", \WebAPI\Exception::WRONG_PASSWORD);
    	}    	
    	
    	$key = $this->getWebapiMapper()->findKeyByName($username);
    	if ($key->getId() == 0) { //key not exists - create one
    		$this->getWebapiMapper()->addKey($username, $username);
    		$key = $this->getWebapiMapper()->findKeyByName($username);
    	}
    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_ADD, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array('bounduser' => $username));
    	return array('key' => $key);    	
    }
    
    /**
     * @internal Not documented since its ui only web-api
     */
    public function apiKeysDisableKeyAction() {
    	$this->isMethodPost();
    	 
    	// temp - need to be remove and replaced by the original setIdentity
    	$authService = $this->getLocator('Zend\Authentication\AuthenticationService');
    	$this->setIdentity($authService->getIdentity());
    
    	$username = $this->identity->getUsername();
    	$key = $this->getWebapiMapper()->findKeyByName($username);
    	if ($key->getId() == 0) { //key not exists - create one
	    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_REMOVE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('bounduser' => $username));
    		throw new Exception(_t('This key not exists'));
    	}
    
    	$key = $this->getWebapiMapper()->findKeyByName($username);
    	$this->getWebapiMapper()->deleteKeysById(array($key->getId()));
    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_REMOVE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array('bounduser' => $username));
    	return array();
    }
    
    /**
     * @internal Not documented since its ui only web-api
     */
    public function apiKeysEnableStudioKeyAction() {
    	$this->isMethodPost();
    	 
    	$params = $this->getParameters();
    	$this->validateMandatoryParameters($params, array('password'));
    
    	$this->setIdentity($this->identity);
    	$username = $this->identity->getUsername();
    	 
    	$identity = $this->Authentication()->getIdentity();
    	if (! $this->Authentication()->authenticateOnly($identity->getIdentity(), $params['password'])){
    		$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_ADD, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('bounduser' => $username));
    		throw new \WebAPI\Exception("The current password for user {$identity->getIdentity()} is incorrect", \WebAPI\Exception::WRONG_PASSWORD);
    	}
    	 
    	$key = $this->getWebapiMapper()->findKeyByName('ZendStudio');
    	if ($key->getId() == 0) { //key not exists - create one
    		$this->getWebapiMapper()->addKey('ZendStudio', $username);
    		$key = $this->getWebapiMapper()->findKeyByName('ZendStudio');
    	}
    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_ADD, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array('bounduser' => $username));
    	return array('key' => $key);
    }
    
    /**
     * @internal Not documented since its ui only web-api
     */
    public function apiKeysDisableStudioKeyAction() {
    	$this->isMethodPost();
    
    	// temp - need to be remove and replaced by the original setIdentity
    	$authService = $this->getLocator('Zend\Authentication\AuthenticationService');
    	$this->setIdentity($authService->getIdentity());
    
    	$username = $this->identity->getUsername();
    	$key = $this->getWebapiMapper()->findKeyByName('ZendStudio');
    	if ($key->getId() == 0) { //key not exists - create one
    		$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_REMOVE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('bounduser' => $username));
    		throw new Exception(_t('This key not exists'));
    	}
    
    	$key = $this->getWebapiMapper()->findKeyByName('ZendStudio');
    	$this->getWebapiMapper()->deleteKeysById(array($key->getId()));
    	$this->auditMessage(AuditTypeInterface::AUDIT_WEBAPI_KEY_REMOVE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array('bounduser' => $username));
    	return array();
    }
}