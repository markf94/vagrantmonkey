<?php
namespace Prerequisites\Validator\Directive;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\DirectiveContainer,
	ZendServer\Configuration\Directives\Translator,
	ZendServer\Configuration\Directives\Compare,
	ZendServer\Log\Log;

class Equal extends AbstractValidator {
	const NOT_EQUAL  = 'notEqual';
	const VALID  = 'validDirectiveEquals';
    
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var string
	 */
	protected $comparisonTarget;
	
	/**
	 * @var boolean
	 */
	protected $isRequiredOn;	
	
	/**
	 * @param string $compareTo
	 * @param boolean $isRequiredOn
	 * @throws Zwas_Exception
	 */
	function __construct($equalTo, $isRequiredOn=true) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("Directive '%%name%%' should be %%comparisonTarget%% (is '%%value%%')");
		$this->abstractOptions['messageTemplates'][self::NOT_EQUAL] = _t("Directive '%%name%%' should be %%comparisonTarget%% (is '%%value%%')");
		$this->abstractOptions['messageVariables']['comparisonTarget'] = 'comparisonTarget';
		$this->abstractOptions['messageVariables']['name'] = 'name';
		
		if (! is_string($equalTo)) {
			throw new Exception(
					_t('Value passed in equalTo parameter must be fully canonicalized into a string form for validation'),
					Exception::ASSERT);
		}
		$this->comparisonTarget = trim($equalTo);
		$this->isRequiredOn = $isRequiredOn;
	}
	
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		if (! ($value instanceof DirectiveContainer)) {
			if ($this->isRequiredOn === false) {
				log::debug("skipping validation of a depreacated directive which is required to be off");
				return true;
			}
			
			throw new Exception(_t('Directive validation value must be a Directives_Element'),
				Exception::ASSERT);
		}
		
		$this->setValue(Translator::getStringFileValue($value));
		$this->name = $value->getName();
		
		if (Compare::isValueEqual($value, $this->comparisonTarget)) {
			$this->setValue($value->getFileValue());
			$this->error(self::VALID);
			return true;
		}
		
		$this->setValue($value->getFileValue());
		$this->error(self::NOT_EQUAL);
		
		return false;
	}
	
}
