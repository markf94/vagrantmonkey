<?php

namespace Deployment\Forms;

use Zend\Form;

class ApplicationsAwareForm extends Form\Form {
	
	
	/* @var $deploymentModel \Deployment\Model */
	protected $_deploymentModel;
	
	/**
	 *
	 * @param \Deployment\Model $deploymentModel
	 */
	public function __construct($options, $deploymentModel) {
		parent::__construct($options);
		
		$this->_deploymentModel = $deploymentModel;
	}	
	

}
