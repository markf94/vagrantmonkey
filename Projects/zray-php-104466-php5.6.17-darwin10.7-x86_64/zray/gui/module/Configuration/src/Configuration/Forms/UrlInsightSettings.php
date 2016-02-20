<?php

namespace Configuration\Forms;


use Zend\Form\Fieldset;
use Zend\Form\Form;

class UrlInsightSettings extends Form
{
	
	public function __construct($name = null)
	{
		parent::__construct('url-insight-settings');
		
		// enable/disable url insight
		$this->add(array(
			'name' => 'url_insight_mode',
			'type' => 'Zend\Form\Element\Radio',
			'options' => array(
				'label' => 'URL Insight Mode',
				
				'section' => 'URL Insight',
				'section_description' => 'This area allows you to set the URL Insight mode and configure Z-Ray snapshots.',
				'section_image' => '/images/settings/audit-trail.png',
				
				'value_options' => array(
					array(
						'label_attributes' => array(
							'id' => 'url_insight_enable_label'
						),
						'attributes' => array(
							'id' => 'url_insight_enable_input'
						),
						'label' => _t('Enable URL Insight'),
						'value' => '1',
						'title' => ' ',
						'checked' => 'checked',
					),
					array(
						'label_attributes' => array(
							'id' => 'url_insight_disable_label'
						),
						'attributes' => array(
							'id' => 'url_insight_disable_input'
						),
						'label' => _t('Disable URL Insight'),
						'value' => '0',
						'title' => ' ',
					),
				)
			)
		));
		
		// Save periodic Z-Ray for important URLs: enable/disable
		$this->add(array(
			'name' => 'zray_snapshots_mode',
			'type' => 'Zend\Form\Element\Radio',
			'options' => array(
				'label' => 'Z-Ray Snapshots',
				
				'section' => 'section_sub',
				
				'value_options' => array(
					array(
						'label_attributes' => array(
							'id' => 'zray_snapshots_mode_enable_label',
							'title' => 'Zend Server periodically will take snapshots of Z-Ray for requests. These snapshots allow you to drill down further into a request at a specific point in time.'
						),
						'attributes' => array(
							'id' => 'zray_snapshots_mode_enable_input',
						),
						'label' => _t('Enable Z-Ray Snapshots'),
						'value' => '1',
						'title' => ' ',
						'checked' => 'checked',
					),
					array(
						'label_attributes' => array(
							'id' => 'zray_snapshots_mode_disable_label'
						),
						'attributes' => array(
							'id' => 'zray_snapshots_mode_disable_input',
						),
						'label' => _t('Disable Z-Ray Snapshots'),
						'value' => '0',
						'title' => ' ',
					),
				)
			)
		));
		
		
		//  interval between Z-Ray snapshots
		$this->add(array(
			'name' => 'zray_snapshots_interval',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => 'Z-Ray Snapshots Interval',
				'section' => 'section_sub',
				'suffix' => 'seconds',
			),
			'attributes' => array(
				'id' => 'zray_snapshots_interval',
				'value' => '1800',
				'title' => 'Enter an interval (in seconds) between each snapshot of Z-Ray taken by Zend Server',
			)
		));
		
	}
}

