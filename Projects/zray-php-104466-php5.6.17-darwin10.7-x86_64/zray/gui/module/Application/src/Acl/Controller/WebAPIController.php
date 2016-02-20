<?php

namespace Acl\Controller;

use Application\Module;

use WebAPI\Exception;

use ZendServer\Log\Log;

use ZendServer\Mvc\Controller\WebAPIActionController;

class WebAPIController extends WebAPIActionController
{
	public function aclSetGroupsAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('app_groups' => array()));
		$this->validateMandatoryParameters($params, array('role_groups'));
		
		$roleGroups = $this->validateArray($params['role_groups'], 'role_groups');
		if (! isset($params['role_groups'][Module::ACL_ROLE_ADMINISTRATOR])
				|| (0 == strlen($params['role_groups'][Module::ACL_ROLE_ADMINISTRATOR]))) {
			throw new Exception('An administrator role group must be specified', Exception::INVALID_PARAMETER);
		}
		
		$applicationGroups = array();
		if (isset($params['app_groups'])) {
			$applicationGroups = $this->validateArray($params['app_groups'], 'app_groups');
		}
		
		$groupsMapper = $this->getLocator('Acl\Db\MapperGroups'); /* @var $groupsMapper \Acl\Db\MapperGroups */
		foreach ($roleGroups as $name => $group) {
			if (0 < strlen($group)) {
				$groupsMapper->setRoleMapping($name, $group);
			} else {
				$groupsMapper->deleteMapping($name);
			}
		}
		foreach ($applicationGroups as $name => $group) {
			if (0 < strlen($group)) {
				$groupsMapper->setApplicationMapping($name, $group);
			} else {
				$groupsMapper->deleteMapping($name);
			}
		}
		return array('applicationGroups' => $applicationGroups, 'roleGroups' => $roleGroups);
	}
}
