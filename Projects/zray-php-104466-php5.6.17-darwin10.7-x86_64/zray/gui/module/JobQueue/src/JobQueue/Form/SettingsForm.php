<?php
namespace JobQueue\Form;

use Zend\Form\Form;

class SettingsForm extends Form
{
	
	public function __construct($name = null, $isForCluster = false)
	{
		parent::__construct('Settings');
		
		$this->setAttribute('id', 'JobQueue_settings');
		
		// Job Output
		$this->add(array(
			'name' => 'jobs_output',
			'type' => 'Zend\Form\Element\Radio',
			'options' => array(
				'label' => '',
				
				'section' => 'Job Output',
				'section_description' => 'Configure how Zend Server handles jobs output',
				'section_image' => '/images/settings/audit-trail.png',
				
				'value_options' => array(
					array(
						'label_attributes' => array(
							'id' => 'jobs_output_all_label'
						),
						'attributes' => array(
							'id' => 'jobs_output_all_input'
						),
						'label' => _t('Store jobs output'),
						'value' => '1',
						'title' => 'Store jobs output in the database',
						'checked' => 'checked',
					),
					array(
						'label_attributes' => array(
							'id' => 'jobs_output_failed_label'
						),
						'attributes' => array(
							'id' => 'jobs_output_failed_input'
						),
						'label' => _t('Store failed jobs output'),
						'value' => '2',
						'title' => 'Only store the output for failed jobs',
					),
					array(
						'label_attributes' => array(
							'id' => 'jobs_output_none_label'
						),
						'attributes' => array(
							'id' => 'jobs_output_none_input'
						),
						'label' => _t('Do not store jobs output'),
						'value' => '0',
						'title' => 'Do not store the output of the jobs',
					),
				)
			)
		));

		$this->add(array(
			'name' => 'max_job_output_size',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Max output size'),
				'suffix' => 'KB',
				'section' => 'section_sub',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_max_job_output_size',
				'title' => 'Enter (in KB) the maximum database space for storing job output data, 0-63 KB',
				'type' => 'number',
				'required' => false,
				'value' => 1024,
			)
		));

		// Load
		
		
		$maxHttpJobsElement = array(
			'name' => 'max_http_jobs',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Max number of concurrent jobs'),
				
				'section' => 'Load',
				'section_description' => 'Define jobs execution concurrency parameters',
				'section_image' => '/images/settings/general-settings.png',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_max_http_jobs',
				'title' => 'The maximum number of HTTP based jobs which can be executed simultaneously<br>'.
					'Please note that every queue in <a href="/ZendServer/Queues" target="_blank">the queues list</a> has its own value for this property, and it shouldn\'t exceed this value',
				'type' => 'number',
				'required' => false,
				'value' => 5,
			)
		);
		if ($isForCluster) {
			$maxHttpJobsElement['options']['is_bounded_element'] = false;
			$maxHttpJobsElement['options']['has_bounded_element'] = true;
		}
		$this->add($maxHttpJobsElement);
		
		if ($isForCluster) {
			$this->add(array(
				'name' => 'max_http_jobs_for_entire_cluster',
				'type' => 'Zend\Form\Element\Select',
				'options' => array(
					'hide_label' => true,
					'section' => 'Load',
					
					'is_bounded_element' => true,
					'has_bounded_element' => false,
					
					'value_options' => array(
						'0' => 'per server',
						'1' => 'for entire cluster',
					),
				),
				'attributes' => array(
					'id' => 'max_http_jobs_for_entire_cluster_input_on',
					'title' => 'Concurrency limit is set per server or for entire cluster',
					'required' => false,
					'value' => '0', // "per server"
				)
			));
			
		}
		
		// maintenance
		
		$this->add(array(
			'name' => 'history',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Keep history time'),
				'suffix' => 'days',
				'section' => 'Maintenance',
				'section_description' => 'Configure storage settings for completed jobs history',
				'section_image' => '/images/settings/general-settings.png',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_history',
				'title' => 'The maximum amount of time a completed, failed or removed job, is kept in the database',
				'type' => 'number',
				'required' => false,
				'value' => 7,
				'size' => '4',
			)
		));
		
		$this->add(array(
			'name' => 'history_failed',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Keep failed jobs history time'),
				'suffix' => 'days',
				'section' => 'Maintenance',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_history_failed',
				'title' => 'The maximum amount of time a failed job is kept in the database. If set to "0", the global history time value is used.',
				'type' => 'number',
				'required' => false,
				'value' => 0,
			)
		));
		
		$this->add(array(
			'name' => 'db_size_completed',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Max database size for completed jobs'),
				'suffix' => 'MB',
				'section' => 'Maintenance',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_db_size_completed',
				'title' => 'The maximum space on the database for storing completed jobs data',
				'type' => 'number',
				'required' => false,
				'value' => 1024,
			)
		));
		
		$this->add(array(
			'name' => 'db_size_failed',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Max database size for failed jobs'),
				'suffix' => 'MB',
				'section' => 'Maintenance',
			),
			'attributes' => array(
				'id' => 'JobQueue_settings_db_size_failed',
				'title' => 'The maximum space on the database for storing failed jobs data',
				'type' => 'number',
				'required' => false,
				'value' => 1024,
			)
		));
		
	}
}
