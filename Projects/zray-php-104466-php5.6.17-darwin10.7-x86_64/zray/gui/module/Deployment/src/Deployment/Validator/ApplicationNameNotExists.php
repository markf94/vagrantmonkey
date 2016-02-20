<?php

namespace Deployment\Validator;

use Zend\Validator\AbstractValidator,
Zend\Uri\Exception,
ZendServer\Log\Log;

class ApplicationNameNotExists extends AbstractValidator {

	const APP_NAME_EXISTS = 'appNameExists';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::APP_NAME_EXISTS  => "Application name '%value%' already exists",
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
			$names = $this->_deploymentModel->getDeployedApplicationNames();
					
			$names = array_map("strtolower", $names);
			
			if (in_array(strtolower($value), $names)) {
				$this->error(self::APP_NAME_EXISTS);
				return false;
			}
					
			
		} catch (\Exception $ex) {
			Log::warn(_t('Could not validate application name: %s', array($ex->getMessage())));
		}
		
		return true;
	}

}


