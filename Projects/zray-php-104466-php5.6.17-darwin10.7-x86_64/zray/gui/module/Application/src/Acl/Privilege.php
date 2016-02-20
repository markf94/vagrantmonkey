<?php

namespace Acl;

class Privilege {
    /**
     * 
     * @var int
     */
    protected $roleId;
    
    /**
     * 
     * @var int
     */
    protected $resourceId;
    
    /**
     * 
     * @var array
     */
    protected $allowedActions;
    
    /**
     * 
     * @var string
     */
    protected $roleName;
    
    /**
     * 
     * @var string;
     */
    protected $resourceName;
    
    public function __construct($data) {
        $this->setRoleId($data['role_id']);
        $this->setResourceId($data['resource_id']);
        $this->setRoleName($data['role_name']);
        $this->setResourceName($data['resource_name']);
        if ($data['allow']) {
            $this->setAllowedActions(explode(',', $data['allow']));
        }
    }
    
	/**
     * Get the $roleName
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

	/**
     * Get the $resourceName
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

	/**
     * @param string $roleName
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;
    }

	/**
     * @param \module\Application\src\Acl\string; $resourceName
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;
    }

	/**
     * Get the $roleId
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

	/**
     * Get the $resourceId
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

	/**
     * Get the $allowedActions
     * @return array
     */
    public function getAllowedActions()
    {
        return $this->allowedActions;
    }

	/**
     * @param number $roleId
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    }

	/**
     * @param number $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

	/**
     * @param array $allowedActions
     */
    public function setAllowedActions($allowedActions)
    {
        $this->allowedActions = $allowedActions;
    }
  
}

?>