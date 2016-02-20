<?php

namespace DevBar\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Zend\View\Helper\ViewModel;
use Acl\License\Exception;

class ZrayLiveController extends ActionController {

	public function indexAction() {
		if (function_exists('zray_disable')) {
			\zray_disable(true);
		}
		
		$disablePage = false;
		if (isAzureEnv()) {
		    if (function_exists('zray_get_azure_license')) {
		        $license = \zray_get_azure_license();
		        if ($license != ZRAY_AZURE_LICENSE_STANDARD) { // if license not standard - disable page
		          $disablePage = true;
		        }
		    } else { // license not found - disable page
		        $disablePage = true;
		    }
		}
		
		$viewParams = array(
		    'pageTitle' => 'Z-Ray Live!',
			'pageTitleDesc' => '',  /* Daniel */
			'zrayEnabled' => $this->getDirectivesMapper()->getDirectiveValue('zray.enable'), // check config if zray enabled
			'disablePage' => $disablePage,
		);
		
		return $viewParams;
	}
	
}