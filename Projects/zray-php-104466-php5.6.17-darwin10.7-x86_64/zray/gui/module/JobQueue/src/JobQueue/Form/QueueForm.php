<?php
namespace JobQueue\Form;

use Zend\Form\Form;

class QueueForm extends Form
{

	public function __construct($name = null)
	{
		parent::__construct('Queue');
		
		$this->setAttribute('id', 'JobQueue_queue');
		
		$this->add(array(
			'name' => 'name',
			'options' => array(
				'label' => _t('Queue name'),
				'section' => 'Queue Settings',
				'section_image' => '/images/settings/general-settings.png',
			),
			'attributes' => array(
				'id' => 'JobQueue_queue_name',
				'type' => 'text',
				'title' => 'The name of the queue as it will be used on the Queues page and in the API (must contain 2 to 32 characters)',
				'required' => true,
				'maxlength' => 32,
				'placeholder' => _t('e.g. My queue')
			)
		));

		$this->add(array(
			'type' => 'Zend\Form\Element\Select',
			'name' => 'priority',
			'attributes' => array(
				'id' => 'JobQueue_queue_priority',
				'title' => 'Determines which queue is prefered when selecting a new job to execute',
				'value' => '2',
			),
			'options' => array(
				'section' => 'Queue Settings',
				'label' => 'Priority',
				'value_options' => array(
					'0' => 'Low',
					'1' => 'Below Normal',
					'2' => 'Normal',
					'3' => 'Above Normal',
					'4' => 'High',
				),
			),
		));
		
		$this->add(array(
			'name' => 'max_http_jobs',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Max concurrent jobs'),
				'section' => 'Queue Settings',
			),
			'attributes' => array(
				'id' => 'JobQueue_queue_max_http_jobs',
				'title' => 'The maximum number of HTTP based jobs which can be executed simultaneously from this queue.<br />' .
					'Please note that the global value for max concurrent jobs is defined on the Job <a href="/ZendServer/JobQueue/settings" target="_blank">Job Queue Settings page</a>.',
				'type' => 'number',
				'required' => false,
				'value' => 5,
			)
		));
		
		$this->add(array(
			'name' => 'max_wait_time',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Max job wait time'),
				'suffix' => 'minutes',
				'section' => 'Queue Settings',
				'advanced_section' => true,
			),
			'attributes' => array(
				'id' => 'JobQueue_queue_max_wait_time',
				'title' => 'The maximum amount of time queue jobs can wait before they are executed regardless queue priority',
				'type' => 'number',
				'required' => false,
				'value' => 5,
			)
		));

		$this->add(array(
			'name' => 'http_connection_timeout',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Connection timeout'),
				'suffix' => 'seconds',
				'section' => 'Queue Settings',
				'advanced_section' => true,
			),
			'attributes' => array(
				'id' => 'JobQueue_queue_connection_timeout',
				'title' => 'The amount of time the Job Queue daemon  tries to establish a connection with the back-end server',
				'type' => 'number',
				'required' => false,
				'value' => 30, // seconds
			)
		));
		
		
		$this->add(array(
			'name' => 'http_job_timeout',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Job timeout'),
				'suffix' => 'seconds',
				'section' => 'Queue Settings',
				'advanced_section' => true,
			),
			'attributes' => array(
				'id' => 'JobQueue_queue_http_job_timeout',
				'title' => 'The time frame in which a URL-based queue job must be completed in',
				'type' => 'number',
				'required' => false,
				'value' => 120, // seconds
			)
		));
		
		
		$this->add(array(
			'name' => 'http_job_retry_count',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Job retry count'),
				'section' => 'Queue Settings',
				'advanced_section' => true,
			),
			'attributes' => array(
				'id' => 'JobQueue_queue_http_job_retry_count',
				'title' => 'The number of retries for failed HTTP jobs',
				'type' => 'number',
				'required' => false,
				'value' => 10,
			)
		));
		
		
		$this->add(array(
			'name' => 'http_job_retry_timeout',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => _t('Job retry timeout'),
				'suffix' => 'seconds',
				'section' => 'Queue Settings',
				'advanced_section' => true,
			),
			'attributes' => array(
				'id' => 'JobQueue_queue_http_job_retry_timeout',
				'title' => 'The amount of time between retries for failed HTTP jobs',
				'type' => 'number',
				'required' => false,
				'value' => 1,
			)
		));
		
	}
}
