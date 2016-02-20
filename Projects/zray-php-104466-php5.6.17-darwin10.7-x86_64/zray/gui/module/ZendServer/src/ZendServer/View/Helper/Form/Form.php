<?php

namespace ZendServer\View\Helper\Form;

use Zend\View\Helper\AbstractHelper,
	Zend\Form\Form as ZendForm,
	Zend\Form\Element,
	Zend\Form\Fieldset,
	ZendServer\Exception;

class Form extends AbstractHelper {
	/**
	 * Get a filesize label.
	 *
	 * @param ZendForm $form
	 * @return string
	 */
	public function __invoke($form = null, $renderer = null) {
		if (is_null($form)) {
			return $this;
		}
		
		if (is_null($renderer)) {
			$renderer = $this->getView()->zendFormTable();
		}
		
		$result = '';
		
		// Render the opening tag
		$result .= $this->getView()->form()->openTag($form);
		
		foreach ($form->getFieldsets() as $fieldSet) { /* @var $fieldSet Fieldset */
			$result .= '<fieldset><legend>'.$this->getView()->escapehtml($fieldSet->getLabel()).'</legend>';
			if ($fieldSet->getOption('description')) {
				$result .= '<span class="fieldset_description">'. $fieldSet->getOption('description') .'</span>';
			} elseif ($fieldSet->getAttribute('description')) {
				$result .= '<span class="fieldset_description">'. $fieldSet->getAttribute('description') .'</span>';
			}
			$result .= $renderer->openTag();
			
			foreach ($fieldSet->getElements() as $element) { /* @var $element Element */
				$result .= $renderer->element($element);
			}
			
			$result .= $renderer->closeTag();
			
			$result .= '</fieldset>';
		}

		$result .= $renderer->openTag();
		
		foreach ($form->getElements() as $element) { /* @var $element Element */
			$result .= $renderer->element($element);
		}
		
		$result .= $renderer->closeTag();
		
		$result .= $this->getView()->form()->closeTag($form);
		
		return $result;
	}
	
	/**
	 * @param ZendForm $form
	 * @return array
	 */
	public function getElements($form) {
		$elements = $form->getElements();
		foreach ($form->getFieldsets() as $fieldSet) { /* @var $fieldSet Fieldset */
			$elements = array_merge($elements, $fieldSet->getElements());
		}
		return $elements;
	}
}

/*
		FormElementErrors.php
*/
	