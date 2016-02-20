<?php

namespace Acl;

class Resource {
    /**
     * 
     * @var int
     */
    protected $id;
    
    /**
     * 
     * @var string
     */
    protected $name;
    
    public function __construct($data) {
        $this->setId($data['resource_id']);
        $this->setName($data['resource_name']);
    } 
	/**
     * Get the $id
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * Get the $name
     */
    public function getName()
    {
        return $this->name;
    }

	/**
     * @param number $id
     */
    public function setId($id)
    {
        $this->id = $id;
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