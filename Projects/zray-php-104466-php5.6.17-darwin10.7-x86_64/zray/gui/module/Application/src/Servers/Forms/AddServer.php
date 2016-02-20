<?php
namespace Servers\Forms;

use Zend\Form;
use Application\Module;
use Zend\Validator\Regex as Regex;

class AddServer extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setName('add-server-form')
			 ->setAttribute('onclick', 'return false;');
		
		$this->add(array(
				'name' => 'serverName',
				'attributes' => array(
						'type' => 'text',
						'placeholder' => _t('Server Name')
				)
		));
		
		$this->add(array(
				'name' => 'serverIp',
				'attributes' => array(
						'type' => 'text',
						'placeholder' => _t('Server IP')
				)
		));
		
		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
						'type' => 'submit',
						'value' => _t('Add Server'),
						'onclick' => 'AddServerToCluster()'
				)
		));
		

		$this->add(array(
				'name' => 'cancel',
				'attributes' => array(
						'type' => 'button',
						'value' => _t('Cancel'),
						'onclick' => 'hideAddServerTooltip()'
				)
		));
	}
}

