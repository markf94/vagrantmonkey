<?php

namespace Deployment\InputFilter;

use Zend\InputFilter\Factory as baseFactory;
use Zend\Validator\Regex;
use ZendServer\Validator\HostWithPort;
use ZendServer\Validator\UriPath;
use Deployment\Validator\ApplicationNameNotExists;
use Deployment\Model;
use ZendServer\Log\Log;
use Deployment\Validator\VirtualHostPort;

class Factory extends baseFactory {
	
	const APPLICATION_DISPLAY_NANE_VALIDATION_REGEX = '#^[\w \-_\(\)&\%~!\+:/]+$#';
	
	/**
	 * @var Model
	 */
	private $deploymentModel;
	
	public function createInputFilter($inputFilterSpecification) {
		
		$displayNameValidator = new Regex(self::APPLICATION_DISPLAY_NANE_VALIDATION_REGEX);
		$displayNameValidator->setMessage(
				_t('The application\'s display name may only contain letters, numbers, space and the following characters: - _ ( ) & %% ~ ! + : /'),
				Regex::NOT_MATCH); 
		
		$inputFilterSpecification = array_merge($inputFilterSpecification, array(
				'vhosts' => array('validators' => array(new HostWithPort(), new VirtualHostPort())),
				'path' => array('validators' => array(new UriPath())),
				'displayName' => array('validators' => array(
					$displayNameValidator,
					new ApplicationNameNotExists(array(), $this->getDeploymentModel()),
			))
		));
		$inputFilter = parent::createInputFilter($inputFilterSpecification);
		$inputFilter->get('displayName')->setAllowEmpty(true);
		
		return $inputFilter;
	}
	
	/**
	 * @return \Deployment\Model
	 */
	public function getDeploymentModel() {
		return $this->deploymentModel;
	}

	/**
	 * @param \Deployment\Model $deploymentModel
	 */
	public function setDeploymentModel($deploymentModel) {
		$this->deploymentModel = $deploymentModel;
	}

}

