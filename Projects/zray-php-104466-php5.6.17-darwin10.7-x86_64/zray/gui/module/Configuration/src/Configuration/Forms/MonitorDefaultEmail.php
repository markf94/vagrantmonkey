<?php
namespace Configuration\Forms;

use Zend\InputFilter\Factory,
	Zend\InputFilter\InputFilter,
	Zend\Form\Element,
	Zend\Form\Element\Checkbox,
	Zend\Form,
	Zend\Form\Fieldset,
	Zend\Validator\GreaterThan,
	Application\Module,
	Zend\Validator\Regex as Regex;

class MonitorDefaultEmail extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
			 ->setName('monitorDefaults')
			 ->setLabel('Triggered Actions Settings')
			 ->setAttribute('description', 'This area allows you to define the default settings for triggered actions to be executed for events:');

		$this->add(array(
			'name' => 'defaultEmail',
			'options' => array('label' => 'Monitoring rule default email address'),
			'attributes' => array(
				'type' => 'text',
				'description' => 'Enter a comma-separated list of email addresses for receiving event information'
			),
		));

		$this->add(array(
			'name' => 'defaultCustomAction',
			'options' => array('label' => 'Monitoring rule default callback URL'),
			'attributes' => array(
				'type' => 'text',
				'description' => 'Enter a callback URL for a customized action to be executed for each event'
			)
		));

		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
					'type' => 'submit',
					'value' => _t('Save')
				)
		));
	}
}