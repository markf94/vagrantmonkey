<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use DevBar\Listener\AbstractDevBarProducerLeft;
use Application\Module;

class RequestInfo extends AbstractDevBarProducerLeft
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array(
        	'showSilencedLogs' => Module::config()->get('zray')->zend_gui->showSilencedLogs ? 1 : 0,
        ));
        $viewModel->setTemplate('dev-bar/components/request-info');
        return $viewModel;
    }
}

