<?php
namespace Application\Forms\Settings;

use Zend\InputFilter\Factory,
	Zend\Form,
	Zend\Form\Element\Select,
	Application\Module,
	Zend\Validator\EmailAddress;

class Audit extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$auditEmail = $options['auditEmail'];
		$auditScriptUrl = $options['auditScriptUrl'];
		
		$this->setAttribute('method', 'post')
			 ->setName('audit-trail-settings')
			 ->setAttribute('action', 'Audit')
			 ->setLabel('Audit Trail Settings')
			 ->setAttribute('description', 'The Zend Server Audit Trail tracks and records UI and API user activity. Configure the triggered actions for each logged audit:');

		$this->add(array(
			'name' => 'auditEmail',
			'options' => array(
				'label' => 'Audit Email',
			),
			'attributes' => array(
				'value' => $auditEmail,
				'type' => 'text',
				'section' => 'none',
				'description' => 'Enter an email address for receiving audit trails',
			)
		));
		
		$this->add(array(
			'name' => 'auditCustomAction',
			'options' => array(
				'label' => 'Audit Callback URL',
			),
			'attributes' => array(
				'value' => $auditScriptUrl,
				'type' => 'text',
				'section' => 'none',
				'description' => 'Define a URL to be called for each audited action',
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
			'auditEmail' => array('validators' => array(new EmailAddress()), 'allow_empty' => true),
		));
		$this->setInputFilter($validators);
	}
	
	public function disableForm() {
		foreach ($this->getElements() as $element) { /* @var $element \Zend\Form\Element */
			$this->disableFormElement($element);
		}
	
		$this->remove('submit');
	}
	
	public function disableFormElement($element) {
		$element->setAttribute('disabled', 'disabled');
		$element->setAttribute('readonly', 'readonly');
	}
}