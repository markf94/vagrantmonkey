<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use DevBar\Listener\AbstractDevBarProducerLeft;

class Controls extends AbstractDevBarProducerLeft
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array());
        $viewModel->setTemplate('dev-bar/components/controls');
        return $viewModel;
    }
}

