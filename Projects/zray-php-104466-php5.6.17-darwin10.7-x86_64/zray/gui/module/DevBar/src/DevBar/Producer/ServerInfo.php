<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use Application\Module;
use DevBar\Listener\AbstractDevBarProducerRight;

class ServerInfo extends AbstractDevBarProducerRight
{
	/**
	 * @var string
	 */
	private $baseUrl = '';
	
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array());
        $viewModel->setTemplate('dev-bar/components/server-info');
        
        $viewModel->setVariable('zsversion', Module::config('package', 'version'));
        $viewModel->setVariable('baseUrl', $this->getBaseUrl());
        $viewModel->setVariable('collapse', \Application\Module::config('zray', 'zend_gui', 'collapse'));
        
        return $viewModel;
    }
    
    public function setBaseUrl($baseUrl) {
    	$this->baseUrl = $baseUrl;
    	return $this;
    }
    
    public function getBaseUrl() {
    	return $this->baseUrl;
    }
}