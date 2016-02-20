<?php

namespace StudioIntegration\Form;

use Zend\Form\Fieldset;

use StudioIntegration\Configuration as StudioConfig;

use StudioIntegration\ConfigurationHydrator;

use Zend\Form\Form;

class Configuration extends Form {
	public function __construct($name = null) {
		parent::__construct('studio');
		$this->setHydrator(new ConfigurationHydrator());
		$this->setObject(new StudioConfig());
		$this->add(array(
				'name' => 'studioAutoDetection',
				'type' => 'Zend\Form\Element\Radio',
				'options' => array(
						'value_options' => array(
							array(
								'attributes' => array('id' => 'studio-autodetect-true'),
								'label' => _t('On'), 
								'value' => 1,
							),
							array(
								'attributes' => array('id' => 'studio-autodetect-false'),
								'label' => _t('Off'), 
								'value' => 0,
							),
						),
				),
		));
		$this->add(array(
				'name' => 'studioHost',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array(
						'id' => 'studio-host',
				)
		));
		
		$this->add(array(
				'name' => 'studioPort',
				'type' => 'Zend\Form\Element\Text',
				'attributes' => array(
						'id' => 'studio-port',
				)
		));
		
		$this->add(array(
				'name' => 'studioAutoDetectionEnabled',
				'type' => 'Zend\Form\Element\Checkbox',
				'attributes' => array(
						'id' => 'studio-autoDetectBrowser',
				)
		));
		
		$this->add(array(
				'name' => 'studioUseSsl',
				'type' => 'Zend\Form\Element\Checkbox',
				'attributes' => array(
						'id' => 'studio-useSsl',
				)
		));

	}
}

