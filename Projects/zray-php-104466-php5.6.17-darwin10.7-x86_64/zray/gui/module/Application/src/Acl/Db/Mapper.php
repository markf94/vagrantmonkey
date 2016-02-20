<?php
namespace Acl\Db;

use Zend\Db\Select;

use \Configuration\MapperAbstract;
use \ZendServer\Log\Log, \Acl\Role;

class Mapper extends MapperAbstract {
	
	/**
	* @var \Zend\Db\TableGateway\TableGateway
	*/
	protected $rolesTable;
	
	/**
	* @var \Zend\Db\TableGateway\TableGateway
	*/
	protected $resourcesTable;
	
	/**
	* @var \Zend\Db\TableGateway\TableGateway
	*/
	protected $privilegesTable;
	
	/**
	 * @return \ZendServer\Set
	 */
	public function getRoles() {
		// get roles from the DB
		/* @var \Zend\Db\Sql\Select */
		$select = new \Zend\Db\Sql\Select();
		$select->from($this->getRolesTable()->getTable());
		$select->order(array('ROLE_ID' => 'ASC'));
		
		$roles = $this->getRolesTable()->selectWith($select)->toArray();
		
		foreach ($roles as &$role) {
			if ($role['role_parent']) {
				$role['parent_name'] = $this->getRoleParentName($roles, $role['role_parent']);
			}
		}
		unset($role);
		return new \ZendServer\Set($roles, '\Acl\Role');
	}
	
	public function getResources() {
		$resources = $this->getResourcesTable()->select()->toArray();
		return new \ZendServer\Set($resources, '\Acl\Resource');
	}
	
	public function getPrivileges() {
		$sql = 'SELECT priv.*, role.role_name, resource.resource_name' . 
				' FROM ' . $this->getPrivilegesTable()->getTable() . ' AS priv' .
				' JOIN ' . $this->getRolesTable()->getTable() . ' AS role USING(role_id)' .
				' JOIN ' . $this->getResourcesTable()->getTable() . ' AS resource USING(resource_id)';
		$privileges = $this->getRolesTable()->getAdapter()->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
		return new \ZendServer\Set($privileges->toArray(), '\Acl\Privilege');
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway
	 */
	public function getRolesTable()
	{
		return $this->rolesTable;
	}

	/**
	 * @return \Zend\Db\TableGateway\TableGateway
	 */
	public function getResourcesTable()
	{
		return $this->resourcesTable;
	}

	/**
	 * @return \Zend\Db\TableGateway\TableGateway
	 */
	public function getPrivilegesTable()
	{
		return $this->privilegesTable;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $rolesTable
	 * @return \Audit\Db\Mapper
	 */
	public function setRolesTable($rolesTable)
	{
		$this->rolesTable = $rolesTable;
		return $this;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $resourcesTable
	 * @return \Audit\Db\Mapper
	 */
	public function setResourcesTable($resourcesTable)
	{
		$this->resourcesTable = $resourcesTable;
		return $this;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $privilegesTable
	 * @return \Audit\Db\Mapper
	 */
	public function setPrivilegesTable($privilegesTable)
	{
		$this->privilegesTable = $privilegesTable;
		return $this;
	}
	
	/**
	 * 
	 * @param array $roles
	 * @param int $parentId
	 * @return string|NULL
	 */
	protected function getRoleParentName($roles, $parentId) {
		foreach ($roles as $role) {
			if ($parentId == $role['role_id']) {
				return $role['role_name'];
			}
		}
		return null;
	}
	
}