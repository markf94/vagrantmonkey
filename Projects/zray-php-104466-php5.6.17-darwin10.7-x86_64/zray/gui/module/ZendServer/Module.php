<?php

namespace ZendServer;

use ZendServer\License\ChangeListener;

use Zend\EventManager\EventInterface;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;

use ZendServer\Authentication\Adapter\DbTable;

use ZendServer\Authentication\Adapter\Ldap;
use ZendServer\Authentication\Adapter\Azure;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;

use ZendServer\Configuration\Container as ConfigurationContainer;

use Application\Module as appModule;
use ZendServer\Permissions\AclQuery;
use ZendServer\Log\Log;
use Zend\Json\Json;
use Configuration\DdMapper;
use ZendServer\Configuration\Manager;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;
use Zend\Crypt\Hash;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

if (! function_exists('_t')) {
	require_once (__DIR__ . DIRECTORY_SEPARATOR . 'functions.php');// require the global convenience functions
}

class Module implements AutoloaderProvider, ServiceProviderInterface, ViewHelperProviderInterface
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
	
	public function getConfig() {
		
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getViewHelperConfig() {
		return array(
			'invokables' => array(
				'ParseMarkdown' => 'ZendServer\View\Helper\ParseMarkdown'
			)
		);
	}
	
	/*
	 * (non-PHPdoc)
	* @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
	*/
	public function getServiceConfig() {
		$module = $this;
		return array(
			'aliases' => array(
				'AuthAdapterLdap' => 'ZendServer\Authentication\Adapter\Ldap',
			    'AuthAdapterAzure' => 'ZendServer\Authentication\Adapter\Azure',
			    'AuthAdapterZrayStandalone' => 'ZendServer\Authentication\Adapter\ZrayStandalone',
				'AuthAdapterDbTable' => 'ZendServer\Authentication\Adapter\DbTable',
				'ZendServerAcl' => 'ZendServer\Permissions\AclQuery',
			),
			'invokables' => array(
			),
			'factories' => array(
				'ZendServer\Permissions\AclQuery' => function($sm) {
					$acl = new AclQuery();
					if ($sm->has('ZendServerIdentityAcl')) {
						$acl->setAcl($sm->get('ZendServerIdentityAcl'));
					}
					if ($sm->has('ZendServerLicenseAcl')) {
						$acl->setEditionAcl($sm->get('ZendServerLicenseAcl'));
					}
					return $acl;
				},
				'ViewManager' => 'ZendServer\Mvc\Service\ViewManagerFactory',
					
				'ZendServer\Configuration\Container'  => function($sm) {
					$adapter = new ConfigurationContainer();
					$adapter->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					$adapter->setExtensionsMapper($sm->get('Configuration\MapperExtensions'));
					$adapter->setLibrariesMapper($sm->get('DeploymentLibrary\Mapper'));
					if (isZrayStandaloneEnv()) {
						$zeMapFile = getCfgVar('zend.conf_dir') . DIRECTORY_SEPARATOR . 'zend_extensions_map.json';
					} else {
						$zeMapFile = getCfgVar('zend.install_dir') . DIRECTORY_SEPARATOR . 'share/zend_extensions_map.json';
					}
					$content = @file_get_contents($zeMapFile, Json::TYPE_OBJECT);
					if ($content !== false) {
						$adapter->setDdMapper(new DdMapper($content));
					} else {
						Log::warn(_t('Cannot file the file share/zend_extensions_map.json'));
					}
					$adapter->setManager(new Manager());
					return $adapter;
				},
				'ZendServer\Authentication\Adapter\Ldap' => function($sm) {
					$adapter = new Ldap(array('zend_server_authentication' => appModule::config('zend_server_authentication')->toArray()));
					$adapter->setMapperGroups($sm->get('Acl\Db\MapperGroups'));
					$adapter->setGroupsAttribute(appModule::config('authentication','groupsAttribute'));
					return $adapter;
				},
				'ZendServer\Authentication\Adapter\Azure' => function($sm) {
				    $adapter = new Azure(array('zend_server_authentication' => appModule::config('zend_server_authentication')->toArray()));
				    return $adapter;
				},
				'ZendServer\Authentication\Adapter\DbTable' => function($sm) {
					$dbAdapter = $sm->get(Connector::DB_CONTEXT_GUI);
					$adapter = new DbTable($dbAdapter, 'GUI_USERS', 'NAME', 'PASSWORD', function($dbCreds, $userCreds) {
		                return $dbCreds === Hash::compute('sha256', $userCreds);
		            });
					return $adapter;
				},
				'ZendServer\Filter\Mapper' => function($sm) {
				    $mapper = new \ZendServer\Filter\Mapper(new TableGateway('GUI_FILTERS', $sm->get(Connector::DB_CONTEXT_GUI)));
				    return $mapper;
				},
			)
		);
	}
}
