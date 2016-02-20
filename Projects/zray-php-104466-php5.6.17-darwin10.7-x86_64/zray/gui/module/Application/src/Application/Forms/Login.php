<?php
namespace Application\Forms;

use Zend\InputFilter\Factory;

use Zend\InputFilter\InputFilter;

use Zend\Form\Element;

use Zend\Form;
use Application\Module;
use Zend\Validator\Regex as Regex;
use Zend\Validator\NotEmpty;

class Login extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
			 ->setName('login');
		
		if (isset($options['simpleAuth']) && $options['simpleAuth']) {
			$this->add(array(
					'name' => 'username-field',
					'type' => 'Zend\Form\Element\Select',
					'options' => array(
							'label' => _t('Username'),
							'options' => $options['users']
					),
			));			
		} else {
			$this->add(array(
					'name' => 'username-field',
					'attributes' => array(
							'type' => 'text',
							'placeholder' => _t('Username'),
							'required' => 'required'
					)
			));			
		}
		
		$this->add(array(
				'name' => 'username',
				'attributes' => array(
						'type' => 'hidden',
				)
		));
		
		$this->add(array(
			'name' => 'password',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => _t('Password'),
				'required' => 'required',
			)
		));
		$this->add(array(
			'name' => 'redirectTo',
			'type' => 'Zend\Form\Element\Hidden'
		));
		
		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Login'
			)
		));
		
		// add the actual validators
		$inputFactory = new Factory();
		$validators = $inputFactory->createInputFilter(array(
				'username-field' => array('validators' => array(new NotEmpty()), 'allow_empty' => false),
				'username' => array('validators' => array(new NotEmpty()), 'allow_empty' => false),
				'password' => array('validators' => array(new NotEmpty()), 'allow_empty' => false),
		));
		$this->setInputFilter($validators);
	}
}

