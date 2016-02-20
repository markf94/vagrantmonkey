<?php
namespace ZendServer\View\Helper\Form\Renderer;

use Zend\View\Helper\AbstractHelper,
	Zend\Form\Element;

class Table extends AbstractHelper {

	/**
	 * @return Table
	 */
	public function __invoke() {
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function openTag() {
		return '<table class="zend-form-table">';
	}
	
	/**
	 * @return string
	 */
	public function closeTag() {
		return '</table>';
	}
	
	/**
	 * @param Element $element
	 * @return string
	 */
	public function element(Element $element) {
		$label = is_null($element->getLabel()) ? '' : $this->getView()->FormLabel($element);
		
		$type = $element->getAttribute('type');
		$value = $this->getView()->FormElement($element);
		$errors = $this->getView()->FormElementErrors($element);
		
		$required = '';
		if (!is_null($element->getAttribute('required'))) {
			$required = ' class="required-field" ';
		}
		
		$type = $element->getAttribute('type');
		if ($type == 'hidden') {
			return $value;
		}
		
		$description = '';
		if (!is_null($element->getAttribute('description'))) {
			$description = '<div class="zend-form-table-description">' . $element->getAttribute('description') . '</div>';
		}
		
		// check label only type
		if ($type == 'label') {
			return <<<ELEMENT
			<tr>
				<td class="zend-form-label-element" colspan="3">{$label}</td>
			</tr>
ELEMENT;
		}
		
		return <<<ELEMENT
<tr>
	<td {$required}>{$label}</td>
	<td>{$value}{$description}</td>
	<td>{$errors}</td>
</tr>
ELEMENT;
	}
}