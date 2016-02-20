<?php

namespace MonitorRules;

class Trigger
{
	/**
	 *
	 * @var int
	 */
	protected $id;
	
    /**
     *
     * @var int
     */
    protected $severity;
    
    /**
     *
     * @var array[\MonitorRules\Condition]
     */
    
    protected $conditions;
    
    /**
     *
     * @var array[\MonitorRules\Actions]
     */
    protected $actions;
    
	public function __construct(array $trigger)
	{
	    $this->setId($trigger['TRIGGER_ID']);
	    $this->setSeverity($trigger['SEVERITY']);
	    
	    $conditions = array();
	    if (isset($trigger['conditions']) && is_array($trigger['conditions'])) {
	        $conditions = $this->createConditions($trigger['conditions']);
	    }
	    $this->setConditions($conditions);
	    
	    $actions = array();
	    if (isset($trigger['actions']) && is_array($trigger['actions'])) {
	        $actions = $this->createActions($trigger['actions']);
	    }
	    $this->setActions($actions);
	}
	
	/**
	 * Get the $severity
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
     * Get the $severity
     */
    public function getSeverity()
    {
        return $this->severity;
    }

	/**
     * Get the $conditions
     */
    public function getConditions()
    {
        return $this->conditions;
    }

	/**
     * Get the $actions
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param number $id
     */
    public function setId($id)
    {
    	$this->id = $id;
    }
    
	/**
     * @param number $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

	/**
     * @param multitype:\MonitorRules\Condition  $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

	/**
     * @param multitype:\MonitorRules\Actions  $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }
    
    protected function createConditions(array $conditions)
    {
        $objConditions = array();
        foreach ($conditions as $conditionId => $condition) {
            $objConditions[$conditionId] = new Condition($condition);
        }
        return $objConditions;
    }
    
    public function createActions(array $actions)
    {
        $objActions = array();
        foreach ($actions as $actionId => $action) {
            $objActions[$actionId] = new Action($action);
        }
        return $objActions;
    }
}