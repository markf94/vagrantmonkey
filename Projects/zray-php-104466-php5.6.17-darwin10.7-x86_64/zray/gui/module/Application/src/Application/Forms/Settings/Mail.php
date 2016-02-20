<?php
namespace Application\Forms\Settings;

use Zend\InputFilter\Factory,
	Zend\Form,
	Zend\Form\Element\Select,
	Zend\Form\Element\Radio,
	Application\Module,
	Zend\Validator\EmailAddress;

class Mail extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
			 ->setName('mail-settings')
			 ->setAttribute('action', 'Mail')
			 ->setLabel('Mail Settings')
			 ->setAttribute('description', 'This area allows you to define the mailing account that is used by Zend Server for sending alerts and notifications:');

		$mailService = new Select('zend_gui.mail_service');
		$mailService->setLabel('Mail Service');
		$mailService->setAttribute('description', 'Select the mail service you are using (Custom, Gmail, Yahoo, Exchange)');
		$mailService->setAttribute('section', 'mail');		
		$mailService->setValueOptions(array(
			array('label' => _t('Custom'), 'value' => 'custom'),
			array('label' => _t('Gmail'), 'value' => 'gmail'),
			array('label' => _t('Yahoo'), 'value' => 'yahoo'),
			array('label' => _t('Exchange'), 'value' => 'outlook'),
		));
		$mailService->setValue(Module::config('mail', 'mail_service'));
		$this->add($mailService);
		
		$mailType = new Select('zend_gui.mail_type');
		$mailType->setLabel('Mail Type');
		$mailType->setAttribute('description', 'Select the mail type you are using (SMTP, Sendmail)');
		$mailType->setAttribute('section', 'mail');
		$mailType->setValueOptions(array(
				array('label' => _t('SMTP'), 'value' => 'smtp'),
				array('label' => _t('Sendmail'), 'value' => 'sendmail'),
		));
		$mailType->setValue(Module::config('mail', 'mail_type'));
		$this->add($mailType);
		
		$this->add(array(
				'name' => 'zend_gui.mail_host',
				'options' => array(
					'label' => 'Mail Host',
				),
				'attributes' => array(
						'value' => Module::config('mail', 'mail_host'),
						'type' => 'text',
						'section' => 'mail',
						'description' => 'Enter the host of the mail account you are using',
				)
		));
		
		$this->add(array(
				'name' => 'zend_gui.mail_port',
				'options' => array(
					'label' => 'Mail Port',
				),
				'attributes' => array(
						'value' => Module::config('mail', 'mail_port'),
						'type' => 'text',
						'section' => 'mail',
						'description' => 'Enter the port of the mail account you are using',
				)
		));
		
		$authentication = new Radio('zend_gui.authentication');
		$authentication->setLabel('Authentication');
		$authentication->setAttribute('description', 'Select whether you would like to authenticate messages from Zend Server');
		$authentication->setAttribute('section', 'mail');
		$authentication->setValueOptions(array(
				array('label' => _t('On'), 'value' => 1),
				array('label' => _t('Off'), 'value' => 0),
		));
		$authentication->setValue(Module::config('mail', 'authentication'));
		$this->add($authentication);
		
		$authenticationMethod = new Select('zend_gui.authentication_method');
		$authenticationMethod->setLabel('Authentication Method');
		$authenticationMethod->setAttribute('description', 'Select your preferred method of authentication (Plain, Login, CRAM-MD5)');
		$authenticationMethod->setAttribute('section', 'mail');
		$authenticationMethod->setValueOptions(array(
				array('label' => _t('Plain'), 'value' => 'plain'),
				array('label' => _t('Login'), 'value' => 'login'),
				array('label' => _t('CRAM-MD5'), 'value' => 'crammd5'),
		));
		$authenticationMethod->setValue(Module::config('mail', 'authentication_method'));
		$this->add($authenticationMethod);
		
		$authenticationSecurity = new Select('zend_gui.mail_ssl');
		$authenticationSecurity->setLabel('Authentication Security');
		$authenticationSecurity->setAttribute('description', 'Select the type of encryption for authentication (None,SSL, TLS)');
		$authenticationSecurity->setAttribute('section', 'mail');
		$authenticationSecurity->setValueOptions(array(
				array('label' => _t('None'), 'value' => 'none'),
				array('label' => _t('Use SSL'), 'value' => 'ssl'),
				array('label' => _t('Use TLS'), 'value' => 'tls'),
		));
		$authenticationSecurity->setValue(Module::config('mail', 'mail_ssl'));
		$this->add($authenticationSecurity);
		
		$this->add(array(
				'name' => 'zend_gui.mail_username',
				'options' => array(
						'label' => 'Authentication Username',
				),
				'attributes' => array(
						'value' => Module::config('mail', 'mail_username'),
						'type' => 'text',
						'section' => 'mail',
						'description' => 'Enter the username you would like to use for authentication',
				)
		));
		
		$this->add(array(
				'name' => 'zend_gui.mail_password',
				'options' => array(
						'label' => 'Authentication Password',
				),
				'attributes' => array(
						'value' => Module::config('mail', 'mail_password'),
						'type' => 'password',
						'section' => 'mail',
						'description' => 'Enter the password you would like to use for authentication',
				)
		));
		
		$this->add(array(
				'name' => 'zend_gui.return_to_address',
				'options' => array(
						'label' => 'Return-to Address',
				),
				'attributes' => array(
						'value' => Module::config('mail', 'return_to_address'),
						'type' => 'text',
						'section' => 'mail',
						'description' => 'Enter an email address for responding emails',
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
			'return_to_address' => array('validators' => array(new EmailAddress()), 'allow_empty' => true),
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