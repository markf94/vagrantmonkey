<?php

namespace PageCache\Model;

class SplitByCondition {
	protected $superGlobal;
	protected $element;
	
	/**
	 * @return the $superGlobal
	 */
	public function getSuperGlobal() {
		return $this->superGlobal;
	}

	/**
	 * @return the $element
	 */
	public function getElement() {
		return $this->element;
	}

	/**
	 * @param field_type $superGlobal
	 */
	public function setSuperGlobal($superGlobal) {
		$this->superGlobal = $superGlobal;
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
	
	public function toArray() {
		return array (
				"global" => $this->superGlobal
				,"element" => \PageCache\Rule::cleanupElement($this->element)				
		);
	}

}

?>