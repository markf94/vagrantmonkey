<?php

namespace Application\Controller;

use ZendServer\Mvc\Controller\ActionController, Zend\View\Model\ViewModel;

class UnderconstructionController extends ActionController {
	
	/**
	 * Action called if matched action does not exist
	 *
	 * @return array
	 */
	public function notFoundAction() {
		
		$routeMatch = $this->getEvent ()->getRouteMatch ();
		$routeMatch->setParam ( 'controller', 'Underconstruction' );
		$routeMatch->setParam ( 'action', 'index' );
		
		$viewModel = new ViewModel ();
		return $viewModel;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend\Mvc\Controller.ActionController::indexAction()
	 */
	public function indexAction() {
		return array ();
	}

}

