<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;

class FunctionStats extends AbstractDevBarProducer
{

	/**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array(
        	'custom_namespaces' => $this->defaultNamespaces,
        	'producerEnabled' => $this->directivesMapper->getDirectiveValue('zray.collect_functions'),
            'azure' => isAzureEnv(),
		    'zrayStandalone' => isZrayStandaloneEnv(),
        ));
        $viewModel->setTemplate('dev-bar/components/function-stats');
        return $viewModel;
    }
    
    protected $defaultNamespaces;
    
    /**
     * 
     * @param string $defaultNamespaces - comma separated values of known frameworks namespaces
     * @return \DevBar\Producer\FunctionStats
     */
    public function setDefaultNamespaces($defaultNamespaces) {
    	// rebuild the list - trim, unique, and remove empty values 
    	$defaultNamespaces = explode(',', $defaultNamespaces);
    	array_walk($defaultNamespaces, function(&$elem) {
        	$elem = trim($elem);
    	});
    	$defaultNamespaces = array_filter($defaultNamespaces, 'strlen');
    	$defaultNamespaces = array_unique($defaultNamespaces);
    	$defaultNamespaces = implode(',', $defaultNamespaces);
    	
    	$this->defaultNamespaces = $defaultNamespaces;
    	return $this;
    }

}

