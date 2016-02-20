<?php
namespace Audit\Forms;

use Zend\Validator\EmailAddress;
use ZendServer\Validator\AbsoluteUriPath;
use Zend\Form;
use Application\Module;
use Zend\Validator\Regex as Regex;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;

class Settings extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('id', 'AuditSettingsForm');
		
		$this->add(array(
				'name' => 'email',
				'options' => array('label' => _t('Email')),
				'attributes' => array(
						'type' => 'text',
						'id' => 'email',
						'placeholder' => _t('name@example.com'),
						'description' => _t('Notify this email address when an action is performed'),
				)
		));
		$this->add(array(
				'name' => 'callbackUrl',
				'options' => array('label' => _t('Callback URL')),
				'attributes' => array(
						'type' => 'text',
						'id' => 'callbackUrl',
						'placeholder' => _t('http://url.com/filename.php'),
						'description' => _t('Call this URL when an action is performed')
						
				)
		));
		
		$inputFactory = new Factory();
		$validators = $inputFactory->createInputFilter(array(
				'callbackUrl' => array('validators' => array(new AbsoluteUriPath()), 'allow_empty' => true),
				'email' => array('validators' => array(new EmailAddress()), 'allow_empty' => true)
		));
		$this->setInputFilter($validators);
			
	}
}
