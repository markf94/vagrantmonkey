<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use DevBar\Listener\AbstractDevBarProducerRight;
use ZendServer\Log\Log;
use Application\Module;

class StudioIntegration extends AbstractDevBarProducerRight
{
	/**
	 * @var \StudioIntegration\Configuration
	 */
	private $studioConfig;
	
	/**
	 * @var \Configuration\ExtensionContainer
	 */
	private $debuggerComponent;
	
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array());
        $viewModel->setTemplate('dev-bar/components/studio-integration');
		$viewModel->setVariable('studioConfig', array(
        		'autoDetect' 		=> $this->getStudioConfig()->getAutoDetect(),
        		'useSsl' 			=> $this->getStudioConfig()->getSsl(),
        		'autoDetectPort' 	=> $this->getStudioConfig()->getPort(),
        		'autoDetectHost' 	=> $this->getStudioConfig()->getHost(),
        		'debuggerEnabled'	=> $this->getDebuggerComponent()->isLoaded(),
				'clientTimeout' 	=> $this->getStudioConfig()->getTimeout(),
        		'settingsString' 	=> array(),
        ));
		$viewModel->setVariable('zsversion', Module::config('package', 'version'));
        return $viewModel;
    }
    
    public function setStudioConfig(\StudioIntegration\Configuration $studioConfig) {
    	$this->studioConfig = $studioConfig;
    	return $this;
    }
    
    public function getStudioConfig() {
    	return $this->studioConfig;
    }
    
    public function setDebuggerComponent(\Configuration\ExtensionContainer $debuggerComponent) {
    	$this->debuggerComponent = $debuggerComponent;
    	return $this;
    }
    
    private function getDebuggerComponent() {
    	return $this->debuggerComponent;
    }
}

