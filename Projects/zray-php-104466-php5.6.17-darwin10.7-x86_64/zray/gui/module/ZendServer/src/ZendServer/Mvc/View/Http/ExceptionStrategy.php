<?php

namespace ZendServer\Mvc\View\Http;

use Zend\Http\PhpEnvironment\Response;

use ZendServer\Exception;

use Zend\Mvc\MvcEvent;

use Zend\Mvc\View\Http\ExceptionStrategy as baseStrategy;
use ZendServer\Log\Log;

class ExceptionStrategy extends baseStrategy {
	
	/**
	 * @var string
	 */
	private $permissionsTemplate;

	/*
	 * (non-PHPdoc)
	 * @see \Zend\Mvc\View\Http\ExceptionStrategy::prepareExceptionViewModel()
	 */
	public function prepareExceptionViewModel(MvcEvent $e)
	{
		// Do nothing if no error in the event
		$error = $e->getError();
		if (empty($error)) {
			return;
		}
		
		// Do nothing if the result is a response object
		$result = $e->getResult();
		if ($result instanceof Response) {
			return;
		}
		
		parent::prepareExceptionViewModel($e);
		$exception = $e->getParam('exception');
		Log::err($exception);
		if ($exception instanceof Exception && ($exception->getCode() == Exception::ACL_PERMISSION_DENIED)) {
			$viewModel = $e->getResult();
			$viewModel->setVariable('message', _t('Current user has insufficient permissions to access this action'));
			$viewModel->setTemplate($this->getPermissionsTemplate());
			$e->setResult($viewModel);
			$response = $e->getResponse();
			$response->setStatusCode(401);
		} elseif ($exception instanceof Exception  && ($exception->getCode() == Exception::ACL_EDITION_PERMISSION_DENIED)) {
			$viewModel = $e->getResult();
			$viewModel->setVariable('message', _t('The license you are currently using does not support this action')); 
			$viewModel->setTemplate($this->getPermissionsTemplate());
			$e->setResult($viewModel);
			$response = $e->getResponse();
			$response->setStatusCode(401);
		} elseif ($exception instanceof Exception  && ($exception->getCode() == Exception::DATABASE_CONNECTION)) {
			$viewModel = $e->getResult();
			$viewModel->setVariable('message', _t('Zend Server could not connect to the database')); 
			$e->setResult($viewModel);
			$response = $e->getResponse();
			$response->setStatusCode(500);
		}
		
		return;
	}
	
	
	/**
	 * @return string $permissionsTemplate
	 */
	public function getPermissionsTemplate() {
		return $this->permissionsTemplate;
	}
	
	/**
	 * @param string $permissionsTemplate
	 * @return ExceptionStrategy
	 */
	public function setPermissionsTemplate($permissionsTemplate) {
		$this->permissionsTemplate = $permissionsTemplate;
		return $this;
	}
}

