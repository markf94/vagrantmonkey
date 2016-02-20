<?php

namespace Configuration\Forms;


use Zend\Form\Fieldset;
use Zend\Form\Form;

class MonitorSettings extends Form
{
	
	public function __construct($name = null)
	{
		parent::__construct('monitor-settings');
		
		// enable/disable monitor
		$this->add(array(
			'name' => 'monitoring',
			'type' => 'Zend\Form\Element\Radio',
			'options' => array(
				'label' => '',
				
				'section' => 'Monitoring Mode',
				'section_description' => 'Set the Zend Server monitoring mode.  Disabling Zend Server monitoring means that triggered events are not reported.',
				'section_image' => '/images/settings/audit-trail.png',
				
				'value_options' => array(
					array(
						'label_attributes' => array(
							'id' => 'monitor_enable_label'
						),
						'attributes' => array(
							'id' => 'monitor_enable_input'
						),
						'label' => _t('Enable Zend Server monitoring'),
						'value' => '1',
						'title' => ' ',
						'checked' => 'checked',
					),
					array(
						'label_attributes' => array(
							'id' => 'monitor_disable_label'
						),
						'attributes' => array(
							'id' => 'monitor_disable_input'
						),
						'label' => _t('Disable Zend Server monitoring'),
						'value' => '0',
						'title' => ' ',
					),
				)
			)
		));
		
		// events aggregation by: route, URL, none
		$this->add(array(
			'name' => 'events_aggregation',
			'type' => 'Zend\Form\Element\Radio',
			'options' => array(
				'label' => '',
				
				'section' => 'Events Aggregation',
				'section_description' => 'Event aggregation is used to group together similar events. Using aggregation is a good way to prevent event propagation which can consume database storage and server resources and affect performance.',
				'section_image' => '/images/settings/audit-trail.png',
				
				'value_options' => array(
					array(
						'label_attributes' => array(
							'id' => 'aggregate_by_route_label'
						),
						'attributes' => array(
							'id' => 'aggregate_by_route_input'
						),
						'label' => _t('Aggregate by route'),
						'value' => 'route',
						'title' => 'Aggregate Zend Server monitoring events triggered by requests with the same route. Zend Server can only aggregate events for applications<br>'.
							'with an existing route definition. Click <a href="javascript:;" id="plugin_concept_help_link">here</a> for more information. Note: If there is no available route definition, Zend Server will aggregate by URL',
						'checked' => 'checked',
					),
					array(
						'label_attributes' => array(
							'id' => 'aggregate_by_url_label'
						),
						'attributes' => array(
							'id' => 'aggregate_by_url_input'
						),
						'label' => _t('Aggregate by URL'),
						'value' => 'url',
						'title' => 'Aggregate Zend Server monitoring events triggered by requests with the same URL',
					),
					array(
						'label_attributes' => array(
							'id' => 'no_aggregation_label'
						),
						'attributes' => array(
							'id' => 'no_aggregation_input'
						),
						'label' => _t('No aggregation'),
						'value' => 'none',
						'title' => 'Disable Zend Server monitoring event aggregation. This mode is best suited for a development environment',
					),
				)
			)
		));
		
		// enable/disable monitor
		$this->add(array(
			'name' => 'code_tracing_for_events',
			'type' => 'Zend\Form\Element\Radio',
			'options' => array(
				'label' => '',
				
				'section' => 'Code Tracing for Events',
				'section_description' => 'Zend Code Tracing for events helps you analyze the root cause of a problem in your application by capturing the full execution of your application in real-time. If enabled, Zend Code Tracing will run in the background, and when an event is triggered, all trace information will be stored with the event.',
				'section_image' => '/images/settings/audit-trail.png',
				
				'value_options' => array(
					array(
						'label_attributes' => array(
							'id' => 'code_tracing_for_events_enable_label'
						),
						'attributes' => array(
							'id' => 'code_tracing_for_events_enable_input'
						),
						'label' => _t('Enable code tracing for event rules'),
						'value' => '2',
						'title' => ' ',
						'checked' => 'checked',
					),
					array(
						'label_attributes' => array(
							'id' => 'code_tracing_for_events_disable_label'
						),
						'attributes' => array(
							'id' => 'code_tracing_for_events_disable_input'
						),
						'label' => _t('Disable code tracing for event rules'),
						'value' => '0',
						'title' => ' ',
					),
				)
			)
		));
		
		//  Delete events which did not occur in the last X days
		$this->add(array(
			'name' => 'delete_events_occur',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => 'Delete events which did not occur in the last',
				'suffix' => 'days',
				
				'section' => 'Periodic Cleanup Settings',
				'section_description' => 'This area allows you to define which events will be deleted in Zend Server\'s cleanup process',
				'section_image' => '/images/settings/notification-center.png',
			),
			'attributes' => array(
				'id' => 'delete_events_occur',
				'value' => '30',
				'title' => 'Define the period of time (in days) after which events are to be deleted during the cleanup process',
			)
		));

		
		// monitoring rule default email
		$this->add(array(
			'name' => 'monitoring_rule_default_email',
			'type' => 'Zend\Form\Element\Text',
			'options' => array(
				'label' => 'Monitoring rule default email address',
				
				'section' => 'Triggered Actions Settings',
				'section_description' => 'This area allows you to define the default settings for triggered actions to be executed for events:',
				'section_image' => '/images/settings/general-settings.png',
			),
			'attributes' => array(
				'id' => 'monitoring_rule_default_email',
				'value' => '',
				'placeholder' => 'foo.bar@example.com',
				'size' => '60',
				'title' => 'Enter a comma-separated list of email addresses for receiving event information',
			)
		));
		
		$this->add(array(
			'name' => 'monitoring_rule_default_callback_url',
			'type' => 'Zend\Form\Element\Text',
			'options' => array(
				'label' => 'Monitoring rule default callback URL',
				
				'section' => 'Triggered Actions Settings',
			),
			'attributes' => array(
				'id' => 'monitoring_rule_default_callback_url',
				'value' => '',
				'placeholder' => 'http://example.com/foo/bar.php',
				'size' => '60',
				'title' => 'Enter a callback URL for a customized action to be executed for each event',
			)
		));
		
		$this->add(array(
			'name' => 'applyToExisting',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => 'Apply to existing monitor rules',
				
				'section' => 'Triggered Actions Settings',
			),
			'attributes' => array(
				'id' => 'applyToExisting',
				'title' => 'Apply the default email and URL to existing monitoring rules',
			)
		));
		
	}
}
