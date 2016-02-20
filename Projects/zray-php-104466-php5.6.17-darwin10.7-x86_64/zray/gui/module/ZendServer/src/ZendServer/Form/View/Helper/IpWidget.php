<?php

namespace ZendServer\Form\View\Helper;

use Zend\Form\ElementInterface;

use Zend\Form\View\Helper\FormHidden;

class IpWidget extends FormHidden {
	public function __invoke(ElementInterface $element = null) {
		if ($element->getOption('default') && !$element->getValue()) {
			$element->setValue($element->getOption('default'));
		}
		$elementString = parent::__invoke($element);
		return <<<IPWIDGET
$elementString
<script type="text/javascript">
window.addEvent('load', function(){
	new IpWidget('{$this->getId($element)}');
});
</script> 
IPWIDGET;
	}
}

