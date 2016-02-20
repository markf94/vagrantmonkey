<?php
namespace ZendServer\View\Helper\Form\Renderer;

use Zend\View\Helper\AbstractHelper,
	Zend\Form\Element;

class DeployWizard extends AbstractHelper {

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
		return '<table class="zend-form-deploy-wizard">';
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
		$label = is_null($element->getLabel('label')) ? '' : $this->getView()->FormLabel($element);	
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
			$description = '<tr><td colspan="2" style="padding-top: 0; font-size: 12px;"><div class="zend-form-table-description">' . htmlentities($element->getAttribute('description')) . '</div></td>';
		}
		
		return <<<ELEMENT
<tr>
	<td {$required}>{$label}</td>
	<td>{$value}
		<div class="error-wrapper">{$errors}</div>
	</td>
	{$description}
</tr>
</tr>
ELEMENT;
	}
}