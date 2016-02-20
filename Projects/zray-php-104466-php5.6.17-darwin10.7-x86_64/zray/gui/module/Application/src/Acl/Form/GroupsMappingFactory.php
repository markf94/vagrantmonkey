<?php

namespace Acl\Form;

use Zend\Form\Factory;

class GroupsMappingFactory extends Factory {
	/**
	 * @var \Acl\Db\Mapper
	 */
	private $aclMapper;
	/**
	 * @var \Acl\Db\MapperGroups
	 */
	private $groupsMapper;
	/**
	 * @var \Deployment\Model
	 */
	private $deploymentModel;
	
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Form\Factory::createForm()
	 */
	public function createForm($spec) {
		
		$elements = array();
		foreach ($this->getAclMapper()->getRoles() as $role) { /* @var $role \Acl\Role */
			if ($role->getParentId()) {
				$field = array('spec' => array(
						'name' => $role->getName(),
						'options' => array(
							'label' => ucfirst($role->getName()),
						),
						'attributes' => array(
							'id' => "role_groups-{$role->getName()}",
						)
				));
				
				$elements[ucfirst($role->getName())]= $field;
			}
		
		}
		
		// sort the elements by key
		ksort($elements);
		 
		$rolesFieldset = array('spec' => array(
				'name' => 'role_groups',
				'options' => array(
						'label' => _t('Roles to groups mapping'),
						'description' => _t('Enter group names for mapping permissions to your organization\'s Active Directory groups\' membership')
				),
				'elements' => $elements
		));
		 
		$elements = array();
		foreach ($this->getDeploymentModel()->getAllApplicationsInfo()->setHydrateClass('Deployment\Application\InfoContainer')
					as $appInfo) { /* @var $appInfo \Deployment\Application\InfoContainer */

			$field = array('spec' => array(
					'name' => $appInfo->getApplicationId(),
					'attributes' => array(
						'id' => "app_groups-{$appInfo->getApplicationId()}",
					),
					'options' => array(
							'label' => ucfirst($appInfo->getUserApplicationName()),
							'description' => htmlentities($appInfo->getBaseUrl())
					)
			));
		
			$elements[ucfirst($appInfo->getUserApplicationName())]= $field;
		}
		
		// sort the elements by key
		ksort($elements);
		 
		$appsFieldset = array('spec' => array(
				'name' => 'app_groups',
				'options' => array(
					'label' => _t('Applications to groups mapping'),
					'description' => _t('Enter group names for mapping applications\' access rights to your organization\'s Active Directory groups\' membership. Note that a user that has a group mapped to an application will implicitly be assigned the developerLimited role')
				),
				'elements' => $elements
		));
		 
		$spec['fieldsets'][] = $rolesFieldset;
		$spec['fieldsets'][] = $appsFieldset;
		$form = parent::createForm($spec);
		$form->prepare();
		$form->setData($this->getMappedData());
		return $form;
	}
	
	
	/**
	 * @return \Acl\Db\Mapper $aclMapper
	 */
	public function getAclMapper() {
		return $this->aclMapper;
	}

	/**
	 * @return \Acl\Db\MapperGroups $groupsMapper
	 */
	public function getGroupsMapper() {
		return $this->groupsMapper;
	}

	/**
	 * @return \Deployment\Model $deploymentModel
	 */
	public function getDeploymentModel() {
		return $this->deploymentModel;
	}

	/**
	 * @param \Acl\Db\Mapper $aclMapper
	 * @return GroupsMappingFactory
	 */
	public function setAclMapper($aclMapper) {
		$this->aclMapper = $aclMapper;
		return $this;
	}

	/**
	 * @param \Acl\Db\MapperGroups $groupsMapper
	 * @return GroupsMappingFactory
	 */
	public function setGroupsMapper($groupsMapper) {
		$this->groupsMapper = $groupsMapper;
		return $this;
	}

	/**
	 * @param \Deployment\Model $deploymentModel
	 * @return GroupsMappingFactory
	 */
	public function setDeploymentModel($deploymentModel) {
		$this->deploymentModel = $deploymentModel;
		return $this;
	}

	/**
	 * @return array
	 */
	private function getMappedData() {
		$mappedData = array();
		$mappedRoles = $this->getGroupsMapper()->findAllMappedRoles();
		foreach ($mappedRoles as $key => $group) {
			$mappedData['role_groups'][$key]= $group;
		}
		$mappedApplications = $this->getGroupsMapper()->findAllMappedApplications();
		foreach ($mappedApplications as $key => $group) {
			$mappedData['app_groups'][$key]= $group;
		}
		return $mappedData;
	}
}