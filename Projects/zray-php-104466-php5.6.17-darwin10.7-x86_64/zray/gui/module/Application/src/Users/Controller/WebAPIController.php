<?php

namespace Users\Controller;

use Zend\Http\PhpEnvironment\Response;

use Zend\Authentication\Adapter\Ldap;

use ZendServer\Exception;

use ZendServer\FS\FS;

use Zend\Config\Config;

use Application\Module;

use Zend\Config\Writer\Ini;

use Zend\Config\Factory;

use ZendServer\Log\Log;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use  Users\Forms\ChangePassword;

use ZendServer\Mvc\Controller\WebAPIActionController;
use Zend\Ldap\Exception\LdapException;

class WebAPIController extends WebAPIActionController
{
	public function userAuthenticationSettingsAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array('ldap' => array()));
		$this->validateMandatoryParameters($params, array('type', 'ldap', 'password'));
		
		$this->validateAllowedValues($params['type'], 'type', array('simple', 'extended'));
		$ldap = $this->validateArray($params['ldap'], 'ldap');
		
		$encryption = 'none';
		if (isset($ldap['encryption'])) {
			$encryption = $ldap['encryption'];
			unset($ldap['encryption']);
		}
		
		switch ($encryption) {
			case 'ssl':
				$ldap['useSsl'] = '1';
				$ldap['useStartTls'] = '0';
				break;
			case 'tls':
				$ldap['useSsl'] = '0';
				$ldap['useStartTls'] = '1';
				break;
			default :
				$ldap['useSsl'] = '0';
				$ldap['useStartTls'] = '0';
		}
		
		$this->validateString($params['password'], 'password');
		
		try {
			$identity = $this->Authentication()->getIdentity()->getIdentity();
			if (! $this->Authentication()->authenticateOnly($identity, $params['password'])){
				throw new \WebAPI\Exception("Validation for '{$identity}' failed", \WebAPI\Exception::WRONG_PASSWORD);
			}
		} catch (\WebAPI\Exception $e) {
			$this->auditMessage(Mapper::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			throw $e;
		} catch (\Exception $e) {
			$errorMsg = _t("%s failed: %s", array($this->getCmdName(),$e->getMessage()));
			$this->auditMessage(Mapper::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $errorMsg)));
			throw new \WebAPI\Exception($errorMsg, \WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		$ldapConfig = Module::config('zend_server_authentication');
		$authConfig = Module::config('authentication');

		$ldapSettings = array();
		
		if ($params['type'] == 'simple') {
			$authConfig->merge(new Config(array('simple' => '1')));
		} else {
			$newAuthConfig = new Config(array('simple' => '0'));
			if (isset($ldap['groupsAttribute'])) {
				$newAuthConfig->merge(new Config(array('groupsAttribute' => $ldap['groupsAttribute'])));
				unset($ldap['groupsAttribute']);
			}
			$authConfig->merge($newAuthConfig);
			
			$ldapSettings = array_intersect_key($ldap, $ldapConfig->toArray());
			$newLdapConfig = new Config($ldapSettings, true);
			$ldapConfig->merge($newLdapConfig);
			
			$bindname = isset($ldap['username']) && $ldap['username'] ? $ldap['username'] : null; 
			$password = isset($ldap['password']) && $ldap['password'] ? $ldap['password'] : null; 
			
			if ($bindname) {
				//// test new settings
				$authAdapter = new Ldap(array('test' => $ldapConfig->toArray()), $bindname, $password);
				$result = $authAdapter->authenticate();
				if (! $result->isValid()) {
					$this->auditMessage(Mapper::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED,
							array($this->filterAuthConfigForMessage($newLdapConfig->toArray()), $result->getMessages()));
					Log::err($result->getMessages());
					throw new \WebAPI\Exception(_t('Could not connect to Ldap server: %s', array(current($result->getMessages()))), \WebAPI\Exception::AUTH_ERROR); 
				}
			} else {
				/// anonymous bind
				$ldapTest = new \Zend\Ldap\Ldap($ldapConfig->toArray());
				try {
					$ldapTest->bind($bindname, $password);
				} catch (LdapException $ex) {
					$this->auditMessage(Mapper::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED,
							array($this->filterAuthConfigForMessage($newLdapConfig->toArray()), $ex->getMessage()));
					Log::err($ex->getMessage());
					throw new \WebAPI\Exception(_t('Could not connect to Ldap server: %s', array($ex->getMessage())), \WebAPI\Exception::AUTH_ERROR);
				}				
			}
			
			
			try {
				$adminRoleGroup = $ldap['adminRoleGroup'];
				$groupsMapper = $this->getLocator('Acl\Db\MapperGroups'); /* @var $groupsMapper \Acl\Db\MapperGroups */
				$groupsMapper->setRoleMapping(Module::ACL_ROLE_ADMINISTRATOR, $adminRoleGroup);
			} catch (Exception $ex) {
				$this->auditMessage(Mapper::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED,
						array($this->filterAuthConfigForMessage($newLdapConfig->toArray()), $ex->getMessage()));
				Log::err($ex->getMessage());
				Log::debug($ex);
				throw new \WebAPI\Exception($ex->getMessage(), \WebAPI\Exception::INTERNAL_SERVER_ERROR, $ex);
			}
		}
		
		$this->getGuiConfigurationMapper()->setGuiDirectives($authConfig->toArray() + $ldapConfig->toArray());
		$this->getResponse()->setStatusCode(Response::STATUS_CODE_202);
		$this->auditMessage(Mapper::AUDIT_GUI_CHANGE_AUTHENTICATION_SETTINGS, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY,
					array($newLdapConfig ? $this->filterAuthConfigForMessage($newLdapConfig->toArray()) : array()));
		return array('authConfig' => $authConfig->toArray());
	}
	
	
	/**
	 * Change the password for current session user
	 */
	public function setPasswordAction() {
	    $this->isMethodPost();
	    $params = $this->getParameters();
	    $this->validateMandatoryParameters($params, array('password', 'newPassword' , 'confirmNewPassword'));
	    $authService = $this->getLocator('Zend\Authentication\AuthenticationService');
	    $params['username'] = $authService->getIdentity()->getUsername();
	    return $this->changePassword($params);
	}
	
	public function userSetPasswordAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('username', 'password', 'newPassword', 'confirmNewPassword'));
		return $this->changePassword($params);
		
	}
	
	protected function changePassword($params) {
	    $changePasswordForm = $this->getLocator('Users\Forms\ChangePassword');
	    $changePasswordForm->setData($params);
	    if (! $changePasswordForm->isValid()) {
	        $nonValidElements = '';
	        foreach ($changePasswordForm->getMessages() as $field => $errors) {
	            if (!$errors) continue;
	            if (is_array($errors)) {
	                foreach ($errors as $type => $error) {
	                    $nonValidElements .= $field . ': ' . $error;
	                }
	            }
	        }
	        $errorMsg = _t("Invalid parameters: " . $nonValidElements);
	        $this->auditMessage(Mapper::AUDIT_GUI_CHANGE_PASSWORD, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('username' => $params['username'],'errorMessage' => $errorMsg)));
	        throw new \WebAPI\Exception($errorMsg, \WebAPI\Exception::INVALID_PARAMETER);
	    }
	    
	    if ($params['confirmNewPassword'] != $params['newPassword']) {
	        $errorMsg = _t('New password should be identical to the confirmation password');
	        $this->auditMessage(Mapper::AUDIT_GUI_CHANGE_PASSWORD, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('username' => $params['username'],'errorMessage' => $errorMsg)));
	        throw new \WebAPI\Exception($errorMsg, \WebAPI\Exception::WRONG_PASSWORD);
	    }
	    
	    try {
	        $identity = $this->Authentication()->getIdentity();/* @var $identity \Users\Identity */	        
	        if (! $this->Authentication()->authenticateOnly($identity->getUsername(), $params['password'])){
	            throw new \WebAPI\Exception("The current password for user '{$identity->getUsername()}' is incorrect", \WebAPI\Exception::WRONG_PASSWORD);
	        }
	        
	        $usersMapper = $this->getLocator()->get('Users\Db\Mapper'); /* @var $usersMapper \Users\Db\Mapper */
	        $user = $usersMapper->findUserByName($params['username']);
	        if (! $this->isAclAllowed('data:useMultipleUsers') && $user['ROLE'] != Module::ACL_ROLE_ADMINISTRATOR) {
	        	throw new Exception(_t('User %s is disabled', array($params['username'])));
	        }
	        
	        $usersMapper->setUser($params['username'], $params['newPassword']);
	    } catch (\Exception $e) {
	    	Log::err($e->getMessage());
	    	Log::debug($e);
	        $errorMsg = _t("%s failed: %s", array($this->getCmdName(),$e->getMessage()));
	        $this->auditMessage(Mapper::AUDIT_GUI_CHANGE_PASSWORD, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('username' => $params['username'],'errorMessage' => $errorMsg)));
	        throw new \WebAPI\Exception($e->getMessage(), \WebAPI\Exception::INTERNAL_SERVER_ERROR);
	    }
	    $this->auditMessage(Mapper::AUDIT_GUI_CHANGE_PASSWORD, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array('username' => $params['username'])));
	    
	    return array('userName' => $params['username']);
	}
	

	/**
	 * @param array $authConfig
	 * @return array
	 */
	private function filterAuthConfigForMessage($authConfig) {
		return array_diff_key($authConfig, array('password' => false));
	}
	
}
