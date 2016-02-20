<?php

namespace Codetracing;

use ZendServer\Edition;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\Connector;

class Module implements AutoloaderProvider, ServiceProviderInterface
{
	
	public function getServiceConfig() {
		return array(
			'invokables' => array(
				'Codetracing\Dump\Wrapper' => 'Codetracing\Dump\Wrapper',
				'Codetracing\Mapper\Tasks' => 'Codetracing\Mapper\Tasks'
			),
			'factories' => array(
				'Codetracing\Model' => function($sm) {
					$model = new Model();
					$model->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					return $model;
				},
				'Codetracing\TraceFilesMapper' => function($sm) {
				
					$mapper = new TraceFilesMapper();
					$mapper->setTableGateway(new TableGateway('trace_files', $sm->get(Connector::DB_CONTEXT_CODETRACING)));
					
					return $mapper;
				},
				'Codetracing\Trace\AmfFileRetriever' => function($sm) {
				
					$retriever = new Trace\AmfFileRetriever();
					$retriever->setWebapiKeyMapper($sm->get('WebAPI\Db\Mapper'));
					$retriever->setWrapper($sm->get('Codetracing\Dump\Wrapper'));
					
					return $retriever;
				}
			)
		);
	}
	
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
}
