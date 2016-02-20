<?php
namespace StudioIntegration\Form;

use Zend\Form\Fieldset;
use StudioIntegration\Configuration as StudioConfig;
use StudioIntegration\ConfigurationHydrator;
use Zend\Form\Form;

class Xdebug extends Form
{

    /*
    BASIC DEFAULT XDEBUG CONFIGURATION
    xdebug.remote_enable=on
    xdebug.remote_handler=dbgp
    xdebug.remote_host=localhost
    xdebug.remote_port=9000
    */
    
    public function __construct($name = null)
    {
        parent::__construct('xdebug');
        $this->add(array(
            'name' => 'remote_enable',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    array(
                        'attributes' => array(
                            'id' => 'remote_enable_on'
                        ),
                        'label' => _t('On'),
                        'value' => '1'
                    ),
                    array(
                        'attributes' => array(
                            'id' => 'remote_enable_off'
                        ),
                        'label' => _t('Off'),
                        'value' => '0'
                    ),
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'remote_handler',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'remote_handler',
            )
        ));
        
        $this->add(array(
            'name' => 'remote_host',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'remote_host',
            )
        ));
        
        $this->add(array(
            'name' => 'remote_port',
            'type' => 'Zend\Form\Element\Number',
            'attributes' => array(
                'id' => 'remote_port',
            )
        ));
    }
}


/**

All Xdebug Settings
------------

xdebug.auto_trace
xdebug.cli_color
xdebug.collect_assignments
xdebug.collect_includes
xdebug.collect_params
xdebug.collect_return
xdebug.collect_vars
xdebug.coverage_enable
xdebug.default_enable
xdebug.dump.*
xdebug.dump_globals
xdebug.dump_once
xdebug.dump_undefined
xdebug.extended_info
xdebug.file_link_format
xdebug.force_display_errors
xdebug.force_error_reporting
xdebug.halt_level
xdebug.idekey
xdebug.manual_url
xdebug.max_nesting_level
xdebug.overload_var_dump
xdebug.profiler_append
xdebug.profiler_enable
xdebug.profiler_enable_trigger
xdebug.profiler_output_dir
xdebug.profiler_output_name
xdebug.remote_autostart
xdebug.remote_connect_back
xdebug.remote_cookie_expire_time
xdebug.remote_enable
xdebug.remote_handler
xdebug.remote_host
xdebug.remote_log
xdebug.remote_mode
xdebug.remote_port
xdebug.scream
xdebug.show_exception_trace
xdebug.show_local_vars
xdebug.show_mem_delta
xdebug.trace_enable_trigger
xdebug.trace_format
xdebug.trace_options
xdebug.trace_output_dir
xdebug.trace_output_name
xdebug.var_display_max_children
xdebug.var_display_max_data
xdebug.var_display_max_depth

 */