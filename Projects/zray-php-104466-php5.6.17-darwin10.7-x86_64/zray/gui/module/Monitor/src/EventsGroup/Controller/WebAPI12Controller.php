<?php

namespace EventsGroup\Controller;

use ZendServer\Exception;

use ZendServer\Mvc\Controller\WebAPIActionController;

use Zend\Mvc\Controller\ActionController,
	Application\Module,
	ZendServer,
	WebAPI,
	ZendServer\Text,
	Zend\Validator\StringLength,
	ZendServer\Log\Log;

class WebAPIController extends WebAPIActionController
{
	public function monitorGetEventGroupDetailsAction() {
		return $this->forward()->dispatch('EventsGroupWebApi-1_3', array('action' => 'monitorGetEventGroupDetails'));
	}
}
