<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/WebAPI for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace WebAPI;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;

use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use ZendServer\Exception;
use WebAPI\Exception as apiException;
use Zend\Mvc\MvcEvent;
use WebAPI\Authentication\Adapter\SignatureSimple;
use WebAPI\Authentication\Adapter\SignatureGroups;
use WebAPI\Db\Mapper;
use Application\Module as appModule;
use Zend\Http\PhpEnvironment\Request;
use ZendServer\Log\Log;
use WebAPI\View\Resolver\TemplatePathStack;
use Users\Identity;
use ZendServer\FS\FS;
use WebAPI\Authentication\Result;
use Audit\Db\ProgressMapper;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\EventManager\Event;
use Configuration\License\License;
use Zend\Authentication\Storage\NonPersistent;

class Module implements AutoloaderProviderInterface, ServiceProviderInterface, InitProviderInterface
{
	const WEBAPI_CURRENT_VERSION = '1.11';
    const WEBAPI_MINIMUM_VERSION = '1.2';
    const WEBAPI_UNKNOWN_VERSION = '0.0';
    
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

	public function init(ModuleManagerInterface $manager = null) {
    	$this->sharedEvents = $events = $manager->getEventManager()->getSharedManager();
    	$manager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'initializeDependencies'));

    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
    	$app = $e->getApplication();
    	$serviceManager = $app->getServiceManager();
    	
    	$e->setParam('webapi', $this->detectWebAPIRequest($e));
    	
		$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$e->setParam('webapi_ui', (! $e->isError()) && $authService->hasIdentity() );
		$initializers = array(
    			function ($instance) use ($serviceManager) {
    				if ($instance instanceof WebapiRequestCreatorInterface) {
    					$instance->setWebapiKeyMapper($serviceManager->get('WebAPI\Db\Mapper'));
    				}
    			}
    		);
    	
    	$serviceLocators = array(
    		$serviceManager,
    		$serviceManager->get('ControllerLoader'),
    		$serviceManager->get('ControllerPluginManager'),
    		$serviceManager->get('ViewHelperManager'),
    	);
    	
    	foreach ($serviceLocators as $serviceLocator) {
	    	foreach ($initializers as $initializer) {
	    		$serviceLocator->addInitializer($initializer);
	    	}
    	}
     	
	    
	    $app = $e->getApplication();
     	$this->initializeRouterWebAPI($e);

     	// set view parameters - version and format (xml/json)
     	$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'initializePathStack'));
     	
	    if ($e->getParam('webapi')) {
	        // check OS support (some OSs aren't supported)
	   		$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'limitedWebapiOs'));
	   		
	   		// check output format - JSON or XML
	    	$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'limitedWebapiOutput'));
	    	
	    	// check the requested version
	    	$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'applyWebAPIVersion'));
	    	
	    	
	    	$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'checkBootstrapCompleted'));
	    	$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'createCleanController'));
	    	/// run later, to allow other listeners to override 'useSessionControl'
	    	$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'checkRequestSignature'), -10);

	    	$app->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'allow'), 9000);
	    
	    	$app->getEventManager()->attach(MvcEvent::EVENT_RENDER, array($this, 'applyWebAPILayout'));
	    
	    	$app->getEventManager()->attach(MvcEvent::EVENT_FINISH, array($this, 'webapiOutputHeaders'));
	    	Log::debug('WebAPI responder initialized');
     	
	     	/// perform initializeViewStrategies in bootstrap - we can fail anywhere and need these strategies in place to handle these failures
	     	$this->initializeViewStrategies($e);
	     	$this->applyLicenseToAcl($e);
	     	$e->setParam('redirectOnError', false);
            $e->setParam('initViewHelpers', false);
     	}
    }

    public function createCleanController(MvcEvent $e) {
    	$routeMatch = $e->getRouteMatch();
    	$controller = $routeMatch->getParam('controller', '');
    	$controller = substr($controller, 0, strpos($controller, '-'));
    	$routeMatch->setParam('clean-controller-name', $controller);
    }
    
    public function applyLicenseToAcl(MvcEvent $e) {
    	if (! $e->getParam('licenseInfo', null)) {
    		return ;
    	}
    	$serviceManager = $e->getApplication()->getServiceManager();
    	$licenseAcl = $serviceManager->get('ZendServerLicenseAcl');
    	$license = $e->getParam('licenseInfo');
    	$licenseEditionRole = "edition:{$license->getEdition()}";
    	/// deny access to all webapi controllers except for those specifically designated to be allowed if webapi service is denied access
    	if ($e->getParam('webapi') && (! $e->getParam('webapi_ui')) && (! $licenseAcl->isAllowed($licenseEditionRole,'service:accessWebAPI'))) {
	    	foreach(appModule::config('controllers', 'invokables') as $controller => $class) {
	    		$controller = preg_replace('#\-[[:digit:]]+_[[:digit:]]+#', '', $controller);
	    		$licenseAcl->deny($licenseEditionRole, "route:$controller");
	    	}
	    	foreach (appModule::config('allowedWebAPIActions') as $controller => $actions) {
		    	$licenseAcl->allow($licenseEditionRole, "route:$controller", $actions->toArray());
	    	}
    	}
    }
    
	public function initializeDependencies(Event $e)
    {
    	if (! headers_sent()) {
	    	// do not clean output, may wreck webapi and binary actions' output
	    	ini_set('tidy.clean_output', false);
    	}
    }
    
    public function allow(MvcEvent $e) {
    	Log::debug(__METHOD__);
    	
    	if (! $e->getParam('useAclControl',true)) {
    		return ;
    	}
    	
        $routeMatch = $e->getRouteMatch(); /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
		
        $controller = $routeMatch->getParam('controller', '');
        $action = $routeMatch->getParam('action', '');
        $serviceManager = $e->getApplication()->getServiceManager();
        $role = $serviceManager->get('Zend\Authentication\AuthenticationService')->getIdentity()->getRole();
        $acl = $serviceManager->get('ZendServerAcl');
        try {
			try {
		    	if ($e->getParam('webapi') && ((! $e->getParam('webapi_ui')) && $e->getParam('bootstrapCompleted'))) {
					if (! $acl->isAllowedEdition('service:accessWebAPI')) {
						if (! $acl->isAllowedEdition("route:$controller", $action)) {
							
							$e->getApplication()->getEventManager()->trigger(
		    				'AuditMessage', $e->getApplication(), 
		    				array('type' => \Audit\Db\Mapper::AUDIT_GUI_AUTHORIZATION,
		    						'progress' => ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, 
		    						'extraData' => array('role' => $role, 'resource' => "route:$controller", 'action' => $action)));
							
							throw new apiException('This WebAPI action is not supported by the current edition', apiException::NOT_SUPPORTED_BY_EDITION);
						}
					}
				}
			} catch (\Zend\Permissions\Acl\Exception\InvalidArgumentException $ex) {
				/// route controller does not exist, this is a 404
				$newEvent = $this->application->getMvcEvent();
				$newEvent->setError(\Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND);
				$newEvent->setParam('exception', $ex);
				$this->application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
			} catch (apiException $ex) {
				Log::err("Authorization process failed {$ex->getMessage()}");
				throw $ex;
			} catch (Exception $ex) {
				Log::warn("Role {$acl->getIdentity()->getRole()} failed ACL check for $controller:$action");
				throw new apiException($ex->getMessage(), apiException::INSUFFICIENT_ACCESS_LEVEL, $ex);
			} catch (\Exception $ex) {
				Log::err("Authorization process failed {$ex->getMessage()}");
				Log::err($ex);
				throw new apiException("ACL configuration error: {$ex->getMessage()}", apiException::INTERNAL_SERVER_ERROR, $ex);
			}
		} catch (\Exception $ex) {
			$newEvent = $e;
			$newEvent->setError(\Zend\Mvc\Application::ERROR_EXCEPTION);
			$newEvent->setParam('exception', $ex);
			$e->getApplication()->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
		}
    }
    
    /**
     * @param MvcEvent $e
     */
    public function initializeViewStrategies(MvcEvent $e) {
    	$app = $e->getApplication();
    	$serviceManager = $app->getServiceManager();
    	$events            = $app->getEventManager();
                
		$noRouteStrategy   = $serviceManager->get('Zend\Mvc\View\Http\RouteNotFoundStrategy');
		$exceptionStrategy = $serviceManager->get('Zend\Mvc\View\Http\ExceptionStrategy');
		$noRouteStrategy->detach($events);
		$exceptionStrategy->detach($events);
		$noRouteStrategy   = $serviceManager->get('WebAPI\Mvc\View\Http\RouteNotFoundStrategy');
		$exceptionStrategy = $serviceManager->get('WebAPI\Mvc\View\Http\ExceptionStrategy');
		$events->attachAggregate($noRouteStrategy);
		$events->attachAggregate($exceptionStrategy);
    }
    
    /**
     * @param MvcEvent $e
     */
	public function checkRequestSignature(MvcEvent $e) {
    	Log::debug(__METHOD__);
    	if (appModule::config('sessionControl', 'sessionControlEnabled')
    		&& $e->getParam('useSessionControl', true) && $e->getParam('webapi', false)) {
    		
    		/// turn off session control so it won't be executed again
    		$e->setParam('useSessionControl', false);
    		
    		$app = $e->getApplication();
    		$serviceManager = $app->getServiceManager();
    		
    		$e->setParam('webapi_ui', true);
    		$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
    		if(! $authService->hasIdentity()) {
				Log::debug(__METHOD__.':checkSignature');
    			// check signature
    			$e->setParam('webapi_ui', false);
    			$signatureAdapter = $serviceManager->get('WebAPI\Authentication\Adapter\Signature');
    			
   				$result = $authService->authenticate($signatureAdapter);
   				
				$e->setParam('webapi_signature_identity', $result->getIdentity());
   				if (! $result->isValid()) {
					//$this->auditMessage(\Audit\Db\Mapper::AUDIT_GUI_AUTHENTICATION, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, $e, array('messages' => $result->getMessages()));
					Log::err("WebAPI signature authentication failed: " . current($result->getMessages()));
					/// special audit handling
					$application = $e->getTarget();/* @var $application \Zend\Mvc\Application */
					$events = $application->getEventManager();

					$error = $e;
					$error->setError(\Zend\Mvc\Application::ERROR_EXCEPTION)->setController('error');
					if (in_array($result->getCode(), array(Result::FAILURE_CREDENTIAL_INVALID, Result::FAILURE_IDENTITY_NOT_FOUND))) {
						$error->setParam('exception', new apiException(_t('The details of the Web API key you used are incorrect. Please verify the Web API key and hash and try again'), apiException::AUTH_ERROR));
					} elseif ($result->getCode() == Result::FAILURE_SIGNATURE_TIMESKEW) {
						$error->setParam('exception', new apiException(_t('Date and request start time are too far apart'), apiException::TIMES_SKEW_ERROR));
					} else {
						$error->setParam('exception', new apiException(_t('Malformed signature header'), apiException::MALFORMED_REQUEST));
					}

					$results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $error);
					$e->setResult($results);
				}
   			}
	   	}
	}
    
    public function getServiceConfig() {
    	$module = $this;
    	return array(
    			'invokables' => array(
		    		'WebAPI\Mvc\View\Http\RouteNotFoundStrategy' => 'WebAPI\Mvc\View\Http\RouteNotFoundStrategy',
		    		'WebAPI\Mvc\View\Http\ExceptionStrategy' => 'WebAPI\Mvc\View\Http\ExceptionStrategy',
		    	),
    			'factories' => array(
    				'WebAPI\Db\Mapper' => function($sm) {
	    				$mapper = new Mapper(new TableGateway('GUI_WEBAPI_KEYS', $sm->get(Connector::DB_CONTEXT_GUI)));
	    				return $mapper;
	    			},
    				'WebAPI\Authentication\Adapter\Signature' => function($sm) use ($module) {
    					
						if (appModule::config('authentication', 'simple')) {
							$signatureAdapter = new SignatureSimple();
							$signatureAdapter->setUsersMapper($sm->get('Users\Db\Mapper'));
						} else {
							$signatureAdapter = new SignatureGroups();
							$signatureAdapter->setMapperGroups($sm->get('Acl\Db\MapperGroups'));
							$signatureAdapter->setLdapConfig(appModule::config('zend_server_authentication'));
							$signatureAdapter->setGroupsAttribute(appModule::config('authentication','groupsAttribute'));
						}
						$signatureAdapter->setWebApiMapper($sm->get('WebAPI\Db\Mapper'));
						$signatureAdapter->setRequest($sm->get('Request'));
						$signatureAdapter->setTimeskew(appModule::config('authentication','webapi_time_skew'));
						return $signatureAdapter;
					},
    			)
    	);
    }


    /**
     * @param MvcEvent $e
     */
    public function limitedWebapiOs(MvcEvent $e) {
    	if ($e->getRouteMatch()) {
    		$routeMatch = $e->getRouteMatch();
    		$limitedOs = $routeMatch->getParam('limitedos', array());
    		foreach ($limitedOs as $os) {
	    		if (FS::isOs($os)) {
	    			$application = $e->getApplication();/* @var $application \Zend\Mvc\Application */
	    			$events = $application->getEventManager();
	    			
	    			$application = $e->getTarget();/* @var $application \Zend\Mvc\Application */
	    			$events = $application->getEventManager();
	    			$newEvent = $application->getMvcEvent();
	    			$osFullname = PHP_OS;
	    			$newEvent->setError(\Zend\Mvc\Application::ERROR_EXCEPTION)
	    					->setController('error')
			    			->setParam('exception', new apiException("This WebAPI action cannot be used on your Operating System ({$osFullname})", apiException::NOT_SUPPORTED_BY_EDITION));
	    			
	    			$results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
	    			$e->setResult($results);
	    		}
    		}
    	}
    }

    public function initializePathStack(MvcEvent $e) {
    	Log::debug(__METHOD__);
    	$serviceManager = $e->getApplication()->getServiceManager();
    	$pathStack = $serviceManager->get('ViewTemplatePathStack'); /* @var $viewManager \Zend\View\Resolver\TemplatePathStack */
     	
     	$templatePathStack = new TemplatePathStack();
    	$templatePathStack->setPaths($pathStack->getPaths());
    	if ($e->getParam('webapi')) {
	    	$pathStack->setDefaultSuffix("p{$e->getParam('parsedWebAPIOutput')}.phtml");
	    	/// add the webapi path resolver
	    	$templatePathStack->setWebapiVersion($e->getParam('parsedWebAPIVersion'));
	    	$templatePathStack->setDefaultSuffix("p{$e->getParam('parsedWebAPIOutput')}.phtml");
    	} else {
    		$templatePathStack->setWebapiVersion(self::WEBAPI_CURRENT_VERSION);
    		$templatePathStack->setDefaultSuffix("pjson.phtml");
    	}
    	
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('ViewTemplatePathStackWebAPI', $templatePathStack);
    	$serviceManager->setAllowOverride(false);
    	
    	$resolver = $serviceManager->get('ViewResolver'); /* @var $viewResolver \Zend\View\Resolver\AggregateResolver */
    	$resolver->attach($templatePathStack);
    }
    
    public function initializeRouterWebAPI(MvcEvent $e) {
    	$app = $e->getApplication();
    	if ($e->getParam('httpRequest',true)) {
	    	$router = $e->getRouter(); /* @var $router \Zend\Mvc\Router\Http\TreeRouteStack */
	    	$router->addRoutes(appModule::config('webapi_routes_bootstrap'));
	    	$router->addRoutes(appModule::config('webapi_routes'));
    	}
    	Log::debug(__METHOD__);
    }
    
    public function limitedWebapiOutput(MvcEvent $e) {
    	if ($e->getRouteMatch()) {
    		$routeMatch = $e->getRouteMatch();
    		$outputs = $routeMatch->getParam('output', array('xml', 'json'));
    		if (! in_array($e->getParam('parsedWebAPIOutput'), $outputs)) {
    			$application = $e->getApplication();/* @var $application \Zend\Mvc\Application */
    			$events = $application->getEventManager();
    			
    			$application = $e->getTarget();/* @var $application \Zend\Mvc\Application */
    			$events = $application->getEventManager();
    			$outputsImplode = implode(',',$outputs);
    			$newEvent = $this->application->getMvcEvent();
    			$newEvent->setError(\Zend\Mvc\Application::ERROR_EXCEPTION)
    					->setController('error')
		    			->setParam('exception', new apiException("This WebAPI action cannot create {$e->getParam('parsedWebAPIOutput')} output, available output: {$outputsImplode}", apiException::OUTPUT_TYPES_LIMITED));
    			
    			$results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
    			$e->setResult($results);
    		}
    	}
    	Log::debug(__METHOD__);
    }

    
    /**
     * @param unknown_type $e
     * @throws \ZendServer\Exception
     */
    public function applyWebAPIVersion(MvcEvent $e) {
    	Log::debug(__METHOD__);
    	
    	$routeMatch = $e->getRouteMatch(); /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
    	$versions = $routeMatch->getParam('versions', array());
    	

    	try {
	    	$routeMatch->setParam('original-action', $routeMatch->getParam('action'));
	    	$chosenVersion = $controller = '';
	    	
	    	if ('default' == $routeMatch->getMatchedRouteName() || (! $versions)) {
	    		$e->setParam('webapi-action', $routeMatch->getParam('action'));
	    		$chosenVersion = self::WEBAPI_MINIMUM_VERSION;
	    		$controller = 'error';
	    		throw new apiException("The requested action '{$routeMatch->getParam('action')}' does not exist on this server", apiException::UNKNOWN_METHOD);
	    	}
	    	
	    	if (version_compare($e->getParam('parsedWebAPIVersion'), self::WEBAPI_CURRENT_VERSION, '>')) {
	    		$chosenVersion = self::WEBAPI_MINIMUM_VERSION;
	    		$supportedVersions = array();
	    		for ($ver = (self::WEBAPI_MINIMUM_VERSION); $ver <= (self::WEBAPI_CURRENT_VERSION+0.1); $ver += 0.1) {
	    			$supportedVersions[] = sprintf('application/vnd.zend.serverapi;version=%.1F', $ver);
	    		}
	    		// greater than the latest version
	    		throw new apiException("Unknown version, supported versions:" . PHP_EOL . implode(PHP_EOL, $supportedVersions), apiException::API_VERSION_NOT_SUPPORTED);
	    	} elseif ($e->getParam('parsedWebAPIVersion') == self::WEBAPI_UNKNOWN_VERSION) {
	    		// No version specified, find minimum version
	    		$chosenVersion = array_reduce($versions, function ($v, $w) {
	    			return version_compare($v, $w, '<') ? $v : $w;
	    		}, self::WEBAPI_CURRENT_VERSION);
	    	} elseif (in_array($e->getParam('parsedWebAPIVersion'), $versions)) {
		   		// if we have the exact version implemented explicitly
	    		$chosenVersion = $e->getParam('parsedWebAPIVersion');
	    	} else {
	    		/// otherwise, find the highest version which is smaller than parsedWebAPIVersion
	    		$parsedWebAPIVersion = $e->getParam('parsedWebAPIVersion');
	    		$chosenVersion = array_reduce($versions, function ($v, $w) use ($parsedWebAPIVersion) {
	    			// smaller than parsedWebAPIVersion
	    			if (version_compare($w, $parsedWebAPIVersion, '<')) {
	    				/// greater than current greatest
		    			return version_compare($v, $w, '>=') ? $v : $w;
	    			}
	    			/// otherwise return the current value
	    			return $v;
	    		}, 0);
	    		/// if no such version exists (we got the original back, or we got the initial value), fail the entire operation
	    		if (version_compare($chosenVersion, $e->getParam('parsedWebAPIVersion'), '==') || $chosenVersion == 0) {
	    			if ($chosenVersion == 0) {
	    				$chosenVersion = self::WEBAPI_MINIMUM_VERSION;
	    			}
                    $e->setError(\Zend\Mvc\Application::ERROR_CONTROLLER_INVALID);
	    			throw new apiException('WebAPI action is not supported by this version', apiException::API_VERSION_NOT_SUPPORTED);
	    		}
	    	}
	    	
	    	$controller = $routeMatch->getParam('controller');
	    	if (version_compare($e->getParam('parsedWebAPIVersion'), self::WEBAPI_CURRENT_VERSION, '==')) {
	    		/// requested version is the current version
	    		$e->setParam('resolvedWebAPIVersion', self::WEBAPI_CURRENT_VERSION);
	    	} else {
	    		/// requested version is not the current version
	    		$e->setParam('resolvedWebAPIVersion', $chosenVersion);
	    	}
	    	$controllerVersion = str_replace('.', '_', $chosenVersion);
	    	$routeMatch->setParam('controller', "{$controller}-{$controllerVersion}");

    	} catch (apiException $ex) {
    		$e->setParam('resolvedWebAPIVersion', $chosenVersion);
    		if ($ex->getCode() == apiException::UNKNOWN_METHOD) {
    			Log::err($ex->getMessage());
    		} else {
	            Log::err("WebAPI version negotiation failed: {$ex->getMessage()}");
    		}
            
            if ($e->getName() != MvcEvent::EVENT_DISPATCH_ERROR) {
	            $application = $e->getTarget();/* @var $application \Zend\Mvc\Application */
	            $events = $application->getEventManager();

	            $error = $e;
	            $error->setError(\Zend\Mvc\Application::ERROR_EXCEPTION)
	                  ->setController('error')
	                  ->setParam('exception', $ex);
	
	            $results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $error);
	            $e->setResult($results);
            }
    	}
    	

    	$viewVersion = $chosenVersion;
    	if ($routeMatch->getParam('viewsmap')) {
    		$viewsmap = $routeMatch->getParam('viewsmap');
    		if (isset($viewsmap[$chosenVersion])) {
    			$viewVersion = $viewsmap[$chosenVersion];
		    	Log::debug("Using view script from version {$viewVersion}");
    		}
    	}
    	
    	$resolver = $e->getApplication()->getServiceManager()->get('ViewTemplatePathStackWebAPI');
    	$resolver->setWebapiVersion($viewVersion);
    	
    	$e->setParam('webapi-action', $routeMatch->getParam('action'));
    	
    	Log::debug("WebAPI route set ({$controller}, {$chosenVersion})");
    	Log::info("WebAPI command {$routeMatch->getParam('action')} was called");
    }
    
    public function webapiOutputHeaders($e) {
    	$app = $e->getTarget();
    	$response = $app->getResponse(); /* @var $response \Zend\Http\PhpEnvironment\Response */
    	$headers = $response->getHeaders();
    	if (! $headers->has('Content-Type')) {
	    	$headers->addHeaders(array('Content-Type' => "application/vnd.zend.serverapi+{$e->getParam('parsedWebAPIOutput')};version={$e->getParam('resolvedWebAPIVersion')}"));
    	}
    	$response->setHeaders($headers);
    	Log::debug(__METHOD__);
    }

    public function applyWebAPILayout(MvcEvent $e) {
    	$viewModel = $e->getViewModel(); /* @var $viewModel \Zend\View\Model\ViewModel */
        
        if ($e->getRouteMatch()) {
            $routeName = $e->getRouteMatch()->getMatchedRouteName();
        } else {
            $route = new \Zend\Mvc\Router\Http\Segment('/Api/[:action]');
            $routeMatch = $route->match($e->getRequest(), strlen($e->getRequest()->getBaseUrl()));
            if($routeMatch) {
                $routeName = $routeMatch->getParam('action');
            } else {
                $routeName = 'unknown';
            }
        }
        
        $identity = $e->getParam('webapi_signature_identity', new Identity('Unknown'));
    	
    	$viewModel->setVariables(
    		array(
    			'webApiUrl' => "http://www.zend.com/server/api/{$e->getParam('resolvedWebAPIVersion')}",
    			'apiKeyName' => $identity->getIdentity(),
    			'apiMethod' => $routeName,
    		));
    	
    	Log::debug(__METHOD__);
    }

    public function checkBootstrapCompleted(MvcEvent $e) {
    	Log::debug(__METHOD__);
    	$routeMatch = $e->getRouteMatch();
    	if ((! $e->getParam('bootstrapCompleted'))) {
    		if (! $routeMatch->getParam('bootstrap', false)) {
		    	/// not a bootstrap allowed route, fail with serverNotReady
	    		$application = $e->getTarget();/* @var $application \Zend\Mvc\Application */
	            $events = $application->getEventManager();
                
	            $ex = new \WebAPI\Exception("Bootstrap is needed in {$routeMatch->getMatchedRouteName()}", \WebAPI\Exception::SERVER_NOT_READY);
	            $error = $application->getMvcEvent();
	            $error->setError(\Zend\Mvc\Application::ERROR_EXCEPTION)
	                  ->setParam('exception', $ex);
	            
	            $error->setRouteMatch($routeMatch);
	            
	            $results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $error);
	            $e->setResult($results);
		    	Log::debug('Bootstrap needed, display an error');
    		} else {
	    		/// if bootstrap is not completed, switch off session control for webapi authentication
	    		$e->setParam('useSessionControl', false);
	    		/// need to provide an identity for webapi requests
	    		$authService = $e->getApplication()->getServiceManager()->get('Zend\Authentication\AuthenticationService');
	    		$identity = new Identity('Unknown');
	    		$identity->setRole(appModule::ACL_ROLE_BOOTSTRAP);
	    		$storage = new NonPersistent();
	    		$storage->write($identity);
	    		$authService->setStorage($storage);
    		}
    	} elseif ($e->getParam('bootstrapCompleted') && ($e->getRouteMatch()->getParam('bootstraponly', false))) {
    		Log::err('Cannot run bootstrap');
    		$application = $e->getTarget();/* @var $application \Zend\Mvc\Application */
    		$events = $application->getEventManager();
    		
    		$ex = new \WebAPI\Exception('This server is already bootstrapped', \WebAPI\Exception::SERVER_ALREADY_BOOTSTRAPPED);
    		$error = $application->getMvcEvent();
    		$error->setError(\Zend\Mvc\Application::ERROR_EXCEPTION)
    		->setParam('exception', $ex);
    		 
    		$error->setRouteMatch($routeMatch);
    		 
    		$results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $error);
    		$e->setResult($results);
    	}
    }

	/**
     * @param MvcEvent $e
     * @return boolean
     */
    private function detectWebAPIRequest(MvcEvent $e) {
    	if (! $e->getParam('httpRequest', true)) {
    		return false;
    	}
    	$request = $e->getRequest();
    	$headers = $request->getHeaders();/* @var $headers \Zend\Http\Headers */

    	if ($headers->has('XACCEPT')) {
	    	$acceptHeader = $headers->get('XACCEPT');/* @var $acceptHeader \Zend\Http\Header\GenericHeader */
	    	$acceptHeader = \Zend\Http\Header\Accept::fromString("Accept: {$acceptHeader->getFieldValue()}");
    	} else {
	    	$acceptHeader = $headers->get('ACCEPT');/* @var $acceptHeader \Zend\Http\Header\Accept */
    	}
    	
    	$versions = array();
    	if ($acceptHeader instanceof \Zend\Http\Header\Accept
    			&& 0 < preg_match('#^application/vnd\.zend\.serverapi#', $acceptHeader->getFieldValue())) {
    		
    			$header = explode(';', $acceptHeader->getFieldValue());
    		
	    		if (count($header) == 1) {
	    			$q = '';
	    			$version = '';
	    			$type = current($header);
	    		} elseif (count($header) == 2) {
	    			$q = '';
		    		list($type, $version) = $header;
	    		} else {
		    		list($type, $version, $q) = $header;
	    		}
	
	    		$matches = array();
	    		
	    		preg_match('/^q\=(?P<q>\d+\.\d+)/', $q, $matches);
	    		// set default value for q of the lowest possible 0.0 if not found
	    		if (! isset($matches['q']) || empty($matches['q'])) {
	    			$q = '0.0';
	    		}
	    		
	    		preg_match('/^version\=(?P<version>\d+\.\d+)/', $version, $matches);
	    		if(! isset($matches['version']) || empty ($matches['version'])) {
	    			$matches['version'] = '0';
	    		}
	    		
	    		$version = $matches['version'];
	    		if (! isset($versions[$version])) {
	    			$versions[$version] = $q;
	    		} else {
	    			if (self::versionsCompare($versions[$version], $q)) {
	    				$versions[$version] = $q;
	    			}
	    		}

	    		// match string application/vnd.zend.serverapi+xml;version= with version number #.#
	    		preg_match('/^application\/vnd\.zend\.serverapi\+(?P<output>xml|json)/', $type, $matches);
	    	
	    		// set default value for version of the olders version possible 1.0 if not found
	    		if (! isset($matches['output']) || empty($matches['output'])) {
	    			$type = 'xml';
	    		} else {
	    			$type = $matches['output'];
	    		}
				$output[$version] = $type;
    	}
    	
    	if (! count($versions)) {
    		return false;
    	}
    	
    	$sorted = $this->sortVersions($versions);
    	$chosenVersion = current(array_unique(array_keys($sorted)));
    	$e->setParam('parsedWebAPIVersion', $chosenVersion);
    	$e->setParam('parsedWebAPIOutput', $output[$chosenVersion]);
    	
    	Log::debug("WebAPI initialized ({$chosenVersion}, {$output[$chosenVersion]})");
    	return true;
    }

    /**
     * @param array $versions
     * @return array
     */
    private function sortVersions(array $versions) {
    	uasort($versions, function ($a, $b) {
    		return version_compare($a, $b, '<');
    	});
    	return $versions;
    }
}
