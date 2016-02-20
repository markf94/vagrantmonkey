<?php

namespace MonitorRules;


class Type 
{
    /**
     * @var int
     */
    protected $id;
    
    /**
     * @var string
     */
    protected $name;
    /**
     * @var boolean
     */
    protected $enabled;
    
	public function __construct($rule, $key) 
    {
        $this->setId($rule['TYPE_ID']);
        $this->setName($rule['TYPE_NAME']);
        $this->setEnabled(isset($rule['TYPE_ENABLED']) ? $rule['TYPE_ENABLED'] : true);
    }
    
    /**
     * Get the $id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * Get the $name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
	/**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
	/**
     * @param int $id
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

	/**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}