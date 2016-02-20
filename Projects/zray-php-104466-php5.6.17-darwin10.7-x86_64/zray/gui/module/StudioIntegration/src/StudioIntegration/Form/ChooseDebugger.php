<?php
namespace StudioIntegration\Form;

use Zend\Form\Fieldset;
use StudioIntegration\Configuration as StudioConfig;
use StudioIntegration\ConfigurationHydrator;
use Zend\Form\Form;

class ChooseDebugger extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('choose-debugger');
        $this->add(array(
            'name' => 'Debugger',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    array(
                        'label_attributes' => array(
                            'id' => 'choose-debugger-zend-label',
                        ),
                        'attributes' => array(
                            'id' => 'choose-debugger-zend'
                        ),
                        'label' => _t('Zend Debugger'),
                        'value' => 'Zend Debugger'
                    ),
                    array(
                        'label_attributes' => array(
                            'id' => 'choose-debugger-xdebug-label',
                        ),
                        'attributes' => array(
                            'id' => 'choose-debugger-xdebug'
                        ),
                        'label' => _t('Xdebug'),
                        'value' => 'xdebug'
                    ),
                    array(
                        'label_attributes' => array(
                            'id' => 'choose-debugger-none-label',
                        ),
                        'attributes' => array(
                            'id' => 'choose-debugger-none'
                        ),
                        'label' => _t('None'),
                        'value' => 'none'
                    ),
                )
            )
        ));
    }
}

