<?php

namespace Deployment\Validator;

use Zend\Validator\AbstractValidator,
Zend\Uri\Exception,
ZendServer\Log\Log;

class ApplicationBaseUrlNotExists extends AbstractValidator {

	const APP_BASE_URL_EXISTS = 'appBaseUrlExists';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::APP_BASE_URL_EXISTS  => "Application base url '%value%' already exists",
	);
	
	/* @var $deploymentModel \Deployment\Model */
	protected $_deploymentModel;

	/**
	 * 
	 * @param array $options
	 * @param \Zend\Di\ServiceManager $sm
	 */
	public function __construct($options = array(), $deploymentModel = NULL) {
			
		parent::__construct($options);
		
		$this->_deploymentModel = $deploymentModel;
				
	}
	
	public function isValid($value) {
		$this->setValue($value);

		try {
			$names = $this->_deploymentModel->getDeployedBaseUrls();
			
			Log::debug(var_export($names, true));
			
			if (in_array($value, $names)) {
				$this->error(self::APP_BASE_URL_EXISTS);
				return false;
			}
					
			
		} catch (Exception $ex) {
			
		}
		
		return true;
	}

}


