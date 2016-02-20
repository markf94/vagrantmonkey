<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;

class Queries extends AbstractDevBarProducer
{
	/**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array(
        	'backtraceEnabled' => $this->directivesMapper->getDirectiveValue('zray.collect_backtrace'),
        	'producerEnabled' => $this->directivesMapper->getDirectiveValue('zray.collect_sql'),
            'azure' => isAzureEnv(),
		    'zrayStandalone' => isZrayStandaloneEnv(),
        ));
        $viewModel->setTemplate('dev-bar/components/queries');
        return $viewModel;
    }
    
}

