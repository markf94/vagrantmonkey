<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module;

class AppLogo extends AbstractHelper {
	
	/**
	 * @param string $appName
	 * @param string $logo
	 * @param string $class
	 * @return string
	 */
	public function __invoke($appName, $logo, $class = 'application-wizard-logo') {
		if (! empty($class)) {
			$class = 'class="' . $this->view->escapeHtml($class) . '"';
		}
		
		if (! empty($logo)) {
			$appName = $this->view->escapeHtml($appName);
			$classElement = '';
			return '<img ' . $class . ' src="data:image/png;base64,' . base64_encode($logo) . 
				   '" alt="' .
				   $appName . '" />';
		}
		
		$defaultLogo = Module::config()->baseUrl . '/images/deployment-default-logo.png';
		return '<img ' . $class . ' src="' . $defaultLogo . '" />';
	}
}