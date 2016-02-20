<?php
namespace ZendServer\Mvc\Controller;


use ZendServer\Permissions\AclQuery;

use ZendServer\Permissions\AclQuerierInterface;

use ZendServer\Log\Log;

use Application\Module, 
	Zend\Mvc\Controller\AbstractActionController as baseActionController,
	ZendServer\Exception,
	Zend\Uri\UriFactory,
	Zend\Validator,
	Zend\Json\Json,
	WebAPI,
	WebAPI\Db\Mapper as WebAPIMapper,
	WebAPI\SignatureGenerator;

class ActionController extends baseActionController implements AclQuerierInterface {

	/**
	 * @var AclQuery
	 */
	private $acl;
	
	/**
	 * @param AclQuery $acl
	 * @return \ZendServer\Mvc\Controller\ActionController
	 */
	public function setAcl(AclQuery $acl) {
		$this->acl = $acl;
		return $this;
	}

	/**
	 *
	 * @param \WebAPI\Db\ApiKeyContainer $keyContainer
	 * @param string $methodName
	 * @param Boolean $isGet
	 * @param array $parameters
	 * @throws Exception
	 * @return Zend\Http\Response
	 */
	protected function execWebAPIRequestOnSelf($keyContainer, $methodName, $isGet=true, array $parameters = array()) {		
		return $this->execWebAPIRequest('localhost', $keyContainer, $methodName, $isGet, $parameters);
	}	
	
	/**
	 * 
	 * @param integer $serverId
	 * @param string $methodName
	 * @param Boolean $isGet
	 * @param array $parameters
	 * @throws Exception
	 * @return Zend\Http\Response
	 */
	protected function execWebAPIRequestOnClusterMember($serverId, $methodName, $isGet=true, array $parameters = array()) {		
		$serverSet = $this->getServersMapper()->findServersById(array($serverId));
		
		if (0 == $serverSet->count()) {
			throw new Exception(_t("Server ID '%s' was not found",array($serverId)));
		}		

		if (! ($key = $this->getWebapiMapper()->findKeyByName(WebAPIMapper::SYSTEM_KEY_NAME))) {
			throw new Exception(_t("Could not retrieve the webapi key to connect to cluster member"));
		}

		return $this->execWebAPIRequest($serverSet->current()->getNodeIp(), $key, $methodName, $isGet, $parameters);
	}
	
	/**
	 * @param string $ip
	 * @param \WebAPI\Db\ApiKeyContainer $key
	 * @param string $methodName
	 * @param Boolean $isGet
	 * @param array $parameters
	 * @throws Exception
	 * @return Zend\Http\Response
	 */
	private function execWebAPIRequest($ip, $key, $methodName, $isGet=true, array $parameters = array()) {
		$baseUrl = Module::config('baseUrl');
			
		$uri = UriFactory::factory("http://{$ip}");
		$uri->setPath("{$baseUrl}/Api/{$methodName}");
		$uri->setPort(Module::config('installation', 'defaultPort'));
		$uri->setScheme('http');
		
		$userAgent = 'Zend Server Remote UI';
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
			
		$signatureGenerator = new SignatureGenerator();
		$signature = $signatureGenerator
		->setHost("{$uri->getHost()}:{$uri->getPort()}")
		->setUserAgent($userAgent)
		->setDate($date)
		->setRequestUri($uri->getPath())
		->generate($key->getHash());
		
		$isGet ? $httpMethod = 'GET' : $httpMethod = 'POST';
		$httpClient = new \Zend\Http\Client();
		$httpClient->setUri($uri);
		$httpClient->setMethod($httpMethod);
		if ($isGet) {
			$httpClient->setParameterGet($parameters);
		} else {
			$httpClient->setParameterPost($parameters);
		}
		
		$httpClient->setHeaders(array(
				'Accept' => 'application/vnd.zend.serverapi+json;version=1.3',
				'User-Agent'        => $userAgent,
				'Date'              => $date,
				'X-Zend-Signature'  => $key->getName() . ';' . $signature,
		));
		
		try {
			Log::debug("Propagate webapi '{$methodName}' action to {$uri}");
			$response = $httpClient->send();
		}
		catch (\Zend\Http\Exception\ExceptionInterface $e) {
			throw new Exception(_t("webapi '{$methodName}' request to %s failed to execute: %s", array($uri, $e->getMessage())), null, $e);
		}
		
		if (! $response->isSuccess()) {
			try {
				$responseDecoded = Json::decode($response->getBody(), Json::TYPE_ARRAY);
				if (isset($responseDecoded['errorData']['errorMessage'])) {
					$errorMessage = $responseDecoded['errorData']['errorMessage'];
				} else {
					$errorMessage = 'Bad response format';
				}
			} catch (Exception $e) {
				$errorMessage = "Bad response format, not a json ({$e->getMessage()})";
			}
				
			throw new Exception(_t("webapi '{$methodName}' request to %s failed: %s",array ($uri, $errorMessage)));
		}
		
		return $response;		
	}
	
	/**
	 *
	 * @param integer $integer
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateInteger($integer, $parameterName) {
		if (! is_numeric($integer)) {
			throw new \ZendServer\Exception (_t("Parameter '%s' must be an integer",array($parameterName)));
		}
	
		return intval($integer);
	}

	/**
	 *
	 * @param string $param
	 * @param string $paramName
	 * @return boolean
	 */
	protected function validateBoolean($param, $paramName) {
		$param = strtoupper($param);
		if ($param === 'TRUE') {
			return true;
		} elseif ($param === 'FALSE') {
			return false;
		} else {
			throw new \ZendServer\Exception(_t("Parameter '%s' must be either 'TRUE' or 'FALSE'",array($paramName) ), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 * @brief Check the path of a package file - now only checks the extension ZIP or ZPK
	 * @param <unknown> $path 
	 * @param <unknown> $paramName 
	 * @return  
	 */
	protected function validateZpkPath($path, $paramName) {
		if (!preg_match('%\.(zpk|zip)$%i', $path)) {
			throw new \ZendServer\Exception(_t("Parameter '%s' be a valid package file (*.zpk or *.zip)", array($paramName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return true;
	}
	
	/**
	 * @return \Configuration\MapperExtensions
	 */
	protected function getExtensionsMapper() {
		return $this->getLocator()->get('Configuration\MapperExtensions');
	}
	
	/**
	 * @return \Configuration\MapperDirectives
	 */
	protected function getDirectivesMapper() {
		return $this->getLocator()->get('Configuration\MapperDirectives');
	}

	/**
	 * @return \Deployment\Model
	 */
	protected function getDeploymentMapper() {
		return $this->getLocator()->get('Deployment\Model');
	}
		
	/**
	 * @return \DeploymentLibrary\Mapper
	 */
	protected function getDeploymentLibraryMapper() {
		return $this->getLocator()->get('DeploymentLibrary\Mapper');
	}
	
	 /**
	 * @return \Plugins\Mapper
	 */
	protected function getPluginsMapper() {
		return $this->getLocator()->get('Plugins\Mapper');
	}
	
	/**
	 * @return \Vhost\Mapper\Vhost
	 */
	protected function getVhostMapper() {
		return $this->getLocator()->get('Vhost\Mapper\Vhost');
	}
	
	/**
	 * @return \Servers\Db\Mapper
	 */
	protected function getServersMapper() {
		return $this->getLocator()->get('Servers\Db\Mapper');
	}
	
	/**
	 * @return \Messages\Db\MessageMapper
	 */
	protected function getMessagesMapper() {
		return $this->getLocator()->get('Messages\Db\MessageMapper');
	}

	/**
	 * @return \Messages\Db\MessageFilterMapper
	 */
	protected function getFilterMessagesMapper() {
		return $this->getLocator()->get('Messages\Db\MessageFilterMapper');
	}
		
	/**
	 * @return \Zsd\Db\TasksMapper
	 */
	protected function getTasksMapper() {
		return $this->getLocator()->get('Zsd\Db\TasksMapper');
	}
		
	/**
	 * @return \GuiConfiguration\Mapper\Configuration
	 */
	protected function getGuiConfigurationMapper() {
		return $this->getLocator()->get('GuiConfiguration\Mapper\Configuration');
	}
	
	/**
	 * @return \Configuration\MapperReplies
	 */
	protected function getRepliesMapper() {
		return $this->getLocator()->get('Configuration\MapperReplies');
	}
	
	/**
	 * @return \MonitorRules\Model\Mapper
	 */
	protected function getMonitorRulesMapper() {
		return $this->getLocator()->get('MonitorRules\Model\Mapper');
	}

	/**
	 * @return \Audit\Db\Mapper
	 */
	protected function getAuditMapper() {
		return $this->getLocator()->get('Audit\Db\Mapper');
	}

	/**
	 * @return \Audit\Db\ProgressMapper
	 */
	protected function getAuditProgressMapper() {
		return $this->getLocator()->get('Audit\Db\ProgressMapper');
	}
	
	/**
	 * @return \Audit\Db\SettingsMapper
	 */
	protected function getAuditSettingsMapper() {
		return $this->getLocator()->get('Audit\Db\SettingsMapper');
	}
	
	/**
	 * @return \Notifications\Db\NotificationsMapper
	 */
	protected function getNotificationsMapper() {
		return $this->getLocator()->get('Notifications\Db\NotificationsMapper');
	}
	
	/**
	 * @return \Notifications\Db\NotificationsActionsMapper
	 */
	protected function getNotificationsActionMapper() {
		return $this->getLocator()->get('Notifications\Db\NotificationsActionsMapper');
	}

	/**
	 * @return \JobQueue\Model\Mapper
	 */
	protected function getJobqueueMapper() {
		return $this->getLocator()->get('JobQueue\Model\Mapper');
	}

	/**
	 * @return \Users\Db\Mapper
	 */
	protected function getUsersMapper() {
		return $this->getLocator()->get('Users\Db\Mapper');
	}
	
	/**
	 * @return \Deployment\Db\Mapper
	 */
	protected function getDeploymentDbMapper() {
		return $this->getLocator()->get('Deployment\Db\Mapper');
	}

	/**
	 * @return \WebAPI\Db\Mapper
	 */
	protected function getWebapiMapper() {
		return $this->getLocator()->get('WebAPI\Db\Mapper');
	}	

	/**
	 * @return \Snapshots\Db\Mapper
	 */
	protected function getSnapshotsMapper() {
		return $this->getLocator()->get('Snapshots\Db\Mapper');
	}
	
	/**
	 * @return \LibraryUpdates\Db\Mapper
	 */
	protected function getLibraryUpdatesMapper() {
		return $this->getLocator()->get('LibraryUpdates\Db\Mapper');
	}

	/**
	 * @return \ZendServer\Filter\Mapper
	 */
	protected function getFilterMapper() {
		return $this->getLocator('ZendServer\Filter\Mapper');
	}	

	/**
	 * @return \Zsd\Db\NodesProfileMapper
	 */
	protected function getNodesProfileMapper() {
		return $this->getLocator()->get('Zsd\Db\NodesProfileMapper');
	}
	
	/**
	 * @return \DevBar\Db\RequestsMapper
	 */
	protected function getDevBarRequestsMapper() {
		return $this->getLocator()->get('DevBar\Db\RequestsMapper');
	}
	
	/**
	 * @return \DevBar\Db\SqlQueriesMapper
	 */
	protected function getDevBarSqlQueriesMapper() {
		return $this->getLocator()->get('DevBar\Db\SqlQueriesMapper');
	}
	
	/**
	 * @return \DevBar\Db\RuntimeMapper
	 */
	protected function getDevBarRuntimeMapper() {
		return $this->getLocator()->get('DevBar\Db\RuntimeMapper');
	}
	
	/**
	 * @return \DevBar\Db\LogEntriesMapper
	 */
	protected function getDevBarLogEntriesMapper() {
		return $this->getLocator()->get('DevBar\Db\LogEntriesMapper');
	}
	
	/**
	 * @return \DevBar\Db\FunctionsMapper
	 */
	protected function getDevBarFunctionsMapper() {
		return $this->getLocator()->get('DevBar\Db\FunctionsMapper');
	}
	
	/**
	 * @return \DevBar\Db\MonitorEventsMapper
	 */
	protected function getDevBarMonitorEventsMapper() {
		return $this->getLocator()->get('DevBar\Db\MonitorEventsMapper');
	}
	
	/**
	 * @return \DevBar\Db\SuperglobalsMapper
	 */
	protected function getDevBarSuperglobalsMapper() {
		return $this->getLocator()->get('DevBar\Db\SuperglobalsMapper');
	}
	
	/**
	 * @return \DevBar\Db\ExceptionsMapper
	 */
	protected function getDevBarExceptionsMapper() {
		return $this->getLocator()->get('DevBar\Db\ExceptionsMapper');
	}
	
	/**
	 * @return \DevBar\Db\ExtensionsMapper
	 */
	protected function getDevBarUserDataMapper() {
		return $this->getLocator()->get('DevBar\Db\ExtensionsMapper');
	}
	
	/**
	 * @return \DevBar\Db\ExtensionsMetadataMapper
	 */
	protected function getDevBarExtensionsMetadataMapper() {
		return $this->getLocator()->get('DevBar\Db\ExtensionsMetadataMapper');
	}
	
	/**
	 * @return \DevBar\Db\RequestsUrlsMapper
	 */
	protected function getDevBarRequestsUrlsMapper() {
		return $this->getLocator()->get('DevBar\Db\RequestsUrlsMapper');
	}
	
	/**
	 * @return \DevBar\Db\SqlStatementsMapper
	 */
	protected function getDevBarSqlStatementsMapper() {
		return $this->getLocator()->get('DevBar\Db\SqlStatementsMapper');
	}
	
	/**
	 * @return \DevBar\Db\BacktraceMapper
	 */
	protected function getDevBarBacktraceMapper() {
		return $this->getLocator()->get('DevBar\Db\BacktraceMapper');
	}
	
	/**
	 * @return \ZendServer\Edition
	 */
	protected function getEditionServer() {
		return $this->getLocator('editionServer');
	}	
	

	/**
	 * @return \Configuration\License\ZemUtilsWrapper
	 */
	protected function getZemUtilsWrapper() {
		return $this->getLocator('Configuration\License\ZemUtilsWrapper');
	}

	/**
	 * @return \Codetracing\Model
	 */
	protected function getCodetracingModel() {
		return $this->getLocator('Codetracing\Model');
	}	
	
	/**
	 * @param array $defaults
	 * @return \Zend\Stdlib\ParametersDescription
	 */
	protected function getParameters(array $defaults = array()) {
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		if ($request->isGet()) {
			$parameters = $request->getQuery();
		} else {
			$parameters = $request->getPost();
		}
	
		$parameters->fromArray(array_merge($defaults, $parameters->toArray()));
		return $parameters;
	}
	
	/**
	 * @param PhpRequest $request
	 * @param array $params of parameter names
	 * @throws WebAPI\Exception
	 */
	protected function validateMandatoryParameters(\Zend\Stdlib\Parameters $requestParams, array $params) {	
		foreach ($params as $param) {
			if ((! isset($requestParams[$param])) || ('' === $requestParams[$param])) {
				throw new WebAPI\Exception(_t('This action requires the %s parameter', array($param)), WebAPI\Exception::MISSING_PARAMETER);
			}
		}
	}
	
	/**
	 * Backwards compatibility with beta3 locator - if both name and params are passed, normal DI locator is used
	 * @return \Zend\Di\ServiceManager
	 */
	protected function getLocator($nameOrAlias = null, $params = null) {
		if (is_null($nameOrAlias)) {
			return $this->getServiceLocator();
		}
		
		if (is_null($params)) {
			return $this->getServiceLocator()->get($nameOrAlias);
		}
		
		return $this->getServiceLocator()->get($nameOrAlias);
	}
	
	/**
	 * @param string $resource
	 * @param string $priv
	 * @return boolean
	 */
	protected function isAclAllowed($resource = null, $priv = null) {
		return $this->acl->isAllowed($resource, $priv);
	}
	
	/**
	 * @param string $resource
	 * @param string $priv
	 * @return boolean
	 */
	protected function isAclAllowedIdentity($resource = null, $priv = null) {
		return $this->acl->isAllowedIdentity($resource, $priv);
	}
	
	/**
	 * @param string $resource
	 * @param string $priv
	 * @return boolean
	 */
	protected function isAclAllowedEdition($resource = null, $priv = null) {
		return $this->acl->isAllowedEdition($resource, $priv);
	}
	
}

