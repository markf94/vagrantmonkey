<?php

namespace Configuration\Audit\ExtraData;
use Configuration\MapperDirectives;
use Audit\ExtraData\ParserInterface;
use ZendServer\Log\Log;

class DirectivesParser implements ParserInterface {
	
	/**
	 * @var MapperDirectives
	 */
	private $directivesMapper;
	
	/**
	 * @var array
	 */
	private $extraData;
	
	/**
	 * @return MapperDirectives
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}

	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
	}

	/**
	 * @param array $extraData
	 */
	public function setExtraData($extraData) {
		$this->extraData = $extraData;
	}
	
	/* (non-PHPdoc)
	 * @see \Audit\ExtraData\ParserInterface::toArray()
	 */
	public function toArray() {
		
		$directiveNames = array_keys($this->extraData);
		foreach ($directiveNames as &$name) {
			if (strpos($name, 'zend_gui.') === false) {
				$name = 'zend_gui.' . $name;
			}
		}

		$oldValues = $this->getDirectivesMapper()->getDirectivesValues($directiveNames);
		$extraData = array();
		
		foreach ($this->extraData as $name => $value) {
			$fullName = 'zend_gui.' . $name;

			if (isset($oldValues[$name]) || isset($oldValues[$fullName])) {
				if (isset($oldValues[$name])) {
					$oldvalue = $oldValues[$name];
				} else {
					$oldvalue = $oldValues[$fullName];
					$name = $fullName;
				}
				
				$extraData[] = array("GUI Setting: {$name}, Old value: {$oldvalue}, New value: {$value}");
			} else {
				$extraData[] = array("GUI Setting: {$name}, New value: {$value}");
			}
		}
		
		return $extraData;
	}
}

