<?php

namespace Configuration\Controller;

use ZendServer\Set;

use Zend\View\Model\ViewModel;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module;

class ServerInfoController extends ActionController
{
    public function indexAction() {
    	$viewModel = new ViewModel();
     	$serversMapper = $this->getLocator('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
	    $servers = $serversMapper->findAllServers();
	    $viewModel->setVariable('servers', $servers);
	    
	    $statsModel = $this->getLocator()->get('Statistics\Model'); /* @var $statsModel \Statistics\Model */
	    $viewModel->setVariable('statAvgCpuUsage', $statsModel->getContainer(array(), ZEND_STATS_TYPE_AVG_CPU_USAGE));
	    $viewModel->setVariable('statAvgMemUsage', $statsModel->getContainer(array(), ZEND_STATS_TYPE_AVG_MEMORY_USAGE));
	    $viewModel->setVariable('pageTitle', 'PHP Info');
		$viewModel->setVariable('pageTitleDesc', ''); /* Daniel */
		return $viewModel;
	}
	
}