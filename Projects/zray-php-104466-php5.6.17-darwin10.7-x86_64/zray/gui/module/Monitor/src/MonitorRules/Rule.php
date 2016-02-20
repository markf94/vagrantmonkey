<?php

namespace MonitorRules;


class Rule 
{
    /**
     * @var int
     */
    protected $id;
    
    /**
     * @var int
     */
    protected $parentId;
    
    /**
     * 
     * @var int
     */
    protected $appId;
    
    /**
     * 
     * @var string
     */
    protected $name;
    
    /**
     * 
     * @var boolean
     */
    protected $enabled;
    
    /**
     * 
     * @var string
     */
    protected $type;
    
    /**
     * 
     * @var string
     */
    protected $description;
    
    /**
     * 
     * @var string
     */
    protected $url;

    /**
     *
     * @var integer
     */
    protected $creator;
        
    /**
     *
     * @var array[\MonitorRules\Trigger]
     */
    protected $triggers;

    /**
     *
     * @var array[\MonitorRules\Condition]
     */    
    protected $conditions;   
    
	public function __construct($rule, $key) 
    {
    	if (!$rule) {
    		return;
    	}
    	
    	$this->setId($rule['RULE_ID']);
    	$this->setType($rule['RULE_TYPE_ID']);
        $this->setParentId($rule['RULE_PARENT_ID']);
        $this->setAppId($rule['APP_ID']);
        $this->setName($rule['NAME']);
        $this->setEnabled($rule['ENABLED']);
        $this->setDescription($rule['DESCRIPTION']);
        $this->setUrl($rule['URL']);
        $this->setCreator($rule['CREATOR']);
        
        if (isset($rule['triggers']) && is_array($rule['triggers'])) {
            $triggers = $this->createTriggers($rule['triggers']);
            $this->setTriggers($triggers);
        }

        if (isset($rule['conditions']) && is_array($rule['conditions'])) {
        	$conditions = $this->createConditions($rule['conditions']);
        	$this->setConditions($conditions);
        }
       
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
     * Get the $parentId
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

	/**
     * Get the $appId
     * @return int
     */
    public function getAppId()
    {
        return $this->appId;
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
     * Get the $enabled
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

	/**
     * Get the $type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

	/**
     * Get the $description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Get the $triggers
     */
    public function getTriggers()
    {
        return $this->triggers;
    }

    /**
     * Get the $conditions
     */
    public function getConditions()
    {
    	return $this->conditions;
    }
    
	/**
	 * @return string $url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return integer $creator
	 */
	public function getCreator() {
		return $this->creator;
	}
	
	/**
	 * @param string $url
	 * @return Rule
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}

	/**
	 * @param integer $creator
	 * @return Rule
	 */
	public function setCreator($creator) {
		$this->creator = $creator;
		return $this;
	}
	
	/**
     * @param field_type $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

	/**
     * @param field_type $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

	/**
     * @param field_type $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

	/**
     * @param field_type $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

	/**
     * @param field_type $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

	/**
     * @param field_type $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

	/**
     * @param field_type $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * @param multitype:\MonitorRules\Trigger  $triggers
     */
    public function setTriggers($triggers)
    {
        $this->triggers = $triggers;
    }
    
    /**
     * Get all properties as array
     * @return multitype:number \MonitorRules\string \MonitorRules\int \MonitorRules\integer
     */
    public function getProperties() {
        return array(
            'rule_type_id' => $this->getType(),
            'rule_parent_id' => $this->getParentId(),
            'app_id' => $this->getAppId(),
            'name' => $this->getName(),
            'enabled' => $this->getEnabled() ? 1 : 0,
            'description' => $this->getDescription(),
            'url' => $this->getUrl(),
            'creator' => $this->getCreator(),
        );
    }
    
    protected function createTriggers(array $triggers)
    {
        $objTriggers = array();
        foreach ($triggers as $triggerId => $trigger) {
            $objTriggers[$triggerId] = new Trigger($trigger);
        }
        return $objTriggers;
    }

    /**
     * @param multitype:\MonitorRules\Condition  $conditions
     */
    public function setConditions($conditions)
    {
    	$this->conditions = $conditions;
    }
    
    protected function createConditions(array $conditions)
    {
    	$objConditions = array();
    	foreach ($conditions as $conditionId => $condition) {
    		$objConditions[$conditionId] = new Condition($condition);
    	}
    	return $objConditions;
    }    
}

