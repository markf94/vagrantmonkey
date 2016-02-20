<?php
namespace ZendServer\Configuration\Directives;

use Configuration\DirectiveContainer,
	ZendServer\Configuration\Directives\Translator;

class Compare {
	
	/**
	 * Checks if the value passed is the same as the one in the directive, taking
	 * into consideration translation of the value (e.g. 'On' and '1' are the same
	 * for a boolean directive)
	 *
	 * @param Directives_Element $directive
	 * @param mixed $value
	 * @return boolean
	 */
	static public function isValueEqual(DirectiveContainer $directive, $value) {
		$dummyDirectiveElement = clone $directive;
		$dummyDirectiveElement->setFileValue($value);

		return ((string)Translator::getRealFileValue($directive) ===
				(string)Translator::getRealFileValue($dummyDirectiveElement));
	}
	
	/**
	 * Check if the directive's value is different from its global value (e.g. for a boolean
	 * directive 1 = on = yes)
	 *
	 * @param Directives_Element $directive
	 * @return boolean
	 */
	static public function isDirectiveChanged(DirectiveContainer $directive) {
		return (Translator::getRealFileValue($directive) != Translator::getRealMemoryValue($directive)); 
	}
}