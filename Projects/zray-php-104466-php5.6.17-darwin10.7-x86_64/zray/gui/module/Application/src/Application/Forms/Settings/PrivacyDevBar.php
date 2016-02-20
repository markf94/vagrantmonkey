<?php
namespace Application\Forms\Settings;

use Zend\InputFilter\Factory,
	Zend\Form,
	Zend\Form\Element\Select,
	Zend\Validator\GreaterThan,
	Zend\Validator\Hostname,
	Application\Validators\DefaultServer,
	Zend\Validator\Digits,
	Application\Module;
use Zend\Validator\Uri;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Hidden;
use Zend\Form\Annotation\Input;
use ZendServer\Log\Log;

class PrivacyDevBar extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
		->setName('devbar-privacy-settings')
		->setAttribute('action', 'DevBar')
		->setLabel(_t('Privacy Settings'))
		->setAttribute('description', _t('Configure Z-Ray privacy settings:'));
		
		// Enable attribure masking
		$enableAttributeMasking = new Radio('zray.enable_attribute_masking');
		$enableAttributeMasking->setLabel(_t('Hide sensitive data'));
		$enableAttributeMasking->setAttribute('id', 'zray.enable_attribute_masking');
		$enableAttributeMasking->setAttribute('description', _t('Enable or disable masking of key attributes to ensure user privacy.'));
		$enableAttributeMasking->setAttribute('section', '');
		$enableAttributeMasking->setValueOptions(array(
		    array('label' => _t('Yes'), 'value' => 1),
		    array('label' => _t('No'), 'value' => 0),
		));
		
		$enableAttributeMasking->setValue($options['enableAttributesMasking']->getFileValue());
		$this->add($enableAttributeMasking);
		
		// Attributes masking list
		$this->add(array(
		    'name' => 'zray.attribute_masking_list',
		    'options' => array(
		        'label' => _t('Attribute List'),
		    ),
		    'attributes' => array(
		        'value' => $options['attributesMaskingList']->getFileValue(),
		        'id' => 'zray.attribute_masking_list',
		        'type' => 'text',
		        'size' => 60,
		        'section' => '',
		        'description' => _t('Enter a comma-separated list of key attributes for which values should be masked. Values can be one of: whole word, prefix (e.g. *word), suffix (e.g. word*). Case insensitive.'),
		    )
		));

		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
						'type' => 'submit',
						'value' => 'Save' // no label since background has built in text
				)
		));

		// add the actual validators
		$inputFactory = new Factory();
		$validators = $inputFactory->createInputFilter(array(
		));
		$this->setInputFilter($validators);
	}
	
	public function disableForm() {
		foreach ($this->getElements() as $element) { /* @var $element \Zend\Form\Element */
			$element->setAttribute('disabled', 'disabled');
			$element->setAttribute('readonly', 'readonly');
		}
		
		$this->remove('submit');
	}
}