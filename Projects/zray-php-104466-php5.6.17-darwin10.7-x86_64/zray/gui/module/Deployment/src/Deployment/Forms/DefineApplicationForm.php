<?php
namespace Deployment\Forms;

use Zend\Form,
	Zend\InputFilter\Factory;
use ZendServer\Validator\UriPath,
	Deployment\Validator\ApplicationNameNotExists,
	Deployment\Validator\ApplicationBaseUrlNotExists,
	Deployment\Forms\ApplicationsAwareForm;

class DefineApplicationForm extends ApplicationsAwareForm {
	
	public function __construct($options, $deploymentModel) {
		parent::__construct($options, $deploymentModel);
		
		$this->setAttribute('id', 'defineApplicationForm');

		$this->add(array(
				'name' => 'baseUrl',
				'options' => array(
						'label' => _t('Base URL'),
				),
				'attributes' => array(
						'id' => 'baseUrl',
						'type' => 'text',
						'onkeydown' => 'changeBaseUrl()',
						'required' => true,
						'placeholder' => _t('e.g. http://my.website.com/path ...')
				)
		));
		
		$this->add(array(
				'name' => 'name',
				'options' => array(
						'label' => _t('Application Name'),
				),
				'attributes' => array(
						'id' => 'name',
						'type' => 'text',
						'placeholder' => _t('e.g. My Application'),
						'onkeyup' => 'changeAppName()',
						'required' => true,
				)
		));
		
		$this->add(array(
				'name' => 'version',
				'options' => array(
						'label' => _t('Version'),
				),
				'attributes' => array(
						'id' => 'version',
						'type' => 'text',
						'placeholder' => _t('e.g. 1.0')
				)
		));
		
		$this->add(array(
				'name' => 'logo',
				'options' => array(
						'label' => _t('Logo'),
				),
				'attributes' => array(
						'type' => 'hidden',
						'id' => 'logo',
						'placeholder' => _t('Logo')
				)
		));
		
		
		$this->add(array(
				'name' => 'healthCheck',
				'options' => array(
						'label' => _t('Health Check'),
				),
				'attributes' => array(
						'id' => 'healthCheck',
						'type' => 'text',
						'placeholder' => _t('e.g. /index.php'),
						'description' => _t('This URL polls your application for issues.')
				)
		));
		
		
		$inputFactory = new Factory();
		$validators = $inputFactory->createInputFilter(array(
				'baseUrl' => array('validators' => array(new ApplicationBaseUrlNotExists(array(), $this->_deploymentModel))),
				'healthCheck' => array('validators' => array(new UriPath())),
				'name' => array('validators' => array(new ApplicationNameNotExists(array(), $this->_deploymentModel)))
		));
		$this->setInputFilter($validators);
		
	}
	
	public function setBaseUrls($baseUrls) {
		$this->get('baseUrl')->setAttribute('options', $baseUrls);
	}
	
}

