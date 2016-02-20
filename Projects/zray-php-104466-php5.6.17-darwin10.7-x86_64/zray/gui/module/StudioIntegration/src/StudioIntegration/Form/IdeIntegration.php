<?php
namespace StudioIntegration\Form;

use Zend\Form\Fieldset;
use StudioIntegration\Configuration as StudioConfig;
use StudioIntegration\ConfigurationHydrator;
use Zend\Form\Form;

class IdeIntegration extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('zend-debugger-settings');

        $this->add(array(
            'name' => 'studioBreakOnFirstLine',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    array(
                        'attributes' => array('id' => 'studio-BreakOnFirstLine-true'),
                        'label' => _t('On'),
                        'value' => 1,
                    ),
                    array(
                        'attributes' => array('id' => 'studio-BreakOnFirstLine-false'),
                        'label' => _t('Off'),
                        'value' => 0,
                    ),
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'studioUseRemote',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    array(
                        'attributes' => array('id' => 'studio-UseRemote-true'),
                        'label' => _t('On'),
                        'value' => 1,
                    ),
                    array(
                        'attributes' => array('id' => 'studio-UseRemote-false'),
                        'label' => _t('Off'),
                        'value' => 0,
                    ),
                ),
            ),
        ));

    }
}

