<?php

namespace Acl;

class Role {
    
    protected $id;
    protected $parentId;
    protected $parentName;
    protected $name;
	
	public function __construct($data) {
	    $this->setId($data['role_id']);
	    $this->setParentId($data['role_parent']);
	    $this->setName($data['role_name']);
	    if (isset($data['parent_name'])) {
	        $this->setParentName($data['parent_name']);
	    }
	}
	
	/**
	 * Get the $parentName
	 */
	public function getParentName()
	{
	    return $this->parentName;
	}
	
	/**
	 * @param field_type $parentName
	 */
	public function setParentName($parentName)
	{
	    $this->parentName = $parentName;
	}
	 
	/**
     * Get the $id
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * Get the $parentId
     */
    public function getParentId()
    {
        return $this->parentId;
    }

	/**
     * Get the $name
     */
    public function getName()
    {
        return $this->name;
    }

	/**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

	/**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

	/**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

?>