<?php

namespace MonitorRules;

class Condition
{
    /**
     * 
     * @var string
     */
    private $operation;
    
    /**
     *
     * @var string
     */
    private $attribute;
    
    /*
     * @var integer
     */
    private $id;
    
    /**
     *
     * @var string
     */
    private $operand;
	
	public function __construct(array $condition)
	{
		$this->id = $condition['CONDITION_ID'];
	    $this->operation = $condition['OPERATION'];
	    $this->attribute = $condition['ATTRIBUTE'];
	    $this->operand = $condition['OPERAND'];
	}
	
	/**
	 * Translate the attribute to the operation
	 */
	public function attributeToOperation() {
		
		switch($this->attribute) {
			case 'error-type':
				$this->operation = 'bitwise-and';
				return;
			case 'function-name':
				$this->operation = 'string-in-list';
				return;
			default:
				$this->operation = 'number-greater-than';
				return;
		}	
	}
	
	/**
     * Get the $operation
     */
    public function getOperation()
    {
        return $this->operation;
    }
    
    /**
     * Get the $operation
     */
    public function getId()
    {
    	return $this->id;
    }

	/**
     * Get the $attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

	/**
     * Get the $value
     */
    public function getOperand()
    {
        return $this->operand;
    }

	/**
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

	/**
     * @param string $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

	/**
     * @param string $value
     */
    public function setOperand($operand)
    {
        $this->operand = $operand;
    }  
}