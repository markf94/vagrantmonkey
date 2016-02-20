<?php
namespace Application\Forms\Settings;

use Zend\InputFilter\Factory,
	Zend\Form,
	Zend\Form\Element\Select,
	Zend\Validator\GreaterThan,
	Zend\Validator\Hostname,
	Application\Validators\DefaultServer,
	Zend\Validator\Digits,
	Application\Module;

use Zend\Validator\Uri;

class General extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
			 ->setName('general-settings')
			 ->setAttribute('action', 'General')
			 ->setLabel('General Settings')
			 ->setAttribute('description', 'This area allows you to configure Zend Server general settings to better suit your personal preferences and working environment:');

		$this->add(array(
			'name' => 'zend_gui.resultsPerPage',
			'options' => array(
				'label' => 'Results per page',
			),
			'attributes' => array(
				'value' => Module::config('list', 'resultsPerPage'),
				'type' => 'text',
				'section' => 'list',
				'description' => 'Set the amount of entries to be displayed on pages containing listed items',
			)
		));

		$this->add(array(
			'name' => 'zend_gui.defaultServer',
			'options' => array(
				'label' => 'Default Server',
			),
			'attributes' => array(
				'value' => Module::config('deployment', 'defaultServer'),
				'type' => 'text',
				'section' => 'deployment',
				'description' => 'Define the default server to be used in application URLs for deploying and defining new applications',
			)
		));
		
		$this->add(array(
				'name' => 'zend_monitor.gui_host_name',
				'options' => array(
						'label' => 'External URL (zend_monitor.gui_host_name)',
				),
				'attributes' => array(
						'value' => $options['guiHostName']->getFileValue(),
						'type' => 'text',
						'section' => '',
						'description' => 'Define a URL to be used for external references to Zend Server (e.g. mysite.com)',
				)
		));
		
		$logVerbosityOptions = array(
				array('label' => 'Debug', 'value' => 'DEBUG'),
				array('label' => 'Info', 'value' => 'INFO'),
				array('label' => 'Notice', 'value' => 'NOTICE'),
				array('label' => 'Warning', 'value' => 'WARN'),
				array('label' => 'Error', 'value' => 'ERR'),
				array('label' => 'Critical', 'value' => 'CRIT'),
				array('label' => 'Alert', 'value' => 'ALERT'),
				array('label' => 'Emergency', 'value' => 'EMERG'),
		);
		
		$logVerbosity = new Select('zend_gui.logVerbosity');
		$logVerbosity->setLabel('Log Verbosity');
		$logVerbosity->setAttribute('description', 'Select the level of verbosity for the UI log displayed on the Logs page');
		$logVerbosity->setAttribute('section', 'logging');		
		$logVerbosity->setValueOptions($logVerbosityOptions);
		$logVerbosity->setValue(Module::config('logging', 'logVerbosity'));
				
		$this->add($logVerbosity);
		
		$this->add(array(
			'name' => 'zend_gui.timeout',
			'options' => array(
				'label' => 'Logout Timeout',
			),
			'attributes' => array(
				'value' => Module::config('logout', 'timeout'),
				'type' => 'text',
				'section' => 'logout',
				'description' => 'Set the timeout (mins) for automatic user logout (to disable, set to 0)',
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
			'resultsPerPage'	=> array('validators' => array(new Digits(), new GreaterThan(array('min' => 0))), 'allow_empty' => false),
			'defaultServer' 	=> array('validators' => array( new DefaultServer()), 'allow_empty' => true),
			'timeout' 			=> array('validators' => array(new Digits(), new GreaterThan(array('min' => -1))), 'allow_empty' => false),
			'externalUrl' 		=> array('validators' => array(new Uri(array('allowRelative' => true, 'allowAbsolute' => true))), 'allow_empty' => true),
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