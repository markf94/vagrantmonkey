<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class DatePicker extends AbstractHelper {
	
	/**
	 * @param string $container
	 * @return string
	 */
	public function __invoke($container, $params) {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/datepicker/Locale.en-US.DatePicker.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/datepicker/Picker.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/datepicker/Picker.Attach.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/datepicker/Picker.Date.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zPicker.Date.js');
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/js/datepicker/datepicker_vista/datepicker_vista.css');
		
		$timerpicker = (isset($params['timerpicker'])) ? $params['timerpicker'] : 'true';
		$xoffset = (isset($params['xoffset'])) ? $params['xoffset'] : '5';
		$yoffset = (isset($params['yoffset'])) ? $params['yoffset'] : '0';
		$pickOnly = (isset($params['pickOnly'])) ? $params['pickOnly'] : '';
		$format = (isset($params['format'])) ? $params['format'] : '';
		
		
		return <<<CHART
		new zPickerDate($$('{$container}'), {
			timePicker: {$timerpicker},
			positionOffset: {x: {$xoffset}, y: {$yoffset}},
			pickOnly: '{$pickOnly}',
			pickerClass: 'datepicker_vista',
			useFadeInOut: !Browser.ie,
			format: '{$format}',
		});
CHART;
	}
}

