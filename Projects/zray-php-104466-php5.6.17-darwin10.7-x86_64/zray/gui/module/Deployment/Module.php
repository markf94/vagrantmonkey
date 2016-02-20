<?php

namespace Deployment;

use Deployment\View\Helper\ApplicationUrl;

use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

use Zend\EventManager\EventInterface;

use Application\Module as appModule;

use Zend\EventManager\Event;

use ZendDeployment_Manager;

use DeploymentLibrary\Mapper as DeploymentLibraryMapper;
use DeploymentLibrary\Db\Mapper as UpdatesMapper;
use DeploymentLibrary\Db\Mapper as DeploymentLibraryUpdateMapper;

use Deployment\Validator\ApplicationNameNotExists;
use Deployment\Forms\SetInstallation,
	Deployment\Forms\DefineApplicationForm;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;

use Zend\Crypt\PublicKey\Rsa\PublicKey;

use Zend\Mvc\MvcEvent;

use ZendServer\Log\Log;

use Zend\ModuleManager\Feature\InitProviderInterface;

use Zend\ModuleManager\ModuleManager,
	Zend\EventManager\Event as ManagerEvent,
    Zend\EventManager\StaticEventManager,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Application\View,
	Zend\Http\Header\Accept;
use DeploymentLibrary\Controller\Plugin\SetUpdateCookie;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;
use Vhost\Mapper\Vhost;
use Vhost\Db\Tasks;
use Vhost\VhostContainer;
use Vhost\Validator\VhostValidForRedeploy;
use Vhost\Validator\VhostValidForDeploy;
use Vhost\StdLib\Hydrator\VhostApplications;
use Deployment\Mapper\Deploy;
use Vhost\Mapper\AddVhost;
use Vhost\Mapper\EditVhost;
use Vhost\View\Helper\ReplyMessages;
use Configuration\License\License;

class Module implements ViewHelperProviderInterface, AutoloaderProvider, BootstrapListenerInterface, ServiceProviderInterface, ConfigProviderInterface
{
	/**
	 * @var boolean
	 */
	private $supportedByWebserver;
	
	public function onBootstrap(EventInterface $e) {
		$this->initializeFeatures($e);
		$serviceManager = $e->getApplication()->getServiceManager();
		$serviceManager->addInitializer(function ($instance) use ($serviceManager) {
			if ($instance instanceof IdentityApplicationsAwareInterface) {
				$instance->setIdentityFilter($serviceManager->get('Deployment\IdentityFilter'));
			}
		});
		
		if (! $this->supportedByWebserver) {
			$e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, function(MvcEvent $event) {
				if (in_array(preg_replace('#\-[0-9]+_[0-9]+#', '', $event->getRouteMatch()->getParam('controller')), array('VhostWebAPI', 'Vhost'))) {
					$newEvent = $event;
					$newEvent->setError(\Zend\Mvc\Application::ERROR_EXCEPTION);
					$newEvent->setParam('exception', new \Exception(_t('Vhosts are not supported on this operating system')));
					$event->getApplication()->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
				}
			});
		}
		
	}
	/*
	 * (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\ViewHelperProviderInterface::getViewHelperConfig()
	 */
	public function getViewHelperConfig() {
		return array(
				'invokables' => array(
					'ReplyMessages' => 'Vhost\View\Helper\ReplyMessages'
				),
				'factories' => array(
					'ApplicationUrl' => function($sm) {
						$helper = new ApplicationUrl();
						$helper->setDefaultServer(appModule::config('deployment', 'defaultServer'));
						return $helper;
					},
				)
			);
	}
	
	public function getServiceConfig() {
		$module = $this;
		return array(
			'invokables' => array(
					'Vhost\Form\Vhost' => 'Vhost\Form\Vhost',
					'Vhost\Filter\Dictionary' => 'Vhost\Filter\Dictionary',
					'Vhost\Filter\Filter' 	  => 'Vhost\Filter\Filter',
					'Vhost\Filter\Translator' => 'Vhost\Filter\Translator',
			),
			'factories' => array(
				'Vhost\Mapper\EditVhost' => function ($sm) {
					$mapper = new EditVhost();
					$mapper->setRepliesMapper($sm->get('Configuration\MapperReplies'));
					$mapper->setServersMapper($sm->get('Servers\Db\Mapper'));
					$mapper->setVhostMapper($sm->get('Vhost\Mapper\Vhost'));
					return $mapper;
				},
				'Vhost\Mapper\AddVhost' => function ($sm) {
					$mapper = new AddVhost();
					$mapper->setRepliesMapper($sm->get('Configuration\MapperReplies'));
					$mapper->setServersMapper($sm->get('Servers\Db\Mapper'));
					$mapper->setVhostMapper($sm->get('Vhost\Mapper\Vhost'));
					return $mapper;
				},
				'Vhost\Mapper\Tasks' => function ($sm) {
					$tasks = new \Vhost\Mapper\Tasks();
					$tasks->setZsdHealth($sm->get('Zsd\ZsdHealthChecker'));
					return $tasks;
				},
				'Vhost\Validator\VhostValidForRedeploy' => function($sm) {
					$validator = new VhostValidForRedeploy();
					$validator->setVhostMapper($sm->get('Vhost\Mapper\Vhost'));
					return $validator;
				},
				'Vhost\Validator\VhostValidForDeploy' => function($sm) {
					$validator = new VhostValidForDeploy();
					$validator->setVhostMapper($sm->get('Vhost\Mapper\Vhost'));
					return $validator;
				},
				'DeploymentLibrary\Mapper' => function ($sm) use ($module) {
					$mapper = new DeploymentLibraryMapper();
					$mapper->setDeploySupportedByWebserver($module->getSupportedByWebserver());
					return $mapper;
				},
				'DeploymentLibrary\Db\Mapper' => function ($sm) use ($module) {
					$mapper = new UpdatesMapper(new TableGateway('GUI_LIBRARY_UPDATES', $sm->get(Connector::DB_CONTEXT_GUI)));
					return $mapper;
				},
				'Deployment\Model' => function ($sm) use ($module) {
					$model = new Model();
					$model->setManager(new ZendDeployment_Manager());
					$model->setDeploySupportedByWebserver($module->getSupportedByWebserver());
					if (! appModule::config('authentication', 'simple')) {
						$model->getEventManager()->attach('preRemove', function(Event $e) use ($sm) {
							$application = $e->getTarget(); /* @var $application \Deployment\Application\Container */
							$mapperGroups = $sm->get('Acl\Db\MapperGroups'); /* @var $mapperGroups \Acl\Db\MapperGroups */
							$mapperGroups->deleteMapping($application->getApplicationId());
						});
					}
					return $model;
				},
				'Deployment\Db\Mapper' => function($sm) use($module) {
					$mapper = new \Deployment\Db\Mapper(new TableGateway('deployment_downloads', $sm->get(Connector::DB_CONTEXT_ZDD)));
					return $mapper;
				},
				'Deployment\FilteredAccessMapper' => function ($sm) use ($module) {
					$mapper = new FilteredAccessMapper();
					$mapper->setModel($sm->get('Deployment\Model'));
					return $mapper;
				},
				'Deployment\IdentityFilter' => function($sm) {
					if (! appModule::config('authentication', 'simple')) {
						$mapperGroups = $sm->get('Acl\Db\MapperGroups');
						$filter = new IdentityFilterGroups();
						$filter->setMapperGroups($mapperGroups);
					} else {
						$filter = new IdentityFilterSimple();
					}
					$filter->setDeploymentMapper($sm->get('Deployment\Model'));
					return $filter;
				},
				'Deployment\Form\SetInstallation' => function ($sm) use ($module) {
					$form = new SetInstallation(null, $sm->get('Deployment\Model'));
                    $form->setVhostMapper($sm->get('Vhost\Mapper\Vhost'));
                    $form->setVhostValidator($sm->get('Vhost\Validator\VhostValidForDeploy'));
                    $form->init();
					return $form;
				},
				'Deployment\Form\DefineApplicationForm' => function ($sm) use ($module) {
					$form = new DefineApplicationForm(null, $sm->get('Deployment\Model'));
					return $form;
				},
				'Vhost\Mapper\Vhost' => function ($sm) use ($module) {
					$mapper = new Vhost();
					$mapper->setVhostTasks($sm->get('Vhost\Mapper\Tasks'));
                    $mapper->setDeploymentMapper($sm->get('Deployment\Model'));
                    $mapper->setVhostsManaged($module->getSupportedByWebserver());
                    $mapper->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
                    $mapper->setMessagesMapper($sm->get('Messages\Db\MessageMapper'));
					return $mapper;
				},
				'Deployment\Mapper\Deploy' => function ($sm) use ($module) {
					$mapper = new Deploy();
					$mapper->setManager(new ZendDeployment_Manager());
					$mapper->setDeploymentMapper($sm->get('Deployment\Model'));
					$mapper->setVhostsMapper($sm->get('Vhost\Mapper\Vhost'));
					return $mapper;
				}
			)
		);
	}
	
	public function getControllerPluginConfig() {
		$module = $this;
		return array(
				'factories' => array(
						'SetLibrariesUpdateCookie' => function($sm) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
							$plugin = new SetUpdateCookie();
							$plugin->setAcl($sm->getServiceLocator()->get('ZendServerAcl'));
							$plugin->setLibrariesMapper($sm->getServiceLocator()->get('DeploymentLibrary\Mapper'));
							$plugin->setUpdatesMapper($sm->getServiceLocator()->get('DeploymentLibrary\Db\Mapper'));
							return $plugin;
						},
				));
	}
	
	/**
	 * @param MvcEvent $e
	 */
	public function initializeFeatures(MvcEvent $e) {
		$request = $e->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		if ($e->getParam('httpRequest')) {
			$webserver = $request->getServer('SERVER_SOFTWARE', '');
			if (strpos($webserver, 'Apache') !== false
			|| strpos($webserver, 'lighttpd') !== false
			|| strpos($webserver, 'nginx') !== false) {
				$this->supportedByWebserver = true;
			} else {
				$this->supportedByWebserver = false;
			}
		} else {
			$this->supportedByWebserver = false;
		}
	}
	
	public function getAutoloaderConfig() {
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
								'Prerequisites' => __DIR__ . '/src/Prerequisites',
								'DeploymentLibrary' => __DIR__ . '/src/DeploymentLibrary',
								'Vhost' => __DIR__ . '/src/Vhost',
						),
				),
		);
	}
	
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	/**
	 * @return boolean $supportedByWebserver
	 */
	public function getSupportedByWebserver() {
		return $this->supportedByWebserver;
	}

}
