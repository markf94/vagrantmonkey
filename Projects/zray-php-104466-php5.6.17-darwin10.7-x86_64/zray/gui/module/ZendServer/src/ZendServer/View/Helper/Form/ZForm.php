<?php
/**
 * Generic form html generator
 * exapmles:
 * 		\gui\module\JobQueue\src\JobQueue\Form\SettingsEventsForm.php
 */
 
namespace ZendServer\View\Helper\Form;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZForm extends AbstractHelper {
	
	protected $form = null;
	
	static protected $jsPublished = false;
	
	/**
	 * 
	 * @param unknown $form
	 * @param array $values
	 * @return string (html)
	 */
	public function __invoke($form, $values = array()) {
		$this->form = $form;
		$html = '';
		/* @var ZendServer\View\Renderer\PhpRenderer */
		$view = $this->getView(); 
		
		// open form tag. set action to the current page
		$html.= $view->form()->openTag($form);
		
		$boundedElementDescription = null;
		
		$section = null;
		$sectionTagOpen = false;
		$sectionSubStarted = false;
		$advancedSectionStarted = false;
		foreach ($form->getElements() as $element) {
			// open/close section sub
			if (!$sectionSubStarted && $element->getOption('section') == 'section_sub') {
				$sectionSubStarted = true;
				$html.=  '<div class="section_sub hidden">';
			} elseif ($sectionSubStarted && $element->getOption('section') != 'section_sub') {
				$sectionSubStarted = false;
				$html.=  '</div>';
			}
			
			// open/close advanced section
			if (!$advancedSectionStarted && $element->getOption('advanced_section') == true) {
				$advancedSectionStarted = true;
				$html.=  '<div id="advanced_section" class="advanced_section hidden">';
			} elseif ($advancedSectionStarted && $element->getOption('advanced_section') != true) {
				$advancedSectionStarted = false;
				$html.=  '</div>';
				$html.= 
					'<div class="form-row">'.
						'<a href="javascript:;" id="advanced-section-header" class="advanced-section-header">'._t('Show Advanced Settings').'...</a>'.
					'</div>';

			}
			
			// put header for new section
			if ($element->getOption('section') != 'section_sub' && $element->getOption('section') !== $section) {
				// close previous section
				if (!is_null($section)) {
					$sectionTagOpen = false;
					$html.= '</section>';
				}
				
				$section = $element->getOption('section');
				$sectionId = strtolower(str_replace(' ', '_', $section));
				$sectionToDisplay = ucfirst(str_replace('_', ' ', $section));
				$sectionImage = $element->getOption('section_image') ? 'style="background-image: url('.$view->basePath().$element->getOption('section_image').');"' : ''; 
				// open new section wrapper
				$sectionTagOpen = true;
				$html.=  "<section id='section_wrapper_{$sectionId}'>";
				
				// display the section header
				$html.=  "<h3 {$sectionImage} id='section_{$sectionId}'>"._t($sectionToDisplay).'</h3>';
				
				$sectionDescription = $element->getOption('section_description');
				if (!empty($sectionDescription)) {
					$html.= "<p class=\"section_description\">{$sectionDescription}</p>";
				}
			}
			
			// open (start) the element
			if ($element->getOption('is_bounded_element')) {
				$html.=  '<span class="bounded-element">';
			} else {
				$html.=  '<div class="form-row">';
				if ($element->getOption('has_bounded_element')) {
					$html.=  '<span class="bounded-element">';
				}
			}
			
			// check if the element is optional, build checkbox above / before it
			if ($element->getOption('optional')) {
				$optionalOptions = $element->getOption('optional');
				
				// check if element enabled by values?
				if (isset($values[$element->getName() . '_enabled'])) {
					$optionalOptions['enabled'] = $values[$element->getName() . '_enabled'];
				}
				
				// disable the element when it's not enabled
				if (!isset($optionalOptions['enabled']) || !$optionalOptions['enabled']) {
					$checked = '';
					$element->setAttribute('disabled', 'disabled');
				} else {
					$checked = 'checked="checked"';
				}
				
				$checkboxTitle = '';
				if (isset($optionalOptions['title']) && !empty($optionalOptions['title'])) {
					$checkboxTitle = htmlentities(strip_tags($optionalOptions['title']), ENT_QUOTES, 'utf-8');
				}
				
				// draw the checkbox that enables/disables the element
				$checkboxForOptional_Id = 'optional_checkbox_'.$element->getName();
				
				$classForLabeledCheckbox = '';
				if (isset($optionalOptions['label']) && !empty($optionalOptions['label'])) {
					$classForLabeledCheckbox = 'zform-checkbox-for-optional-with-label';
				}
				
				// the checkbox HTML
				$checkboxHtml = '<input type="checkbox" id="'.$checkboxForOptional_Id.'" title="'.$checkboxTitle.'" for="'.$element->getName().'" class="zform-checkbox-for-optional '.$classForLabeledCheckbox.'" '.$checked.'>';
				
				if (isset($optionalOptions['label']) && !empty($optionalOptions['label'])) {
					$html.= 
						'<div><label for="'.$checkboxForOptional_Id.'">'.
							$checkboxHtml.' '.$optionalOptions['label'].
						'</label></div>';
				} else {
					$html.= $checkboxHtml;
				}
			}
			
			// set value
			if (isset($values[$element->getName()])) {
				$element->setValue($values[$element->getName()]);
			}

			// get element type
			$elementType = get_class($element);
			
			// prepare the title (`title` is used for explanation below the field, and for the `title` attribute)
			$elementTitle = $element->getAttribute('title');
			$elementTitleAttribute = htmlentities(strip_tags($element->getAttribute('title')), ENT_QUOTES, 'utf-8');
			$element->setAttribute('title', $elementTitleAttribute);
			
			// get label html
			$label = $element->getLabel() ? $view->formlabel($element) : '';
			$labelOnTheLeft = true;
			$labelWrapperClass = 'zform-label';
			$elemWrapperClass = 'zform-element';
			
			// get field html
			switch ($elementType) {
				case 'Zend\Form\Element\Checkbox':
					$labelOnTheLeft = false;
		
					// update the label
					$options = $element->getOptions();
					$options['label_attributes'] = array(
						'class' => 'label-on-the-right'
					);
					$element->setOptions($options);
					$fieldHtml = $view->formcheckbox($element);
					break;
				case 'Zend\Form\Element\Select':
					$fieldHtml = $view->formselect($element);
					break;
				case 'Zend\Form\Element\Number':
					$fieldHtml = $view->formnumber($element);
					break;
				case 'Zend\Form\Element\Email':
					$fieldHtml = $view->formemail($element);
					break;
				case 'Zend\Form\Element\Url':
					$fieldHtml = $view->formurl($element);
					break;
				case 'Zend\Form\Element\File':
					$fieldHtml = $view->formfile($element);
					break;
				case 'Zend\Form\Element\Radio':
					
					// build custom view for radio group
					$fieldHtml = '';
					$valueOptions = $element->getValueOptions(); 
					if (!empty($valueOptions)) foreach ($valueOptions as $k => $valueOption) {
						$fieldHtml.= '<div class="value_option_wrapper">';
						if (isset($values[$element->getName()])) {
							$checked = ($values[$element->getName()] == $valueOption['value']) ? 'checked="checked"' : '';
						} else {
							$checked = (isset($valueOption['checked']) && strcasecmp($valueOption['checked'], 'checked') == 0) ? 'checked="checked"' : '';
						}
						$title = isset($valueOption['title']) ? htmlentities($valueOption['title'], ENT_QUOTES, 'utf-8') : '';
						$titleAttr = empty($title) ? '' : ' title="'.htmlentities(strip_tags($valueOption['title']), ENT_QUOTES, 'utf-8').'" '; 
						
						$valueOptionId = isset($valueOption['attributes']) && isset($valueOption['attributes']['id']) ? $valueOption['attributes']['id'] : uniqid('value_option_');
						
						$value = $valueOption['value'];
						
						$valueOptionLabel = isset($valueOption['label']) ? $valueOption['label'] : '';
						$fieldHtml.= "<label for='{$valueOptionId}'><input id='{$valueOptionId}' type='radio' value='{$value}' name='{$element->getName()}' {$checked} {$titleAttr}>";
						$fieldHtml.= "&nbsp;{$valueOptionLabel}</label>";
						
						if (isset($valueOption['title']) && $valueOption['title']) {
							$fieldHtml.= "<p>{$valueOption['title']}</p>";
						}
						
						if (!isset($valueOptions[$k]['label_attributes'])) $valueOptions[$k]['label_attributes'] = array(); 
						if (!isset($valueOptions[$k]['label_attributes']['class'])) $valueOptions[$k]['label_attributes']['class'] = ''; 
						$valueOptions[$k]['label_attributes']['class'].= trim($valueOptions[$k]['label_attributes']['class'].' zform-radio-button-label');
						
						$fieldHtml.= '</div>'; // value_option_wrapper
					}
					$element->setValueOptions($valueOptions);
					$labelWrapperClass.= ' zform-label-radio';
					$elemWrapperClass.= ' zform-element-radio';
					break;
				default:
					// check special cases
					if ($element->getOption('extended_type') && strcasecmp($element->getOption('extended_type'), 'hosts_list') == 0) {
						$fieldHtml = $view->formHostsList($element);
					} elseif ($element->getOption('extended_type') && strcasecmp($element->getOption('extended_type'), 'ip_widget') == 0) {
						$fieldHtml = $view->formIpWidget($element);
					} else {
						$fieldHtml = $view->formelement($element);
					}
					break;
			};
		
			// add suffix for the field
			$suffix = $element->getOption('suffix') ? '<small class="suffix">'.($element->getOption('suffix')).'</small>' : '';
			
			// display the html
			$labelHtml = $element->getOption('hide_label') ? '&nbsp;' : "<span class='{$labelWrapperClass}'>{$label}</span>";
			if ($labelOnTheLeft) {
				if (!empty($suffix)) {
					$suffix = "&nbsp;{$suffix}";
				}
				$html.=  "{$labelHtml}<span class='{$elemWrapperClass}'>{$fieldHtml}{$suffix}</span>";
			} else {
				$html.= "<span class='{$elemWrapperClass}'>{$fieldHtml}{$suffix}</span>{$labelHtml}";
			}
		
			if ($element->getOption('has_bounded_element') && !$element->getOption('is_bounded_element')) {
				$boundedElementDescription = $elementTitle;
			} elseif (!$element->getOption('has_bounded_element') && !$element->getOption('is_bounded_element')) {
				if ($elementTitle) {
					$html.=  '<p>'.$elementTitle.'</p>';
				}
			}
			 
			 // close the element
			if ($element->getOption('has_bounded_element')) {
				$html.=  '</span>';
			} else {
				if ($element->getOption('is_bounded_element')) {
					$html.=  '</span>';
				}
				
				// display the description for all the bounded elements
				if ($boundedElementDescription) {
					$html.=  '<p>'.$boundedElementDescription.'</p>';
					$boundedElementDescription = null;
				}
				
				$html.=  '</div>';
			}
		}
		
		// close "advanced settings" sectiokn
		if ($advancedSectionStarted) {
			$advancedSectionStarted = false;
			$html.=  '</div>';
			$html.= 
				'<div class="form-row">'.
					'<a href="javascript:;" id="advanced-section-header" class="advanced-section-header">'._t('Show Advanced Settings').'...</a>'.
				'</div>';
		}
		
		// close section sub
		if ($sectionSubStarted) {
			$html.=  '</div>';
			$sectionSubStarted = false;
		}
		
		// close "section"
		if ($sectionTagOpen) {
			$html.=  '</section>';
			$sectionTagOpen = false;
		}
		
		$html.=  $view->form()->closeTag($form);
		$html.= '<script>'.$this->getStaticJavascript().'</script>';
		$html.= '<script>'.$this->getJavascript().'</script>';
		return $html;
	}
	
	
	protected function getElementsHierarchy() {
		if (!$this->form) return '';
		 
		// map the elements' hierarchy
		$elementsChildren = array();
		$lastElement = null;
		foreach ($this->form->getElements() as $element) {
			$section = $element->getOption('section');
			$relatedTo = $element->getOption('related_to');
			
			// add all the related elements
			if (!is_null($lastElement)) {
				foreach ($this->form->getElements() as $subElement) {
					if ($subElement->getOption('related_to') == $element->getName()) {
						if (!isset($elementsChildren[$lastElement->getName()]) || !is_array($elementsChildren[$lastElement->getName()])) {
							$elementsChildren[$lastElement->getName()] = array();
						}
						 
						$elementsChildren[$lastElement->getName()][] = $subElement;
					}
				}
			}
			 
			// add all the section subs
			if (strcasecmp($section, 'section_sub') == 0) {
				if (!is_null($lastElement) && (!$relatedTo || $relatedTo == $lastElement->getName())) {
					if (!isset($elementsChildren[$lastElement->getName()]) || !is_array($elementsChildren[$lastElement->getName()])) {
						$elementsChildren[$lastElement->getName()] = array();
					}
					
					$elementsChildren[$lastElement->getName()][] = $element;
				}
			} else {
				$lastElement = $element;
			}
		}

		// remove duplicates
		foreach ($elementsChildren as $parentName => $listOfChildren) {
			$newArr = array();
			$names = array();
			foreach ($listOfChildren as $child) {
				if (!in_array($child->getName(), $names)) {
					$names[] = $child->getName();
					$newArr[] = $child;
				}
			}
			$elementsChildren[$parentName] = $newArr;
		}
		
		return $elementsChildren;
	}
	
	/**
	 * @brief Return js code that has to run only once
	 * @return string
	 */
	protected function getStaticJavascript() {
		if (self::$jsPublished) return '';
		self::$jsPublished = true;
		
		$js = '
		var getElementValue = function(el) {
			if (!el) {
				console.error("parameter `el` is undefined");
				return false;
			}
			if (el.tagName.toLowerCase() == "input" && el.getAttribute("type").toLowerCase() == "checkbox") {
				// checkbox
				if (el.checked) {
					return el.getAttribute("value") ? el.getAttribute("value") : "1";
				} else {
					return "0";
				}
			} else if (el.tagName.toLowerCase() == "input" && el.getAttribute("type").toLowerCase() == "radio") {
				// radio buttons
				
				// find the form element
				var formElem = el;
				while (formElem && formElem.tagName != "FORM") formElem = formElem.parentNode;
				if (!formElem) return false;
				
				var elName = el.getAttribute("name");
				if (!elName) return false;
				
				for (var i=0, totalElems = formElem.elements[elName].length; i < totalElems; i++) {
					var radioEl = formElem.elements[elName][i];
					if (radioEl.checked) {
						return radioEl.getAttribute("value");
					}
				}
				
				return false; // no element checked
			} else {
				return el.getAttribute("value") || false;
			}
		}
		
		var showHideElementSubSection = function(elem, oppositeBehavior) {
			var val = getElementValue(elem);
			val = val && val != "0";
			
			var formRow = elem;
			while (formRow && !formRow.classList.contains("form-row")) formRow = formRow.parentNode;
			if (!formRow) return;
			var subSection = formRow.nextSibling;
			
			if (oppositeBehavior) val = !val;
			if (val) {
				// show sub section
				if (subSection.classList.contains("hidden")) subSection.classList.remove("hidden");
			} else {
				// hide sub section
				if (!subSection.classList.contains("hidden")) subSection.classList.add("hidden");
			}
		};
		';
		
		return $js;
	}
	
	/**
	 * @brief The next js runs for every form (several times)
	 * @return  
	 */
	protected function getJavascript() {
		$mappedElements = $this->getElementsHierarchy();
		
		$js = '
		var advancedSectionHeader = document.getElementById("advanced-section-header");
		if (advancedSectionHeader) {
			var showHideAdvancedSection = function() {
				if (advancedSectionHeader.classList.contains("closed-state")) {
					advancedSectionHeader.classList.remove("closed-state");
					advancedSectionHeader.textContent = advancedSectionHeader.textContent.replace("Show", "Hide");
					
					if (document.getElementById("advanced_section").classList.contains("hidden")) {
						document.getElementById("advanced_section").classList.remove("hidden")
					}
				} else {
					advancedSectionHeader.classList.add("closed-state");
					advancedSectionHeader.textContent = advancedSectionHeader.textContent.replace("Hide", "Show");
					
					if (!document.getElementById("advanced_section").classList.contains("hidden")) {
						document.getElementById("advanced_section").classList.add("hidden")
					}
				}
			};
			advancedSectionHeader.addEventListener("click", showHideAdvancedSection);
			showHideAdvancedSection();
		}

		
		window.addEvent("load", function() {';
			foreach ($mappedElements as $elemName => $elemChildren) {
				$parentElement = $this->form->get($elemName);
				if (!$parentElement) continue;
				$js.= '
					[].forEach.call(document.querySelectorAll("[name=\''.$parentElement->getName().'\']"), function(elem) {
						elem.addEvent("change", function(e) {
							var oppositeBehavior = '.($parentElement->getOption('hideSubSectionOnCheck') ? 'true' : 'false').';
							showHideElementSubSection(e.target, oppositeBehavior);
						});
						showHideElementSubSection(elem, '.($parentElement->getOption('hideSubSectionOnCheck') ? 'true' : 'false').');
					});
				';
			}
			
			// assign click event on optional elements
			$optionalElementsNames = $this->getOptionalElementsNames();
			if (!empty($optionalElementsNames)) foreach ($optionalElementsNames as $optionalElementName) {
				$js.= '
				document.querySelector(\'input[type="checkbox"][for="'.$optionalElementName.'"]\').addEventListener("click", function(e) {
					var elem = e.target;
					[].forEach.call(document.querySelectorAll(\'[name="'.$optionalElementName.'"]\'), function(inputElem) {
						if (elem.checked) {
							inputElem.removeAttribute("disabled");
						} else {
							inputElem.setAttribute("disabled", "disabled");
						}
					});
				});
				';
			}
			
			$js.= '
		});';
		
		return $js;
	}
	
	
	/**
	 * @brief return elements that have "optional" feature
	 * @return array
	 */
	protected function getOptionalElementsNames() {
		$names = array();
		foreach ($this->form->getElements() as $element) {
			if ($element->getOption('optional')) {
				$names[] = $element->getName();
			}
		}
		
		return $names;
	}
}

