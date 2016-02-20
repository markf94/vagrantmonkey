<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module;

class AppDetails extends AbstractHelper {
	
	/**
	 * @param \Deployment\Application\Container $appInfo
	 * @return string
	 */
	public function __invoke(\Deployment\Application\Container $app) {
	    return array(
                'baseUrl' => $this->getView()->applicationUrl($app->getBaseUrl()),
	            'appName' => $app->getApplicationName(), 
                'userAppName' => $app->getUserApplicationName(), 
                'appVersion' => $app->getAppVersionId(), 
                'creationTime' => date('M j, Y, h:i a', $app->getCreationTime()), 
                'lastUsed' => date('M j, Y, h:i a', $app->getLastUsed()), 
                'version' => $app->getVersion()
	            );
	}
}

