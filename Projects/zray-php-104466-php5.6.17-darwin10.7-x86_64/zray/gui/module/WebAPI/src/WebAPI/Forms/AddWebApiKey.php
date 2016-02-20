<?php
namespace WebAPI\Forms;

use Zend\Authentication\Storage\Session;

use Zend\Form,
	Zend\InputFilter\Factory,
	Deployment\SessionStorage,
	Deployment\Model,
	Deployment\Application\Package,
	ZendServer\Validator\ApiKeyName,
	ZendServer\Log,
	ZendServer\Text,
	ZendServer\Exception;
use Zend\Validator\StringLength;

class AddWebApiKey extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);

		$this->setName('addWebApiKey');
		$this->setAttribute('action', '');
		$this->setAttribute('onclick', 'return false;');

		$this->add(array(
				'name' => 'name',
				'attributes' => array(
						'type' => 'text',
						'placeholder' => _t('Key Name')
				)
		));

		$this->add(array(
				'name' => 'username',
				'attributes' => array(
						'type' => 'text',
						'placeholder' => _t('Bound User')
				)
		));
		
		$this->add(array(
				'name' => 'user',
				'attributes' => array(
						'type' => 'select',
						'placeholder' => _t('Access Level')
				)
		));
		
		$this->add(array(
				'name' => 'hash',
				'attributes' => array(
						'type' => 'string',
				),
		));
		
		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
						'type' => 'submit',
						'value' => _t('Add Key'),
						'onclick' => 'addKey()'
				)
		));

		$this->add(array(
				'name' => 'cancel',
				'attributes' => array(
						'type' => 'button',
						'value' => _t('Cancel'),
						'onclick' => 'hideAddKeyTip()'
				)
		));
		
		$inputFactory = new Factory();
		$validators = $inputFactory->createInputFilter(array(
				'name' => array('validators' => array(new ApiKeyName())),
				'hash' => array(
						'validators' => array(new StringLength(array('min' => 64, 'max' => 64))),
						'allow_empty' => true,
						'required' => true
				)
		));
		$this->setInputFilter($validators);
	}
	
	public function setUsers($users) {
		$baseUrl = $this->get('user')->setAttribute('options', $users);
	}
}

