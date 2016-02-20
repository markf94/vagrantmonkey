<?php

namespace StudioIntegration\Form;

use Zend\Form\Form;

class HostsList extends Form {
	public function __construct($name = null) {
		parent::__construct('hosts');
		$this->add(array(
				'name' => 'studioAllowedHostsList',
				'type' => 'Zend\Form\Element\Hidden',
		));
		
		$this->add(array(
				'name' => 'studioDeniedHostsList',
				'type' => 'Zend\Form\Element\Hidden',
		));
		
	}
}

