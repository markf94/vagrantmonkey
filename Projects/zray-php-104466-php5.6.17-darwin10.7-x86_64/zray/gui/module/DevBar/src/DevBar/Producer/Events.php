<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;

class Events extends AbstractDevBarProducer
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array(
        	'producerEnabled' => $this->directivesMapper->getDirectiveValue('zray.collect_events'),
        ));
        $viewModel->setTemplate('dev-bar/components/events');
        return $viewModel;
    }
}

