<?php

namespace UrlInsight;

use Zend\ModuleManager\Feature\ServiceProviderInterface,
	Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Zend\ServiceManager\ServiceManager,
	Zend\Db\TableGateway\TableGateway,
	Application\Db\Connector;

class Module implements AutoloaderProvider, ServiceProviderInterface
{
	
	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
						),
				),
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
			'factories' => array(
				'UrlInsight\Db\RequestsMapper' => function (ServiceManager $sm) {
					// get the requested period, and choose the right table (-1 is "all" -> 'monthly')
					$request = $sm->get('Request');
					$period = $request->getQuery('period', 24);
					$tableSuffix = $period < 24 + 1 && $period > 0 ? 'daily' : ($period < 24 * 14 + 1 && $period > 0 ? 'weekly' : 'monthly');
					
					$mapper = new \UrlInsight\Db\RequestsMapper();
					$mapper->setTableGateway(new TableGateway('urlinsight_requests_' . $tableSuffix, $sm->get(Connector::DB_CONTEXT_UrlInsight)));
					return $mapper;
				},
				'UrlInsight\Db\ZraySnapshotsMapper' => function (ServiceManager $sm) {
					$mapper = new \UrlInsight\Db\ZraySnapshotsMapper();
					$mapper->setTableGateway(new TableGateway('urlinsight_zray_requests', $sm->get(Connector::DB_CONTEXT_UrlInsight)));
					return $mapper;
				},
			)
		);
	}
}
