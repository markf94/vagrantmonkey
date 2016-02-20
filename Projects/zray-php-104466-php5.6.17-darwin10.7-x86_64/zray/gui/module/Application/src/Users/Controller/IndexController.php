<?php

namespace Users\Controller;

use ZendServer\Log\Log;

use Zend\View\Model\ViewModel;

use Zend\Form\Factory;

use Zend\Form\Form;

use Acl\Form\GroupsMapFactory;

use Users\Forms\LdapProperties;

use ZendServer\Mvc\Controller\ActionController,
Users\Forms\ChangePassword,
Application\Module;

class IndexController extends ActionController
{
	public function indexAction() {
		
		$usersMapper = $this->getLocator('Users\Db\Mapper'); /* @var $usersMapper \Users\Db\Mapper */
		$users = $usersMapper->getUsers()->toArray();
		
		$authConfig = Module::config('authentication');
		
		$authSource = $authConfig->simple ? 'simple' : 'extended';
		$groupsAttribute = $authConfig->groupsAttribute;
		$acl = $this->getLocator('ZendServerAcl'); /* @var $acl \ZendServer\Permissions\AclQuery */
		
		$changePasswordForm = new ChangePassword();
		$passwordElement = $changePasswordForm->get('password');
		$passwordElement->setAttribute('description', '<em>' . _t('Enter the \'' . $acl->getIdentity()->getIdentity() . '\' current password for authentication') . '</em>');
		$attributes = $passwordElement->getAttributes();
		
		$groupsMappingForm = null;
		$ldapPropertiesForm = new LdapProperties();
		$data = Module::config('zend_server_authentication')->toArray();
		
		$mapper = $this->getLocator('Acl\Db\MapperGroups'); /* @var $mapper \Acl\Db\MapperGroups */
		$mappedRoles = $mapper->findAllMappedRoles();
		if (Module::config('authentication', 'simple')) {
			$data['adminRoleGroup'] = isset($mappedRoles[Module::ACL_ROLE_ADMINISTRATOR]) ? $mappedRoles[Module::ACL_ROLE_ADMINISTRATOR] : '';
		} else {
			$mappedGroupsFactory = $this->getLocator('Acl\Form\GroupsMappingFactory'); /* @var $mappedGroupsFactory \Acl\Form\GroupsMappingFactory */
			$groupsMappingForm = $mappedGroupsFactory->createForm(array('input_filter' => array()));
			$data['adminRoleGroup'] = $mappedRoles[Module::ACL_ROLE_ADMINISTRATOR];
			$data['groupsAttribute'] = $groupsAttribute;
			if ($data['useSsl']) {
				$data['encryption'] = 'ssl';
			} elseif ($data['useStartTls']) {
				$data['encryption'] = 'tls';
			} else {
				$data['encryption'] = 'none';
			}
			
		}

		$ldapPropertiesForm->setData(array('ldap' => $data));
		
		
		$isAllowedToChangeUserPassword = $acl->isAllowed('route:UsersWebAPI', 'userSetPassword');
		
		$filteredUsers = array();
		foreach ($users as $user) {
			$user['CAN_CHANGE'] = ($isAllowedToChangeUserPassword || $user['NAME'] == $acl->getIdentity()->getIdentity());
			$filteredUsers[] = $user;
		}
		// sorting by name 
		usort($filteredUsers, function(array $user1, array $user2) {
			return strcasecmp($user1['NAME'], $user2['NAME']);
		});

		$viewModel = new ViewModel();
		$viewModel->setTemplate("users/index/{$authSource}");
		$viewModel->setVariables(array('pageTitle' => 'Users',
					 'pageTitleDesc' => '',  /* Daniel */
					'groupsMappingForm' => $groupsMappingForm, 'authSource' => $authSource,
			'users' => $filteredUsers, 'changePasswordForm' => $changePasswordForm, 'ldapPropertiesForm' => $ldapPropertiesForm,
			'isAllowedToChangeUserPassword' => $isAllowedToChangeUserPassword));
		return $viewModel;
	}
}
