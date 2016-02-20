<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;

class RunTime extends AbstractDevBarProducer
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('dev-bar/components/run-time');
        return $viewModel;
    }
}

