<?php
namespace DevBar\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use ZendServer\FS\FS;
use ZendServer\Log\Log;
use Zend\Http\PhpEnvironment\Request;
use Application\Module;
use StudioIntegration\Debugger\Validator\Access;
use DevBar\Validator\BaseUrlAccess;
use Zend\Uri\Http;
use Audit\Db\Mapper;
use Audit\Db\ProgressMapper;
use DevBar\AccessTokenContainer;
use ZendServer\Exception;
use DevBar\Validator\PageId;
use DevBar\Validator\AccessToken;
use Configuration\DirectiveContainer;

class IndexController extends ActionController
{
	
	/**
	 * Display page with zray. 
	 * Used to inject zray to the page, by adding an iframe with src to this controller action.
	 * @return \Zend\View\Model\ViewModel
	 */
	public function zrayInjectAction() {
		if (function_exists('zray_disable')) {
			\zray_disable(true);
		}
		
		// get the page id
		$pageId = $this->getRequest()->getQuery('pageId', '');
		$pageIdValidator = new PageId();
		if (!empty($pageId) && !$pageIdValidator->isValid($pageId)) {
			throw new Exception(_t('\'pageId\' parameter must be a valid pageId value'));
		}
		
		// prepare the vire model
		$viewModel = new ViewModel();
		$viewModel->setVariable('pageId', $pageId);
		$viewModel->setTerminal(true);
		
		return $viewModel;
	}
	
	public function galleryAction() {
	    return $this->redirect()->toRoute('pluginsGalleryPage');
	}
	
	public function iframeAction() {
		if (function_exists('zray_disable')) {
			\zray_disable(true); 
		}
		
		$viewModel = new ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setTemplate('dev-bar/index/iframe');
		
		$embedded = $this->getRequest()->getQuery('embedded', '0'); 
		$historyEmbedded = $this->getRequest()->getQuery('historyEmbedded', '0'); 
		$pageId = $this->getRequest()->getQuery('pageId', '');
		$url = $this->getRequest()->getQuery('url', '');
		$host = $this->getRequest()->getQuery('host', '');
		$inIframe = $this->getRequest()->getQuery('iframe', '');
		$token = $this->getRequest()->getQuery('token', '');
		$requestsSeparated = $this->getRequest()->getQuery('requestsSeparated', '');

		try {
			$pageIdValidator = new PageId();
			if (!empty($pageId) && !$pageIdValidator->isValid($pageId)) {
				throw new Exception(_t('\'pageId\' parameter must be a valid pageId value'));
			}
			
			$urlValidator = new Http($url);
			if ((! $urlValidator->isAbsolute()) || ( ! $urlValidator->isValid())) {
				throw new Exception(_t('\'url\' parameter must be a valid absolute URL'));
			}

			/// sanitize URL
			$urlValidator->setQuery(null);
			$url = $urlValidator->toString();
			
			$urlValidator = new Http('http://' . $host);
			if (! $urlValidator->isValid()) {
				throw new Exception(_t('\'host\' parameter must be a valid URL'));
			}
			
			if ($token) {
				$tokenValidator = new AccessToken();
				if (! $tokenValidator->isValid($token)) {
					throw new Exception(_t('\'token\' parameter must be a valid Z-Ray Access Token'));
				}
			}
			
			$viewModel->setVariable('embedded', $embedded);
			$viewModel->setVariable('historyEmbedded', $historyEmbedded);
			$viewModel->setVariable('pageId', $pageId);
			$viewModel->setVariable('url', $url);
			$viewModel->setVariable('host', $host);
			$viewModel->setVariable('inIframe', intval($inIframe)); // sanitize iframe indicator
			$viewModel->setVariable('token', $token);
			$viewModel->setVariable('inputFilterFailed', false);
			$viewModel->setVariable('requestsSeparated', $requestsSeparated ? 1 : 0);
			
		} catch (Exception $ex) {
			Log::err('Z-Ray bar input validation error: %s', array($ex->getMessage()));
			Log::debug($ex);
			
			$viewModel->setVariable('embedded', 0);
			$viewModel->setVariable('historyEmbedded', 0);
			$viewModel->setVariable('pageId', '');
			$viewModel->setVariable('url', '');
			$viewModel->setVariable('host', '');
			$viewModel->setVariable('inIframe', 0);
			$viewModel->setVariable('token', '');
			$viewModel->setVariable('inputFilterFailed', true);
			$viewModel->setVariable('requestsSeparated', 0);
			
		}
		
		$build = Module::config('package', 'build');
		if (Module::config('debugMode', 'zend_gui', 'debugModeEnabled')) {
			$build .= '-' . rand(10000000, 99999999);
		}
		$viewModel->setVariable('build', $build);
		
		$disableByCookie = !$embedded && ($this->getRequest()->getCookie() !== false && $this->getRequest()->getCookie()->offsetExists('ZRayDisable'));
		$disableByDirective = $this->getDirectivesMapper()->getDirectiveValue('zray.enable') == 0;
		
		// check that edition and user role have access to devbar webapi actions
		$disableByAcl = (! $this->isAclAllowedEdition('route:DevBarWebApi', 'devBarGetRequestsInfo'));
		
		$hideDevBar = false;
		// if disabled by cookie OR not allowed by acl - hide DevBar
		if (!$embedded && ($disableByCookie || $disableByAcl || $disableByDirective)) {
			$hideDevBar = true;
		}
		
		if ($hideDevBar) {
			return $this->getResponse()->setContent('');
		}
		
		return $viewModel;
	}
	
	public function settingsAction() {
		
		/* @var $directivesMapper \Configuration\MapperDirectives */
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); 
		
		
		$directiveToVarMapper = array(
			'zray.enable' => 'devBarEnabled',
			'zend_gui.showInIframe' => 'devBarShowInIframe',
			'zend_gui.collapse' => 'devBarCollapse',
			'zend_gui.maxRequests' => 'devBarMaxRequests',
			'zend_gui.maxElementsPerLevel' => 'maxElementsPerLevel',
			'zend_gui.maxElementsInTree' => 'maxElementsInTree',
			'zend_gui.maxTreeDepth' => 'maxTreeDepth',
			'zend_gui.showSilencedLogs' => 'devBarShowSilencedLogs',
			'zray.zendserver_ui_url' => 'zendserverUiUrl',
			'zray.attribute_masking_list' => 'attributesMaskingList',
			'zray.enable_attribute_masking' => 'enableAttributeMasking',
			'zray.enable_extensibility' => 'collectData',
			'zray.collect_backtrace' => 'collectBacktrace',
			'zray.collect_backtrace.sql_queries' => 'collectBacktraceSQL',
			'zray.collect_backtrace.errors_warnings' => 'collectBacktraceErrors',
			'zray.max_number_log_entries' => 'maxErrorEntries',
			'zray.disable_injection' => 'disableInjection',
			'zray.disable_actions' => 'disableActions',
			'zray.history_time' => 'historyTime',
			'zray.max_db_size' => 'maxDbSize',
			'zray.cleanup_frequency' => 'cleanupFrequency',
		);
		
		$directives = $directivesMapper->selectSpecificDirectives(array_keys($directiveToVarMapper));
		foreach ($directives as $directive) {
			${$directiveToVarMapper[$directive->getName()]} = $directive;
		}
		
		$config = $this->getServiceLocator()->get('Configuration');
		$config = $config['installation']['zend_gui'];
		
		if (!isset($maxTreeDepth)) {
			$maxTreeDepth = new DirectiveContainer(array('DISK_VALUE' => 15, 'NAME' => 'zend_gui.maxTreeDepth'));
		}

		$devBarSettingsForm = new \Application\Forms\Settings\DevBar(array(
            'devBarEnabled' => $devBarEnabled, 
            'showInIframe' => $devBarShowInIframe,
            'showSilencedLogs' => $devBarShowSilencedLogs,
            'zray.zendserver_ui_url' => $zendserverUiUrl,
            'collapse' => $devBarCollapse,
            'maxRequests' => $devBarMaxRequests,
            'maxElementsPerLevel' => $maxElementsPerLevel,
            'maxElementsInTree' => $maxElementsInTree,
            'maxTreeDepth' => $maxTreeDepth,
            'historyTime' => $historyTime,
            'maxDbSize' => $maxDbSize,
            'cleanupFrequency' => $cleanupFrequency,
		));
		
		$devBarGranularityForm = new \DevBar\Forms\Settings\DataGranularityDevBar(array(
            'collectExtensionData' => $collectData, 
            'collectBacktrace' => $collectBacktrace,
            'collectBacktraceSQL' => $collectBacktraceSQL,
            'collectBacktraceErrors' => $collectBacktraceErrors,
            'max_number_log_entries' => $maxErrorEntries,
		));
		
		$devBarDataPrivacyForm = new \Application\Forms\Settings\PrivacyDevBar(array(
            'enableAttributesMasking' => $enableAttributeMasking,
            'attributesMaskingList' => $attributesMaskingList,
		));
		 
		$devBarAllowed = $this->isAclAllowed('route:DevBarWebApi', 'devBarGetRequestsInfo');
		$useZRaySecureMode = $this->isAclAllowedEdition('data:useZRaySecureMode');
		
		if (isAzureEnv()) {
		    if (function_exists('zray_get_azure_license')) {
		        $license = \zray_get_azure_license();
		        if ($license != ZRAY_AZURE_LICENSE_STANDARD) { // if license not standard - disable page
		            $useZRaySecureMode = false;
		        }
		    } else { // license not found - disable page
		        $useZRaySecureMode = false;
		    }
		}
		
		// @TODO add conditions for ZRay standalone
		
		$remoteAddr = $this->getRequest()->getServer('REMOTE_ADDR');
		return array(
			'pageTitle' => 'Settings',
            'pageTitleDesc' => '',  /* Daniel */
		    
			'devBarSettingsForm' => $devBarSettingsForm,
		    'devBarGranularityForm' => $devBarGranularityForm,
		    'devBarDataPrivacyForm' => $devBarDataPrivacyForm,
		    
			'devBarAllowed' => $devBarAllowed,
			'devbarEnabled' => $devBarEnabled->getFileValue() != 0,
			'devbarMode' =>  $devBarEnabled->getFileValue(),
			'defaultGuiPort' => $config['defaultPort'],
			'securedGuiPort' => $config['securedPort'],
			'useZRaySecureMode' => $useZRaySecureMode,
		    'azure' => isAzureEnv(),
		    'zrayStandalone' => isZrayStandaloneEnv(),
		    'disableInjection' => isset($disableInjection) ? $disableInjection->getFileValue() : false, 
		    'disableActions' => isset($disableActions) ? $disableActions->getFileValue() : false,
		    'remoteAddr' => $remoteAddr,
		);	
	}
	
	public function accessModeAction() {
		/* @var $directivesMapper \Configuration\MapperDirectives */
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); 
		
		$directiveToVarMapper = array(
			'zray.enable' => 'devBarEnabled',
			'zend_gui.showInIframe' => 'devBarShowInIframe',
			'zend_gui.collapse' => 'devBarCollapse',
			'zend_gui.maxRequests' => 'devBarMaxRequests',
			'zend_gui.maxElementsPerLevel' => 'maxElementsPerLevel',
			'zend_gui.maxElementsInTree' => 'maxElementsInTree',
			'zend_gui.maxTreeDepth' => 'maxTreeDepth',
			'zend_gui.showSilencedLogs' => 'devBarShowSilencedLogs',
			'zray.zendserver_ui_url' => 'zendserverUiUrl',
			'zray.attribute_masking_list' => 'attributesMaskingList',
			'zray.enable_attribute_masking' => 'enableAttributeMasking',
			'zray.enable_extensibility' => 'collectData',
			'zray.collect_backtrace' => 'collectBacktrace',
			'zray.collect_backtrace.sql_queries' => 'collectBacktraceSQL',
			'zray.collect_backtrace.errors_warnings' => 'collectBacktraceErrors',
			'zray.max_number_log_entries' => 'maxErrorEntries',
			'zray.disable_injection' => 'disableInjection',
			'zray.disable_actions' => 'disableActions',
			'zray.history_time' => 'historyTime',
			'zray.max_db_size' => 'maxDbSize',
			'zray.cleanup_frequency' => 'cleanupFrequency',
		);
		
		$directives = $directivesMapper->selectSpecificDirectives(array_keys($directiveToVarMapper));
		foreach ($directives as $directive) {
			${$directiveToVarMapper[$directive->getName()]} = $directive;
		}
		
		$config = $this->getServiceLocator()->get('Configuration');
		$config = $config['installation']['zend_gui'];
		
		if (!isset($maxTreeDepth)) {
			$maxTreeDepth = new DirectiveContainer(array('DISK_VALUE' => 15, 'NAME' => 'zend_gui.maxTreeDepth'));
		}

		$devBarSettingsForm = new \Application\Forms\Settings\DevBar(array(
            'devBarEnabled' => $devBarEnabled, 
            'showInIframe' => $devBarShowInIframe,
            'showSilencedLogs' => $devBarShowSilencedLogs,
            'zray.zendserver_ui_url' => $zendserverUiUrl,
            'collapse' => $devBarCollapse,
            'maxRequests' => $devBarMaxRequests,
            'maxElementsPerLevel' => $maxElementsPerLevel,
            'maxElementsInTree' => $maxElementsInTree,
            'maxTreeDepth' => $maxTreeDepth,
			'maxTreeDepth' => $maxTreeDepth,
			'historyTime' => $historyTime,
			'maxDbSize' => $maxDbSize,
			'cleanupFrequency' => $cleanupFrequency,
		));
		
		$devBarGranularityForm = new \DevBar\Forms\Settings\DataGranularityDevBar(array(
            'collectExtensionData' => $collectData, 
            'collectBacktrace' => $collectBacktrace,
            'collectBacktraceSQL' => $collectBacktraceSQL,
            'collectBacktraceErrors' => $collectBacktraceErrors,
            'max_number_log_entries' => $maxErrorEntries,
		));
		
		$devBarDataPrivacyForm = new \Application\Forms\Settings\PrivacyDevBar(array(
            'enableAttributesMasking' => $enableAttributeMasking,
            'attributesMaskingList' => $attributesMaskingList,
		));
		 
		$devBarAllowed = $this->isAclAllowed('route:DevBarWebApi', 'devBarGetRequestsInfo');
		$useZRaySecureMode = $this->isAclAllowedEdition('data:useZRaySecureMode');
		
		if (isAzureEnv()) {
		    if (function_exists('zray_get_azure_license')) {
		        $license = \zray_get_azure_license();
		        if ($license != ZRAY_AZURE_LICENSE_STANDARD) { // if license not standard - disable page
		            $useZRaySecureMode = false;
		        }
		    } else { // license not found - disable page
		        $useZRaySecureMode = false;
		    }
		}
		
		$remoteAddr = $this->getRequest()->getServer('REMOTE_ADDR');
		
		return array(
			'pageTitle' => 'Mode',
            'pageTitleDesc' => '',  /* Daniel */
		    
			'devBarSettingsForm' => $devBarSettingsForm,
		    'devBarGranularityForm' => $devBarGranularityForm,
		    'devBarDataPrivacyForm' => $devBarDataPrivacyForm,
		    
			'devBarAllowed' => $devBarAllowed,
			'devbarEnabled' => $devBarEnabled->getFileValue() != 0,
			'devbarMode' =>  $devBarEnabled->getFileValue(),
			'defaultGuiPort' => $config['defaultPort'],
			'securedGuiPort' => $config['securedPort'],
			'useZRaySecureMode' => $useZRaySecureMode,
		    'azure' => isAzureEnv(),
		    'zrayStandalone' => isZrayStandaloneEnv(),
		    'disableInjection' => isset($disableInjection) ? $disableInjection->getFileValue() : false, 
		    'disableActions' => isset($disableActions) ? $disableActions->getFileValue() : false,
		    'remoteAddr' => $remoteAddr,
		);
	}
	
	public function modeAction() {
	    return $this->getSettingsForPanel('access', 'Mode');
	}
	
	public function tokensAction() {
	    return $this->getSettingsForPanel('tokens', 'Access Tokens');
	}
	
	public function advancedAction() {
	    return $this->getSettingsForPanel('settings', 'Settings');
	}
	
	public function dataGranularityAction() {
	    return $this->getSettingsForPanel('granularity', 'Data Granularity');
	}
	
	public function privacySettingsAction() {
	    return $this->getSettingsForPanel('privacy', 'Privacy Settings');
	}
	
	public function indexAction() {	
		$this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/javascript');
		
		$pageId = $this->getRequest()->getQuery('pageId', '');
		$embedded = $this->getRequest()->getQuery('embedded', '0');
		$requestsSeparated = $this->getRequest()->getQuery('requestsSeparated', '0');
		
		
		if (!empty($pageId)) {
			$viewModel = $this->forward()->dispatch('DevBarWebApi-1_8', array('action' => 'devBarGetRequestsInfo', 'pageId' => $pageId)); /* @var $viewModel \Zend\View\Model\ViewModel */
		} else {
			$viewModel = new ViewModel(array(
				'requests' => array(),
			));
		}
		
		$requestsParams = $viewModel->getVariables(); 
		
		if (isAzureEnv() || isZrayStandaloneEnv()) {
		    $viewModel->setVariable('notifications', array());
		} else {
		    $viewModel = $this->forward()->dispatch('NotificationsWebApi-1_6', array('action' => 'getNotifications')); /* @var $viewModel \Zend\View\Model\ViewModel */
		    $viewModel->setVariables($requestsParams);
		}
			
		$viewModel->setTerminal(true);
		$viewModel->setTemplate('dev-bar/index/index');
		
		$inIframe = (bool) $this->getRequest()->getQuery('iframe', '');
		$viewModel->setVariable('inIframe', $inIframe);
		
		// requestsSeparated - display the requests panel above the bar
		$viewModel->setVariable('requestsSeparated', $requestsSeparated ? 1 : 0);
		
		$disableByCookie = !$embedded && ($this->getRequest()->getCookie() !== false && $this->getRequest()->getCookie()->offsetExists('ZRayDisable'));
		
		// check that edition and user role have access to devbar webapi actions
		$disableByAcl = (! $this->isAclAllowedEdition('route:DevBarWebApi', 'devBarGetRequestsInfo'));
		
		$hideDevBar = false;
		// if disabled by cookie OR not allowed by acl - hide DevBar
		if ($disableByCookie || $disableByAcl) {
			$hideDevBar = true;
		}
		
		$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
		$isLoggedIn = $authService->hasIdentity();
		
		$config = $this->getServiceLocator()->get('Configuration');
		
		if (!$hideDevBar && !$isLoggedIn && $config['zray']['zend_gui']['enforceAccessControl']) {
			$elevate = false;
			
			/// retrieve the URL that was requested by the client
			$requests = $this->getServiceLocator()->get('DevBar\Db\RequestsMapper');
			$userRequest = $requests->getFirstRequests($pageId);
			$userRequestUrl = $userRequest->getUrl();
			$tokenExpired = false;
			
			/// enforce whitelist and token only if selective mode is on
			if ($this->getDirectivesMapper()->getDirectiveValue('zray.enable') == 2) {
				
			    $tokenMapper = $this->getServiceLocator()->get('DevBar\Db\AccessTokensMapper');
			    $allowedHosts = $tokenMapper->findAllowedHosts();
			    
			    if (! empty($allowedHosts)) {
    				$validator = new Access(array(
    					'allow_hosts' => $allowedHosts,
    				));
    				
    				if ($validator->isValid($this->getRequest()->getServer('REMOTE_ADDR'))) {
    					$elevate = true;
    				} else {
    					Log::err('Devbar will not be displayed: ' . current($validator->getMessages()));
    					$hideDevBar = true;
    				}
			    } else {
			        $elevate = true;
			    }

				/// if passed whitelist check, check token
				if ($elevate) {
					$tokenMapper = $this->getServiceLocator()->get('DevBar\Db\AccessTokensMapper');
					$token = $this->getRequest()->getQuery('token', '');
					
					$foundTokens = array();
					$foundToken = null;
					$tokenFound = false;
					if (! empty($token)) {
    					$foundToken = $tokenMapper->findTokenByHash($token);
    					if ($foundToken->getId()) {
    					   $tokenFound = true;
    					   $foundTokens[] = $foundToken;
    					}
					} else {
					    $tokenFound = true;
					    $allTokens = $tokenMapper->findTokens();
					    foreach ($allTokens as $token) {
					        if (empty($token->getToken())) {
					           $foundTokens[] = $token;
					        }
					    }
					}
					
					if ($tokenFound) {
					    $messages = array();
					    $innerElevate = false;
					    $innerHideDevBar = false;
					    foreach ($foundTokens as $foundTokenRow) {
					        $innerElevate = false;
					        $innerHideDevBar = false;
					        $messages = array();
    						if ($foundTokenRow->getTtl() > time()) {
    							$innerElevate = true;
    						} else {
    							$messages[] = 'No statistics collected: Token has expired';
    							$innerElevate = false;
    							$tokenExpired = true;
    						}
    						
    						$validator = new Access(array(
    							'allow_hosts' => $foundTokenRow->getAllowedHosts(),
    						));
    						
    						if ($innerElevate && $validator->isValid($this->getRequest()->getServer('REMOTE_ADDR'))) {
    							$innerElevate = true;
    						} else {
    							$messages[] = 'Z-Ray will not be displayed: ' . current($validator->getMessages());
    							$innerElevate = false;
    							$innerHideDevBar = true;
    						}
    						
    						/// enforce baseurl for the token, if one is set
    						if ($innerElevate && $foundTokenRow->getBaseUrl()) {
    							$validator = new BaseUrlAccess(array('baseUrl' => $foundTokenRow->getBaseUrl()));
    							
    							if ($validator->isValid($userRequestUrl)) {
    								$innerElevate = true;
    							} else {
    								$messages[] = 'Z-Ray will not be displayed: ' . current($validator->getMessages());
    								$innerElevate = false;
    								$innerHideDevBar = true;
    							}
    						}
    						
    						if ($innerElevate) {
    						    $foundToken = $foundTokenRow;
    						    break;
    						}
					    }
					    // apply last token result
					    $elevate = $innerElevate;
					    $hideDevBar = $innerHideDevBar;
					    // log messages
					    foreach ($messages as $message) {
					       Log::err($message);
					    }
					} else {
						Log::err('Z-Ray will not be displayed: Token not found or supplied token is invalid');
						$elevate = false;
						$hideDevBar = true;
					}
				}
			} else {
				$elevate = true;
			}
			
			if (isset($foundToken) && $foundToken instanceof AccessTokenContainer) {
				$userRequestUrlFiltered = str_replace('zsdbt='.$foundToken->getToken(), '', $userRequestUrl);
				$tokenId = $foundToken->getId();
			} else {
				$userRequestUrlFiltered = $userRequestUrl;
				$tokenId = 'No Token';
			}
			
            if ($elevate) {
				if ($this->elevateRole()) {
				    if (! isAzureEnv() && !isZrayStandaloneEnv()) {
					   $this->auditMessage(Mapper::AUDIT_DEVBAR_ACCESS_ELEVATE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array('tokenId' => $tokenId, 'baseUrl' => $userRequestUrlFiltered)), $userRequestUrlFiltered);
				    }
				}
			} else {
				$this->demoteRole();
				if (! isAzureEnv() && !isZrayStandaloneEnv()) {
					$this->auditMessage(Mapper::AUDIT_DEVBAR_ACCESS_ELEVATE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('tokenId' => $tokenId, 'baseUrl' => $userRequestUrlFiltered)), $userRequestUrlFiltered);
				}
			}
			$viewModel->setVariable('useCredentials', true);
		} else { /// if devbar access control is disabled, do not send session cookies to server
			$viewModel->setVariable('useCredentials', false);
		}
		
		$viewModel->setVariable('tokenExpired', isset($tokenExpired) ? $tokenExpired : false);
		
		$blockedExtensions = array();
		
		$licenseMapper = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper'); /* @var $licenseMapper \Configuration\License\ZemUtilsWrapper */
		$licenseInfo = $licenseMapper->getLicenseInfo();
		$edition = $licenseInfo->getEdition();
		
		$profile = Module::config('package', 'zend_gui', 'serverProfile');
		if (strtolower($profile) == 'development') {
		    if (strpos(strtolower($edition), 'enterprise') === false) {
		        $blockedExtensions['xmltoolkit'] = 'Enterprise';
		    }
		} else {
		    if (strpos(strtolower($edition), 'enterprise') === false && strpos(strtolower($edition), 'professional') === false) {
		        $blockedExtensions['xmltoolkit'] = 'Professional and Enterprise';
		    }
		}
		
		$viewModel->setVariable('blockedExtensions', $blockedExtensions); 
		
		if (! $hideDevBar) {
			$licenseExpired = $licenseInfo->isLicenseExpired();
			$viewModel->setVariable('licenseExpired', $licenseExpired);
			
			$bootstrapCompleted = \Application\Module::config('bootstrap', 'completed') == '1';
			$viewModel->setVariable('bootstrapCompleted', $bootstrapCompleted);
			
			$viewModel->setVariable('baseUrl', $this->getRequest()->getQuery('url', ''));
			$viewModel->setVariable('host', $this->getRequest()->getQuery('host', ''));
			
			$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
			$devBarShowInIframe = $embedded || (bool) Module::config('zray', 'zend_gui', 'showInIframe');
			$viewModel->setVariable('showInIframe', $devBarShowInIframe);
			$maxRequests = Module::config('zray', 'zend_gui', 'maxRequests');
			$maxElementsPerLevel = Module::config('zray', 'zend_gui', 'maxElementsPerLevel');
			$maxElementsInTree = Module::config('zray', 'zend_gui', 'maxElementsInTree');
			$disableActions = $directivesMapper->selectSpecificDirectives(array('zray.disable_actions'))->current()->getFileValue();
			
			$customData = array();
			$this->getEventManager()->addIdentifiers('devbar');
			$requestViews = $this->getEventManager()->trigger('DevBarModulesLeft');
			$customData = $this->getEventManager()->trigger('DevBarModules');
			$settingsViews = $this->getEventManager()->trigger('DevBarModulesRight');
			
			// fetch all custom data config
			$customDataConfig = array();
			foreach ($customData as $custom) {
			    if ($custom instanceof \Zend\View\Model\ViewModel) {
    				$customVariables = $custom->getVariables();
    				if ($customVariables instanceof \Zend\View\Variables) {
    					$customConfig = $customVariables->getArrayCopy();
    					if (isset($customConfig['params']['name'])) {
    						$customDataConfig[$customConfig['params']['extensionName']][$customConfig['params']['name']] = array('params' => $customConfig['params']);
    					}
    				}
			    }
			}
			
			$viewModel->setVariable('customDataConfig', $customDataConfig);
			
			$viewModel->setVariable('requestViews', $requestViews);
			$viewModel->setVariable('settingsViews', $settingsViews);
			$viewModel->setVariable('customData', $customData);
			$viewModel->setVariable('maxRequests', $maxRequests);
			$viewModel->setVariable('maxElementsPerLevel', $maxElementsPerLevel);
			$viewModel->setVariable('maxElementsInTree', $maxElementsInTree);
			$viewModel->setVariable('disableActions', $disableActions);
			
			// get studio integration settings
			$viewModel->setVariable('ideConfig', array(
			    'studioAutoDetection'        => Module::config('studioIntegration', 'zend_gui', 'studioAutoDetection'),
			    'studioAutoDetectionEnabled' => Module::config('studioIntegration', 'zend_gui', 'studioAutoDetectionEnabled'),
			    'studioAutoDetectionPort'    => Module::config('studioIntegration', 'zend_gui', 'studioAutoDetectionPort'),
			    'studioBreakOnFirstLine'     => Module::config('studioIntegration', 'zend_gui', 'studioBreakOnFirstLine'),
			    'studioClientTimeout'        => Module::config('studioIntegration', 'zend_gui', 'studioClientTimeout'),
			    'studioHost'                 => Module::config('studioIntegration', 'zend_gui', 'studioHost'),
			    'studioPort'                 => Module::config('studioIntegration', 'zend_gui', 'studioPort'),
			    'studioUseRemote'            => Module::config('studioIntegration', 'zend_gui', 'studioUseRemote'),
			    'studioUseSsl'               => Module::config('studioIntegration', 'zend_gui', 'studioUseSsl'),
			));
			
		    if (isAzureEnv() || isZrayStandaloneEnv()) {
			    $viewModel->setVariable('zendDebuggerEnabled', false);
				$viewModel->setVariable('zendCodeTracingEnabled', false);
			} else {
				// check if zend debugger is enabled
			     $zendDebuggerExtension = $this->getExtensionsMapper()->selectExtension('Zend Debugger');
			     $viewModel->setVariable('zendDebuggerEnabled', ($zendDebuggerExtension->getStatus() == 'Loaded'));
				 
				 // check if zend debugger is enabled
				$zendCodeTracingExtension = $this->getExtensionsMapper()->selectExtension('Zend Code Tracing');
				$viewModel->setVariable('zendCodeTracingEnabled', ($zendCodeTracingExtension->getStatus() == 'Loaded'));
			}
			
			// get studio integration settings
			$viewModel->setVariable('ideConfig', array(
			    'studioAutoDetection'        => Module::config('studioIntegration', 'zend_gui', 'studioAutoDetection'),
			    'studioAutoDetectionEnabled' => Module::config('studioIntegration', 'zend_gui', 'studioAutoDetectionEnabled'),
			    'studioAutoDetectionPort'    => Module::config('studioIntegration', 'zend_gui', 'studioAutoDetectionPort'),
			    'studioBreakOnFirstLine'     => Module::config('studioIntegration', 'zend_gui', 'studioBreakOnFirstLine'),
			    'studioClientTimeout'        => Module::config('studioIntegration', 'zend_gui', 'studioClientTimeout'),
			    'studioHost'                 => Module::config('studioIntegration', 'zend_gui', 'studioHost'),
			    'studioPort'                 => Module::config('studioIntegration', 'zend_gui', 'studioPort'),
			    'studioUseRemote'            => Module::config('studioIntegration', 'zend_gui', 'studioUseRemote'),
			    'studioUseSsl'               => Module::config('studioIntegration', 'zend_gui', 'studioUseSsl'),
			));
			
			// check if zend debugger is enabled
            if(!isAzureEnv() && !isZrayStandaloneEnv()) {
                $zendDebuggerExtension = $this->getExtensionsMapper()->selectExtension('Zend Debugger');
                $viewModel->setVariable('zendDebuggerEnabled', ($zendDebuggerExtension->getStatus() == 'Loaded'));
			
                // check if zend debugger is enabled
                $zendCodeTracingExtension = $this->getExtensionsMapper()->selectExtension('Zend Code Tracing');
                $viewModel->setVariable('zendCodeTracingEnabled', ($zendCodeTracingExtension->getStatus() == 'Loaded'));
            } else {
                $viewModel->setVariable('zendCodeTracingEnabled', false);
                $viewModel->setVariable('zendDebuggerEnabled', false);

            }
			
			// set studio integration timeout
			if (!isAzureEnv() && !isZrayStandaloneEnv()) {
			     $studioClient = $this->getServiceLocator()->get('DevBar\Producer\StudioIntegration');
			     $viewModel->setVariable('studioClientTimeout', $studioClient->getStudioConfig()->getTimeout());
			}
			
			$collapseShortcuts = \Application\Module::config('zray', 'zend_gui', 'collapse');
			$viewModel->setVariable('shortcuts', array('collapse' => $collapseShortcuts));
		}
		
		if ($hideDevBar) {
			return $this->getResponse()->setContent('');
		}
		
		return $viewModel;
	}
	
	public function loadAssetAction() {
		$params = $this->getParameters();
	
		$requestId = $params['requestId'];
		$extension = $params['extension'];
		$assetName = $params['asset'];
	
		$requestsMapper = $this->getServiceLocator()->get('DevBar\Db\RequestsMapper');
		$request = $requestsMapper->getRequest($requestId);
	
		if (! $request->getId()) {
			throw new Exception('Request not found');
		}
	
		$extensionsMetadataMapper = $this->getServiceLocator()->get('DevBar\Db\ExtensionsMetadataMapper');
		$assetContent = $extensionsMetadataMapper->loadAssetFile($requestId, $extension, $assetName);
		$assetMime = $extensionsMetadataMapper->assetMime($requestId, $extension, $assetName);
	
		$response = $this->getResponse(); /* @var $response \Zend\Http\Response */
		$response->setContent($assetContent);
		$response->getHeaders()->addHeaderLine('Content-Type', $assetMime);
		return $response;
	}
	
	public function SSLTestAction() {
		$this->Layout('layout/login.phtml');
		return array();
	}
	
	/**
	 * @param string $url
	 * @return array
	 */
	private function getSqlQueries($url) {
		$filename = FS::createPath(getCfgVar('zend.temp_dir'), md5($url).'.sql');
		if (FS::fileExists($filename)) {
			$result = file($filename);
			return $result === false ? array() : $result;
		}
		return array();
	}
	
	
	private function getLogEntries($requestId) {
		$logEntriesMapper = $this->getLocator()->get('DevBar\Db\LogEntriesMapper');
		$logEntries = $logEntriesMapper->getEntries($requestId);
		return $logEntries->toArray();
	}
	
	private function getMonitorEvents($requestId) {
		$monitorEventsMapper = $this->getLocator()->get('DevBar\Db\MonitorEventsMapper');
		$monitorEvents = $monitorEventsMapper->getMonitorEvents($requestId);
		return $monitorEvents->toArray();
	}
	
	private function getSettingsForPanel($panelType, $pageTitle) {
	    $viewModel = new ViewModel($this->settingsAction());
	
	    $viewModel->setVariable('panel', $panelType);
	    $viewModel->setVariable('pageTitle', $pageTitle);
	    $viewModel->setTemplate('dev-bar/index/settings');
	
	    return $viewModel;
	}
}
