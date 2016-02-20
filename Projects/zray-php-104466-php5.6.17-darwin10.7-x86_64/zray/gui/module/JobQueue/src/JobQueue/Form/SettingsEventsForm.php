<?php
namespace JobQueue\Form;

use Zend\Form\Form;

class SettingsEventsForm extends Form
{

	public function __construct($name = null)
	{
		parent::__construct('Settings');
		
		$this->setAttribute('id', 'JobQueue_settings');
		
		// Events
		
		// Fire Job Execution Delay events
		
		$this->add(array(
			'name' => 'job_execution_delay_event_enabled',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => _t('Job execution delay event enabled'),
				'section' => 'Job Execution Delay',
				'section_description' => '',
				'section_image' => '/images/settings/notification-center.png',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_delay_event_enabled',
				'title' => 'Report an event when a job exceeds its defined execution time',
				'required' => false,
			)
		));
		
		$this->add(array(
			'name' => 'job_time_skew_allowed',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Max job execution delay'),
				'section' => 'section_sub',
				'suffix' => 'seconds',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_time_skew_allowed',
				'title' => 'Define the maximum waiting time for a job after which an event is triggered',
				'required' => false,
				'value' => 1,
			)
		));
		
		$this->add(array(
			'name' => 'job_execution_delay_event_severity',
			'type' => 'Zend\Form\Element\Select',
			'options' => array(
				'label' => _t('Severity'),
				'section' => 'section_sub',
				'value_options' => array(
					'1' => 'Critical',
					'0' => 'Warning',
					'-1' => 'Notice',
				),
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_delay_event_severity',
				'title' => 'Select the severity level for this event',
				'required' => false,
				'value' => '1', // "Critical"
			)
		));
		
		$this->add(array(
			'name' => 'job_execution_delay_event_email',
			'type' => 'Zend\Form\Element\Email',
			'options' => array(
				'label' => _t('Send email'),
				'section' => 'section_sub',
				
				// is the field optional? if `optional` is set, than a checkbox will appear above / before the field
				'optional' => array(
					'title' => 'Check to enable email sending',
					'enabled' => false, // is enabled by default?
				),
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_delay_event_email',
				'title' => 'Enter an email to receive a notification when this event is triggered',
				'type' => 'email',
				'required' => false,
				'placeholder' => 'jon.snow@example.com',
			)
		));
		
		$this->add(array(
			'name' => 'job_execution_delay_event_call_url',
			'type' => 'Zend\Form\Element\Url',
			'options' => array(
				'label' => _t('Call URL'),
				'section' => 'section_sub',
				
				'optional' => array(
					'enabled' => false, // is enabled by default?
					'title' => 'Check to enable calling URL',
				),
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_delay_event_call_url',
				'title' => 'Enter a URL to be called when this event is triggered',
				'type' => 'url',
				'required' => false,
				'placeholder' => 'http://example.com/path/to/script.php',
			)
		));
		
		
		
		// Fire Job Execution Error events
		
		$this->add(array(
			'name' => 'job_execution_error_event_enabled',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => _t('Job execution error event enabled'),
				'section' => 'Job Execution Error',
				'section_description' => '',
				'section_image' => '/images/settings/notification-center.png',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_error_event_enabled',
				'title' => 'Report an event when a job fails execution',
				'required' => false,
			)
		));
		
		$this->add(array(
			'name' => 'job_execution_error_event_severity',
			'type' => 'Zend\Form\Element\Select',
			'options' => array(
				'label' => _t('Severity'),
				'value_options' => array(
					'1' => 'Critical',
					'0' => 'Warning',
					'-1' => 'Notice',
				),
				'section' => 'section_sub',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_error_event_severity',
				'title' => 'Select the severity level for this event',
				'required' => false,
				'value' => '1', // "Critical"
			)
		));
		
		$this->add(array(
			'name' => 'job_execution_error_event_email',
			'type' => 'Zend\Form\Element\Email',
			'options' => array(
				'label' => _t('Send email'),
				'section' => 'section_sub',
				
				'optional' => array(
					'enabled' => false, // is enabled by default?
					'title' => 'Check to enable email sending',
				),
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_error_event_email',
				'title' => 'Enter an email to receive a notification when this event is triggered',
				'type' => 'email',
				'required' => false,
				'placeholder' => 'jon.snow@example.com',
			)
		));
		
		$this->add(array(
			'name' => 'job_execution_error_event_call_url',
			'type' => 'Zend\Form\Element\Url',
			'options' => array(
				'label' => _t('Call URL'),
				'section' => 'section_sub',
				
				'optional' => array(
					'enabled' => false, // is enabled by default?
					'title' => 'Check to enable calling URL',
				),
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_execution_error_event_call_url',
				'title' => 'Enter a URL to be called when this event is triggered',
				'type' => 'url',
				'required' => false,
				'placeholder' => 'http://example.com/path/to/script.php',
			)
		));
		
		
		// Fire Logical Failure events
		$this->add(array(
			'name' => 'job_logical_error_event_enabled',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => _t('Job logical error event enabled'),
				'section' => 'Job Logical Error',
				'section_description' => '',
				'section_image' => '/images/settings/notification-center.png',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_logical_error_event_enabled',
				'title' => 'Report an event when a logical error occurs during job execution',
				'required' => false,
			)
		));
		
		$this->add(array(
			'name' => 'job_logical_error_event_severity',
			'type' => 'Zend\Form\Element\Select',
			'options' => array(
				'label' => _t('Severity'),
				'value_options' => array(
					'1' => 'Critical',
					'0' => 'Warning',
					'-1' => 'Notice',
				),
				'section' => 'section_sub',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_logical_error_event_severity',
				'title' => 'Select the severity level for this event',
				'required' => false,
				'value' => '1', // "Critical"
			)
		));
		
		$this->add(array(
			'name' => 'job_logical_error_event_email',
			'type' => 'Zend\Form\Element\Email',
			'options' => array(
				'label' => _t('Send email'),
				'section' => 'section_sub',
				
				'optional' => array(
					'enabled' => false, // is enabled by default?
					'title' => 'Check to enable email sending',
				),
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_logical_error_event_email',
				'title' => 'Enter an email to receive a notification when this event is triggered',
				'type' => 'email',
				'required' => false,
				'placeholder' => 'jon.snow@example.com',
			)
		));
		
		$this->add(array(
			'name' => 'job_logical_error_event_call_url',
			'type' => 'Zend\Form\Element\Url',
			'options' => array(
				'label' => _t('Call URL'),
				'section' => 'section_sub',
				
				'optional' => array(
					'enabled' => false, // is enabled by default?
					'title' => 'Check to enable calling URL',
				),
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_job_logical_error_event_call_url',
				'title' => 'Enter a URL to be called when this event is triggered',
				'type' => 'url',
				'required' => false,
				'placeholder' => 'http://example.com/path/to/script.php',
			)
		));
			   
	}
}