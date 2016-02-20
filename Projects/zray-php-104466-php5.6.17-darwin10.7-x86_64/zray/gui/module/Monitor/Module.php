<?php

namespace Monitor;

use EventsGroup\Controller\Plugin\EventEmail;

use Zend\EventManager\EventInterface;

use Application\Module as appModule;

use MonitorUi\Model\Model;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;

use Zend\ModuleManager\ModuleManager as Manager,
    Zend\EventManager\StaticEventManager,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Application\View,
	Zend\EventManager\Event as ManagerEvent,
	ZendServer\Log\Log,
	Zend\Http\Header\Accept;
use MonitorRules\Action\MailSettingsListener;
use Zend\Db\TableGateway\TableGateway;
use EventsGroup\Db\Mapper;
use EventsGroup\BacktraceSourceRetriever;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;

class Module implements AutoloaderProvider, BootstrapListenerInterface, ConfigProviderInterface, ServiceProviderInterface
{
	
	/**
     * @var \Zend\Mvc\Application
     */
    protected $application = null;
    
    
	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
								'Issue' => __DIR__ . '/src/Issue',
								'MonitorUi' => __DIR__ . '/src/MonitorUi',
								'MonitorRules' => __DIR__ . '/src/MonitorRules',
								'EventsGroup' => __DIR__ . '/src/EventsGroup',
						),
				),
		);
	}
	
	
	public function onBootstrap(EventInterface $e) {
		$this->application = $e->getApplication();
		if ($e->getParam('dbConnected', true)) {
			$this->application->getServiceManager()->get('Deployment\Model')->getEventManager()->attach('preRemove', array($this, 'onDeploymentPostRemove'));
		}
	}
	
	public function onDeploymentPostRemove(ManagerEvent $e) {
    	   	
    	/* @var $app \Deployment\Application\Container */
    	$app = $e->getTarget();
		$params = $e->getParams();
		
		Log::debug("Monitor: onDeploymentPostRemove with app id " . $app->getApplicationId() . " and params " . var_export($params, true));

		/* @var $monRulesMapper \MonitorRules\Model\Mapper */
		$monRulesMapper = $this->application->getServiceManager()->get('MonitorRules\Model\Mapper');
				
		Log::debug("Removing rules of application " . $app->getApplicationId());
		
		$monRulesMapper->removeApplicationRules($app->getApplicationId());

		$deleted = 0;
		if ($app->getApplicationName() == \Deployment\Controller\WebAPIController::DEMO_APP_NAME) {
			Log::debug("Deleting events of Demo Application. App " . $app->getApplicationId());
			$monitorUiModel = $this->application->getServiceManager()->get('MonitorUi\Model\Model'); /* @var $monitorUiModel \MonitorUi\Model\Model */
			$deleted = $monitorUiModel->deleteIssuesByFilter(array('applicationIds'=> array($app->getApplicationId())));
		}
		
		if ($deleted) {
			$tasksMapper = $this->application->getServiceManager()->get('MonitorRules\Model\Tasks'); /* @var $tasksMapper \MonitorRules\Model\Tasks */
			$serversMapper = $this->application->getServiceManager()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
			$tasksMapper->syncMonitorRulesChanges($serversMapper->findRespondingServersIds());
		}
		
    }
    
    /*
     * (non-PHPdoc)
    * @see \Zend\ModuleManager\Feature\ControllerPluginProviderInterface::getControllerPluginConfig()
    */
    public function getControllerPluginConfig() {
    	$module = $this;
    	return array(
    			'factories' => array(
    					'EventEmail' => function($sm) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
    						$plugin = new EventEmail();
    						$plugin->setEventGroupMapper($sm->getServiceLocator()->get('EventsGroup\Db\Mapper'));
    						$plugin->setIssueMapper($sm->getServiceLocator()->get('Issue\Db\Mapper'));
    						return $plugin;
    					},
    			)
    	);
    }
    
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
	 */
	public function getServiceConfig() {
		return array(
			'invokables' => array(
				'Issue\Filter\Dictionary' => 'Issue\Filter\Dictionary',
				'MonitorRules\Model\Tasks' => 'MonitorRules\Model\Tasks'
			),
			'factories' => array(
				'EventsGroup\Db\Mapper' => function($sm) {
					$mapper = new Mapper(new TableGateway('events', $sm->get(Connector::DB_CONTEXT_MONITOR)));
					return $mapper;
				},
				'MonitorUi\Model\Model' => function($sm) {
					$model = new Model();
					$model->setFilterMapper($sm->get('ZendServer\Filter\Mapper'));
					$model->setFilterTranslator($sm->get('Issue\Filter\Translator'));
					$model->setIssueMapper($sm->get('Issue\Db\Mapper'));
					$model->setEventsGroupMapper($sm->get('EventsGroup\Db\Mapper'));
					return $model;
				},
				'MonitorRules\Action\MailSettingsListener' => function($sm) {
					$listener = new MailSettingsListener();
					$listener->setNotificationsMapper($sm->get('Notifications\Db\NotificationsMapper'));
					return $listener;
				},
				'MonitorRules\Model\Mapper' => function($sm) {
					$mapper = new \MonitorRules\Model\Mapper();
					$mapper->setRulesTable(new TableGateway('ZSD_MONITOR_RULES', $sm->get(Connector::DB_CONTEXT_ZSD)));
					$mapper->setActionsTable(new TableGateway('ZSD_MONITOR_ACTIONS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					$mapper->setConditionsTable(new TableGateway('ZSD_MONITOR_RULE_CONDITIONS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					$mapper->setTriggersTable(new TableGateway('ZSD_MONITOR_RULE_TRIGGERS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					$mapper->setRuleTypesTable(new TableGateway('ZSD_MONITOR_RULE_TYPES', $sm->get(Connector::DB_CONTEXT_ZSD)));
					
					$mailSettingsListener = $sm->get('MonitorRules\Action\MailSettingsListener');
					
					$mapper->setEventManager($sm->get('EventManager'));
					$mapper->getEventManager()->attach('update-monitorrule-trigger-action',
													array($mailSettingsListener, 'checkMailSettings'));
					return $mapper;
				},
				'Issue\Filter\Translator' => function($sm) {
				    $translator = new \Issue\Filter\Translator();
				    $translator->setMonitorRuleMapper($sm->get('MonitorRules\Model\Mapper'));
				    return $translator;
				},
				'Issue\Db\Mapper' => function($sm) {
				    $mapper = new \Issue\Db\Mapper();
                    $mapper->setTableGateway(new TableGateway('events', $sm->get(Connector::DB_CONTEXT_MONITOR)));
				    return $mapper;
				},
				'EventsGroup\BacktraceSourceRetriever' => function ($sm) {
					$retriever = new BacktraceSourceRetriever($sm->get('EventsGroup\Db\Mapper'), $sm->get('WebAPI\Db\Mapper'), $sm->get('Servers\Db\Mapper'));
					return $retriever;
				}
			)
		);
	}

}
