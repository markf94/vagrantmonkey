<?php

namespace Logs\Controller;

use Zend\Stdlib\Parameters;

use ZendServer\Set;

use Zend\View\Model\ViewModel;

use ZendServer\Edition;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module;

class IndexController extends ActionController
{
    public function indexAction() {
    	$logMapper = $this->getLocator('Logs\Db\Mapper'); /* @var $logReader \Logs\Db\Mapper */
    	$logfiles = array_keys($logMapper->findAllEnabledLogFiles());

    	$edition = new Edition();
    	$singleServer = $edition->isSingleServer();
    	
    	$viewModel = new ViewModel();
    	$viewModel->setVariable('singleServer', $singleServer);
    	$logLines = Module::config('logReader', 'defaultLineChunk');
    	$viewModel->setVariable('lines', $logLines);
    	 
    	if ($singleServer) {
    		$viewModel->setVariable('servers', new Set(array()));
    	} else {
	    	$serversMapper = $this->getLocator('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
	    	$servers = $serversMapper->findRespondingServers();
	    	$viewModel->setVariable('servers', $servers);
    	}
    	
    	$viewModel->setVariable('logfiles', $logfiles);
    	$viewModel->setVariable('pageTitle', 'Logs');
		$viewModel->setVariable('pageTitleDesc', ''); /* Daniel */
		
		return $viewModel;
	}
	
	public function exportAction() {
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$request->setQuery(new Parameters($request->getQuery()->toArray()));
		$response = $this->forward()->dispatch('LogsWebApi-1_3', array('action' => 'logsGetLogfile'));
		$this->getEvent()->setParam('do-not-compress', true);
		$this->response = $response;
		return $response;
	}
}
