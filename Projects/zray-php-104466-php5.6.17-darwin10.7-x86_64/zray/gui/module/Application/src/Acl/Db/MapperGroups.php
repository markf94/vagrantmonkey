<?php

namespace Acl\Db;

use Configuration\MapperAbstract;

class MapperGroups extends MapperAbstract {
	const LINK_TYPE_ROLE = 1;
	const LINK_TYPE_APPLICATION = 2;
	
	/**
	 * @return array
	 */
	public function findAllMappedRoles() {
		$resultSet = $this->getTableGateway()->select(array('LINK_TYPE' => self::LINK_TYPE_ROLE));
		
		$groups = array();
		foreach ($resultSet as $group) {
			$groups[$group['NAME']] = $group['LDAP_GROUP'];
		}
		
		return $groups;
	}
	
	/**
	 * @return array
	 */
	public function findAllMappedApplications() {
		$resultSet = $this->getTableGateway()->select(array('LINK_TYPE' => self::LINK_TYPE_APPLICATION));
		
		$groups = array();
		foreach ($resultSet as $group) {
			$groups[$group['NAME']] = $group['LDAP_GROUP'];
		}
		
		return $groups;
	}
	
	/**
	 * @param string $role
	 * @param string $group
	 * @return integer
	 */
	public function setRoleMapping($role, $group) {
		$result = $this->getTableGateway()->update(array('LDAP_GROUP' => $group), array('NAME' => $role));
		if (0 == $result) {
			$result = $this->getTableGateway()->insert(array('NAME' => $role, 'LDAP_GROUP' => $group, 'LINK_TYPE' => self::LINK_TYPE_ROLE));
		}
		return $result;
	}
	/**
	 * @param string $appId
	 * @param string $group
	 * @return integer
	 */
	public function setApplicationMapping($appId, $group) {
		$result = $this->getTableGateway()->update(array('LDAP_GROUP' => $group), array('NAME' => $appId));
		if (0 == $result) {
			$result = $this->getTableGateway()->insert(array('NAME' => $appId, 'LDAP_GROUP' => $group, 'LINK_TYPE' => self::LINK_TYPE_APPLICATION));
		}
		return $result;
	}
	/**
	 * @param string $name
	 * @return number
	 */
	public function deleteMapping($name) {
		return $this->getTableGateway()->delete(array('NAME' => $name));
	}
}