<?php
namespace Prerequisites\Validator\Directive;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\DirectiveContainer,
	ZendServer\Configuration\Directives\Translator;

class Min extends AbstractValidator {
	const NOT_MIN  = 'notMin';
    const VALID = 'validDirectiveMin';
    
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var string
	 */
	protected $comparisonTarget;
	
	/**
	 * @param string $compareTo
	 * @throws Zwas_Exception
	 */
	function __construct($compareTo) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("Directive '%%name%%' value should be at least %%comparisonTarget%% (is '%%value%%')");
		$this->abstractOptions['messageTemplates'][self::NOT_MIN] = _t("Directive '%%name%%' value should be at least %%comparisonTarget%% (is '%%value%%')");
		$this->abstractOptions['messageVariables']['comparisonTarget'] = 'comparisonTarget';
		$this->abstractOptions['messageVariables']['name'] = 'name';
		
		if (! is_string($compareTo)) {
			throw new Exception(
					_t('Value passed in compareTo parameter must be fully canonicalized into a string form for validation'),
					Exception::ASSERT);
		}
		$this->comparisonTarget = trim($compareTo);
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		if (! ($value instanceof DirectiveContainer)) {
			throw new Exception(_t('Directive validation value must be a Directives_Element'),
				Exception::ASSERT);
		}
		
		$unsupportedTypes = array(
				DirectiveContainer::TYPE_BOOLEAN,
				DirectiveContainer::TYPE_INT_BOOLEAN,
				DirectiveContainer::TYPE_STRING);
		
		$comparisonDirective = clone $value;
		$comparisonDirective->setFileValue($this->comparisonTarget);
		
		$compareValue = Translator::getRealFileValue($value);
		$this->setValue(Translator::getStringFileValue($value));
		
		$this->name = $value->getName();
		
		if (in_array($value->getType(), $unsupportedTypes)) {
			$this->error(self::VALID);
			return true;
		}
		
		if ($compareValue >= Translator::getRealFileValue($comparisonDirective)) {
			$this->error(self::VALID);
			return true;
		}
		
		$this->error(self::NOT_MIN);
		return false;
	}
}
