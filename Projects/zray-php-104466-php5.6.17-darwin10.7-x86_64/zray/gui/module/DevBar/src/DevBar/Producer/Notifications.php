<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use DevBar\Listener\AbstractDevBarProducerRight;
use ZendServer\Log\Log;
use Application\Module;

class Notifications extends AbstractDevBarProducerRight
{
    
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array());
        $viewModel->setTemplate('dev-bar/components/notifications');
		
        return $viewModel;
    }    
}