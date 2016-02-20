<?php
namespace Prerequisites;

use Zend\Validator\AbstractValidator,
	Zend\Validator\ValidatorChain;

// it was zend_validate - need to check if change to validate chain or something else
class Validate extends ValidatorChain {

	/**
	 * @return array
	 */
	public function getValidators() {
		return $this->validators;
	}
	
	/**
	 * This validation process accumulates messages regardless of success or failure
	 * 
	 * @param mixed $value
	 * @see Zend_Validate::isValid()
	 */
	public function isValid($value)
    {
        $this->messages = array();
        $this->errors   = array();
        $result = true;
        foreach ($this->getValidators() as $element) {
            $validator = $element['instance'];
			if(! $validator->isValid($value)){
				$result = false;
			}
            $messages = $validator->getMessages();
            $this->messages = array_merge($this->messages, $messages);

            if (! $result) {
	            $this->errors = array_merge($this->errors,   array_keys($messages));
	            if ($element['breakChainOnFailure']) {
	                break;
	            }
            }
        }
        return $result;
    }
	
	
}
