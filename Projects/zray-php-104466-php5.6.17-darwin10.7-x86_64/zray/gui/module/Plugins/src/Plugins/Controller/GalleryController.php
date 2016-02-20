<?php

namespace Plugins\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;
	
class GalleryController extends ActionController {

	public function indexAction() {
		$config = $this->getServiceLocator()->get('Configuration');

		$showHidden = is_null($this->getRequest()->getQuery('hidden')) ? false : true;
		
		$viewModel = new ViewModel();
		$viewModel->setVariable('storeListApiUrl', $config['plugins']['zend_gui']['storeApiUrl'].'list.php');
		$viewModel->setVariable('storeUpdatesApiUrl', $config['plugins']['zend_gui']['storeApiUrl'].'updates.php');
		$viewModel->setVariable('storeDownloadApiUrl', $config['plugins']['zend_gui']['storeApiUrl'].'download.php');
		$viewModel->setVariable('serverInfo', $this->ServerInfo()->get());
		$viewModel->setVariable('pageTitle', 'Gallery');
		$viewModel->setVariable('showHidden', $showHidden);
		
		return $viewModel;
	}
}