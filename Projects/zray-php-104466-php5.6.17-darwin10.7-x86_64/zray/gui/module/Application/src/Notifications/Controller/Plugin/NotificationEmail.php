<?php
namespace Notifications\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
	Application\Module;

class NotificationEmail extends AbstractPlugin {
	public function __invoke($params) {
		$serversVars = $this->getRequestVariables('NotificationsWebApi-1_3', 'getNotifications');
		$notifications = $serversVars['notifications'];
		
		return array('notifications' => $notifications);
	}
	
	/**
	 * Make a webapi request and return its variables array
	 * @param string $controller
	 * @param string $action
	 * @return array
	 */
	private function getRequestVariables($controller, $action) {
		$method = $this->controller->getRequest()->getMethod();
		$this->controller->getRequest()->setMethod('GET');
	
		$serversView = $this->controller->forward()->dispatch($controller, array('action' => $action)); /* @var $serversView \Zend\View\Model\ViewModel */
		$serversVars = $serversView->getVariables();
	
		$this->controller->getRequest()->setMethod($method);
	
		return $serversVars;
	}
}