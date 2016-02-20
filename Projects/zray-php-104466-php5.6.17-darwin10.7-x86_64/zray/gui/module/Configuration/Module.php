<?php

namespace Configuration;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;
use Configuration\License\ChangeListener;
use Configuration\License\LicenseChangeAnalyzer;
use Zend\EventManager\EventManager;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;
use Configuration\License\ZemUtilsWrapper;
use Configuration\Audit\ExtraData\DirectivesParser;
use Configuration\Task\ConfigurationPackage;
use Zsd\Db\TasksMapper;
use Application\Db\DirectivesFileConnector;
use ZendServer\Log\Log;
use Zend\Json\Json;

class Module implements AutoloaderProvider, ServiceProviderInterface
{
	/*
	 * (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
	 */
	public function getServiceConfig() {
		return array(
					'invokables' => array(
						'Configuration\License\Wrapper' => 'Configuration\License\Wrapper',
						'Configuration\Task\ConfigurationPackage' => 'Configuration\Task\ConfigurationPackage',
					),
					'factories' => array(
						'Configuration\Task\ConfigurationPackageFreshDb' => function($sm) {
							$package = $sm->get('Configuration\Task\ConfigurationPackage');
							
							$connector = new DirectivesFileConnector();
							$adapter = $connector->createDbAdapter(Connector::DB_CONTEXT_ZSD);
							
							$gateway = new TableGateway('ZSD_TASKS', $adapter);
							
							$tasksMapper = $package->getTasksMapper();
							$tasksMapper->setTableGateway($gateway);
							
							$package->setTasksMapper($tasksMapper);
							return $package;
						},
						'Configuration\Audit\ExtraData\DirectivesParser' => function($sm) {
							$parser = new DirectivesParser();
							$parser->setDirectivesMapper($sm->get('\Configuration\MapperDirectives'));
							return $parser;
						},
						'Configuration\License\ZemUtilsWrapper' => function($sm) {
							$wrapper = new ZemUtilsWrapper();
							
							$directives = $sm->get('Configuration\MapperDirectives');
							$licenseDirectives = $directives->getDirectivesValues(array('zend.serial_number', 'zend.user_name'));
							
							if (isset($licenseDirectives['zend.serial_number'])) {
								$wrapper->setLicenseSerial($licenseDirectives['zend.serial_number']);
							}
							
							if (isset($licenseDirectives['zend.user_name'])) {
								$wrapper->setLicenseUser($licenseDirectives['zend.user_name']);
							}
							return $wrapper;
						},
						'Configuration\License\ChangeListener' => function($sm) {
							$listener = new ChangeListener();
							$listener->setAcl($sm->get('ZendServerLicenseAcl'));
							$listener->setUsersMapper($sm->get('Users\Db\Mapper'));
							$listener->setGuiConfigurationMapper($sm->get('GuiConfiguration\Mapper\Configuration'));
							$listener->setlicenseChangeAnalyzer($sm->get('Configuration\License\LicenseChangeAnalyzer'));
							$listener->setAuditSettingsMapper($sm->get('Audit\Db\SettingsMapper'));
							return $listener;
						},
						'Configuration\License\LicenseChangeAnalyzer' => function($sm) {
							$analyzer = new LicenseChangeAnalyzer();
							$analyzer->setZendServerUtils($sm->get('Configuration\License\Wrapper'));
							return $analyzer;
						},
						'Configuration\MapperExtensions' => function($sm) {
							$mapper = new MapperExtensions(new TableGateway('ZSD_EXTENSIONS', $sm->get(Connector::DB_CONTEXT_ZSD)));
							return $mapper;
						},
						'Configuration\MapperReplies' => function($sm) {
							$mapper = new MapperReplies(new TableGateway('ZSD_REPLIES', $sm->get(Connector::DB_CONTEXT_ZSD)));
							$mapper->setTasksMapper($sm->get('Zsd\Db\TasksMapper'));
							return $mapper;
						},
						'Configuration\MapperDirectives' => function($sm) {
						    if (isAzureEnv()) {
								$mapper = new MapperDirectivesAzure();
						    } elseif (isZrayStandaloneEnv()) {
								$mapper = new MapperDirectivesStandalone();
						    } else {
								$mapper = new MapperDirectives();
						    }
							$mapper->setEventManager(new EventManager());
							$mapper->setTableGateway(new TableGateway('ZSD_DIRECTIVES', $sm->get(Connector::DB_CONTEXT_ZSD)));
							$mapper->setTasksMapper($sm->get('Zsd\Db\TasksMapper'));
							
							/// avoid circular dependency
							$profile = $sm->get('Snapshots\Mapper\Profile');
							$profile->setDirectivesMapper($mapper);
							$changeListener = $sm->get('Configuration\License\ChangeListener');
							$changeListener->setProfileMapper($profile);
							
							$mapper->getEventManager()->attach($changeListener);
							return $mapper;
						},
						'Configuration\DbImport' => function($sm) {
							$mapper = new DbImport();
							$mapper->setAdapter($sm->get(Connector::DB_CONTEXT_ZSD));
							$mapper->setTasksMapper($sm->get('Zsd\Db\TasksMapper'));
							$mapper->setDdMapper($sm->get('Configuration\DdMapper'));
							$mapper->setExtensionMapper($sm->get('Configuration\MapperExtensions'));
							return $mapper;
						},
						'Configuration\DdMapper' => function($sm) {
							if (!isAzureEnv()) {
								if (isZrayStandaloneEnv()) {
									$zeMapFile = getCfgVar('zend.conf_dir') . DIRECTORY_SEPARATOR . 'zend_extensions_map.json';
								} else {
									$zeMapFile = getCfgVar('zend.install_dir') . DIRECTORY_SEPARATOR . 'share/zend_extensions_map.json';
								}
								
								if (file_exists($zeMapFile)) {
									$content = file_get_contents($zeMapFile, Json::TYPE_OBJECT);
								} else {
									$content = '[]';
								}
							} else {
								$content = '[]';
							}
							
							return new DdMapper($content);
						}
					)
				);
	}
	/*
	 * (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\AutoloaderProviderInterface::getAutoloaderConfig()
	 */
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
