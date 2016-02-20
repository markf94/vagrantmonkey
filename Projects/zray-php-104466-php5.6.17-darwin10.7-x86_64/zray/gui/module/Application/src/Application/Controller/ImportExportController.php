<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;

use ZendServer\Mvc\Controller\ActionController,
	ZendServer\Log\Log,
	\Servers\Configuration\Mapper;

class ImportExportController extends ActionController
{
    public function indexAction() {
    	if (! $this->isAclAllowed('route:ConfigurationWebApi', 'configurationImport')) {
    		$viewModel = new ViewModel();
    		$viewModel->setTemplate('application/import-export/index-marketing');
    		return $viewModel;
    	}
    	
		$mapper = new Mapper();
    	
    	return array('pageTitle'=>'Import / Export',
					'pageTitleDesc'=>'', /* Daniel */
		'isClusterSupport' => $mapper->isClusterSupport());
	}

	public function exportAction(){
		//TODO Check that only authorized users can do this?
		$exportView = $this->forward()->dispatch('ConfigurationWebApi-1_3', array('action' => 'configurationExport')); /* @var $exportView \Zend\Http\PhpEnvironment\Response */
		return $exportView;
	}
	
	public function importAction(){
		$success = true;
		$message = '';
		$viewModel = new \Zend\View\Model\ViewModel ();

		if (count($_FILES)) {
			try{
				$viewModel = $this->forward()->dispatch('ConfigurationWebApi-1_3', array('action' => 'configurationImport'));
				$viewModel->setVariable('configurationIsImported', true);
			} catch (\Exception $e) {
				Log::err ( 'Could not upload configuration with: '. $e->getMessage() );
				$message = _t( 'Failed to upload configuration: ' .  $e->getMessage());
				$success = false;
			}
		}

		$this->layout('layout/login');
		$viewModel->setTemplate('application/import-export/import');
		$viewModel->message = $message;
		$viewModel->success = $success;			
		return $viewModel;
	}
	
}