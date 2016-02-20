<?php
namespace Application\Forms\Settings;

use Zend\InputFilter\Factory,
	Zend\Form,
	Zend\Form\Element\Select,
	Application\Module,
	Zend\Validator\EmailAddress,
	Zend\Validator\Uri;

class NotificationCenter extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$notificationAction = $options['notificationAction'];
		
		$this->setAttribute('method', 'post')
			 ->setName('notification-center-settings')
			 ->setAttribute('action', 'Notification-Center')
			 ->setLabel('Notification Center Settings')
			 ->setAttribute('description', 'The Zend Server Notification Center is a notification system for viewing live alerts in your working environment. Configure the triggered actions for each notification received:');

		$this->add(array(
			'name' => 'notificationsEmail',
			'options' => array(
				'label' => 'Notifications Email',
			),
			'attributes' => array(
				'value' => $notificationAction->getEmail(),
				'type' => 'text',
				'section' => 'none',
				'description' => 'Enter an email address for receiving notifications',
			)
		));
		
		$this->add(array(
			'name' => 'notificationsCustomAction',
			'options' => array(
				'label' => 'Notifications Callback URL',
			),
			'attributes' => array(
				'value' => $notificationAction->getCustomAction(),
				'type' => 'text',
				'section' => 'none',
				'description' => 'Define a URL to be called for each notification received',
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
			'notificationsEmail' => array('validators' => array(new EmailAddress()), 'allow_empty' => true),
			'notificationsCustomAction' =>  array('validators' => array(new Uri(array('allowRelative' => true, 'allowAbsolute' => true))), 'allow_empty' => true),
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