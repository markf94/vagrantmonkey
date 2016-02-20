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
use Zend\Form\Element\Radio;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Hidden;
use Zend\Form\Annotation\Input;
use ZendServer\Log\Log;

class DevBar extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
			 ->setName('devbar-settings')
			 ->setAttribute('action', 'DevBar')
			 ->setLabel(_t('Advanced Settings'))
			 ->setAttribute('description', _t('Configure Z-Ray advanced settings:'));
		
		// gui URL
		$this->add(array(
		    'name' => 'zray.zendserver_ui_url',
		    'options' => array(
		        'label' => _t('Z-Ray URL'),
		    ),
		    'attributes' => array(
		        'value' => $options['zray.zendserver_ui_url']->getFileValue(),
		        'type' => 'text',
		        'size' => 60,
		        'section' => '',
		        'id' => 'zray.zendserver_ui_url',
		        'placeholder' => _t('Default: auto-generated URL like http://localhost:10081/ZendServer'),
		        'description' => _t('Enter a URL for displaying Z-Ray. For example, the default URL generated by the system is \'http://localhost:10081/ZendServer\'. If you are using a virtualized file system, or have forwarded ports in your network, you may have to specify your own full URL with a port and the \'/ZendServer\' path.'),
		    )
		));

		$showInIframe = new Radio('zend_gui.showInIframe');
		$showInIframe->setLabel(_t('Show in iFrame'));
		$showInIframe->setAttribute('description', _t('Display Z-Ray for each iFrame on the page, or aggregate request information for the entire page in one Z-Ray.'));
		$showInIframe->setAttribute('section', '');
		$showInIframe->setValueOptions(array(
		    array('label' => _t('Yes'), 'value' => 1),
		    array('label' => _t('No'), 'value' => 0),
		));
		$showInIframe->setValue($options['showInIframe']->getFileValue());
		$this->add($showInIframe);
		
		// Maximum requests log
		$this->add(array(
		    'name' => 'zend_gui.maxRequests',
		    'options' => array(
		        'label' => _t('Maximum Requests'),
		    ),
		    'attributes' => array(
		        'value' => $options['maxRequests']->getFileValue(),
		        'type' => 'number',
		        'size' => 4,
		        'section' => '',
		        'id' => 'zend_gui.maxRequests',
		        'placeholder' => 500,
		        'description' => _t('Define the maximum amount of requests Z-Ray can track per page before requiring an information cleanup.'),
		    )
		));
		
		// custom namespaces 
		$this->add(array(
				'name' => 'zend_gui.custom_namespaces',
				'options' => array(
						'label' => _t('Function Groups Filtering'),
				),
				'attributes' => array(
						'value' => Module::config('zray', 'custom_namespaces'),
						'type' => 'text',
						'size' => 60,
						'section' => '',
						'description' => _t('Add a comma-separated list of function group names for filtering functions in Z-Ray.'),
				)
		));		

		// Maximum elements in tree level
		$this->add(array(
				'name' => 'zend_gui.maxElementsPerLevel',
				'options' => array(
						'label' => _t('Tree-View Maximum Items per Level'), 
				),
				'attributes' => array(
						'value' => $options['maxElementsPerLevel']->getFileValue(),
						'type' => 'number',
						'size' => 4,
						'section' => '',
						'id' => 'zend_gui.maxElementsPerLevel',
						'placeholder' => 30,
						'description' => _t('Define the maximum amount of items to be displayed for each level within a Z-Ray tree-view.'), 
				)
		));
		
		// Maximum depth level in tree
		$this->add(array(
				'name' => 'zend_gui.maxTreeDepth',
				'options' => array(
						'label' => _t('Tree-View Maximum Depth Level'),
				),
				'attributes' => array(
						'value' => $options['maxTreeDepth']->getFileValue(),
						'type' => 'number',
						'size' => 2,
						'section' => '',
						'id' => 'zend_gui.maxTreeDepth',
						'placeholder' => 7,
						'description' => _t('Define the maximum depth in a Z-Ray tree-view.'),
				)
		));
		
		// Maximum elements in tree
		$this->add(array(
				'name' => 'zend_gui.maxElementsInTree',
				'options' => array(
						'label' => _t('Tree-View Maximum Items'), 
				),
				'attributes' => array(
						'value' => $options['maxElementsInTree']->getFileValue(),
						'type' => 'number',
						'size' => 4,
						'section' => '',
						'id' => 'zend_gui.maxElementsInTree',
						'placeholder' => 500,
						'description' => _t('Define the maximum amount of items to be displayed in a Z-Ray tree-view (including top and sub levels).<br>Please note that defining high values might effect your browser\'s performance.'), 
				)
		));
		
		// collapse hotkeys
		$this->add(array(
		    'name' => 'zend_gui.collapse',
		    'options' => array(
		        'label' => _t('Collapse/Expand Keyboard Shortcut'),
		    ),
		    'attributes' => array(
		        'value' => strtoupper($options['collapse']->getFileValue()),
		        'type' => 'text',
		        'size' => 60,
		        'section' => '',
		        'id' => 'zend_gui.collapse',
		        'placeholder' => _t('Default: CTRL+ALT+C'),
		        'description' => _t('Enter a keyboard shortcut for collapsing and expanding Z-Ray. To disable the shortcut, leave the field empty.'),
		    )
		));
		
		// hide cleanup settings for Z-Ray standalone
		if (!isZrayStandaloneEnv()) {
			
			$this->add(array(
				'name' => 'cleanUpLabel',
				'options' => array(
						'label' => _t('Clean Up Settings:'),
				),
				'attributes' => array(
						'type' => 'number',
						'id' => 'cleanUpLabel',
				)
			));

			// zray history time in days
			$this->add(array(
					'name' => 'zray.history_time',
					'options' => array(
							'label' => _t('History Time Limit'),
					),
					'attributes' => array(
							'value' => $options['historyTime']->getFileValue(),
							'type' => 'number',
							'size' => 4,
							'section' => '',
							'id' => 'zray.history_time',
							'placeholder' => 7,
							'description' => _t('Time limit for kept requests data (in days).'),
					)
			));
			

			// Database size limit
			$this->add(array(
					'name' => 'zray.max_db_size',
					'options' => array(
							'label' => _t('Database size limit'),
					),
					'attributes' => array(
							'value' => $options['maxDbSize']->getFileValue(),
							'type' => 'number',
							'size' => 4,
							'section' => '',
							'id' => 'zray.max_db_size',
							'placeholder' => 1,
							'description' => _t('Database size limit for requests data (in GB).'),
					)
			));

			// Cleanup frequency
			$this->add(array(
					'name' => 'zray.cleanup_frequency',
					'options' => array(
							'label' => _t('Cleanup frequency'),
					),
					'attributes' => array(
							'value' => $options['cleanupFrequency']->getFileValue(),
							'type' => 'number',
							'size' => 4,
							'section' => '',
							'id' => 'zray.cleanup_frequency',
							'placeholder' => 10,
							'description' => _t('Frequency for automatic cleanup (in minutes).'),
					)
			));
		}
		
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