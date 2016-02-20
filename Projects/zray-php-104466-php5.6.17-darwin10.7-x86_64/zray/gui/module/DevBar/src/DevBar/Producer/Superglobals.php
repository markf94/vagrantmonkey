<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use Configuration\MapperDirectives;
use ZendServer\Log\Log;

class Superglobals extends AbstractDevBarProducer
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array(
        	'producerEnabled' => $this->directivesMapper->getDirectiveValue('zray.collect_superglobals'),
        ));
        $viewModel->setTemplate('dev-bar/components/superglobals');

        return $viewModel;
    }

}

