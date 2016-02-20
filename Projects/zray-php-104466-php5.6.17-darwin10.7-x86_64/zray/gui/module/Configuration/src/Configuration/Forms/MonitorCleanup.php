<?php
namespace Configuration\Forms;

use Zend\InputFilter\Factory,
	Zend\InputFilter\InputFilter,
	Zend\Form\Element,
	Zend\Form,
	Zend\Form\Fieldset,
	Zend\Validator\GreaterThan,
	Application\Module,
	Zend\Validator\Regex as Regex;

class MonitorCleanup extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
			 ->setName('monitorCleanup');

		$this->add(array(
			'name' => 'deleteEventsOccur',
			'options' => array('label' => 'Delete events which did not occur in the last'),
			'attributes' => array(
				'type' => 'text',
				'description' => 'Define the period of time (in days) after which events are to be deleted during the cleanup process'
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