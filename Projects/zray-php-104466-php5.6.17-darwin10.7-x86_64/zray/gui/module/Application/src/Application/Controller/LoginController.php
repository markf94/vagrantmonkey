<?php

namespace Application\Controller;

use Application\SessionStorage;
use Zend\View\Model\ViewModel;

use Zend\Uri\Uri;

use ZendServer\Exception;

use ZendServer\Validator\UriPath;

use Users\Identity;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use ZendServer\Configuration\Manager;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	ZendServer\Log\Log,
	Application\Forms\Login;


class LoginController extends ActionController
{
	protected $version;
	protected $build;
	protected $licenseType;
	protected $licenseExpiryDate;
	protected $authenticationSource;
	protected $licenseEvaluation;
	protected $licenseIsOk;
	protected $daysToExpired;
	protected $licenseNeverExpires;

	/**
	 * @var \Application\Forms\Login
	 */
	protected $loginForm;	
	
	public function redirectAction() {
		return $this->redirect()->toRoute('home');
	}
	
	
	public function indexAction() {
		$params = $this->getParameters(array('redirectTo' => ''));
		$redirectTo = $this->validateRedirectTo($params['redirectTo']);
		if ($this->Authentication()->hasIdentity() && $this->Authentication()->getIdentity()->isLoggedIn()) { // User has already authenticated
			$this->Redirect()->toUrl($redirectTo);
		}
		if (isAzureEnv()) {
		    $authService = $this->getLocator()->get('Zend\Authentication\AuthenticationService'); /* @var $authService AuthenticationService */
		    $tokenAdapter = $this->getLocator()->get('AuthAdapterAzure'); /* @var $tokenAdapter TokenAdapter */
		    $result = $authService->authenticate($tokenAdapter);
		     
		    if ($result->isValid()) {
		        $result->getIdentity()->setLoggedIn();
		        $this->redirect()->toRoute('home');
		    } else {
		        echo ('Authentication failed');
		        exit;
		    }
		    return $this->getResponse();
		}
		
		if (isZrayStandaloneEnv()) {
			/* @var $authService AuthenticationService */
		    $authService = $this->getLocator()->get('Zend\Authentication\AuthenticationService'); 
			
			/* @var \ZendServer\Authentication\Adapter\ZrayStandalone */
			$authAdapter = new \ZendServer\Authentication\Adapter\ZrayStandalone();
				
			// @TODO check why this isn't working. That might cause future problems
			//$authAdapter = $this->getLocator()->get('AuthAdapterZrayStandalone'); 
			
		    $result = $authService->authenticate($authAdapter);
		    if ($result->isValid()) {
		        $result->getIdentity()->setLoggedIn();
		        $this->redirect()->toRoute('home');
		    } else {
		        echo ('Authentication failed');
		        exit;
		    }
		    return $this->getResponse();
		}
		
		$this->authenticationSource = Module::config('authentication', 'simple') ? 'simple' : 'extended';
		
		$this->version = Module::config('package', 'version');//get the current version of server
		$this->build = Module::config('package', 'build');//get the current build of server
		list($this->licenseType, $this->licenseExpiryDate, $this->licenseEvaluation) = $this->getLicenseDetails();// get the license details
		
		$this->Layout('layout/login');
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */		
		if (!$request->isPost()) {
			/// this page must explicitly not be loaded as an ajax call
			return $this->getPageComponents(null, $redirectTo) + array(
						'isAjaxRequest' => $this->getRequest()->isXmlHttpRequest(),
					);
		}
		
		$this->loginForm = $this->getLocator('Application\Forms\Login');
		$parameters = $request->getPost()->toArray();
		$this->loginForm->setData($parameters);
		
		$this->auditMessage()->setIdentity(new Identity($parameters['username']));
		
		if (!$this->loginForm->isValid()) {
			$nonValidElements = '';
			foreach ($this->loginForm->getMessages() as $field => $errors) {
				if (!$errors) continue;
				$nonValidElements .= $field . ',';
			}
			
			$errorMessage = 'Invalid input in field(s): ' . rtrim($nonValidElements, ',');
			$this->auditMessage(Mapper::AUDIT_GUI_AUTHENTICATION, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($errorMessage)));
			return $this->getPageComponents($errorMessage);
		}
		
		if ($parameters['username'] != Module::config('user', 'zend_gui', 'adminUser') && (! $this->isAclAllowedEdition('data:useMultipleUsers'))) {
			$errorMessage = _t('Only the \'admin\' user is allowed to use this edition of Zend Server');
			$this->auditMessage(Mapper::AUDIT_GUI_AUTHENTICATION, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($errorMessage)));
			return $this->getPageComponents($errorMessage);
		}
		
		if (!$this->Authentication()->authenticate($parameters['username'], $parameters['password'])){
			$errorMessage = _t('Username or password incorrect');
			$this->auditMessage(Mapper::AUDIT_GUI_AUTHENTICATION, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($errorMessage)));
			return $this->getPageComponents($errorMessage);
		}
		
		$this->postAuthentication($parameters['username'], $redirectTo);
		
		return $this->getResponse();
	}
	
	private function postAuthentication($username, $redirectTo) {
	    $manager = $this->getLocator()->get('Zend\Session\SessionManager');
	    $manager->regenerateId();
		
		\Application\Module::generateCSRF();
		
	    $this->auditMessage(Mapper::AUDIT_GUI_AUTHENTICATION, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array('User' => $username)));
	    Log::info("authentication completed successfully");
	    
	    $azure = isAzureEnv();
	    $standaloneZray = isZrayStandaloneEnv();
	    if (!$azure && !$standaloneZray) {
	        $this->setLibrariesUpdateCookie();
	        $this->setPluginsUpdateCookie();
	    }
	    
	    if ($redirectTo) {
	        $decoded = urldecode($redirectTo);
	        if ($decoded == '/') {
	            $decoded = '/ZendServer';
	        } 
	        Log::debug("Redirect to" . $decoded);
	        $this->Redirect()->toUrl($decoded);
	    } else {
	        $this->Redirect()->toRoute('home');
	    }
	}
	
	public function logoutAction() {
		$this->Authentication()->getAuthService()->clearIdentity();
		$routeMatch  = $this->getEvent()->getRouteMatch();
		$collectCurrentUrl = $routeMatch->getParam('collectCurrentUrl', false);

        /// clean up session
        $manager = $this->getLocator()->get('Zend\Session\SessionManager'); /* @var $manager \Zend\Session\SessionManager */
        $manager->destroy();
        $manager->regenerateId();

		$viewModel = new ViewModel(array('loginUrl' => Module::config('loginUrl')));
		$viewModel->setVariable('collectCurrentUrl', $collectCurrentUrl && ($routeMatch->getMatchedRouteName() != 'home'));
		$viewModel->setTerminal(true);
		return $viewModel;
	}


	/**
	 * @param string $redirectTo
	 * @return string
	 */
	private function validateRedirectTo($redirectTo) {
		$uri = new Uri($redirectTo);

		$validator = new UriPath();
		if (! $validator->isValid($uri->getPath())) {
			Log::notice('URL to redirect to was malformed, redirect to home');
			return '';
		}
		$redirectTo = $uri->getPath();
		$redirectTo = is_null($redirectTo) ? '/' : $redirectTo;
		if ($uri->getQuery()) {
			$redirectTo .= "?{$uri->getQuery()}";
		}
		
		if ($uri->getFragment()) {
			$redirectTo .= "#{$uri->getFragment()}";
		}
		
		return $redirectTo;
	}
	
	private function getSimpleUsernames() {
		$usersMapper = $this->getLocator('Users\Db\Mapper'); /* @var $usersMapper \Users\Db\Mapper */
		$users = $usersMapper->getUsers()->toArray();
		$usernames = array();
		foreach ($users as $user) {
			$usernames[$user['NAME']] = $user['NAME'];
		}
		
		$acl = $this->getLocator('ZendServerAcl'); /* @var $acl \ZendServer\Permissions\AclQuery */
		if (! $acl->isAllowedEdition('data:useMultipleUsers')) {
		    $usernames = array('admin'=> 'admin');
		}
		
		return $usernames;
	}
	
	private function getPageComponents($error='', $redirectTo = '') {
		if (!$this->loginForm) {
			$authConfig = Module::config('authentication');
			$simpleAuth = $authConfig->simple ? true : false;
			
			$usernames = array();
			if ($simpleAuth) {
				$usernames = $this->getSimpleUsernames();
			}
			
			$this->loginForm = new Login(array('simpleAuth' => $simpleAuth, 'users' => $usernames));
		}
		
		if ($redirectTo) {
			$uri = new Uri($redirectTo);
			$redirectTo = $uri->getPath();
			if ($uri->getQuery()) {
				$redirectTo .= '?'.Uri::encodeQueryFragment($uri->getQuery());
			}
			if ($uri->getFragment()) {
				$redirectTo .= '#'.Uri::encodeQueryFragment($uri->getFragment());
			}
			$this->loginForm->setData(array('redirectTo' => urlencode($redirectTo)));
		}
		
		$config = $this->getLocator()->get('Configuration');
		$manager = new Manager();
		
		$pageComponents = array(
					'form' 	=> $this->loginForm,
					'version' => $this->version,
					'build' => $this->build,
					'profile' => @$config['package']['zend_gui']['serverProfile']?:'',
					'licenseType' => $this->licenseType,
					'licenseExpiryDate' => $this->licenseExpiryDate,
					'licenseEvaluation' => $this->licenseEvaluation,
					'authenticationSource' => $this->authenticationSource,
					'licenseIsOk' => $this->licenseIsOk,
					'licenseNeverExpires' => $this->licenseNeverExpires,
					'daysToExpired' => $this->daysToExpired,
					'osType' => $manager->getOsType(),
				);

		if ($error) {
			$pageComponents += array('error' => _t($error));
		}
		
		return $pageComponents;		
	}
	
	private function getLicenseDetails() {
		
		$utilsWrapper = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper');		
		$this->licenseNeverExpires = $utilsWrapper->getLicenseInfo()->isNeverExpires();
		$this->licenseIsOk = $utilsWrapper->getLicenseInfo()->isLicenseOk();
		$this->daysToExpired = $utilsWrapper->getLicenseExpirationDaysNum();
		return array($utilsWrapper->getLicenseType(), $utilsWrapper->getLicenseFormattedExpiryDate(), $utilsWrapper->getLicenseEvaluation());
	}
}
