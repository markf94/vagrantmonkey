<?php
namespace Servers\Forms;

use Zend\Form;

class JoinCluster extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this
			 ->setName('join-cluster-form')
			 ->setAttribute('onclick', 'return false;');
		
		$this->add(array(
			'name' => 'serverName',
			'attributes' => array(
				'type' => 'text',
				'placeholder' => _t('Server Name'),
				'value' => gethostname()
			)
		));
		
		$nodeIdElement = array(
			'name' => 'nodeIp',
			'attributes' => array(
				'type' => 'text'
			)
		);
		
		isset($_SERVER['SERVER_ADDR']) ? $addr = $_SERVER['SERVER_ADDR'] : $addr = $_SERVER['LOCAL_ADDR']; // on IIS7, SERVER_ADDR is not used
		if ($addr != 'localhost' && $addr != '127.0.0.1' && $addr != '::1') {
			$nodeIdElement['attributes']['value'] = $addr;
		}
		$this->add($nodeIdElement);

		$this->add(array(
			'name' => 'dbHost',
			'attributes' => array(
				'type' => 'text'
			)
		));

		$this->add(array(
			'name' => 'dbName',
			'attributes' => array(
				'type' => 'text'
			)
		));

		$this->add(array(
			'name' => 'dbUsername',
			'attributes' => array(
				'type' => 'text'
			)
		));

		$this->add(array(
			'name' => 'dbPassword',
			'attributes' => array(
				'type' => 'password'
			)
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'id' => 'join-cluster-form-submit',
				'type' => 'submit',
				'value' => _t('Join Cluster'),
				'onclick' => 'joinToCluster()'
			)
		));

		$this->add(array(
			'name' => 'cancel',
			'attributes' => array(
				'type' => 'button',
				'value' => _t('Cancel'),
				'onclick' => 'hideJoinClusterTooltip()'
			)
		));
	}
}

