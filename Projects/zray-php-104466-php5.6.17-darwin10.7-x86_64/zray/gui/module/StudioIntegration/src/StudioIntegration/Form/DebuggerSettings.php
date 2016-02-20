<?php
namespace StudioIntegration\Form;

use Zend\Form\Fieldset;
use StudioIntegration\Configuration as StudioConfig;
use StudioIntegration\ConfigurationHydrator;
use Zend\Form\Form;

class DebuggerSettings extends Form
{

	public function __construct($name = null)
	{
		parent::__construct('debugger-settings');
		
		$this->add(array(
			'name' => 'ActiveDebugger',
			'type' => 'Zend\Form\Element\Radio',
			'options' => array(
				'label' => '',
				
				'section' => 'Debugger',
				'section_description' => 'Select the preferred debugger for debugging on Zend Server',
				'section_image' => '/images/settings/general-settings.png',
				
				'value_options' => array(
					array(
						'label_attributes' => array(
							'id' => 'choose-debugger-zend-label'
						),
						'attributes' => array(
							'id' => 'choose-debugger-zend'
						),
						'label' => _t('Zend Debugger'),
						'value' => 'Zend Debugger',
						'title' => 'An advanced PHP debugger developed by Zend Technologies. The ideal debugger for Zend Studio users.',
						'checked' => 'checked',
					),
					array(
						'label_attributes' => array(
							'id' => 'choose-debugger-xdebug-label'
						),
						'attributes' => array(
							'id' => 'choose-debugger-xdebug',
						),
						'value' => 'xdebug',
						'label' => _t('Xdebug'),
						'title' => 'Popular PHP extension for debugging and profiling. Uses the DBGp debugging protocol.',
					),
					array(
						'label_attributes' => array(
							'id' => 'choose-debugger-none-label'
						),
						'attributes' => array(
							'id' => 'choose-debugger-none'
						),
						'label' => _t('None'),
						'title' => 'Disables both debuggers',
						'value' => 'none',
					),
				)
			)
		));
		
		// Security (IDE Client Filtering) // // // // // // // // // // // // // // // //
		
		$this->add(array(
			'name' => 'studioAllowedHostsList',
			'type' => 'Zend\Form\Element\Hidden',
			'options' => array(
				'extended_type' => 'hosts_list',
				
				'label' => 'Allowed hosts',
				'section' => 'Security',
				
				'section_description' => 'Define the IP addresses that are allowed, and forbidden, to debug PHP on Zend Server',
				'section_image' => '/images/settings/Token-Zend-Server-icon.png',
			),
			'attributes' => array(
				'id' => 'studioAllowedHostsList',
			)
		));
		
		$this->add(array(
			'name' => 'studioDeniedHostsList',
			'type' => 'Zend\Form\Element\Hidden',
			'options' => array(
				'extended_type' => 'hosts_list',
				
				'label' => 'Denied hosts',
				'section' => 'Security',
			),
			'attributes' => array(
				'id' => 'studioDeniedHostsList',
			)
		));
		
		// IDE Client Settings // // // // // // // // // // // // // // // // // // // //
		
		$this->add(array(
			'name' => 'studioAutoDetection',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => 'Autodetect IDE settings',
				'section' => 'IDE Client Settings',
				'hideSubSectionOnCheck' => true,
				
				'section_description' => 'Determine whether Zend Server should automatically detect the IP of the machine hosting your IDE and initiating debugging sessions.',
				'section_image' => '/images/settings/audit-trail.png',
			),                
			'attributes' => array(
				'id' => 'studio-autodetect-true',
			),
		));
		
		$this->add(array(
			'name' => 'studioHost',
			'type' => 'Zend\Form\Element\Hidden',
			'options' => array(
				'extended_type' => 'ip_widget',
				'label' => 'IDE IP address',
				'section' => 'section_sub',
			),
			'attributes' => array(
				'id' => 'studio-host',
				'value' => '127.0.0.1',
			),
		));
		
		$this->add(array(
			'name' => 'studioAutoDetectionEnabled',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => 'Use browser\'s IP Address',
				'section' => 'section_sub',
			),
			'attributes' => array(
				'id' => 'studio-autoDetectBrowser'
			)
		));
		
		$this->add(array(
			'name' => 'studioPort',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => 'Studio port',
				'section' => 'section_sub',
			),
			'attributes' => array(
				'id' => 'studio-port',
				'value' => '10137',
			)
		));
		
		$this->add(array(
			'name' => 'studioUseSsl',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => 'Encrypt communications using SSL',
				'section' => 'section_sub',
			),
			'attributes' => array(
				'id' => 'studio-useSsl'
			)
		));
		
		// Default Debugger Settings (IDE Integration Settings) // // // // // // // // //
		
		$this->add(array(
			'name' => 'studioBreakOnFirstLine',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => 'Stops debugging execution at the first line of code in the page.',
				'section' => 'IDE Integration Settings',
				
				'section_description' => 'Configure IDE debugging settings. These settings are the default debugger settings, but can be overriden from your IDE.',
				'section_image' => '/images/settings/general-settings.png',
			),
			'attributes' => array(
				'id' => 'studio-break-on-first-line',
			)
		));
		
		$this->add(array(
			'name' => 'studioUseRemote',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => 'Debugs the files stored on your file system if they are available. If not, debugs the files located on the web server.',
				'section' => 'IDE Integration Settings',
			),
			'attributes' => array(
				'id' => 'studio-use-remote',
			)
		));
		
		// Xdebug settings // // // // // // // // // // // // // // // // // // // // // // 
		
		$this->add(array(
			'name' => 'remote_enable',
			'type' => 'Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => 'Enable remote',
				'section' => 'Xdebug Settings',
				
				'section_description' => 'Settings for using Xdebug as preferred debugger',
				'section_image' => '/images/settings/general-settings.png',
			),
			'attributes' => array(
				'id' => 'xdebug-remote-enable',
				'title' => 'Enable debugging on Zend Server from an IDE hosted on a remote machine.',
			)
		));
		
		$this->add(array(
			'name' => 'remote_handler',
			'type' => 'Zend\Form\Element\Text',
			'options' => array(
				'label' => 'Remote handler',
				'section' => 'Xdebug Settings',
			),
			'attributes' => array(
				'id' => 'remote_handler',
				'value' => 'dbgp',
				'title' => 'Enter the remote debugging handler ("php3", "gdb", or "dbgp"). Note: xdebug 2.1 and higher supports only "dbgp" protocol.',
			)
		));
		
		$this->add(array(
			'name' => 'remote_host',
			'type' => 'Zend\Form\Element\Hidden',
			'options' => array(
				'extended_type' => 'ip_widget',
				'label' => 'Remote host',
				'section' => 'Xdebug Settings',
			),
			'attributes' => array(
				'id' => 'remote_host',
				'value' => '127.0.0.1',
				'title' => 'Enter the IP of the machine hosting your IDE.',
			),
		));
		
		$this->add(array(
			'name' => 'remote_port',
			'type' => 'Zend\Form\Element\Number',
			'options' => array(
				'label' => 'Enable remote',
				'section' => 'Xdebug Settings',
			),
			'attributes' => array(
				'id' => 'remote_port',
				'value' => '9000',
				'title' => 'Enter the port to which Xdebug tries to connect on the remote host.',
			)
		));
		
		$this->add(array(
			'name' => 'idekey',
			'type' => 'Zend\Form\Element\Text',
			'options' => array(
				'label' => 'IDE Key',
				'section' => 'Xdebug Settings',
			),
			'attributes' => array(
				'id' => 'idekey',
				'value' => '',
				'title' => 'Controls which IDE Key Xdebug should pass on to the DBGp debugger handler.',
			)
		));
	}
}

