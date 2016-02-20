<?php
namespace Application;

use Application\View\Helper\HelpLink;
use Audit\Controller\Plugin\AuditEmail;

use Application\Controller\Plugin\TestEmail;

use Notifications\Controller\Plugin\NotificationEmail;

use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Config;

use ZendServer\Permissions\AclQuerierInterface;

use ZendServer\Permissions\AclQuery;

use Configuration\License\License;

use Application\View\Helper\EditionImage;

use Zend\Loader\StandardAutoloader;

use Zend\Uri\Uri;

use ZendServer\Log\Formatter\Simple;

use Snapshots\Controller\Plugin\CreateConfigurationSnapshot;

use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;

use Zend\Mvc\ModuleRouteListener;

use Zend\EventManager\EventInterface;

use Zend\ModuleManager\ModuleManagerInterface;

use Zend\Validator\Regex;

use Users\Forms\ChangePassword;

use Application\Forms\Login;

use Zend\InputFilter\Factory;

use Logs\LogReader;

use Users\Identity;

use Audit\Db\ProgressMapper;

use Zend\ServiceManager\ServiceManager;

use Zend\ModuleManager\Feature\InitProviderInterface;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\EventManager\EventManager;

use Zend\Mvc\MvcEvent,
	Zend\EventManager\Event as ManagerEvent,
	ZendServer\Edition;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Zend\Log\Logger,
	Zend\Permissions\Acl\Acl,
	ZendServer\Exception,
	ZendServer\Log\Log,
	Messages\Db\MessageFilterMapper;

use Zsd\Db\TasksMapper as Mapper;

use Application\Controller\Plugin\CapabilitiesList;

use Snapshots\Mapper\Profile;

use Notifications\Db\NotificationsMapper;
use Zsd\ZsdHealthChecker;
use Zend\ModuleManager\ModuleEvent;
use ZendServer\Configuration\Manager;
use Servers\Configuration\Mapper as ServersConfigurationMapper;
use Application\Exception\DependencyException;
use Zend\EventManager\Event;
use Zsd\Db\TasksMapperAwareInterface;
use ZendServer\EditionAwareInterface;
use Messages\Db\MessageMapper;
use Notifications\Db\NotificationsActionsMapper;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;
use Notifications\View\Helper\NotificationDescription;
use Application\Db\Adapter\AdapterAwareInterface;
use Servers\Db\ServersAwareInterface;
use ZendServer\FS\FS;
use Audit\Controller\Plugin\InjectAuditMessageInterface;
use Zend\Mvc\Application;
use Zend\Mvc\Exception\InvalidControllerException;
use Zend\Console\Console;
use Application\Module as appModule;
use Zend\Authentication\Storage\NonPersistent;
use DevBar\Listener\AbstractDevBarProducer;
use Zend\I18n\Translator\Translator;

class Module implements ViewHelperProviderInterface, ControllerPluginProviderInterface, AutoloaderProvider, ConfigProviderInterface, InitProviderInterface, BootstrapListenerInterface, ServiceProviderInterface
{
	const ACL_ROLE_ADMINISTRATOR = 'administrator';
	const ACL_ROLE_DEVELOPER = 'developer';
	const ACL_ROLE_BOOTSTRAP = 'bootstrap';
	const ACL_ROLE_GUEST = 'guest';
	
	const INI_PREFIX = 'zend_gui';
	
	protected $view;
	/**
	 * @var \Application\View\Listener
	 */
	protected $viewListener;

	/**
	 * @var \Zend\Config\Config
	 */
	protected static $config;
	
	/**
	 * @var \Zend\Log\Logger
	 */
	protected static $log;
	
	/**
	 * 
	 * @var \Zend\Navigation\Navigation
	 */
	protected $navigation;


	/**
	 * @var ServiceManager
	 */
	static protected $staticManager;
	
	/**
	 * @var bool
	 */
	static protected $isHTTPS = false;
	
	/**
	 * @var ServiceManager
	 */
	protected $serviceManager = null;

	/**
	 * @var EventManager
	 */
	protected $sharedEvents = null;
	/**
	 * @var \Zend\Mvc\Application
	 */
	protected $application = null;
	
	
	/**
	 * May accept a list of configuration directive path steps to retrieve the directive value directly
	 * @param string $step, ....print_r($eventsGroup->getEventsGroupId(),true)
	 * @example Module::config('package', 'edition') returns the current edition directive's value directly
	 * @return \Zend\Config\Config|scalar
	 * @throws \ZendServer\Exception
	 */
	public static function config() {
		if (func_num_args() > 0) {
			$steps = func_get_args();
			$configLevel = static::$config;
			$zend_gui = self::INI_PREFIX;
			foreach ($steps as $step) {
				if (isset($configLevel->$step)) {
					$configLevel = $configLevel->$step;
				} elseif (isset($configLevel->$zend_gui->$step)) {
					$configLevel = $configLevel->$zend_gui->$step;
				} else {
					throw new \ZendServer\Exception("gui directive not found: ". implode('.', $steps));
				}    			
			}
			
			if (isset($configLevel->$zend_gui) && $configLevel->$zend_gui) {
				$configLevel = $configLevel->$zend_gui; // we don't want the surrounding section
			} 
			
			return $configLevel;
		}
		
		return static::$config;
	}
	
	/**
	 * @return boolean
	 */
	public static function isClusterManager() {
		$edition = new Edition();
		return $edition->isClusterManager();
	}
	
	/**
	 * @return boolean
	 */
	public static function isSingleServer() {
		$edition = new Edition();
		return $edition->isSingleServer();
	}
	
	/**
	 * @return boolean
	 */
	public static function isClusterServer() {
		$edition = new Edition();
		return $edition->isClusterServer();
	}

	/**
	 * @return boolean
	 */
	public static function isCluster() {
		return self::isClusterServer() || self::isClusterManager();
	}
	
	/**
	 * @return \Zend\Log\Logger
	 */
	public static function log() {
		return static::$log;
	}
	
	/**
	 * @return ServiceManager
	 */
	public static function serviceManager() {
		return static::$staticManager;
	}
	
	public function getViewHelperConfig() {
		return array(
				'factories' => array(
					'HelpLink' => function($sm) {
						$helper = new HelpLink();
						$request = $sm->getServiceLocator()->get('Request');
						$helper->setRequestUri($request->getServer('REQUEST_URI'));
						$app = $sm->getServiceLocator()->get('Application');
						$helper->setRouteMatch($app->getMvcEvent()->getRouteMatch());
						return $helper;
					},
					'NotificationDescription' => function($sm) {
						$helper = new NotificationDescription();
						$helper->setUtilsWrapper($sm->getServiceLocator()->get('Configuration\License\ZemUtilsWrapper'));
						return $helper;
					},
					'EditionImage' => function($sm) {

						$serverType = 0;
						if (Module::isClusterManager()) {
							$serverType = EditionImage::SERVER_TYPE_STANDALONE_GUI;
						} elseif(Module::isClusterServer()) {
							$serverType = EditionImage::SERVER_TYPE_CLUSTER_MEMBER;
						} elseif(Module::isSingleServer()) {
							$serverType = EditionImage::SERVER_TYPE_SERVER;
						}

						$helper = new EditionImage($serverType);
						return $helper;
					},
				)
			);
	}
	
	/*
	 * (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\ControllerPluginProviderInterface::getControllerPluginConfig()
	 */
	public function getControllerPluginConfig() {
		$module = $this;
		return array(
			'factories' => array(
				'notificationEmail' => function($sm) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
					$plugin = new NotificationEmail();
					return $plugin;
				},
				'testEmail' => function($sm) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
					$plugin = new TestEmail();
					return $plugin;
				},
				'auditEmail' => function($sm) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
					$plugin = new AuditEmail();
					$plugin->setProgressMapper($sm->getServiceLocator()->get('Audit\Db\ProgressMapper'));
					
					return $plugin;
				},
				'CapabilitiesList' => function($sm) use ($module) {
					$plugin = new CapabilitiesList();
					$plugin->setLicenseAcl($sm->getServiceLocator()->get('ZendServerLicenseAcl'));
					$plugin->setLicenseAclConfig($module->config('license', 'acl'));
					return $plugin;
				},
				'CreateConfigurationSnapshot' => function($sm) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
					$plugin = new CreateConfigurationSnapshot();
					$plugin->setSnapshotsMapper($sm->getServiceLocator()->get('Snapshots\Db\Mapper'));
					$plugin->setServiceLocator($sm->getServiceLocator());
					return $plugin;
				},
				'Authentication' => function($sm) use ($module) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
					$azure = isAzureEnv();
					$plugin = new Controller\Plugin\Authentication();
					$plugin->setAuthAdapter($sm->getServiceLocator()->get('ZendServerAuthenticationAdapter'));
					$plugin->setAuthService($sm->getServiceLocator()->get('Zend\Authentication\AuthenticationService'));
					$plugin->setGroupsMapper($sm->getServiceLocator()->get('Acl\Db\MapperGroups'));
					return $plugin;
				},
				'ServerInfo' => function($sm) use ($module) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
					$plugin = new Controller\Plugin\ServerInfo();
					return $plugin;
				},
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
				'AuthAdapterSimple' => 'AuthAdapterDbTable',
				'AuthAdapterExtended' => 'AuthAdapterLdap',
				'AuthAdapterAzure' => 'AuthAdapterAzure',
			),
			'invokables' => array(
				'GuiConfiguration\Mapper\Configuration' => 'GuiConfiguration\Mapper\Configuration',
				'Application\Db\AbstractFactoryConnector' => 'Application\Db\AbstractFactoryConnector',
				'Application\Db\DirectivesFileConnector' => 'Application\Db\DirectivesFileConnector',
				'Zend\Authentication\AuthenticationService' => 'Zend\Authentication\AuthenticationService',
				'Servers\Db\Tasks' => 'Servers\Db\Tasks',
				'Zend\Session\SessionManager' => 'Zend\Session\SessionManager',
				'Servers\Configuration\Mapper' => 'Servers\Configuration\Mapper',
				'Zsd\ZsdHealthChecker' => 'Zsd\ZsdHealthChecker'
			),
			'factories' => array(
				'LibraryUpdates\Db\Mapper' => function ($sm) {
					$mapper = new \LibraryUpdates\Db\Mapper(new TableGateway('GUI_LIBRARY_UPDATES', $sm->get(Connector::DB_CONTEXT_GUI)));
					return $mapper;
				},
				'Snapshots\Db\Mapper' => function ($sm) {
					$mapper = new \Snapshots\Db\Mapper(new TableGateway('GUI_SNAPSHOTS', $sm->get(Connector::DB_CONTEXT_GUI)));
					return $mapper;
				},
				'Acl\License\Mapper' => function($sm) {
					$mapper = new \Acl\License\Mapper();
					$mapper->setAcl($sm->get('ZendServerAcl'));
					return $mapper;
				},
				'Servers\Db\Mapper' => function (ServiceManager $sm) {
					$mapper = new \Servers\Db\Mapper();
					$mapper->setTableGateway(new TableGateway('ZSD_NODES', $sm->get(Connector::DB_CONTEXT_ZSD)));
					$mapper->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					return $mapper;
				},
				'Notifications\Db\NotificationsActionsMapper' => function($sm) {
					$mapper = new NotificationsActionsMapper(new TableGateway('ZSD_NOTIFICATIONS_ACTIONS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					return $mapper;
				},
				'Notifications\Db\NotificationsMapper' => function($sm) {
					$mapper = new NotificationsMapper(new TableGateway('ZSD_NOTIFICATIONS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					$mapper->setNotificationsActionsGateway(new TableGateway('ZSD_NOTIFICATIONS_ACTIONS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					return $mapper;
				},
				'Navigation' => function($sm) use ($module) {
					$factory = new \ZendServer\Navigation\Service\DefaultNavigationFactory();
					/// instantiators are not executed for factories
					$factory->setAcl($sm->get('ZendServerAcl'));

					try {
						$license = $sm->get('Configuration\License\ZemUtilsWrapper')->getLicenseInfo(); /* @var $license License */
						$filter = array();
						if ($license->isCloudLicense()) {
							/// remove license entry page from navigation if the license never expires
							$filter[] = array('controller' => 'License');
						}
							
						$mapper = new ServersConfigurationMapper();
						if (! $mapper->isClusterSupport()) {
							// remove the sc settings page when the cluster isn't supported
							$filter[] = array('controller' => 'SessionClustering');
						}
						
						$azure = isAzureEnv();
						$standaloneZray = isZrayStandaloneEnv();
						if (!$azure && !$standaloneZray) {
							$vhostMapper = $sm->get('Vhost\Mapper\Vhost'); /* @var $vhostMapper \Vhost\Mapper\Vhost */
							if (! $vhostMapper->isVhostsManaged()) {
								$filter[] = array('controller' => 'Vhost');
							}
						}
						
						$factory->setFilterPages($filter);
					} catch(\Exception $ex) {
						Log::notice('License not retrieved, cannot filter navigation bar');
						Log::debug($ex);
					}
						
					return $factory->createService($sm);
				},
				'Snapshots\Mapper\Profile' => function($sm) use ($module) {
					$profile = new Profile();
					$profile->setGuiConfigurationMapper($sm->get('GuiConfiguration\Mapper\Configuration'));
					$profile->setProfiles($module->config('profiles'));
					return $profile;
				},
				'Zsd\Db\TasksMapper' => function($sm) {
					$mapper = new Mapper();
					/// avoid circular dependency detection error
					$mapper->setTableGateway(new TableGateway('ZSD_TASKS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					return $mapper;
				},
				'Zsd\Db\NodesProfileMapper' => function($sm) {
					$mapper = new \Zsd\Db\NodesProfileMapper();
					/// avoid circular dependency detection error
					$mapper->setTableGateway(new TableGateway('ZSD_NODES_PROFILE', $sm->get(Connector::DB_CONTEXT_ZSD)));
					return $mapper;
				},
				'ZendServerAuthenticationAdapter' => function($sm) use ($module) {
					if ($module->config('authentication', 'simple')) {
						$adapter = $sm->get('AuthAdapterSimple');
					
					} elseif ($module->config('authentication', 'adapter')) {
						$adapter = $sm->get($module->config('authentication', 'adapter'));
					} else {
						$adapter = $sm->get('AuthAdapterExtended');
					}
					
					if (! $sm->has('ZendServerAuthenticationAdapter')) {
						$sm->setService('ZendServerAuthenticationAdapter', $adapter);
					}
					return $adapter;
				},
				'Bootstrap\Mapper\Reset' => function($sm) {
					$bootstrap = new \Bootstrap\Mapper\Reset();
					$bootstrap->setGuiConfigurationMapper($sm->get('GuiConfiguration\Mapper\Configuration'));
					return $bootstrap;
				},
				'Bootstrap\Mapper' => function($sm) {
					$bootstrap = new \Bootstrap\Mapper();
					$bootstrap->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					$bootstrap->setUsersMapper($sm->get('Users\Db\Mapper'));
					$bootstrap->setChangePassword(new ChangePassword());
					$bootstrap->setWebapiKeysMapper($sm->get('WebAPI\Db\Mapper'));
					$bootstrap->setGuiConfiguration($sm->get('GuiConfiguration\Mapper\Configuration'));
					$bootstrap->setNotificationsActionsMapper($sm->get('Notifications\Db\NotificationsActionsMapper'));
					$bootstrap->setConfigurationPackage($sm->get('Configuration\Task\ConfigurationPackage'));
					
					$profile = $sm->get('Snapshots\Mapper\Profile');
					$profile->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					$bootstrap->setProfilesMapper($profile);
					
					return $bootstrap;
				},
				'Users\Identity' => function($sm) use ($module) {
					$authService = $sm->get('Zend\Authentication\AuthenticationService');
					if ($authService->hasIdentity()) {
						return $authService->getIdentity();
					}
					$role = $module->isBootstrapCompleted() ? 'guest' : 'bootstrap';
					$identity = new Identity('Unknown', $role);
					return $identity;
				},
				'Acl\Db\Mapper' => function($sm) {
					$aclMapper = new \Acl\Db\Mapper();
					$aclMapper->setRolesTable(new TableGateway('GUI_ACL_ROLES', $sm->get(Connector::DB_CONTEXT_GUI)));
					$aclMapper->setResourcesTable(new TableGateway('GUI_ACL_RESOURCES', $sm->get(Connector::DB_CONTEXT_GUI)));
					$aclMapper->setPrivilegesTable(new TableGateway('GUI_ACL_PRIVILEGES', $sm->get(Connector::DB_CONTEXT_GUI)));
					return $aclMapper;
				},		
				'Acl\Db\MapperGroups' => function($sm) {
					return new \Acl\Db\MapperGroups(new TableGateway('GUI_LDAP_GROUPS', $sm->get(Connector::DB_CONTEXT_GUI)));
				},
				'Acl\Form\GroupsMappingFactory' => function($sm) {
					$factory = new \Acl\Form\GroupsMappingFactory();
					$factory->setAclMapper($sm->get('Acl\Db\Mapper'));
					$factory->setDeploymentModel($sm->get('Deployment\Model'));
					$factory->setGroupsMapper($sm->get('Acl\Db\MapperGroups'));
					return $factory;
				},
				'Messages\Db\MessageMapper' => function($sm) {
					$mapper = new MessageMapper(new TableGateway('ZSD_MESSAGES', $sm->get(Connector::DB_CONTEXT_ZSD)));
					return $mapper;
				},
				'Messages\Db\MessageFilterMapper' => function($sm) {
					$messageFilterMapper = new MessageFilterMapper();
					$messageFilterMapper->setTableGateway(new TableGateway('ZSD_MESSAGES_FILTERS', $sm->get(Connector::DB_CONTEXT_ZSD)));
					$messageFilterMapper->setMessagesGateway(new TableGateway('ZSD_MESSAGES', $sm->get(Connector::DB_CONTEXT_ZSD)));
					return $messageFilterMapper;
				},
				'Logs\LogReader' => function($sm) {
					$reader = new LogReader();
					$reader->setLogsDbMapper($sm->get('Logs\Db\Mapper'));
					return $reader;
				},
				'Logs\Db\Mapper' => function($sm) {
					$reader = new \Logs\Db\Mapper(new TableGateway('GUI_AVAILABLE_LOGS', $sm->get(Connector::DB_CONTEXT_GUI)));
					$reader->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					return $reader;
				},
				
				'Users\Forms\ChangePassword' => function($sm) {
					$loginForm = new ChangePassword();
					$loginForm->setInputFilter($sm->get('Users\InputFilter\Credentials'));
					$loginForm->setValidationGroup('newPassword');
					return $loginForm;
				},
				
				'Application\Forms\Login' => function($sm) {
					$authConfig = Module::config('authentication');
					$simpleAuth = $authConfig->simple ? true : false;
					
					$usernames = array();
					if ($simpleAuth) {
						$usersMapper = $sm->get('Users\Db\Mapper');
						$users = $usersMapper->getUsers()->toArray();
						$usernames = array();
						foreach ($users as $user) {
							$usernames[$user['NAME']] = $user['NAME'];
						}
					}
					
					$loginForm = new Login(array('simpleAuth' => $simpleAuth, 'users' => $usernames));
					$loginForm->setValidationGroup('username', 'password');
					
					return $loginForm;
				},
				'Users\Db\Mapper' => function($sm) use($module) {
					$mapper = new \Users\Db\Mapper(new TableGateway('GUI_USERS', $sm->get(Connector::DB_CONTEXT_GUI)));
					return $mapper;
				},
				'Users\InputFilter\Credentials' => function($sm) use($module) {
					$inputFactory = new Factory();
					
					$userValidator = new Regex('#^[[:graph:]]+$#');
					$userValidator->setMessage(_t('Username field can not contain whitespace characters'));
					
					$passwordValidator = new Regex('#'. str_replace('#', '\\#', ChangePassword::PASSWORD_PATTERN) .'#');
					$passwordValidator->setMessage(_t('Password field may contain alpha numeric and punctuation characters'));
					
					$passwordValidators = array(
						'validators' => array(
							array(
								'name'    => 'StringLength',
								'options' => array(
									'min' => $module->config('user', 'passwordLengthMin'),
									'max' => $module->config('user', 'passwordLengthMax')
								),
							),
							$passwordValidator
						));
						
					$validators = $inputFactory->createInputFilter(array(
						'username' => array('validators' => array(
							array(
								'name'    => 'StringLength',
								'options' => array(
									'min' => $module->config('user', 'usernameLengthMin'),
									'max' => $module->config('user', 'usernameLengthMax')
								),
							),
							$userValidator
						)),
						'password' => $passwordValidators,
						'newPassword' => $passwordValidators,
					));
					
					return $validators;
				},
			),
		);
	}
	
	public function init(ModuleManagerInterface $manager = null) {
		$this->sharedEvents = $events = $manager->getEventManager()->getSharedManager();
		$manager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'initializeConfig'));
		$manager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'initializeDependencies'));
		$manager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'initializeDebugMode'));

	}
	
	/**
	 * @param MvcEvent $e
	 */
	public function checkDependencies(MvcEvent $e) {
		$dependencies = self::config('dependencies');
		
		$booltrue = array(1,true,'1','on','yes');
		$boolfalse = array(0,false,'0','off','no');
		
		$directives = isset($dependencies['directives']) ? $dependencies['directives'] : array();
		/// Translator and service manager have not been initialized yet, we cannot use _t here
		/// expects: array('type' => <options|boolean|string>, 'required' => <array value|boolean value|string value>))
		foreach ($directives->toArray() as $directive => $params) {
			$required = isset($params['required']) ? $params['required'] : null;
			if (is_null($required)) {
				continue;
			}

			$ex = null;
			switch (isset($params['type']) ? $params['type'] : '') {
				case 'options':
					if (! in_array(ini_get($directive), $required)) {
						$ex = new DependencyException(vsprintf('Dependency failure: %s must be of (%s), \'%s\' found', array($directive, implode(',', $required), strval($directiveValue))), DependencyException::CODE_DIRECTIVE);
					}
					break;
				case 'boolean':
					if (is_string($required)) {
						$required = strtolower($required);
					}
					
					if (in_array($required, $booltrue)) {
						$values = $booltrue;
					} else {
						$values = $boolfalse;
					}
					
					$directiveValue = ini_get($directive);
					if (is_string($directiveValue)) {
						$directiveValue = strtolower($directiveValue);
					}

					if (! in_array($directiveValue, $values)) {
						$ex = new DependencyException(vsprintf('Dependency failure: %s must be \'%s\', \'%s\' (or equivalent) found', array($directive, intval($required), intval($directiveValue))), DependencyException::CODE_DIRECTIVE);
					}
					break;
				case 'string':
				default:
					$directiveValue = ini_get($directive);
					if ($directiveValue != $required) {
						$ex = new DependencyException(vsprintf('Dependency failure: %s must be \'%s\', \'%s\' found', array($directive, strval($required), strval($directiveValue))), DependencyException::CODE_DIRECTIVE);
					}
			}
			
			if ($ex instanceof DependencyException) {
				$ex->setContext($directive);
				$e->setParam('dbConnected', false);
				throw $ex;
			}
		}
	
		$extensions = isset($dependencies['extensions']) ? $dependencies['extensions'] : array();
		foreach ($extensions as $extension => $required) {
			if (isset($required['clusteronly']) && $required['clusteronly'] && (! self::isCluster())) {
				/// if the requirement is relevant to cluster only and we are NOT in a cluster
				continue;
			}
			
			if (! extension_loaded($extension)) {
				$ex = new DependencyException(vsprintf('Dependency failure: %s extension must be loaded', array($extension)), DependencyException::CODE_EXTENSION);
				$ex->setContext($extension);
				$e->setParam('dbConnected', false);
				throw $ex;
			}
		}
		
	}
	
	/*
	 * Check if the post request is CSRF valid, if not, throwing error.
	 */
	public function validateCSRF(MvcEvent $e) {
		$routeMatch = $e->getRouteMatch();
		$request = $e->getRequest();
		
		
		if(    ($e->getParam('webapi') && $routeMatch->getParam('controller') == 'DevBarWebApi' && ! $request->isPost())
			|| ($routeMatch->getParam('controller') == 'Login' && $routeMatch->getParam('action') == 'index')
			|| ! $request->isPost()) {
			//Z-Ray as another handling, Login should not pass CSRF validation.
			return true;
		}
		
		
		
		if(!$this->isValidCSRF($request)){
			Log::err("Invalid Request: CSRF Token mismatch");
			throw new Exception('Invalid Request');
		}
	}
	
	/*
	 * Check if we have to add iframe security policy headers
	 */
	public function iframeSecurityHeaders(MvcEvent $e) {
		$routeMatch = $e->getRouteMatch();
		$request = $e->getRequest();
		
		if ($routeMatch->getParam('controller') == 'DevBar' && $routeMatch->getParam('action') == 'iframe') {
			return true;
		}
		
		$headers = $e->getResponse()->getHeaders(); 
		//$headers->addHeaderLine("Content-Security-Policy: frame-ancestors 'self'");
		$headers->addHeaderLine("Content-Security-Policy: frame-src http:");
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\BootstrapListenerInterface::onBootstrap()
	 */
	public function onBootstrap(EventInterface $e) {
		$this->application = $e->getApplication();
		$this->application->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'validateCSRF'));
		$this->application->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'iframeSecurityHeaders'));
		$this->serviceManager = $this->application->getServiceManager();
		self::$staticManager = $this->serviceManager;
		
		$eventsManager = $this->application->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventsManager);
		
		
		$serviceManager = $this->serviceManager;
		
		$initializers = array(
				function ($instance) use ($serviceManager) {
					if ($instance instanceof \Users\IdentityAwareInterface) {
						$identity = $serviceManager->create('Users\Identity');
						$instance->setIdentity($identity);
					}
				},
				function ($instance) use ($serviceManager) {
					if (method_exists($instance, 'setAuthService')) {
						$instance->setAuthService($serviceManager->get('Zend\Authentication\AuthenticationService'));
					}
				},
				function ($instance) use ($serviceManager) {
					if ($instance instanceof \Configuration\License\LicenseAwareInterface || method_exists($instance, 'setLicense')) {
						$license = $serviceManager->get('Configuration\License\ZemUtilsWrapper')->getLicenseInfo();
						$instance->setLicense($license);
					}
				},
				function ($instance) use ($serviceManager) {
					if ($instance instanceof AclQuerierInterface) {
						$instance->setAcl($serviceManager->get('ZendServerAcl'));
					}
				},
				function ($instance) use ($serviceManager) {
					if ($instance instanceof TasksMapperAwareInterface) {
						$instance->setTasksMapper($serviceManager->get('Zsd\Db\TasksMapper'));
					}
				},
				function ($instance) use ($serviceManager) {
					if ($instance instanceof EditionAwareInterface) {
						$instance->setEdition(new Edition());
					}
				},
				function ($instance) use ($serviceManager) {
					if ($instance instanceof ConfigAwareInterface) {
						$namespaces = $instance->getAwareNamespace();
						$config = Module::config(current($namespaces));
						$instance->setConfig($config);
					}
				},
				function ($instance) use ($serviceManager) {
					if ($instance instanceof AdapterAwareInterface) {
						$dbName = $instance->getAdapterDb();
						$instance->setDbAdapter($serviceManager->get($dbName));
					}
				},
				function ($instance) use ($serviceManager) {
					if ($instance instanceof ServersAwareInterface) {
						$instance->setServersMapper($serviceManager->get('Servers\Db\Mapper'));
					}
				},
				function($instance) use ($serviceManager) {
					if ($instance instanceof InjectAuditMessageInterface) {
						$instance->setAuditMessage($serviceManager->get('Audit\Controller\Plugin\AuditMessage'));
					}
				},
				function($instance) use ($serviceManager) {
					if ($instance instanceof AbstractDevBarProducer) {
						$directivesMapper = $serviceManager->get('Configuration\MapperDirectives');
						$instance->setDirectivesMapper($directivesMapper);
					}
				},
		);

		$serviceLocators = array(
			$this->serviceManager,
			$serviceManager->get('ControllerLoader'),
			$serviceManager->get('ControllerPluginManager'),
			$serviceManager->get('ViewHelperManager'),
		);
		
		foreach ($serviceLocators as $serviceLocator) {
			foreach ($initializers as $initializer) {
				$serviceLocator->addInitializer($initializer);
			}
		}
		
		
		// Connector is configuration aware!
		$db = $this->serviceManager()->get('Application\Db\AbstractFactoryConnector');
		$this->serviceManager()->addAbstractFactory($db);
		/// clear the bootstrapCompleted flag if database is to be rebuilt
		$db->getEventManager()->attach('missingMetadata', function() use ($e) {
			$e->setParam('bootstrapCompleted', false);
		});
		
		$app = $this->application;/* @var $app \Zend\Mvc\Application */
		
		$e->setParam('httpRequest', (! Console::isConsole()));
		$e->setParam('bootstrapCompleted', $this->isBootstrapCompleted());
		$e->setParam('redirectOnError', true);
		
		if ($e->getParam('httpRequest')) {
			$baseUrl = static::config('baseUrl');
			$app->getRequest()->setBaseUrl($baseUrl);
		} else {
			$e->setParam('useSessionControl', false);
			$e->setParam('useAclControl', false);
		}
		
		$eventsManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'checkBootstrapCompleted'), -1000);
		$eventsManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'initializeSessionControl'), -1000);
		$eventsManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'checkLicenseValid'), -1000);
		
		$eventsManager->attach(MvcEvent::EVENT_RENDER, array($this, 'initializeView'));
		$eventsManager->attach(MvcEvent::EVENT_RENDER, array($this, 'initializeViewLayout'));
		$eventsManager->attach(MvcEvent::EVENT_RENDER, array($this, 'setRouteParams'));
		
		$eventsManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'initializeLimitedACL'), -2000);
		
		$eventsManager->attach(MvcEvent::EVENT_FINISH, array($this, 'compressOutput'), 100);
		
		$eventsManager->attach('AuditMessage', array($this, 'auditMessage'), -2000);
		
		try {
			$this->initializeLog($e);
			$this->checkDependencies($e);
			$this->initializeSessionManager($e);
			$this->initializeDbConnection($e);
			$this->initializeACL($e);
			$this->detectTimezone();
			$this->initializeLicense($e);
			$this->applyLicenseToAcl($e);
		} catch (\Exception $ex) {
			Log::err($ex);
			$this->initializeLimitedACL($e);
			$events = $this->application->getEventManager();
			$error = $e;
			$error->setError(\Zend\Mvc\Application::ERROR_EXCEPTION);
			if ($ex instanceof \PDOException) {
				$error->setParam('exception', new Exception(_t('Zend Server database error: %s', array($ex->getMessage())), Exception::DATABASE_CONNECTION, $ex));
				$error->setParam('dbConnected', false);
			} elseif ($ex instanceof Exception && $ex->getCode() == Exception::DATABASE_CONNECTION) {
				$error->setParam('exception', $ex);
			} else {
				$error->setParam('exception', new Exception(_t('Zend Server failed during initialization: %s', array($ex->getMessage())), null, $ex));
			}
			$results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $error);
			$results->setStopped(true);
			$e->setResult($results);

		}
		if ((! self::config('authentication', 'simple'))
			 && (! $this->getLocator()->get('ZendServerAcl')->isAllowed('service:Authentication', 'extended'))) {
			/// we require extended authentication but are not authorized for it
			Log::notice('Extended authentication override, not allowed');
			self::config('authentication')->merge(new Config(array('simple' => true)));
		}
		
		$serviceManager->get('translator')->getEventManager()->attach(Translator::EVENT_MISSING_TRANSLATION, function(\Zend\EventManager\Event $event){
			Log::debug("Translator: Missing translation string for `{$event->getParam('message')}`");
		});
		
		
	}
	
	public function compressOutput($e) {
		if (Module::config('debugMode', 'debugModeEnabled') || $e->getParam('do-not-compress', false)) {
			return;
		}
		
		if (@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
			$response = $e->getResponse();
			$content = $response->getBody();
			
			header('Content-Encoding: gzip');
			$content = gzencode($content, 9);
			
			$response->setContent($content);
		}
	}
	
	public function initializeDbConnection(MvcEvent $e) {
		$e->setParam('dbConnected', false);
		$adapter = $this->serviceManager()->get(Connector::DB_CONTEXT_GUI);
		try {
			$adapter->driver->getConnection()->connect();
		} catch (\Exception $ex) {
			throw new Exception('Database connection failed', Exception::DATABASE_CONNECTION, $ex);
		}
		$e->setParam('dbConnected', true);
	}
	
	/**
	 * @brief Get the name of the session cookie
	 * @return  
	 */
	protected function getSessionCookieName() {
		$sessionId = self::config('sessionControl', 'sessionId');
		if (! preg_match('/^[[:alnum:]]+$/i', $sessionId)) {
			Log::warn('Invalid zend_gui.sessionId detected, only alpha numeric characters are accepted');
			$sessionId = 'ZS6SESSID';
		}
		
		// add 'S' to the name when connecting using HTTPS
		if (self::$isHTTPS) $sessionId.= 'S';
		
		return $sessionId;
	}
	
	public function initializeSessionManager(MvcEvent $e) {
		Log::debug(__METHOD__);
		if ($e->getParam('useSessionControl',true)) {
			$serviceManager = $this->serviceManager();
			$sessionManager = $serviceManager->get('Zend\Session\SessionManager');
			
			if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
				//HTTPS, Secure only set to true
				self::$isHTTPS = true;
				session_set_cookie_params(null, '/', null, true, true);
			} else {
				session_set_cookie_params(null, '/', null, null, true);
			}
			
			$sessionManager->setName($this->getSessionCookieName());
			$this->detectRemoteAddr($e);
		}
	}
	
	public function checkBootstrapCompleted(MvcEvent $e) {
		Log::debug(__METHOD__);
		if (! $e->getParam('bootstrapCompleted')) {
			$routeMatch = $e->getRouteMatch(); /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
			if ($routeMatch->getParam('bootstrap', false)) {
				/// need to provide an identity for webapi requests
				$authService = $e->getApplication()->getServiceManager()->get('Zend\Authentication\AuthenticationService');
				$identity = new Identity('Unknown');
				$identity->setRole(appModule::ACL_ROLE_BOOTSTRAP);
				$storage = new NonPersistent();
				$storage->write($identity);
				$authService->setStorage($storage);
			} elseif ($e->getParam('redirectOnError')) {
				$routeMatch->setParam('controller', 'Bootstrap');
				Log::debug('Bootstrap needed, router result is overriden');
			}
			
			$e->setParam('useSessionControl', false);
		}
	}
	
	public function checkLicenseValid(MvcEvent $e) {
		Log::debug(__METHOD__);
		$routeMatch = $e->getRouteMatch(); /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
		/// if bootstrap is complete, the user has an identity and the route is specified NOT to work if license is expired
		if ($this->isBootstrapCompleted() && $routeMatch->getParam('requireIdentity', true)
			&& $e->getParam('hasIdentity', true) && (! $e->getRouteMatch()->getParam('licenseexpired', false))) {
			/// if the license is not valid and we should redirect if errors are found
			$licenseInfo = $e->getParam('licenseInfo'); /* @var $licenseInfo License */
			if ((! $e->getParam('licenseValid') || $licenseInfo->getEdition() == License::EDITION_EMPTY) && $e->getParam('redirectOnError')) {
				/// if the license signature is not valid or the license is empty or it is expired
				if (! $licenseInfo->isSignatureValid() || $licenseInfo->getEdition() == License::EDITION_EMPTY || $licenseInfo->isLicenseExpired()) {
					$routeMatch->setParam('controller', 'Expired');
					Log::warn('License is expired or invalid, redirect to expired interface');
				}
			}
		}
	}
		
	public function initializeDependencies(ManagerEvent $e)
	{
		// we want to override other session handlers here...
		ini_set("session.save_handler", "files");
		ini_set("session.save_path", getCfgVar('zend.temp_dir'));
		// we want to overwrite the display_errors
		ini_set('display_errors', false);
		// preserve backtrack/recursion limit values
		ini_set('pcre.backtrack_limit', 1000000);
		ini_set('pcre.recursion_limit', 100000);
		
		date_default_timezone_set(@date_default_timezone_get());
	}
	
	public function initializeConfig(ManagerEvent $e)
	{
		static::$config = $config = $e->getConfigListener()->getMergedConfig();
	}    

	public function initializeDebugMode(ManagerEvent $e) {
		// debugModeEnabled = true
		if (static::config('debugMode', 'debugModeEnabled')) {
			// re-enable monitor events
			if (function_exists('zend_monitor_event_reporting')) {
				zend_monitor_event_reporting(6143);
			}
		} else { // debugModeEnabled = false
			if (function_exists('zend_codetracing_options')) {
				zend_codetracing_options(0);
			}
			 
			if (function_exists('zend_disable_statistics')) {
				zend_disable_statistics();
			}
			 
			if (function_exists('zend_urlinsight_disable')) {
				zend_urlinsight_disable();
			}
		}
	}

	/**
	 * @param MvcEvent $e
	 */
	public function initializeLog(MvcEvent $e) {
		$azure = isAzureEnv();
		$standaloneZray = isZrayStandaloneEnv();
		if ($azure || $standaloneZray) {
			$writer = new \Zend\Log\Writer\Stream(FS::createPath(getCfgVar('zend.log_dir'), 'zend_server_ui.log'), 'a+');
		} else {
			$writer = new \Zend\Log\Writer\Stream(FS::createPath(dirname(ini_get('error_log')), 'zend_server_ui.log'), 'a+');
		}
		
		if (static::config('debugMode', 'debugModeEnabled')) {
			$formatter = new Simple('%timestamp% %priorityName% (%priority%) [%uri%]: %message% %extra%');
			if ($e->getParam('httpRequest')) {
				$uri = new Uri($e->getRequest()->getServer()->get('REQUEST_URI'));
				$formatter->setUri(str_replace(static::config('baseUrl'), '', $uri->getPath()));
			} else {
				$request = $e->getRequest(); /* @var $request \Zend\Console\Request */
				$routeName = current($request->getParams()->toArray());
				$formatter->setUri($routeName);
			}
			$writer->setFormatter($formatter);
		}
		$logger = new Logger();
		$logger->addWriter($writer);
		
		Logger::registerErrorHandler($logger);
		Logger::registerExceptionHandler($logger);

		if (is_null(Log::getLogger())) {
			Log::init($logger, self::config('logging', 'logVerbosity'));
		}
		Log::debug('log initialized');
	}
	
	public function initializeLicense(MvcEvent $e) {
		Log::debug(__METHOD__);
		$licenseMapper = $this->serviceManager->get('Configuration\License\ZemUtilsWrapper'); /* @var $licenseMapper \Configuration\License\ZemUtilsWrapper */
		$licenseInfo = $licenseMapper->getLicenseInfo();
		$e->setParam('licenseValid', $licenseInfo->isLicenseOk());
		$e->setParam('licenseInfo', $licenseInfo);
	}
	
	public function initializeSessionControl(ManagerEvent $e) {
		Log::debug(__METHOD__);
		$routeMatch = $e->getRouteMatch(); /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
		if (self::config('sessionControl', 'sessionControlEnabled') && $e->getParam('useSessionControl', true)
				&& $routeMatch->getParam('requireIdentity', true)
			) {
			
			$app = $e->getApplication();/* @var $app \Zend\Mvc\Application */
			$authService = $e->getApplication()->getServiceManager()->get('Zend\Authentication\AuthenticationService');
			if ($authService->hasIdentity() && $authService->getIdentity()->isLoggedIn()) {
				$e->setParam('hasIdentity', true);
				return true;
			}
			
			$e->setParam('hasIdentity', false);
			
			$routeMatch->setParam('collectCurrentUrl', true);
			$routeMatch->setParam('action', 'logout');
			$routeMatch->setParam('controller', 'Login');
			Log::notice('Session has no identity, redirecting to Login');
		}
	}
	
	public function initializeView(ManagerEvent $e)
	{
		$app = $this->application;/* @var $app \Zend\Mvc\Application */
		$locator = $this->getLocator();
		
		if ($e->getParam('httpRequest')) {
			$baseUrl = $app->getRequest()->getBaseUrl();
	
			$role = $this->getUserRole($app->getMvcEvent());
		
			/// add form view helpers
			$renderer = $locator->get ( 'ViewManager' )->getRenderer(); /* @var $renderer \ZendServer\View\Renderer\PhpRenderer */
	
			if ($e->getParam('initViewHelpers', true)) {
				
				$renderer->plugin ( 'url' )->setRouter ( $app->getMvcEvent()->getRouter () );
				$renderer->doctype ()->setDoctype ( 'HTML5' );
				$renderer->plugin ( 'basePath' )->setBasePath ( $baseUrl );
		
				$view = $renderer; /* @var $view \ZendServer\View\Renderer\PhpRenderer */
					
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/style.css' );
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/glyphicons.css' );
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/js/simplemodal/assets/css/simplemodal.css' );
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/simplemodal.css' );
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/wizard.css' );
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/configuration.css' );
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/toast.css' );
				$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/spinner.css' );
				
				$azure = isAzureEnv();
				if ($azure) {
					$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/azure.css' );
				}
				
				$standaloneZray = isZrayStandaloneEnv();
				if ($standaloneZray) {
					$view->plugin ( 'headLink' )->appendStylesheet ( $baseUrl . '/css/zray-standalone.css' );
				}
				
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/mootools.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/mootools-more.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/zswebapi.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/general.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/placeholder.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/notificationCenter.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/persistantHeaders.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/ellipsis.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/floatingtips.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/simplemodal/simple-modal.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/FormWizard.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/toast.js' );
				$view->plugin ( 'headScript' )->appendFile ( $baseUrl . '/js/deploymentLibraries/updates.js' );
					
				$view->headLink ( array (
						'rel' => 'shortcut icon',
						'type' => 'image/x-icon',
						'href' => $baseUrl . '/images/favicon.ico'
				) );
					
				$view->doctype ()->setDoctype ( 'HTML5' );
				$view->plugin ( 'basePath' )->setBasePath ( $baseUrl );
				Log::debug('Init helpers');
			}
			
			$renderer->setAcl($this->serviceManager->get('ZendServerAcl'));
			Log::debug('View initialized');
		}
		
	}

	public function initializeViewLayout(MvcEvent $e) {
		if ($e->isError()) {
			try {
				if ($e->getParam('dbConnected', true)) {
					$licenseType = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseType();
					$licenseEvaluation = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseEvaluation();
					$daysToExpired = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseExpirationDaysNum();
					$licenseNeverExpires =  $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseInfo()->isNeverExpires();
					$licenseIsOk = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseInfo()->isLicenseOk();
				} else {
					throw new Exception('No database connection', Exception::ASSERT);
				}
			} catch (\Exception $ex) {
				$licenseType = 'Unknown';
				$licenseEvaluation = false;
				$licenseNeverExpires = false;
				$daysToExpired = false;
				$licenseIsOk = false;
			}
			$app = $e->getApplication();
			$acl = $this->getLocator()->get('ZendServerAcl');
	
			$e->getViewModel()->setVariables(array('role' => ''));
			$e->getViewModel()->setVariables(array('userRole' => ''));
			$e->getViewModel()->setVariables(array('timezoneOffset' => 0));
			$role = $this->getUserRole($e);
			$manager = new Manager();
			$viewModel = $e->getViewModel()->setVariables(array(
					'notificationCenter' => false,
					'isAllowedToRestart' => false,
					'isAllowedToDismiss' => false,
					'acl' => $acl,
					'feedbackUrl' => self::config('feedback', 'email'),
					'licenseType'	=> $licenseType,
					'licenseEvaluation'	=> $licenseEvaluation,
					'logoutTimeout' => self::config('logout', 'timeout'),
					'storeApiUrl' => self::config('plugins', 'storeApiUrl') . 'update.php',
					'serverInfo' => $this->getLocator()->get('ControllerPluginManager')->get('ServerInfo')->get(),
					'sessionId' => self::config('sessionControl', 'sessionId') . (self::$isHTTPS ? 'S' : ''),
					'isI5' => ($manager->getOsType() == Manager::OS_TYPE_IBMI),
					'licenseNeverExpires'   => $licenseNeverExpires,
					'daysToExpired'   => $daysToExpired,
					'licenseIsOk'   => $licenseIsOk,
					'role' => $role,
					'azure' => isAzureEnv(),
					'zrayStandalone' => isZrayStandaloneEnv(),
			));
		} else {
			$app = $e->getApplication();
			$role = $this->getUserRole($e);
			
			$e->getViewModel()->setVariables(array('userRole' => $role));
					
			$this->detectTimezone();
			$tz = @date_default_timezone_get();
			$tz = $this->getTimezoneOffset($tz);
			
			$e->getViewModel()->setVariables(array('timezoneOffset' => $tz));
			
			$locator = $this->serviceManager;
			$acl = $locator->get('ZendServerAcl'); /* @var $acl \ZendServer\Permissions\AclQuery */
				$isAllowedToRestart = $acl->isAllowed('route:ServersWebAPI', 'restartPhp');
				$isAllowedToDismiss = $acl->isAllowed('route:NotificationsWebApi', 'updateNotification');
	
			$licenseType = '';
			try {
				$licenseType = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseType();
				$licenseEvaluation = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseEvaluation();
				$daysToExpired = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseExpirationDaysNum();
				$licenseNeverExpires =  $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseInfo()->isNeverExpires();
				$licenseIsOk = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseInfo()->isLicenseOk();
			} catch (Exception $ex) {
				Log::notice('Unknown or missing license detected');
			}
			
			$manager = new Manager();
			$viewModel = $e->getViewModel()->setVariables(array(
					'notificationCenter' => true,
					'notificationCenterLockedRestart' => self::config('notifications', 'zend_gui', 'lockUiOnRestart'),
					'role' => $role, 
					'isAllowedToRestart' => $isAllowedToRestart,
					'isAllowedToDismiss' => $isAllowedToDismiss, 
					'licenseNeverExpires'   => $licenseNeverExpires,
					'daysToExpired'   => $daysToExpired,
					'licenseIsOk'   => $licenseIsOk,
					'acl' => $acl,
					'feedbackUrl' => self::config('feedback', 'email'),
					'logoutTimeout' => self::config('logout', 'timeout'),
					'storeApiUrl' => self::config('plugins', 'storeApiUrl') . 'update.php',
					'serverInfo' => $this->getLocator()->get('ControllerPluginManager')->get('ServerInfo')->get(),
					'sessionId' => self::config('sessionControl', 'sessionId') . (self::$isHTTPS ? 'S' : ''),
					'licenseType'	=> $licenseType,
					'licenseEvaluation'	=> $licenseEvaluation,
					'isI5' => ($manager->getOsType() == Manager::OS_TYPE_IBMI),
					'azure' => isAzureEnv(),
					'zrayStandalone' => isZrayStandaloneEnv(),
				));
			
			
		}
		Log::debug(__METHOD__.':'.($e->isError() ? 'error' : ''));
		
	}
	
	private function getTimezoneOffset($tz) {
		$dt = new \DateTime(null, new \DateTimeZone($tz));
		return $dt->getOffset()/60/60;
	}
	
	/**
	 *
	 * @param MvcEvent $e
	 */
	public function setRouteParams(MvcEvent $e) {
		$routeMatch = $e->getRouteMatch(); /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
		if ($e->getParam('httpRequest',true)) {
			if($routeMatch) {
				$variables = array (	'controller'	=> $routeMatch->getParam('controller', ''),
						'action' 		=> $routeMatch->getParam('action', ''),
						'isAjaxRequest' => $e->getRequest()->isXmlHttpRequest()
					);
				$e->getViewModel()->setVariables($variables + (array) $e->getViewModel()->getVariables()); // otherwise, previous variables are removed    
			} else {
				Log::debug($e->getRequest()->getServer('REQUEST_URI'));
			}
		}
	}
		
	public function initializeLimitedACL(ManagerEvent $e) {
		Log::debug(__METHOD__);
		$acl = new Acl();
		if (! $this->serviceManager->has('ZendServerLicenseAcl')) {
			$this->serviceManager->setService('ZendServerIdentityAcl', $acl);
			$this->serviceManager->setService('ZendServerLicenseAcl', $acl);
		}
		
		$queryAcl = new AclQuery();
		$queryAcl->setEnabled(false);
		$queryAcl->setAcl($acl);
		$queryAcl->setEditionAcl($acl);
		$this->serviceManager->setAllowOverride(true);
		$acl = $this->serviceManager->setService('ZendServerAcl', $queryAcl);
	}
	
	public function applyLicenseToAcl(ManagerEvent $e) {
		$licenseAcl = $this->serviceManager->get('ZendServerLicenseAcl');
		
		$license = $this->serviceManager->get('Configuration\License\ZemUtilsWrapper')->getLicenseInfo(); /* @var $license License */
		if ($license->isCloudLicense()) { /// cloud license may not be viewed nor changed
			$licenseAcl->deny('edition:'.License::EDITION_ENTERPRISE,'route:License');
		}
	}
	
	public function initializeACL(ManagerEvent $e) {
		$app = $e->getParam('application'); /* @var $app \Zend\Mvc\Application */
		
		$app->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'overrideAclRole'), 10000);
		$app->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'allow'), 10000);
		
		
		$identityAcl = new Acl();
		
		$licenseAcl = new Acl();
		$licenseAcl->addRole('edition:'.License::EDITION_ENTERPRISE);
		$licenseAcl->addRole('edition:'.License::EDITION_DEVELOPER_ENTERPRISE, 'edition:'.License::EDITION_ENTERPRISE);
		$licenseAcl->addRole('edition:'.License::EDITION_PROFESSIONAL, 'edition:'.License::EDITION_DEVELOPER_ENTERPRISE);
		$licenseAcl->addRole('edition:'.License::EDITION_BASIC, 'edition:'.License::EDITION_PROFESSIONAL);
		$licenseAcl->addRole('edition:'.License::EDITION_DEVELOPER, 'edition:'.License::EDITION_BASIC);
		$licenseAcl->addRole('edition:'.License::EDITION_FREE, 'edition:'.License::EDITION_DEVELOPER);
		$licenseAcl->addRole('edition:'.License::EDITION_EMPTY);

		$aclMapper = $this->serviceManager->get('\Acl\Db\Mapper'); /* @var $aclMapper \Acl\Db\Mapper */
		$roles = $aclMapper->getRoles();
		if ($roles) {
			foreach ($roles as $role) {
				/* @var $role \Acl\Role */
				$identityAcl->addRole($role->getName(), $role->getParentName());
			}
		}
		
		$resources = $aclMapper->getResources();
		if ($resources) {
			foreach ($resources as $resource) {
				/* @var $resource \Acl\Resource */
				$identityAcl->addResource($resource->getName());
				$licenseAcl->addResource($resource->getName());
			}
		}
		
		$privileges = $aclMapper->getPrivileges();

		if ($privileges) {
			foreach ($privileges as $privilege) {
				/* @var $privilege \Acl\Privilege */
				$identityAcl->allow($privilege->getRoleName(), $privilege->getResourceName(), $privilege->getAllowedActions());
			}
		}
		
		// add plugins acl permissions
		$config = $this->getLocator()->get('Config');
		if (isset($config['acl']['route'])) {
			foreach ($config['acl']['route'] as $name => $permissions) {
				$identityAcl->addResource('route:' . $name);
				$licenseAcl->addResource('route:' . $name);
				
				if (! isset($permissions['allowedMethods']) || empty($permissions['allowedMethods']) || is_null($permissions['allowedMethods'])) {
					$permissions['allowedMethods'] = array();
				}
				$identityAcl->allow($permissions['role'], 'route:' . $name, $permissions['allowedMethods']);
			}
		}
		
		/// additional editions acl initialization
		/// enterprise may do anything
		$licenseAcl->allow('edition:'.License::EDITION_ENTERPRISE);
		
		$licenseAclConfig = self::config('license', 'acl');
		
		foreach ($licenseAclConfig as $role => $resources) {
			$role = "edition:{$role}";
			foreach ($resources as $resource => $privs) {
				
				if (! $licenseAcl->hasResource($resource)) {
					$licenseAcl->addResource($resource);
				}
				
				if (is_array($privs) || $privs instanceof \Traversable) {
					foreach ($privs as $privilege => $allow) {
						// special configuration case - blanket resource privilege
						// this can be used to allow all privs and then deny a single one
						// @example 'route:UsersWebAPI' => array(false, 'setPassword' => true),
						// Blocks all users actions except for setPassword
						if($privilege === 0) {
							if ($allow) {
								$licenseAcl->allow($role, $resource);
							} else {
								$licenseAcl->deny($role, $resource);
							}
						} else {
							if ($allow) {
								$licenseAcl->allow($role, $resource, $privilege);
							} else {
								$licenseAcl->deny($role, $resource, $privilege);
							}
						}
					}
				} else {
					if ($privs) {
						$licenseAcl->allow($role, $resource);
					} else {
						$licenseAcl->deny($role, $resource);
					}
				}
			}
		}
		
		
		Log::debug('acl initialized');
		if (! self::config('acl', 'aclEnabled')) {
			$identityAcl->allow();
			Log::debug('User role acl initialized with allow-all');
		}

		$this->serviceManager->setService('ZendServerIdentityAcl', $identityAcl);
		$this->serviceManager->setService('ZendServerLicenseAcl', $licenseAcl);
	}


	/**
	 * @param MvcEvent $e
	 */
	public function overrideAclRole(MvcEvent $e) {
		Log::debug(__METHOD__);
		$acl = $this->getLocator()->get('ZendServerAcl'); /* @var $acl AclQuery */
		if (! $e->getParam('bootstrapCompleted')) {
			$acl->setOverrideRole(self::ACL_ROLE_BOOTSTRAP);
		}
	}
	
	public function allow(MvcEvent $e) {
//         $applicationConfig = $this->getLocator()->get('applicationconfig');
//         echo '<pre>';
//         print_r($applicationConfig['plugins']);exit;
		
//         $manager = $this->getLocator()->get('ModuleManager');
//         $modules = $manager->getLoadedModules();
//         print_r($modules);exit;
		
//         $config = $this->getLocator()->get('Config');
//         print_r($config['acl']);exit;
		
		Log::debug(__METHOD__);
		
		if (! $e->getParam('useAclControl',true)) {
			return ;
		}
		
		$routeMatch = $e->getRouteMatch(); /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */

		/// retrieve controller name - either a clean one from some other process or the raw name
		$controller = $routeMatch->getParam('clean-controller-name', $routeMatch->getParam('controller', ''));
		$action = $routeMatch->getParam('action', '');
		
		$resourceRoute = "route:$controller";
		$role = $this->getUserRole($e);

		$acl = $this->getLocator()->get('ZendServerAcl');
		$license = $e->getParam('licenseInfo');
		try {
			try {
				if (! $acl->hasResource($resourceRoute)) {
					throw new InvalidControllerException(_t('The requested resource is missing: %s', array($resourceRoute)));
				}
				
				if (! $acl->isAllowedEdition($resourceRoute, $action)) {
					
					$e->getApplication()->getEventManager()->trigger(
							'AuditMessage', $e->getApplication(), 
							array('type' => \Audit\Db\Mapper::AUDIT_GUI_AUTHORIZATION,
									'progress' => ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, 
									'extraData' => array('edition' => $license->getEdition(), 'resource' => "route:$controller", 'action' => $action)));
					
					throw new Exception(_t('Your license edition (%s) does not allow access to: %s::%s', array($license->getEdition(), $controller, $action)), Exception::ACL_EDITION_PERMISSION_DENIED);
				}
				
				if (! $acl->isAllowedIdentity($resourceRoute, $action)) {
					
					$e->getApplication()->getEventManager()->trigger(
							'AuditMessage', $e->getApplication(), 
							array('type' => \Audit\Db\Mapper::AUDIT_GUI_AUTHORIZATION,
									'progress' => ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, 
									'extraData' => array('role' => $role, 'resource' => "route:$controller", 'action' => $action)));
					
					throw new Exception(_t('Insufficient permissions to access this action: %s::%s', array($controller, $action)), Exception::ACL_PERMISSION_DENIED);
				}
				
			} catch (\Zend\Permissions\Acl\Exception\InvalidArgumentException $ex) {
				$newEvent = $this->application->getMvcEvent();
				$newEvent->setError(\Zend\Mvc\Application::ERROR_EXCEPTION);
				$newEvent->setParam('exception', $ex);
				$this->application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
			} catch (Exception $ex) {
				Log::warn("Role {$acl->getIdentity()->getRole()} or edition {$license->getEdition()} failed ACL check for $controller:$action");
				throw $ex;
			} catch (InvalidControllerException $ex) {
				Log::err("Controller {$controller} not found, unknown controller");
				throw $ex;
			} catch (\Exception $ex) {
				Log::err("Authorization process failed {$ex->getMessage()}");
				Log::err($ex);
				throw new Exception(_t("ACL configuration error: {$ex->getMessage()}"), Exception::ERROR, $ex);
			}
		} catch (InvalidControllerException $ex) {
			$newEvent = $this->application->getMvcEvent();
			$newEvent->setError(\Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND);
			$newEvent->setParam('exception', $ex);
			$this->application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
		} catch (\Exception $ex) {
			$newEvent = $this->application->getMvcEvent();
			$newEvent->setError(\Zend\Mvc\Application::ERROR_EXCEPTION);
			$newEvent->setParam('exception', $ex);
			$this->application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
		}
	}

	/**
	 * @param Event $e
	 */
	public function auditMessage(Event $e) {
		if (isZrayStandaloneEnv()) {
			return true;
		}
		
		$application = $e->getTarget();
		$mvcEvent = $application->getMvcEvent();
		$type = $e->getParam('type');
		$progress = $e->getParam('progress', ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
		$extraData = $e->getParam('extraData', array());
		
		//$type, $progress, $e, $extraData = array()
		$auditMessage = $application->getServiceManager()->get('Audit\Controller\Plugin\AuditMessage');
		$authService = $this->getLocator()->get('Zend\Authentication\AuthenticationService');
		$identity = $authService->getIdentity();
		
		$auditMessage->setIdentity($identity ?: new Identity(_t('Unknown')));
		$auditMessage->setRemoteAddr($mvcEvent->getRequest()->getServer('REMOTE_ADDR'));
		$auditMessage->setEvent($mvcEvent);
		$message = $auditMessage($type, ProgressMapper::AUDIT_NO_PROGRESS, array($extraData));
		
		if (!is_numeric($message->getAuditId())) {
			Log::err("Audit message was not created properly - early bootstrap phazes?");
			return false;
		}
		
		$auditMessageProgress = $application->getServiceManager()->get('Audit\Controller\Plugin\AuditMessageProgress');
		$auditMessageProgress($progress, array(), $message->getAuditId());
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
	public static function setConfig($config) { // TODO - added this function temporarily for mock
		static::$config = $config;
	}    

	/* 
	 * Generate CSRF Access Token and attach it to user's session 
	 */
	public static function generateCSRF() {
		if (function_exists("hash_algos") and in_array("sha512",hash_algos())) {
			$token=hash("sha512",mt_rand(0,mt_getrandmax()));
		} else {
			$token=' ';
			for ($i=0;$i<128;++$i) {
				$r=mt_rand(0,35);
				if ($r<26) {
					$c=chr(ord('a')+$r);
				} else { 
					$c=chr(ord('0')+$r-26);
				} 
				$token.=$c;
			}
		}
		
		$csrfContainer = new \Zend\Session\Container('zs_csrf');
		$csrfContainer->offsetSet('access_token', $token);
		
		return true;
	} 

	/*
	 * Check if request contain valid CSRF token
	 */
	public static function isValidCSRF($request) {
		$validToken = false;
		$csrfContainer = new \Zend\Session\Container('zs_csrf');
		$headers = $request->getHeaders();/* @var $headers \Zend\Http\Headers */		
		if ($headers->has('access_token')) {
			$validToken = ( $headers->get('Access-Token')->getFieldValue() == $csrfContainer->offsetGet('access_token') );
		} else {
			$validToken = ( $request->getPost('access_token') == $csrfContainer->offsetGet('access_token') );
		}
		
		return $validToken;
	}

	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						StandardAutoloader::LOAD_NS => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
								'Acl' => __DIR__ . '/src/Acl',
								'Expired' => __DIR__ . '/src/Expired',
								'Bootstrap' => __DIR__ . '/src/Bootstrap',
								'Email' => __DIR__ . '/src/Email',
								'Logs' => __DIR__ . '/src/Logs',
								'Messages' => __DIR__ . '/src/Messages',
								'GuiConfiguration' => __DIR__ . '/src/GuiConfiguration',
								'Notifications' => __DIR__ . '/src/Notifications',
								'Servers' => __DIR__ . '/src/Servers',
								'LibraryUpdates' => __DIR__ . '/src/LibraryUpdates',
								'Snapshots' => __DIR__ . '/src/Snapshots',
								'Users' => __DIR__ . '/src/Users',
								'Zsd' => __DIR__ . '/src/Zsd',
						),
				),
		);
	}


	// Protected function from here

	public static function isBootstrapCompleted() {
		return self::config('bootstrap', 'completed');
	}

	protected function getUserRole(MvcEvent $e) {
		try{
			$authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
			if (! $authService->hasIdentity()) {
				if (!$this->isBootstrapCompleted()) {
					return 'bootstrap';
				}
			
				return 'guest';
			}
		}catch(Exception $e){
			return 'guest';
		}
		
		$role = $authService->getIdentity()->getRole();
		return $role;
	}
		
	/**
	 * @return ServiceManager
	 */
	protected function getLocator() {
		return $this->serviceManager;
	}
		
	private function offsetToStr($offset) {

		$offset = (int) $offset;
		$offset = $offset / 3600;
		Log::debug("Timezone offset: $offset");
		
		$idsList = timezone_identifiers_list();
		foreach ($idsList as $id) {
			$dt = new \DateTime(null, new \DateTimeZone($id));
			$dtOffset = $dt->getOffset()/60/60;
			if ($dtOffset == $offset) {
				return $id;
			}
		}
		
		return null;
	}

	// Security issue: insecure session handling #ZSRV-15436
	private function detectRemoteAddr(MvcEvent $e) {
		 Log::debug(__METHOD__);
		static $sessionStorage = null;
		
		if (is_null($sessionStorage) || !($sessionStorage instanceof SessionStorage)) {
			$sessionStorage = new SessionStorage();
		}
	   
		if (!$sessionStorage->hasRemoteAddr()) {
			Log::notice("Set the Remote IP in the session: " . \Application\SessionStorage::getClientIp());
			$sessionStorage->setRemoteAddr();
		} else {
		   if ($sessionStorage->getRemoteAddr() != \Application\SessionStorage::getClientIp()) {
				Log::err("The Remote IP is not matching to the session saved IP");
				
				// clear session cookie
				$params = session_get_cookie_params();
				setcookie($this->getSessionCookieName(), '', time() - 1, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
				
				$config = $this->getConfig();
				$baseUrl = isset($config['baseUrl']) ? $config['baseUrl'] : '/ZendServer';
				$e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_RENDER, function(MvcEvent $evt) use ($baseUrl) {
					$evt->stopPropagation();
					$evt->getResponse()->setContent("Redirecting to {$baseUrl}");
					$evt->getResponse()->getHeaders()->addHeaderLine('Location', $baseUrl);
				}, 2001);
			}
		}
		
	}
	
	private function detectTimezone() {
		Log::debug(__METHOD__);
		static $sessionStorage = null;

		if (is_null($sessionStorage) || !($sessionStorage instanceof SessionStorage)) {
			$sessionStorage = new SessionStorage();
		}
		
		if (!$sessionStorage->hasTimezone()) {
			
			$phpTz = @date_default_timezone_get();
			$tz = @date_default_timezone_get();
			
			$dbConn = $this->serviceManager()->get(Connector::DB_CONTEXT_GUI); /* @var $dbConn Zend\Db\Adapter\Adapter */
			if ($dbConn->getDriver()->getConnection()->getResource()->getAttribute(\PDO::ATTR_DRIVER_NAME) !== 'mysql') { // sqlite
				Log::debug("Detecting single server timezone");
				$res = $dbConn->query('SELECT strftime(\'%s\', \'now\') - strftime(\'%s\', \'now\',  \'utc\') as offset', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
				if ($res) {
					foreach ($res as $row) {
						$offset = (string) $row['offset'];
						$tz = $this->offsetToStr($offset);
						break;     				
					}
				}
	
			} else {
				Log::debug("Detecting cluster timezone");
				$res = $dbConn->query('SELECT UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(UTC_TIMESTAMP()) as offset', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
				foreach ($res as $row) {
					$offset = (string) $row['offset'];
					$tz = $this->offsetToStr($offset); 
					break;
				}
			}

			if ($phpTz) {
				Log::debug("PHP detected timezone - $phpTz");
				Log::debug("PHP detected timezone - $tz");
				
				if ( ! in_array($phpTz, \DateTimeZone::listIdentifiers())) {
					Log::notice("Unknown timezone specified $phpTz. Auto-detected used instead, $tz");
					$phpTz = $tz;
				}
				$phpDt = new \DateTime(null, new \DateTimeZone($phpTz));
				$calculatedDt = new \DateTime(null, new \DateTimeZone($tz));
					
				if ($phpDt->getOffset() == $calculatedDt->getOffset()) {
					Log::debug("PHP detected timezone matches server offset");
					$tz = $phpTz;
				}    	
			} else if (!$tz) {
				Log::notice("Could not detect timezone. Reverting to PHP settings...");
				$tz = @date_default_timezone_get();
			}
			
			$sessionStorage->setTimezone($tz);
		}
	
		Log::debug("Timezone detected - " . $sessionStorage->getTimezone());
		date_default_timezone_set($sessionStorage->getTimezone());
	}

}
