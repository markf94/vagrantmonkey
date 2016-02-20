<?php
namespace Deployment\Controller;

use Deployment\Validator\ApplicationBaseUrlNotExists;

use Audit\Db\Mapper;

use Zend\Uri\UriFactory;
use ZendServer\Mvc\Controller\WebAPIActionController;

use Zend\Mvc\Controller\ActionController,
ZendServer\Log\Log,
ZendServer\FS\FS,
Zend\Validator,
Deployment\Model,
WebAPI,
Audit\Db\Mapper as auditMapper,
Audit\Db\ProgressMapper,
Deployment\SessionStorage,
Zsd\Db\TasksMapper,
ZendServer\Exception;

use Deployment\Validator\ApplicationNameNotExists;

use Zend\View\Model\ViewModel;
use Deployment\InputFilter\Factory;
use Deployment\Application\Container;
use ZendServer\Set;
use Vhost\Entity\VhostNode;
use Zend\Http\PhpEnvironment\Response;

class WebAPIController extends WebAPIActionController
{
	const DEMO_APP_NAME = 'ZendDemoApp';	
	const SAMPLES_APP_NAME = 'ZendSamplesApp';	
	
	public function applicationCancelPendingDeploymentAction() {
		$this->isMethodPost();
		
		$wizardId = $this->getRequest()->getQuery('wizardId', 0);
		
		try {
			$sessionStorage = new SessionStorage($wizardId);
			$path = $sessionStorage->getPackageFilePath();
			
			if (file_exists($path)) {
				unlink($path);
			}
		} catch (Exception $e) {
			// Do nothing
		}
		
		return array();
	}
	
	public function deploymentDownloadFileAction() {
		$this->isMethodPost();
	
		$params = $this->getParameters(array('override' => 'FALSE', 'version' => ''));
		$this->validateMandatoryParameters($params, array('url', 'name'));
		$url = $this->validateUri($params['url'], 'url');
		
		$override = $this->validateBoolean($params['override'], 'override');
		
		$download = null;
		try {
			$download = $this->getDeploymentDbMapper()->findByUrl($url);
		} catch (Exception $e) {
			// download wasn't found, create new download task
			$this->getLocator()->get('Deployment\Mapper\Deploy')->downloadFile(null, null, $url, array('url' => $url, 'name' => $params['name'], 'version' => $params['version']));
		}
		
		// download was found
		if (! is_null($download)) {	
			// check if needs to be overridden
			if ($override) {
				$this->getDeploymentDbMapper()->deleteByUrl($url);
				// the download task was found. override the task
				$this->getLocator()->get('Deployment\Mapper\Deploy')->downloadFile(null, null, $url, array('url' => $url, 'name' => $params['name'], 'version' => $params['version']));
			} else { // throw exception
				throw new \WebAPI\Exception(_t("Download request already exists \'%s\'", array($url)), \WebAPI\Exception::DEPLOYMENT_DOWNLOAD_ALREADY_EXISTS);
			}
		}
	
		$this->setHttpResponseCode(Response::STATUS_CODE_202);
		return array();
	}
	
	public function deploymentDownloadFileStatusAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('url'));
		$url = str_replace('%20', ' ', $params['url']);
		
		try {
			/* @var Deployment\Db\Mapper */
			$deploymentMapper = $this->getDeploymentDbMapper();
			$download = $deploymentMapper->findByUrl($url);
			
			// reached final step - remove from db table
			if ($download['status'] == \Deployment\Db\Mapper::STATUS_OK || $download['status'] == \Deployment\Db\Mapper::STATUS_ERROR) {
				$this->getDeploymentDbMapper()->deleteByUrl($url);
			} 
		} catch (Exception $e) {
			throw new \WebAPI\Exception(_t("Download failed. ") . $e->getMessage(), \WebAPI\Exception::DEPLOYMENT_DOWNLOAD_NOT_EXISTS); 
		}
		
		return array('download' => $download);
	}
	
	public function changeApplicationNameAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('application', 'name'));
		
		$this->validateInteger($params['application'], 'application');
		$name = $this->validateString($params['name'], 'name');
		
		$factory = new Factory();
		$factory->setDeploymentModel($this->getDeploymentMapper());
		$validators = $factory->createInputFilter(array());
		$validators->setData(array('displayName' => $name));
		$validators->setValidationGroup('displayName');
		
		if (! $validators->isValid()) {
			$validatorMessages = current($validators->getMessages());
			/// special handling for already-existing application name - studio requires us to return an 409 error
			if (isset($validatorMessages[ApplicationNameNotExists::APP_NAME_EXISTS])) {
				throw new \WebAPI\Exception(
						_t('Invalid userAppName parameter: %s', array(current($validatorMessages))),
						\WebAPI\Exception::APPLICATION_CONFLICT);
			}
			
			throw new \WebAPI\Exception(
					_t('Invalid userAppName parameter: %s', array(current($validatorMessages))),
					\WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$deployedApplication = $this->getDeploymentMapper()->getApplicationById($params['application']);
		$this->getDeploymentMapper()->setApplicationName($deployedApplication->getApplicationId(), $deployedApplication->getBaseUrl(), $name);
		
		
		$deploymentMapper = $this->getLocator('Deployment\FilteredAccessMapper'); /* @var $deploymentMapper \Deployment\FilteredAccessMapper */
		$deployedApplications = $deploymentMapper->getMasterApplicationsByIds(array($params['application']));
		$deployedApplications->setHydrateClass('\Deployment\Application\Container');
		
		$servers = array();
		foreach ($deployedApplications as $application) { /* @var $application \Deployment\Application\Container */
			$appId = $application->getApplicationId();
			$servers[$appId] = $this->getDeploymentMapper()->getServersStatusByAppId($appId);
		}

		return array('applications' => $deployedApplications, 'servers' => $servers);
	}
	
	public function applicationGetDetailsAction() {				
		$this->isMethodGet();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('application'));
		
		$this->validateInteger($params['application'], 'application');
		
		$deployedApplication = $this->getDeploymentMapper()->getApplicationById($params['application']);
		if (!($deployedApplication instanceof Container)) {
			throw new \WebAPI\Exception(_t('Application \'%s\' does not exist', array($params['application'])), \WebAPI\Exception::APPLICATION_DOES_NOT_EXISTS);
		}

		$prerequisites = '';
		$metadata = $deployedApplication->getPackageMetaData();
		if ($metadata instanceof \ZendDeployment_PackageMetaData_Interface) {
			$prerequisites = $metadata->getPrerequisites();
			$prerequisites = preg_replace('/[\s]+/', '', $prerequisites);
		}

		$servers = $this->getDeploymentMapper()->getServersStatusByAppId($params['application']);
		$serversIds = $this->getServersMapper()->findRespondingServersIds();
		// if the server is not responding we exclude it from the list. Bug #ZSRV-9670
		foreach ($servers as $id => $serverData) {
			if (! in_array($id, $serversIds)) {
				unset($servers[$id]);		
			}
		}
		
		return array (
				'application' => $deployedApplication,
				'prerequisites' => $prerequisites,
				'servers' => $servers,
		);
	}

	public function applicationGetStatusAction() {	
		$this->isMethodGet();
		$params = $this->getParameters(
				array('applications' => array(), 'direction' => 'ASC')
		);
		
		$applications = $this->validateArray($params['applications'], 'applications');
		foreach ($applications as $idx=>$application) {
			$this->validateString($application, "applications[{$idx}]");
		}
		$deploymentMapper = $this->getLocator('Deployment\FilteredAccessMapper'); /* @var $deploymentMapper \Deployment\FilteredAccessMapper */
		$deployedApplications = $deploymentMapper->getMasterApplicationsByIds($applications, $params['direction']);
		$deployedApplications->setHydrateClass('\Deployment\Application\Container');
		
		$servers = array();
		$keys = array();
		// collect the applications ids
		foreach ($deployedApplications as $appContainer) {
			$keys[] = $appContainer->getApplicationId();
		}
		$servers = $this->getDeploymentMapper()->getServersStatusByAppIds($keys);
		
		return array('applications' => $deployedApplications, 'servers' => $servers, 'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers()));
	}
	
	public function redeployAllApplicationsAction() {
        $this->validateLicenseValid();
		$params = $this->getParameters(
			array(
				'servers' => array(0),
			)
		);
		
		$this->validateArray($params['servers'], 'servers');
		foreach($params['servers'] as $key => $server) {
			$this->validateInteger($server, "servers[$key]");
		}

		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_REDEPLOY_ALL, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $auditMessage \Audit\Container */
			$this->getDeploymentMapper()->redeployAllApplications($params['servers']);
		} catch (\ZendServer\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$deployedApplications = $this->getDeploymentMapper()->getMasterApplications();
		$deployedApplications->setHydrateClass('\Deployment\Application\Container');
		$this->setHttpResponseCode('202', 'Accepted');
		
		$appServers = array();
		foreach ($deployedApplications as $app) { /* @var $app \Deployment\Application\Container */
			$appServers[$app->getApplicationId()] = $this->getDeploymentMapper()->getServersStatusByAppId($app->getApplicationId());
		}
	
		return array('applications' => $deployedApplications, 'servers' => $appServers);
	}
	
	public function applicationDefineAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		
		$params = $this->getParameters(
				array(
						'name' => '',
						'baseUrl' => '',
						'version' => '',
						'healthCheck' => '',
						'logo' => ''
				)
		);
		
		$this->validateMandatoryParameters($params, array('name', 'baseUrl'));
		
		$name = $params['name'];
		if ($name) {
			$validator = new ApplicationNameNotExists(array(), $this->getLocator('Deployment\Model'));
			if (!$validator->isValid($name)) {
				throw new WebAPI\Exception(_t("Application named %s already exists", array($name)), WebAPI\Exception::INVALID_PARAMETER);
			}
			
			// display name validation
			$this->validateRegex($name, \Deployment\InputFilter\Factory::APPLICATION_DISPLAY_NANE_VALIDATION_REGEX, 'name');
		}
		
		$baseUrl = $params['baseUrl'];
		$validatorBaseUrl = new ApplicationBaseUrlNotExists(array(), $this->getLocator('Deployment\Model'));
		if (!$validatorBaseUrl->isValid($baseUrl)) {
			throw new WebAPI\Exception(_t("Application base url '%s' already exists", array($baseUrl)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$version = $params['version'];
		$healthCheck = $params['healthCheck'];
		$logo = $params['logo'];
		if (! empty($logo)) {
			$theLogo = file_get_contents(urldecode($logo));
		} else {
			$theLogo = '';
		}
		
		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_DEFINE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array('baseUrl' => $baseUrl, 'name' => $name))); /* @var $auditMessage \Audit\Container */
			$baseUrl = rtrim($baseUrl, '/');
			// check if the base url is already occuped by another application
			if (! is_null($this->getDeploymentMapper()->getApplicationByBaseUrl($baseUrl))) {
				throw new \Deployment\Exception(_t(': an application with the same Base URL already exists'));
			}
			$this->getDeploymentMapper()->defineApplication($baseUrl, $name, $version, $healthCheck, $theLogo);
			$deployedApplication = $this->getDeploymentMapper()->getApplicationByBaseUrl($baseUrl);
			if (is_null($deployedApplication)) {
				throw new \Deployment\Exception(_t('Define operation failed, the application was not created'));
			}
		} catch (\Exception $e) {
			Log::err("Deployment failed: " . $e->getMessage());
			if ($auditMessage) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			} else {
				$this->auditMessage(auditMapper::AUDIT_APPLICATION_DEFINE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			}
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
			throw new WebAPI\Exception(_t('Deployment failed %s', array($e->getMessage())),	WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY,
				array(	array(_t('Application name: %s', array($deployedApplication->getUserApplicationName()))),
						array(_t('Base URL: %s', array($deployedApplication->getBaseUrl())))));
		
		Log::info("Application has been deployed");		
		
		$serversIds = $this->getServersMapper()->findRespondingServersIds();
		$this->getTasksMapper()->insertTasksServers($serversIds, TasksMapper::COMMAND_APPS_LIST_UPDATED);
		$this->getLocator()->get('MonitorRules\Model\Tasks')->syncMonitorRulesChanges($serversIds);
		$this->setHttpResponseCode('202', 'Accepted');
		
		$servers = $this->getDeploymentMapper()->getServersStatusByAppId($deployedApplication->getApplicationId());
		$viewModel = new ViewModel(array('application' => $deployedApplication, 'servers' => $servers));
		$viewModel->setTemplate('deployment/web-api/application-info');
		return $viewModel;
	}
	
	/**
	 * Deploy a new application to the server or cluster. 
	 * This process is asynchronous - the initial request will wait until the application is 
	 * uploaded and verified, and the initial response will show information about the application 
	 * being deployed - however the staging and activation process will proceed after the response 
	 * is returned. The user is expected to continue checking the application 
	 * status using the applicationGetStatus method until the deployment process is complete.
	 * 
	 * Parameters:
	 *  appPackage - Required. Application package file. 
	 *	  Content type for the file must be `application/vnd.zend.applicationpackage`.
	 *  baseUrl - Required. Base URL to deploy the application to. Must be an HTTP URL.
	 *  createVhost - Create a virtual host based on the base URL if such a virtual host 
	 *	  wasn't already created by Zend Server. Default is FALSE
	 *  defaultServer - Deploy the application on the default server; the base URL host provided will be ignored and replaced with <default-server>.  
	 *	  In case of a conjunction of this parameter and createVhost, the latter will be ignored.
	 *	  Default is FALSE
	 *  userAppName - String. Free text for user defined application identifier; if not specified, the baseUrl parameter will be used
	 *  ignoreFailures - Boolean. Ignore failures during staging if only some servers reported failures; If all servers report failures the operation will fail in any case. 
	 *	  The default value is FALSE - meaning any failure will return an error
	 *  userParams - Hashmap. Set values for user parameters defined in the package; Depending on package definitions, this parameter may be required; 
	 *	  Each user parameter defined in the package must be provided as a key for this parameter	
	 *	  
	 */
	public function applicationDeployAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(
				array(
						'ignoreFailures' => 'FALSE',
						'createVhost' => 'FALSE',
						'defaultServer' => 'FALSE',
						'userParams' => array(),
						'userAppName' => '',
				)
		);		
			   
	    $this->validateMandatoryParameters($params, array('baseUrl'));
	    // fixed bug #ZSRV-14114, when the baseUrl contains * as host and default server is FALSE - convert the baseUrl to <default-server>
	    if (strstr($params['baseUrl'], '*')) {
			$params['defaultServer'] = 'TRUE';
		}
		
		$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
		$uploaddir = $this->getGuiTempDir();
		$fileTransfer = $this->setFileTransfer();
		
		$deployedApplication = $this->deployApplication($params, $fileTransfer->getFilename());
		
		Log::info("Application has been deployed");
		$this->setHttpResponseCode('202', 'Accepted');
		
		$servers = $this->getDeploymentMapper()->getServersStatusByAppId($deployedApplication->getApplicationId());
		$viewModel = new ViewModel(array('application' => $deployedApplication, 'servers' => $servers));
		$viewModel->setTemplate('deployment/web-api/application-info');
		return $viewModel;
	}
	
	/**
	 * @param array $validatorMessages
	 */
	protected function validateAppnameConflict($validatorMessages) {
    	//appName conflict validation begins only on api version >= 1.9
    	return true;
	}
	
	/**
	 * @param array $params
	 * @param string $filename
	 */
	private function deployApplication($params, $filename) {
		$baseUrl = $params['baseUrl'];
		$this->validateBaseUrl($params['baseUrl']);
		
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
		$createVhost = $this->validateBoolean($params['createVhost'], 'createVhost');
		$defaultServer = $this->validateBoolean($params['defaultServer'], 'defaultServer');
		$userParams = $params['userParams'];
		$this->validateUserParams($params['userParams']);
		
		$userAppName = $params['userAppName'];
		$this->validateString($params['userAppName'], 'userAppName');
		
		$factory = new Factory();
		$factory->setDeploymentModel($this->getDeploymentMapper());
		$validators = $factory->createInputFilter(array());
		$validators->setData(array('displayName' => $userAppName));
		$validators->setValidationGroup('displayName');
		
		if (! $validators->isValid()) {
			$validatorMessages = current($validators->getMessages());
			/// special handling for already-existing application name - studio requires us to return an 409 error
			if(!$this->validateAppnameConflict($validatorMessages)){
			    throw new \WebAPI\Exception(
			        _t('Invalid userAppName parameter: %s', array(current($validatorMessages))),
			        \WebAPI\Exception::APPLICATION_CONFLICT);
			}
				
			throw new \WebAPI\Exception(
					_t('Invalid userAppName parameter: %s', array(current($validatorMessages))),
					\WebAPI\Exception::INVALID_PARAMETER);
		}
		
		// if the user application name is empty set it to the baseUrl, by default
		if (! $userAppName) {
			$userAppName = $baseUrl;
		}
		
		// defaultServer = TRUE 	=> replace server name with <default-server>
		if ($defaultServer) {
			$baseUrl = $this->getDeploymentMapper()->convertUriToDefaultServer($baseUrl);
		}
		Log::debug('BaseUrl is ' . $baseUrl);
		// @todo: Is component loaded?
		// @todo: Verify has target? Check if zend server is responding?
		
		$application = $this->getDeploymentMapper()->getApplicationByBaseUrl($baseUrl);
		// @todo: Check if application isBeingDeployed or isBeingRolledback
		// In case it does, set status 202 (Accepted) in response and return application
		
		// @todo: Check if application isBeingRemoved, return exception if it does
		
		// @todo: Check if application already exists
		if ($application) {
			// @todo: Check if application status is not 'NOT_EXISTS'
			
			/**
			 * @link https://il-jira/browse/ZSRV-9090 added specific message for applications from define suggestions box
			 */
			if ($application->getAppStatus() != \ZendDeployment_Application_Interface::STATUS_INTEGRATION_CANDIDATE) {
				Log::err('Application with baseUrl ' . $baseUrl . ' already exists');
				throw new WebAPI\Exception(
						_t("This application has already been installed"),
						WebAPI\Exception::BASE_URL_CONFLICT
				);
			} else {
				$this->getDeploymentMapper()->removeIntegrationCandidateApplication($application->getApplicationId());
			}
		}
		
		$deploymentPackage = $this->generatePackage($filename);
		$this->getDeploymentMapper()->validatePackage($filename);
		$userParams = $this->processUserParams($userParams, $deploymentPackage);
		$this->validateRequiredParams($baseUrl, $deploymentPackage, $userParams); // as of some issues with ZF2 B4, we validate that all mandatory params were passed and are not empty
		$form = $this->validateParams($userParams, $baseUrl, $deploymentPackage);

		if ($createVhost) {
			Log::notice('\'createVhost\' parameter is deprecated and may become unusable in future versions'); 
			
			if (strpos($baseUrl, 'https://') === 0) {
				throw new WebAPI\Exception('HTTPS or SSL secure virtual hosts may only be created using vhost management actions', WebAPI\Exception::BASE_URL_CONFLICT);
			}
		}
		
		$zendParams = $this->getDeploymentMapper()->createZendParams($userAppName, $ignoreFailures, $baseUrl, $createVhost, $defaultServer, false);
		
		$deploymentPackage = $this->getDeploymentMapper()->storePendingDeployment(
				$filename,
				$userParams,
				$zendParams
		);
		
		if (file_exists($filename)) {
		    unlink($filename);
		}
		
		try {
			$prerequisites = $deploymentPackage->getPrerequisites();
			$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
			$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
			$configurationContainer->createConfigurationSnapshot(
					$configuration->getGenerator()->getDirectives(),
		    		$configuration->getGenerator()->getExtensions(),
		    		$configuration->getGenerator()->getLibraries(),
		    		$configuration->getGenerator()->needServerData());
		} catch (\Exception $e) {
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
			throw new WebAPI\Exception('Package prerequisites could not be validated: ' . $e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		if (! $configuration->isValid($configurationContainer)) {
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
			$messagesFilter = new \Prerequisites\MessagesFilter();
			$messages = $messagesFilter->filter($configuration->getMessages());
			Log::err(print_r($this->flattenMessagesArray($messages), true));
			throw new WebAPI\Exception(PHP_EOL . implode(PHP_EOL, $this->flattenMessagesArray($messages)), WebAPI\Exception::UNMET_DEPENDENCY);
		}
		
		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_DEPLOY,
                ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array('Application name: '. $userAppName), 'userParams' => $userParams, 'zendParams' => $zendParams), $baseUrl); /* @var $auditMessage \Audit\Container */
			$this->getLocator()->get('Deployment\Mapper\Deploy')->deployApplication($baseUrl);

            $deployedApplication = $this->getDeploymentMapper()->getApplicationByBaseUrl($baseUrl);
		} catch (\Deployment\Exception $e) {
			Log::err("Deployment failed: " . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
					'errorMessage' => $e->getMessage()));
			if ($e->getCode() == \Deployment\Exception::VHOST_NOT_FOUND) {
				throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::MISSING_VIRTUAL_HOST);
			}
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
					'errorMessage' => $e->getMessage()));
			Log::err("Deployment failed: " . $e->getMessage());
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
			throw new WebAPI\Exception(
					_t('Deployment failed %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		return $deployedApplication;
	}

	
	/**
	 * Update an existing application.
	 * The package provided must be of the same application. Additionally any new parameters or new values to existing parameters must be provided. 
	 * This process is asynchronous - the initial request will wait until the package is uploaded and verified, and the initial response will show information about the new version being deployed.
	 * However, the staging and activation process will proceed after the response is returned. 
	 * The user is expected to continue checking the application status using the applicationGetStatus method until the deployment process is complete.
	 * 
	 * Parameters:
	 * 	appId - Required. Application ID to update. 
	 *  appPackage - Required. Application package file.
	 *	  Content type for the file must be `application/vnd.zend.applicationpackage`.
	 *  ignoreFailures - Boolean. Ignore failures during staging if only some servers reported failures; If all servers report failures the operation will fail in any case. 
	 *	  The default value is FALSE - meaning any failure will return an error
	 *  userParams - Hashmap. Set values for user parameters defined in the package; Any required parameters that were not defined in previous deployments of the same application will be required. 
	 *  	Any parameters with already defined values are not required, but may be specified again if a new value is to be set.
	 *	  Each user parameter defined in the package must be provided as a key for this parameter	 *	  
	 */
	public function applicationUpdateAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(
				array(
						'ignoreFailures' => 'FALSE',
						'userParams' => array(),
				)
		);

		Log::info('WebAPI applicationUpdate was called');		
		$this->validateMandatoryParameters($params, array('appId'));
		
		$appId = $params['appId'];
		$this->validateInteger($appId, 'appId');
		
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
		$userParams = $params['userParams'];
		$this->validateUserParams($params['userParams']);

		// @todo: Is component loaded?
		// @todo: Verify has target? Check if zend server is responding?

		if (!($existingApplication = $this->getDeploymentMapper()->getApplicationById($appId))) {
			Log::err("applicationUpdate failure - 'appId' $appId does not exist");
			throw new WebAPI\Exception(
					_t('Application with \'appId\' "%s" does not exist', array($appId)),
					WebAPI\Exception::NO_SUCH_APPLICATION
			);			
		}

		$baseUrl = $existingApplication->getBaseUrl();
		$fileTransfer = $this->setFileTransfer();
		
		// @todo: Check if application isBeingDeployed or isBeingRolledback or isBeingRemoved
		
		$zendParams = $this->getDeploymentMapper()->createZendParams($existingApplication->getUserApplicationName(), $ignoreFailures, $existingApplication->getBaseUrl());
		
		$oldUserParams = $existingApplication->getUserParams();
		if (is_array($oldUserParams)) {
			$userParams = array_merge($oldUserParams, $userParams);
			Log::debug('Merging new user parameters with the values found in the existing application');// @todo - translate
		}
		
		$filename = $fileTransfer->getFilename();
		$deploymentPackage = $this->getDeploymentMapper()->storePendingDeployment(
				$filename,
				$userParams,
				$zendParams
		);
		
		if (file_exists($filename)) {
		    unlink($filename);
		}
		
		if (($newAppName = $deploymentPackage->getPackageFile()->getName()) != ($orgAppName = $existingApplication->getApplicationName())) {
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
			throw new WebAPI\Exception(
							_t('An upgrade can only be executed for the same application. You can not upgrade "%s" to "%s"', array($orgAppName, $newAppName)),
							WebAPI\Exception::APPLICATION_CONFLICT
			);
		}   	
		
		$userParams = $this->processUserParams($userParams, $deploymentPackage);
		$this->validateRequiredParams($baseUrl, $deploymentPackage, $userParams); // as of some issues with ZF2 B4, we validate that all mandatory params were passed and are not empty
		$form = $this->validateParams($userParams, $baseUrl, $deploymentPackage);		
		
		
	    try {
		    $prerequisites = $deploymentPackage->getPrerequisites();
		    $configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
		    $configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
		    $configurationContainer->createConfigurationSnapshot(
		    		$configuration->getGenerator()->getDirectives(),
		    		$configuration->getGenerator()->getExtensions(),
		    		$configuration->getGenerator()->getLibraries(),
		    		$configuration->getGenerator()->needServerData()
		    		);
		} catch (\Exception $e) {
		    $this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
		    throw new WebAPI\Exception('Package prerequisites could not be validated: ' . $e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		if (! $configuration->isValid($configurationContainer)) {
		        $this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
		        $messagesFilter = new \Prerequisites\MessagesFilter();
		        $messages = $messagesFilter->filter($configuration->getMessages());
		        Log::err(print_r($this->flattenMessagesArray($messages), true));
		        throw new WebAPI\Exception(PHP_EOL . implode(PHP_EOL, $this->flattenMessagesArray($messages)), WebAPI\Exception::INVALID_PARAMETER);
		} 
		
		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_UPGRADE, ProgressMapper::AUDIT_NO_PROGRESS, array('userParams' => $userParams, 'zendParams' => $zendParams), $baseUrl); /* @var $auditMessage \Audit\Container */
			$this->getDeploymentMapper()->updateApplication($baseUrl, $appId, $this->getLocator());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_REQUESTED);
			$deployedApplication = $this->getDeploymentMapper()->getApplicationByBaseUrl($baseUrl);
		} catch (\Deployment\Exception $e) {
			Log::err("applicationUpdate failed: " . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
					'errorMessage' => $e->getMessage()));
			if ($e->getCode() == \Deployment\Exception::VHOST_NOT_FOUND) {
				throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::MISSING_VIRTUAL_HOST);
			}
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
					'errorMessage' => $e->getMessage()));
			Log::err("applicationUpdate failed: " . $e->getMessage());
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
			throw new WebAPI\Exception(
					_t('Deployment failed %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		Log::info("Application has been updated");
		$this->setHttpResponseCode('202', 'Accepted');
		$servers = $this->getDeploymentMapper()->getServersStatusByAppId($deployedApplication->getApplicationId());
		
		$viewModel = new ViewModel(array('application' => $deployedApplication, 'servers' => $servers));
		$viewModel->setTemplate('deployment/web-api/application-info');
		return $viewModel;
	}	  
	
	/**
	 * Synchronize an existing application, whether in order to fix a problem or to reset an installation. 
	 * This process is asynchronous. 
	 * The initial request will start the Synchronize process and the initial response will show information about the application 
	 * being Synchronized - however the synchronization process will proceed after the response is returned. 
	 * The user is expected to continue checking the application status using the 
	 * applicationGetStatus method until the process is complete.
	 * 
	 * Parameters:
	 * 	appId - String, Required. Application ID to Synchronize 
	 *  servers - Array. List of server IDs.
	 *  ignoreFailures - Boolean. Ignore failures during staging or activation if only some servers reported failures;
	 *	  Default: FALSE
	 */
	public function applicationSynchronizeAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(
				array(
						'ignoreFailures' => 'FALSE',
						'servers' => null,
				)
		);
		
		$this->validateMandatoryParameters($params, array('appId'));
		$appId = $params['appId'];
		$this->validateInteger($appId, 'appId');
		
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
		
		// @todo: Is component loaded?
		// @todo: Verify has target? Check if zend server is responding?
		
		if (!($existingApplication = $this->getDeploymentMapper()->getApplicationById($appId))) {
			Log::err("Failed to synchronize application- 'appId' $appId does not exist");
			throw new WebAPI\Exception(
					_t("This application does not exist"),
					WebAPI\Exception::NO_SUCH_APPLICATION
			);
		}

		$status = null;
		$edition = new \ZendServer\Edition();
		if (! $edition->isSingleServer()) { // we want the app status of this server rather than global status (we do NOT want to prevent multiple servers from redeploying the same app)
			$status = $this->getDeploymentMapper()->getServerStatusByAppId($appId, $edition->getServerId());
		}		
		if ($existingApplication->cannotRedeploy($status)) {
			Log::debug("This application cannot be synchronised, the application is currently being modified: {$existingApplication->getStatus()}");
			throw new WebAPI\Exception(
					_t("This application cannot be synchronised"),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		// @todo: Check if application isBeingDeployed or isBeingRolledback or isBeingRemoved
		try {
			if (!$existingApplication->isDefinedApplication()) {
				$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_REDEPLOY, ProgressMapper::AUDIT_PROGRESS_REQUESTED,
						array(array('Application Name' => $existingApplication->getUserApplicationName())), $existingApplication->getBaseUrl()); /* @var $auditMessage \Audit\Container */
				$this->getDeploymentMapper()->redeployApplication($existingApplication, $ignoreFailures, $params['servers'], $this->getLocator());
			} else {
				$this->getDeploymentMapper()->redeployApplication($existingApplication, $ignoreFailures, $params['servers'], $this->getLocator());
			}
		} catch (\Exception $e) {
			Log::err("Failed to synchronize application:" . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to synchronize application: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		$this->setHttpResponseCode('202', 'Accepted');

		$deployedApplications = $this->getDeploymentMapper()->getMasterApplication($existingApplication->getApplicationId());
		$deployedApplications->setHydrateClass('\Deployment\Application\Container');
		
		$appServers = array($existingApplication->getApplicationId() => $this->getDeploymentMapper()->getServersStatusByAppId($existingApplication->getApplicationId()));
		$viewModel = new ViewModel(array('applications' => $deployedApplications, 'servers' => $appServers));
		$viewModel->setTemplate('deployment/web-api/redeploy-all-applications');
		return $viewModel;
	}
	
	/**
	 * Remove / undeploy an existing application. 
	 * This process is asynchronous - the initial request will start the removal process and the initial response will show 
	 * information about the application being removed - however the removal process will proceed after the response is returned. 
	 * The user is expected to continue checking the application status using the applicationGetStatus 
	 * method until the removal process is complete. 
	 * Once applicationGetStatus contains no information about the specific application, it has been completely removed
	 * 
	 * Parameters:
	 *	 appId - String, Required. Application ID to remove
	 */
	public function applicationRemoveAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(array('ignoreFailures' => 'FALSE'));
		$this->validateMandatoryParameters($params, array('appId'));
		$appId = $params['appId'];
		$removeApplicationData = $params['removeApplicationData']; //flag -> 1 for removing, 0 for keeping!
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
		
		$this->validateInteger($appId, 'appId');
		// @todo: Is component loaded?
		// @todo: Verify has target? Check if zend server is responding?

		if (!($existingApplication = $this->getDeploymentMapper()->getApplicationById($appId))) {
			Log::err("Failed to remove application- appId $appId does not exist");
			throw new WebAPI\Exception(
					_t("This application does not exist"),
					WebAPI\Exception::NO_SUCH_APPLICATION
			);
		}
		
		// @todo: Check if application isBeingDeployed or isBeingRolledback or isBeingRemoved
		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_REMOVE,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Application name: %s', array($existingApplication->getUserApplicationName())))),
					$existingApplication->getBaseUrl()); /* @var $auditMessage \Audit\Container */			
			$existingApplication = $this->getDeploymentMapper()->getApplicationById($appId);
			$this->getDeploymentMapper()->removeApplication($existingApplication, $ignoreFailures ,$removeApplicationData);					
			
		} catch (\Exception $e) {
			Log::err("Failed to remove application:" . $e->getMessage());
			
			if ($existingApplication->isDefinedApplication()) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array("message" => $e->getMessage()));
			}
			
			throw new WebAPI\Exception(
					_t('Failed to remove application %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		if ($existingApplication->isDefinedApplication()) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array());
		}
		
		Log::info("Application with id: $appId has been removed");		
		$this->setHttpResponseCode('202', 'Accepted');
		
		$servers = $this->getDeploymentMapper()->getServersStatusByAppId($existingApplication->getApplicationId());
		$viewModel = new ViewModel(array('application' => $existingApplication, 'servers' => $servers));
		$viewModel->setTemplate('deployment/web-api/application-info');
		return $viewModel;
	}
	
	/**
	 * Rollback an existing application to its previous version. 
	 * This process is asynchronous - the initial request will start the rollback process and the initial 
	 * response will show information about the application being rolled back. 
	 * The user is expected to continue checking the application status using the 
	 * applicationGetStatus method until the process is complete.
	 * 
	 * Parameters:
	 *	 appId - String, Required. Application ID to rollback 
	 */
	public function applicationRollbackAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters();
				$this->validateMandatoryParameters($params, array('appId'));
		$appId = $params['appId'];
		$this->validateInteger($appId, 'appId');
		// @todo: Is component loaded?
		// @todo: Verify has target? Check if zend server is responding?

		if (!($existingApplication = $this->getDeploymentMapper()->getApplicationById($appId))) {
			Log::err("Failed to roll back application - appId $appId does not exist");
			throw new WebAPI\Exception(
					_t("This application does not exist"),
					WebAPI\Exception::NO_SUCH_APPLICATION
			);
		}
		
		// @todo: Check if application isBeingDeployed or isBeingRolledback or isBeingRemoved
		if (!$existingApplication->isRollbackable()) {
			Log::debug("The application can not be rolled back");
			throw new WebAPI\Exception(
					_t("The application can not be rolled back"),
					WebAPI\Exception::NO_ROLLBACK_AVAILABLE
			);
		}
		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_ROLLBACK, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $auditMessage \Audit\Container */			
			$this->getDeploymentMapper()->applicationRollback($existingApplication);
			$rollbackedApplication = $this->getDeploymentMapper()->getApplicationById($appId);
		} catch (\Exception $e) {
			Log::err("Failed to roll back application:" . $e->getMessage());
			throw new WebAPI\Exception(
					_t('applicationRollback failed %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}

		Log::info("Application has been rolled back");		
		$this->setHttpResponseCode('202', 'Accepted');
		 
		$servers = $this->getDeploymentMapper()->getServersStatusByAppId($appId);
		$viewModel = new ViewModel(array('application' => $rollbackedApplication, 'servers' => $servers));
		$viewModel->setTemplate('deployment/web-api/application-info');
		return $viewModel;
	}

	protected function getGuiTempDir() {
		return FS::getGuiTempDir();
	}
	
	/**
	 * Trim variables and array (incl. multi-dimensional ones)
	 *
	 * @param mixed $value
	 * @return mixed; null if the param didn't exist
	 */
	protected function trimParam($value) {
		if (is_array($value)) {
			return array_map(array('self', __FUNCTION__), $value);
		}
	
		return trim($value);
	}
	
	/**
	 *
	 * @param string $baseUrl
	 * @throws WebAPI\Exception
	 */
	protected function validateBaseUrl($baseUrl){
		$baseUrl = $this->trimParam($baseUrl);
		
		if (\Deployment\Model::DEFAULT_SERVER == $baseUrl) {
			return $baseUrl;
		}
		
		if (0 !== strpos($baseUrl, 'http')) {
			$fakeUri = 'http://' . $baseUrl;
		} else {
			$fakeUri = $baseUrl;
		}
		
		try {
			$uri = UriFactory::factory($fakeUri);
		} catch (\Zend\URI\Exception $e) {
			$message = _t("Parameter 'baseUrl' must be a valid URL: %s", array($e->getMessage()));
			throw new WebAPI\Exception($message, WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$baseUrlValidator = new \Deployment\Validator\VirtualHostPort();
		if (! $baseUrlValidator->isValid($uri->getPort())) {
			Log::err('Invalid parameter \'baseUrl\'');
			$message = _t("Parameter 'baseUrl' must be a valid URL: %s", array((string)current($baseUrlValidator->getMessages())));
			throw new WebAPI\Exception($message, WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $uri->toString();
		
	}
	
	/**
	 *
	 * @param array $params
	 * @return array
	 * @throws WebAPI\Exception
	 */
	protected function validateUserParams($userParams)
	{
		$userParams = $this->trimParam($userParams);
		if (! is_array($userParams)) {
			throw new WebAPI\Exception(
					_t("Parameter 'userParams' must be an array of values for the uploaded application package"),
					WebAPI\Exception::INVALID_PARAMETER
			);
		}
	}
	
	protected function setFileTransfer() {
		$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
		$uploaddir = $this->getGuiTempDir();
		$fileTransfer->setDestination($uploaddir);
		if (! $fileTransfer->receive()) {
			throw new WebAPI\Exception(
					_t("Package file upload failed"),
					WebAPI\Exception::INVALID_PARAMETER
			);
		}
		
		Log::debug('File is uploaded to ' . $uploaddir);
		return $fileTransfer;
	}

	/**
	 * 
	 * @param string $filename
	 * @throws WebAPI\Exception
	 * @return \Deployment\Application\Package
	 */
	protected function generatePackage($filename) {
		try {
			$deploymentPackage = \Deployment\Application\Package::generate($filename);
		} catch (\Exception $e) {
			Log::err("Failed to validate application package: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t("Failed to validate application package: %s", array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		 
		return $deploymentPackage;
	}

	/**
	 * 
	 * @param array $userParams
	 * @param string $baseUrl
	 * @param \Deployment\Application\Package $deploymentPackage
	 * @return \Zend\Form\Form
	 */
	protected function validateParams($userParams, $baseUrl, $deploymentPackage) {
		$form = $this->getFormValidator($baseUrl, $deploymentPackage);		
		$form->setData($userParams);
		
		if (! $form->isValid()) {
			$validationErrors = $this->getValidatorErrors($form);
			Log::err('User supplied parameters have failed validation according to the package\'s definitions: ' . print_r($validationErrors, true));
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);			
			throw new WebAPI\Exception(
					_t('User supplied parameters have failed validation according to the package\'s definitions: %s', $validationErrors), 
					WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $form;
	}  

	protected function getValidatorErrors(\Zend\Form\Form $form) {
		$allMessages = array();
		foreach ($form->getMessages() as $elementName => $elementMessages) {
			if (! $elementMessages) {
				continue;
			}
			
			foreach ($elementMessages as $messageKey => $message) {
				$allMessages[$elementName] = "{$elementName} parameter - {$messageKey} validation:{$message}";
			}
		}
		
		return $allMessages;
	}

	/**
	 * @param string $baseUrl
	 * @param \Deployment\Application\Package $deploymentPackage
	 * @return \Zend\Form\Form
	 * @throws WebAPI\Exception
	 */
	protected function getFormValidator($baseUrl, \Deployment\Application\Package $deploymentPackage) {
		try {
			$form = $this->getDeploymentMapper()->getUserParamsForm($deploymentPackage->getRequiredParams());
		} catch (\Exception $e) {
			$this->getDeploymentMapper()->cancelPendingDeployment($baseUrl);
			Log::err("Failed to validate user parameters:" . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to validate user parameters: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		return $form;
	}
	
	/**
	 * @param string $baseUrl
	 * @param \Deployment\Application\Package $deploymentPackage
	 * @param array $userParams
	 * @throws WebAPI\Exception
	 */	
	protected function validateRequiredParams($baseUrl, \Deployment\Application\Package $deploymentPackage, array $userParams) {
		$params = $deploymentPackage->getRequiredParams();
		if (!isset($params['elements'])) return;
		
		foreach ($params['elements'] as $element) {	
			$elementName = $element['spec']['name'];		
			if ($this->isRequiredEmptyElement($element) && !(isset($userParams[$elementName]) && $userParams[$elementName] !== '')) {
				throw new WebAPI\Exception(_t("Parameter '{$elementName}' is missing"), WebAPI\Exception::MISSING_PARAMETER);
			}
		}
	}
	
	protected function isRequiredElement($elementAttributes) {
		if (isset($elementAttributes['required']) && $elementAttributes['required']) {
			return true;
		}
		
		return false;		
	}
	
	protected function isRequiredEmptyElement(array $element) {
		$elementAttributes = $element['spec']['attributes'];
		if ($this->isRequiredElement($elementAttributes) && !(isset($elementAttributes['value']) && $elementAttributes['value'] !== '')) {
			return true;
		}
	
		return false;
	}
	
	protected function processUserParams($userParams, $deploymentPackage) { // ZF2 B4 bugs requires us to do some handling here
		$userParams = $this->populateUserParamas($userParams, $deploymentPackage);
		
		return $this->removeEmptyNonRequiredParams($userParams, $deploymentPackage);
	}
	
	/**
	 * @param array $userParams
	 * @param \Deployment\Application\Package $deploymentPackage
	 * @return array
	 */
	protected function populateUserParamas($userParams, $deploymentPackage) {
		$params = $deploymentPackage->getRequiredParams();
		
		if (!isset($params['elements'])) return $userParams;
		
		foreach ($params['elements'] as $element) {		
			$elementAttributes = $element['spec']['attributes'];
			$elementName = $element['spec']['name'];

			if (!isset($userParams[$elementName])) {
				isset($elementAttributes['value']) ? $value = $elementAttributes['value'] : $value = '';
				$userParams[$elementName] = $value; // @todo - adding empty fileds as of ZF2 B4 bug, that misses validation messages when missing required fields are not passed
			}
		}
		
		return $userParams;
	}

	/**
	 * @param array $userParams
	 * @param \Deployment\Application\Package $deploymentPackage
	 * @return array
	 */
	protected function removeEmptyNonRequiredParams($userParams, $deploymentPackage) {
		$params = $deploymentPackage->getRequiredParams();
		if (!isset($params['elements'])) return $userParams;
	
		foreach ($params['elements'] as $element) {
			$elementAttributes = $element['spec']['attributes'];
			$elementName = $element['spec']['name'];
			
			if ($this->isRequiredElement($elementAttributes)) { // removing non required params only
				continue;
			}
				
			if (isset($userParams[$elementName]) && $userParams[$elementName] === '') {
				unset($userParams[$elementName]);
			}
		}
	
		return $userParams;
	}	
	
	/**
	 * @param array $namespaces
	 * @return array
	 */
	private function flattenMessagesArray(array $namespaces) {
	    $flatMessages = array();
	    foreach ($namespaces as $namespace => $elements) {
	        foreach ($elements as $name => $messages) {
	            foreach ($messages as $message) {
	                $flatMessages[] = _t('(%s) %s: %s', array(ucfirst($namespace), $name, $message));
	            }
	        }
	    }
	    return $flatMessages;
	}
}
