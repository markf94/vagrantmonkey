<?php
namespace DevBar\Forms\Settings;

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

class DataGranularityDevBar extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->setAttribute('method', 'post')
			 ->setName('devbar-granularity')
			 ->setAttribute('action', 'DevBar')
			 ->setLabel(_t('Data Collection Settings'))
			 ->setAttribute('description', _t('Configure Z-Ray data collection settings:'));
		
		$collectBacktrace = new Radio('zray.collect_backtrace');
		$collectBacktrace->setLabel(_t('Enable Backtracing'));
		$collectBacktrace->setAttribute('description', _t('Enable or disable backtracing in Z-Ray.'));
		$collectBacktrace->setAttribute('section', '');
		$collectBacktrace->setOptions(array_merge($collectBacktrace->getOptions(), array('sub-section-parent' => true)));
		$collectBacktrace->setValueOptions(array(
				array('label' => _t('Yes'), 'value' => 1),
				array('label' => _t('No'), 'value' => 0),
		));
		$collectBacktrace->setValue($options['collectBacktrace']->getFileValue());
		$this->add($collectBacktrace);
		
		$collectBacktraceErrors = new Radio('zray.collect_backtrace.errors_warnings');
		$collectBacktraceErrors->setLabel(_t('Enable Backtracing for Errors and Warnings'));
		$collectBacktraceErrors->setAttribute('id', 'zray.collect_backtrace.errors_warnings');
		$collectBacktraceErrors->setAttribute('description', _t('Enable or disable collection of backtraces for Errors and Warnings.'));
		$collectBacktraceErrors->setAttribute('section', '');
		$collectBacktraceErrors->setValueOptions(array(
		    array('label' => _t('Yes'), 'value' => 1),
		    array('label' => _t('No'), 'value' => 0),
		));
		$collectBacktraceErrors->setOptions(array_merge($collectBacktraceErrors->getOptions(), array('sub-section' => true)));
        $collectBacktraceErrors->setValue($options['collectBacktraceErrors']->getFileValue());
		$this->add($collectBacktraceErrors);
		
		// Enable collection backtrace for SQL queries 
		$collectBacktraceSQL = new Radio('zray.collect_backtrace.sql_queries');
		$collectBacktraceSQL->setLabel(_t('Collect Backtrace for Database queries'));
		$collectBacktraceSQL->setAttribute('id', 'zray.collect_backtrace.sql_queries');
		$collectBacktraceSQL->setAttribute('description', _t('Enable or disable collection of backtraces for Database Queries.'));
		$collectBacktraceSQL->setAttribute('section', '');
		$collectBacktraceSQL->setValueOptions(array(
		    array('label' => _t('Yes'), 'value' => 1),
		    array('label' => _t('No'), 'value' => 0),
		));
		$collectBacktraceSQL->setOptions(array_merge($collectBacktraceSQL->getOptions(), array('sub-section' => true)));
		$collectBacktraceSQL->setValue($options['collectBacktraceSQL']->getFileValue());
		$this->add($collectBacktraceSQL);

		// Enable collection extension data
		$collectExtensionData = new Radio('zray.enable_extensibility');
		$collectExtensionData->setLabel(_t('Collect Extension Data'));
		$collectExtensionData->setAttribute('id', 'zray.enable_extensibility');
		$collectExtensionData->setAttribute('description', _t('Enable or disable collection of data for Z-Ray extensions. Disabling data collection will remove extension panels from Z-Ray.'));
		$collectExtensionData->setAttribute('section', '');
		$collectExtensionData->setValueOptions(array(
		    array('label' => _t('Yes'), 'value' => 1),
		    array('label' => _t('No'), 'value' => 0),
		));
		$collectExtensionData->setValue($options['collectExtensionData']->getFileValue());
		$this->add($collectExtensionData);
		
		//  Max number of log entries in the Errors & Warnings panel
		$this->add(array(    'name'           => 'zray.max_number_log_entries',
                    		 'options'        => array('label' => _t('Max Log Entries'),),
                    		 'attributes'     => array(
                    		 'value'          => $options['max_number_log_entries']->getFileValue(),
                    		 'type'           => 'number',
                    		 'size'           => 5,
                    		 'section'        => '',
                    		 'id'             => 'zray.max_number_log_entries',
                    		 'placeholder'    => 10000,
                    		 'description'    => _t('Set the max number of log entries displayed in the Errors & Warnings panel.'),)
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