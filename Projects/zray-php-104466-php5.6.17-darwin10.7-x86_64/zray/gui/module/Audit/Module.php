<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Audit for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Audit;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Audit\Controller\Plugin\AuditMessage;
use Audit\Controller\Plugin\AuditMessageProgress;
use Audit\Db\Mapper;
use Zend\Db\TableGateway\TableGateway;
use Audit\Db\ProgressMapper;
use Audit\Db\SettingsMapper;
use Audit\View\Helper\auditType;
use Audit\View\Helper\auditTypeById;
use Application\Db\Connector;
use Application\Db\DirectivesFileConnector;
use ZendServer\Log\Log;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractController;

class Module implements BootstrapListenerInterface, AutoloaderProviderInterface, ViewHelperProviderInterface, ControllerPluginProviderInterface, ServiceProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getViewHelperConfig() {
    	return array(
    			'factories' => array(
    				'auditType' => function($sm) {
    					$auditType = new auditType();
    					$auditType->setDictionary($sm->getServiceLocator()->get('Audit\Dictionary'));
    					return $auditType;
    				},
    				'auditTypeById' => function($sm) {
    					$auditType = new auditTypeById();
    					$auditType->setDictionary($sm->getServiceLocator()->get('Audit\Dictionary'));
    					return $auditType;
    				},
    			));
    }
    
    public function getControllerPluginConfig() {
    	$module = $this;
    	return array(
    		'factories' => array(
    			'AuditMessageClusterLeap' => function($sm) {
    				/// use create - we want to get a new instance
	    			$auditMessage = $sm->create('AuditMessage'); /* @var $auditMessage AuditMessage */
	    			$auditMessage->setLeap();
	    			$connector = new DirectivesFileConnector();
	    			
	    			$auditMapper = $auditMessage->getAuditMapper();
	    			
	    			$tableGateway = new TableGateway($auditMapper->getTableGateway()->getTable(), $connector->createDbAdapter(Connector::DB_CONTEXT_ZSD));
	    			$auditMessage->setAuditMapper($auditMapper->setTableGateway($tableGateway));
	    			return $auditMessage;
	    		},
    			'AuditMessageProgressClusterLeap' => function($sm) {
	    			$auditMessageProgress = $sm->create('AuditMessageProgress'); /* @var $auditMessageProgress AuditMessageProgress */
	    			$connector = new DirectivesFileConnector();
	    			
	    			$tableGateway = new TableGateway($auditMessageProgress->getAuditProgressMapper()->getTableGateway()->getTable(), $connector->createDbAdapter(Connector::DB_CONTEXT_ZSD));
	    			$auditMessageProgress->getAuditProgressMapper()->setTableGateway($tableGateway);
	    			
	    			$tableGateway = new TableGateway($auditMessageProgress->getServersDbMapper()->getTableGateway()->getTable(), $connector->createDbAdapter(Connector::DB_CONTEXT_ZSD));
	    			$auditMessageProgress->getServersDbMapper()->setTableGateway($tableGateway);
	    			return $auditMessageProgress;
	    		},
	    		/// needed for AuditMessage event calls
    			'AuditMessage' => function($sm) {
					return $sm->getServiceLocator()->get('Audit\Controller\Plugin\AuditMessage');
				},
				'AuditMessageProgress' => function($sm) {
					return $sm->getServiceLocator()->get('Audit\Controller\Plugin\AuditMessageProgress');
				},
    		));
    }
    
    public function getServiceConfig() {
    	$module = $this;
    	return array(
    			'invokables' => array(
		    		'Audit\Dictionary' => 'Audit\Dictionary',
    				'Audit\Forms\Settings' => 'Audit\Forms\Settings'
		    	),
    			'factories' => array(
    				/// needed for AuditMessage event
    				'Audit\Controller\Plugin\AuditMessage' => function ($sm) {
	    				$auditMessage = new AuditMessage();
	    				$auditMessage->setAuditMapper($sm->get('Audit\Db\Mapper'));
	    				return $auditMessage;
	    			},
					'Audit\Controller\Plugin\AuditMessageProgress' => function($sm) {
						$auditMessage = new AuditMessageProgress();
						$auditMessage->setServersMapper($sm->get('Servers\Configuration\Mapper'));
						$auditMessage->setServersDbMapper($sm->get('Servers\Db\Mapper'));
						$auditMessage->setAuditProgressMapper($sm->get('Audit\Db\ProgressMapper'));
						return $auditMessage;
					},
					'Audit\Db\Mapper' => function($sm) {
						$auditMapper = new Mapper();
						$auditMapper->setTableGateway(new TableGateway('ZSD_AUDIT', $sm->get(Connector::DB_CONTEXT_ZSD)));
						$auditMapper->setProgressMapper($sm->get('Audit\Db\ProgressMapper'));
						return $auditMapper;
					},
					'Audit\Db\ProgressMapper' => function($sm) {
						$auditMapper = new ProgressMapper();
						$auditMapper->setTableGateway(new TableGateway('ZSD_AUDIT_PROGRESS', $sm->get(Connector::DB_CONTEXT_ZSD)));
						return $auditMapper;
					},
					'Audit\Db\SettingsMapper' => function($sm) {
						$auditMapper = new SettingsMapper();
						$auditMapper->setPropertiesTable(new TableGateway('ZSD_AUDIT_PROPERTIES', $sm->get(Connector::DB_CONTEXT_ZSD)));
						return $auditMapper;
					},
    			)
    		);
    }
	/* (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\BootstrapListenerInterface::onBootstrap()
	 */
	public function onBootstrap(\Zend\EventManager\EventInterface $e) {
		$eventsManager = $e->getApplication()->getEventManager();
		/// catch all for audit entries that failed
		$eventsManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function(MvcEvent $e) {
			$controller = $e->getTarget(); /* @var $controller \Zend\Mvc\Controller\AbstractController */
			if ($controller instanceof AbstractController) {
				$auditMessage = $controller->auditMessage()->getMessage();
				if ($auditMessage instanceof Container) {
					$exception = $e->getParam('exception');
					if ($exception instanceof \Exception) {
						$message = $exception->getMessage();
					} else {
						$message = '';
					}
					
					$controller->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($message)));
					Log::debug("Audit entry {$auditMessage->getAuditId()} is marked as failed due to an exception");
				}
			}
		}, -2000);
	}

}
