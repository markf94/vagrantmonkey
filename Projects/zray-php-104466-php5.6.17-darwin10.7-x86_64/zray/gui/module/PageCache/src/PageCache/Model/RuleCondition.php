<?php

namespace PageCache\Model;

class RuleCondition {
	
	
	protected $superGlobal;
	protected $element;
	protected $matchType;
	protected $value;
	
	/**
	 * @return the $element
	 */
	public function getElement() {
		return $this->element;
	}

	/**
	 * @param field_type $element
	 */
	public function setElement($element) {
		
		if (strpos($element, "[") !== 0) {
			$element = "[" . $element . "]";
		}
		
		$this->element = $element;
	}

	/**
	 * @return the $superGlobal
	 */
	public function getSuperGlobal() {
		return $this->superGlobal;
	}

	/**
	 * @return the $matchType
	 */
	public function getMatchType() {
		return $this->matchType;
	}

	/**
	 * @return the $value
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param field_type $superGlobal
	 */
	public function setSuperGlobal($superGlobal) {
		$this->superGlobal = $superGlobal;
	}

	/**
	 * @param field_type $matchType
	 */
	public function setMatchType($matchType) {
		$this->matchType = $matchType;
	}

	/**
	 * @param field_type $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function toArray() {
		return array (
				"global" => $this->superGlobal
				,"element" => \PageCache\Rule::cleanupElement($this->element)
				, "type" => $this->matchType
				, "value" => $this->value
				);
	}
}

?>