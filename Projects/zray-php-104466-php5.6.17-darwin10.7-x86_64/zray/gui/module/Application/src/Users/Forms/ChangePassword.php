<?php
namespace Users\Forms;

use Zend\Form;
use Application\Module;
use Zend\Validator\Regex as Regex;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;

class ChangePassword extends Form\Form {
	
	const PASSWORD_PATTERN = '^[\w\]\[!"#$%&\'()*+,.\/:;<=>?@\^_`{|}~-]+$';
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('id', 'ChangePasswordForm');
		
		$this->add(array(
				'name' => 'username',
				'attributes' => array(
						'id' => 'username',
						'type' => 'hidden',
						'required' => true,
				)
		));
		
		$this->add(array(
				'name' => 'password',
				'options' => array(
						'label' => _t('Current Password'),
				),
				
				'attributes' => array(
						'id' => 'password',
						'type' => 'password',
						'description' => '<em>' . _t('Enter your current password for authentication') . '</em>',
						'required' => true,
		)));
		
		$this->add(array(
				'name' => 'newPassword',
				'options' => array(
					'label' => _t('New Password'),
				),
				'attributes' => array(
						'id' => 'newPassword',
						'type' => 'password',
						'required' => true,
				)
		));

		$this->add(array(
				'name' => 'confirmNewPassword',
				'options' => array(
					'label' => _t('Confirm New Password'),
				),
				'attributes' => array(
						'id' => 'confirmNewPassword',
						'type' => 'password',
						'required' => true,
				)
		));
		
	}
}

