<?php
namespace Configuration\View\Helper;

use Configuration\DirectiveContainer;

use Zend\View\Helper\AbstractHelper;


class DirectiveJson extends AbstractHelper {
	/**
	 * @param array $directive
	 * @return string
	 */
	public function __invoke(DirectiveContainer $directive) {
		
		// output sanitation for ZSRV-7503
		$fileValue = preg_replace('/^"(.+)"$/', '$1', $directive->getFileValue());
		$defaultValue = preg_replace('/^"(.+)"$/', '$1', $directive->getDefaultValue());

		/// support indication of no previous value being available
		if ($directive->hasPreviousValue()) {
			$previousValue = preg_replace('/^"(.+)"$/', '$1', $directive->getPreviousValue());
		} else {
			$previousValue = null;
		}
		
		$dirArray = array(
				'name' 			=> $directive->getName(),
				'section' 		=> $directive->getSection(),
				'fileValue' 	=> $fileValue,
				'defaultValue' 	=> $defaultValue,
				'previousValue'	=> $previousValue,
				'description' 	=> $directive->getDescription(),
				'type'			=> $directive->getType(),
				'listValues'	=> $directive->getlistValues(),
				'context'		=> $directive->getContext(),
				'contextName'	=> $directive->getContextName(),
				'units' 		=> $directive->getUnits());
				
		return $this->getView()->json($dirArray, array());
	}
	
}
