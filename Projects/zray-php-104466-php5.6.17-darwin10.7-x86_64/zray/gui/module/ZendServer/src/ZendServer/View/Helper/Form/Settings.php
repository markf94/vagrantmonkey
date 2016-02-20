<?php

namespace ZendServer\View\Helper\Form;

use Zend\View\Helper\AbstractHelper,
	Zend\Form\Form as ZendForm,
	Zend\Form\Element,
	Zend\Form\Fieldset,
	ZendServer\Exception;

class Settings extends AbstractHelper {
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
			$renderer = $this->getView()->zendFormSettings();
		}
		
		$result = '';
		
		// Render the opening tag
		$result .= $this->getView()->form()->openTag($form);
		
		$result .= $renderer->openTag();
		
		$hasSubSections = false;
		foreach ($form->getElements() as $element) { /* @var $element Element */
		    if ($element->getOption('sub-section') === true) {
		        $hasSubSections = true;
		    }
			$result .= $renderer->element($element);
		}
		
		$result .= $renderer->closeTag();
		
		$result .= $this->getView()->form()->closeTag($form);
		
		$formId = $form->getName();
		$title = $form->getLabel();
		$description = '';
		if ($form->hasAttribute('description')) {
			$description = $form->getAttribute('description');
		}
		
		$jsForSubSections = '';
		if ($hasSubSections) {
		    $jsForSubSections = $this->getJSForSubSections();
		}
		
		return <<<FORM
<div class="settings-wrapper" id="{$formId}" onsubmit="return false;" >
	<h2>{$title}</h2>
	<div class="settings-desc">
		{$description}
	</div>
	{$result}
</div>
{$jsForSubSections}
FORM;
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
	
	/**
	 * JS script to show/hide sub sections in the form
	 * @return string
	 */
	protected function getJSForSubSections() {
	    return <<<SUBSECTIONJS
		<script>
		(function() {
			
			var getElementValue = function(el) {
				if (el.tagName.toLowerCase() == 'input' && el.getAttribute('type').toLowerCase() == 'checkbox') {
					// checkbox
					if (el.checked) {
						return el.getAttribute('value') ? el.getAttribute('value') : '1';
					} else {
						return '0';
					}
				} else if (el.tagName.toLowerCase() == 'input' && el.getAttribute('type').toLowerCase() == 'radio') {
					// radio buttons
					
					// find the form element
					var formElem = el;
					while (formElem && formElem.tagName != 'FORM') formElem = formElem.parentNode;
					if (!formElem) return false;
					
					var elName = el.getAttribute('name');
					if (!elName) return false;
					
					for (var i=0, totalElems = formElem.elements[elName].length; i < totalElems; i++) {
						var radioEl = formElem.elements[elName][i];
						if (radioEl.checked) {
							return radioEl.getAttribute('value');
						}
					}
					
					return false; // no element checked
				} else {
					return el.getAttribute('value') || false;
				}
			}
			
			var showHideSubSection = function(parentInputEl) {
				var subSectionItem = parentInputEl.getParent('[sub-section-parent="true"]').getNext().getNext();
			
				while (subSectionItem && subSectionItem.get('sub-section') == "true") {
					var parentElValue = getElementValue(parentInputEl);
					var parentIsChecked = (parentElValue && parentElValue != '0');
					// show the element
					if (parentIsChecked) {
						subSectionItem.removeClass('hidden');
					} else {
						subSectionItem.addClass('hidden');
					}
					
					// show element's description
					subSectionItem = subSectionItem.getNext();
					
					if (parentIsChecked) {
						subSectionItem.removeClass('hidden');
					} else {
						subSectionItem.addClass('hidden');
					}
			
					// go to the next sibling
					subSectionItem = subSectionItem.getNext(); 
				}
			};
			
			var processedInputsNames = [];
			$$('[sub-section-parent="true"] input').each(function(el) {
				if (processedInputsNames.indexOf(el.getAttribute('name')) < 0) {
					processedInputsNames.push(el.getAttribute('name'));
					if (el.getParent('[sub-section-parent="true"]')) {
						showHideSubSection(el);
					}
				}
			});
	        
			$$('[sub-section-parent="true"] input').addEvent('change', function(e) {
				var el = e.target;
				showHideSubSection(el);
			});
			
		})();
		</script>
SUBSECTIONJS;
	}
}