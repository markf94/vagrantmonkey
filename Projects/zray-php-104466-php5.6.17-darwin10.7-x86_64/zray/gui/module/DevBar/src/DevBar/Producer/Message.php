<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use Configuration\MapperDirectives;
use DevBar\Db\TokenMapper;
use Zend\Http\PhpEnvironment\Request;
use DevBar\Listener\AbstractDevBarProducerLeft;

class Message extends AbstractDevBarProducerLeft
{
	
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array());
        $viewModel->setTemplate('dev-bar/components/message');
        
        $message = null;
        if (isset($_COOKIE['debug_session_id']) && ! empty($_COOKIE['debug_session_id'])) {
            $message = _t('Z-Ray information is unavailable for requests with debugging enabled.');
        }
        
        $viewModel->setVariable('message', $message);
        
        return $viewModel;
    }
}

