<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use Configuration\MapperDirectives;
use DevBar\Db\TokenMapper;
use Zend\Http\PhpEnvironment\Request;
use DevBar\Listener\AbstractDevBarProducerLeft;

class Secure extends AbstractDevBarProducerLeft
{
	
	/**
	 * @var TokenMapper
	 */
	private $tokenMapper;
	
	/**
	 * @var Request
	 */
	private $request;
	
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel(array());
        $viewModel->setTemplate('dev-bar/components/secure');
        
        $inSecureMode = false;
        
        $devBarEnabled = $this->getDirectivesMapper()->selectSpecificDirectives(array('zray.enable'));
        $enabled = $devBarEnabled->current(); /* @var $enabled \Configuration\DirectiveContainer */
        if ($enabled->getFileValue() == 2) {
        	$inSecureMode = true;
        	
        	$tokenHash = $this->getRequest()->getQuery('token', '');
        	$token = $this->getTokenMapper()->findTokenByHash($tokenHash);
        	
        	if ($token->getId()) {
        		$viewModel->setVariable('token', $token);
        	}
        }
        
        $viewModel->setVariable('inSecureMode', $inSecureMode);
        $viewModel->setVariable('isAzure', isAzureEnv());
        $viewModel->setVariable('zrayStandalone', isZrayStandaloneEnv());
        return $viewModel;
    }
    
    /**
     * @return TokenMapper
     */
    public function getTokenMapper() {
    	return $this->tokenMapper;
    }
    
    /**
     * @param TokenMapper $tokenMapper
     */
    public function setTokenMapper($tokenMapper) {
    	$this->tokenMapper = $tokenMapper;
    }
    
    /**
     * @return TokenMapper
     */
    public function getRequest() {
    	return $this->request;
    }
    
    /**
     * @param Request $request
     */
    public function setRequest($request) {
    	$this->request = $request;
    }
}

