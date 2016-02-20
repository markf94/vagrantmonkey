<?php

namespace DevBar\Controller;
use ZendServer\Mvc\Controller\WebAPIActionController,
	WebAPI,
	ZendServer\Set,
	DevBar\Db\RequestsMapper;
use ZendServer\Log\Log;
use Zend\View\Model\ViewModel;
use WebAPI\Exception;
use Audit\Db\Mapper;
use Audit\Db\ProgressMapper;
use DevBar\Db\TokenMapper;
use Application\Module;
use Zend\Crypt\Hash;

class WebAPIController extends WebAPIActionController
{
    
    const DEFAULT_REQUESTS_LIMIT = 10;
    const MAX_REQUESTS_LIMIT = 500;
    
    // when there is a lot of data, and the limit is high, an `out-of-memory` error
    // can occure. Thus, we split the reading process into iterations and check memory
    // after each iteration 
    const MAX_REQUESTS_PER_ITERATION = 5; 
    const MAX_ACCEPTABLE_MEMORY_USAGE_PERCENT = 40; 
	
	public function devBarListAccessTokensAction(){
		$this->isMethodGet();
		$params = $this->getParameters(array('page' => 0, 'order' => TokenMapper::TOKEN_FIELD_ID, 'direction' => 'DESC', 'limit' => TokenMapper::TOKEN_LIMIT_DEFAULT));
		
		$page = $this->validateInteger($params['page'], 'page');
		$direction = $this->validateDirection($params['direction'], 'direction');
		$limit = $this->validateInteger($params['limit'], 'limit');
		$order = $this->validateAllowedValues($params['order'], 'order', array(TokenMapper::TOKEN_FIELD_ID,TokenMapper::TOKEN_FIELD_TITLE, TokenMapper::TOKEN_FIELD_TOKEN, TokenMapper::TOKEN_FIELD_TTL));
		$tokenMapper = $this->getServiceLocator()->get('DevBar\Db\AccessTokensMapper');
		$tokens = $tokenMapper->findTokens($page - 1, $limit, $order, $direction);
		$totalCount = $tokenMapper->count();
		return array('tokens' => $tokens, 'totalCount' => $totalCount);
	}
	
	public function zrayCreateSelectiveAccessAction() {
	    return $this->zrayCreateAccessTokenAction();
	}
	
	public function zrayCreateAccessTokenAction(){
		$this->isMethodPost();
		$params = $this->getParameters(array('baseUrl' => '', 'ttl' => 60*60*24, 'title' => '', 'token' => 'TRUE', 'actions' => 'FALSE', 'inject' => 'TRUE'));
		$this->validateMandatoryParameters($params, array('iprange'));

		$ttl = $this->validateInteger($params['ttl'], 'ttl');
		$title = $this->validateString($params['title'], 'title');
		
		if ($params['baseUrl']) {
			$baseUrl = $this->validateUri($params['baseUrl'], 'baseUrl');
		} else {
			$baseUrl = '';
		}
		
		$token = $this->validateBoolean($params['token'], 'token');
		$actions = $this->validateBoolean($params['actions'], 'actions');
		$inject = $this->validateBoolean($params['inject'], 'inject');
		
		if ($token) {
		    $token = Hash::compute('sha256', mt_rand(0, mt_getrandmax()));
		} else {
		    $token = '';
		}
		
		$iprange = $params['iprange'];
		$trimmedIpRange = '';
		foreach(explode(',', $iprange) as $range) {
			$this->validateIpOrRange(trim($range));
			$trimmedIpRange[] = trim($range);
		}
		if (! empty($trimmedIpRange)) {
			$trimmedIpRange = implode(',', $trimmedIpRange);
		}
		
		if (! isAzureEnv() && !isZrayStandaloneEnv()) {
		    $auditMessage = $this->auditMessage(Mapper::AUDIT_DEVELOPER_TOKEN_ADD,
                                		        ProgressMapper::AUDIT_PROGRESS_REQUESTED,
                                		        array(array('title' => $title, 'Base Url' => $baseUrl, 'time to live' => $ttl, 'ip range' => $trimmedIpRange)));
		}
		
		/* @var DevBar\Db\AccessTokensMapper */
		$accessTokensMapper = $this->getServiceLocator()->get('DevBar\Db\AccessTokensMapper');
		$token = $accessTokensMapper->createToken($trimmedIpRange, $baseUrl, $ttl, $title, $token, $actions, $inject);
		if (! isAzureEnv() && !isZrayStandaloneEnv()) {
		  $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		}
		
		// check if the token was created for Z-Ray standalone also. 
		// if "$token" is a string, it's the error message from the function
		if (isZrayStandaloneEnv() && is_string($token)) {
			throw new WebAPI\Exception(_t($token), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		return array('token' => $token);
	}
	
	public function zrayExpireAccessTokenAction(){
		$this->isMethodPost();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('tokenId'));
		$tokenId = $this->validateInteger($params['tokenId'], 'tokenId');
		
		$tokenMapper = $this->getServiceLocator()->get('DevBar\Db\AccessTokensMapper');
		
		if (! isAzureEnv()) {
		    $auditMessage = $this->auditMessage(  Mapper::AUDIT_DEVELOPER_TOKEN_EXPIRE,
		                                          ProgressMapper::AUDIT_PROGRESS_REQUESTED,
		                                          array(array('tokenId' => $tokenId)));
		}
		
		$targetToken = $tokenMapper->findTokenById($tokenId);
		if ($targetToken->getId()) {
			$token = $tokenMapper->expireToken($tokenId);
			$targetToken = $tokenMapper->findTokenById($targetToken->getId());
		} else {
		    if (! isAzureEnv()) {
		        $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
		    }
			throw new Exception(_t('Access token not found'), Exception::NO_SUCH_ACCESS_TOKEN);
		}
		if (! isAzureEnv()) {
		    $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		}
		if (isZrayStandaloneEnv() && is_string($token)) {
			throw new WebAPI\Exception(_t($token), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$viewModel = new ViewModel(array('token' => $targetToken));
		$viewModel->setTemplate('dev-bar/web-api/zray-create-access-token');
		return $viewModel;
	}
	
	public function zrayDeleteByIdsAction() {
	   try {
			$this->isMethodPost();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('ids'));
			
			$messageParams = array(array('ids' => $params['ids']));
			$audit = $this->auditMessage(Mapper::AUDIT_ZRAY_DELETE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $messageParams); /* @var $audit \Audit\Container */
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, $messageParams);
			$this->validateArray($params['ids'], 'ids');
			foreach ($params['ids'] as $key => $id) {
				$this->validateInteger($id, "ids[$key]");
			}
		} catch (Exception $e) {
			$this->throwWebApiException($e, 'Input validation failed', WebAPI\Exception::INVALID_PARAMETER);
		}
	    
		try {
    		$this->getDevBarRuntimeMapper()->removeDevBarRequests($params['ids']);
    		$this->getDevBarFunctionsMapper()->removeDevBarRequests($params['ids']);
    		$this->getDevBarMonitorEventsMapper()->removeDevBarRequests($params['ids']);
    		$this->getDevBarSuperglobalsMapper()->removeDevBarRequests($params['ids']);
    		$this->getDevBarExceptionsMapper()->removeDevBarRequests($params['ids']);
    		$this->getDevBarUserDataMapper()->removeDevBarRequests($params['ids']);
    		$this->getDevBarExtensionsMetadataMapper()->removeDevBarRequests($params['ids']);
    		
    		
    		$sqlQueriesExtraData = $this->getDevBarSqlQueriesMapper()->getRequestSqlQueriesExtraData($params['ids']);
    		$logEntriesExtraData = $this->getDevBarLogEntriesMapper()->getRequestLogEntriesExtraData($params['ids']);
    		
    		$this->getDevBarLogEntriesMapper()->removeDevBarRequests($params['ids']);
    		$this->getDevBarBacktraceMapper()->removeDevBarRequests($sqlQueriesExtraData, $logEntriesExtraData);
    		
    		
    		$requestIds = array();
    		$requestExtraData = $this->getDevBarRequestsMapper()->getRequestExtraIdsForRemove($params['ids']);
    		$this->getDevBarRequestsUrlsMapper()->removeDevBarRequests($requestExtraData);
    		$this->getDevBarRequestsMapper()->removeRequests($params['ids']);
    		
    		$this->getDevBarSqlStatementsMapper()->removeDevBarRequests($sqlQueriesExtraData);
    		
		} catch (\Exception $e) {
	        Log::err(_t('Could not delete zray requests '. implode(', ', $params['ids']). '. Error: ' . $e->getMessage()));
		    $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, $messageParams);
		    $this->throwWebApiException($e, 'Failed to delete zray requests', WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $messageParams);
		
		return array('success' => true);
	}
	
	public function zrayRemoveAccessTokenAction(){
		$this->isMethodPost();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('tokenId'));
		$tokenId = $this->validateInteger($params['tokenId'], 'tokenId');
		
		/* @var DevBar\Db\AccessTokensMapper */
		$tokenMapper = $this->getServiceLocator()->get('DevBar\Db\AccessTokensMapper');
		
		if (! isAzureEnv()) {
    		$auditMessage = $this->auditMessage(    Mapper::AUDIT_DEVELOPER_TOKEN_REMOVE,
                                        		    ProgressMapper::AUDIT_PROGRESS_REQUESTED,
                                        		    array(array('tokenId' => $tokenId)));
		}
		
		$targetToken = $tokenMapper->findTokenById($tokenId);
		if ($targetToken->getId()) {
			$token = $tokenMapper->deleteToken($tokenId);
		} else {
		    if (! isAzureEnv()) {
		        $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
		    }
			throw new Exception(_t('Access token not found'), Exception::NO_SUCH_ACCESS_TOKEN);
		}
	    if (! isAzureEnv()) {
		    $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		}
		if (isZrayStandaloneEnv() && is_string($token)) {
			throw new WebAPI\Exception(_t($token), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$viewModel = new ViewModel(array('token' => $targetToken));
		$viewModel->setTemplate('dev-bar/web-api/zray-create-access-token');
		return $viewModel;
	}
	
	/**
	 * @param $requestsResult
	 */
	protected function getRequestsInfo($requestsResult, $hasCustomData = false) {
		
		$requests = array();
		$requestsIds = array();
		$sqlQueries = array();
		$logEntries = array();
		$monitorEvents = array();
		$exceptions = array();
		$functionsCount = array();
		$superglobals = array();
		$performRetry = array(); // id => bool
		if ($requestsResult->count() > 0) {
			foreach ($requestsResult as $requestResult) {
				// must be indexes by unique id since later we merge arrays by unique ids !!!
				$requests[$requestResult->getId()] = $requestResult;
				// perform retry on requests that ended less than 10 seconds ago
				$performRetry[$requestResult->getId()] = ($requestResult->getStartTime() + $requestResult->getRunTime()) > (round(microtime(true) * 1000) - 10000);
				$requestsIds[] = (int) $requestResult->getId();
				$sqlQueries[$requestResult->getId()] = array();
				$logEntries[$requestResult->getId()] = array();
				$monitorEvents[$requestResult->getId()] = array();
				$exceptions[$requestResult->getId()] = array();
				$functionsCount[$requestResult->getId()] = 0;
			}
		}
			
		$sqlQueriesMapper = $this->getDevBarSqlQueriesMapper();
		$sqlQueriesResult = $sqlQueriesMapper->getQueries($requestsIds);
		foreach ($sqlQueriesResult as $sqlQueryResult) {
			$sqlQueries[$sqlQueryResult->getRequestId()][] = $sqlQueryResult;
		}
		
		$logEntriesMapper = $this->getLocator()->get('DevBar\Db\LogEntriesMapper');
		$logEntriesResult = $logEntriesMapper->getEntries($requestsIds);
		foreach ($logEntriesResult as $logEntryResult) {
			$logEntries[$logEntryResult->getRequestId()][] = $logEntryResult;
		}
		
		$exceptionsMapper = $this->getLocator()->get('DevBar\Db\ExceptionsMapper');
		$exceptionsResult = $exceptionsMapper->getExceptions($requestsIds);
		foreach ($exceptionsResult as $exceptionResult) {
			$exceptions[$exceptionResult->getRequestId()][] = $exceptionResult;
		}
		
		$monitorEvents = array();
		$eventsData = array();
		
		if (!isAzureEnv() && !isZrayStandaloneEnv()) {
    		// collect monitor events
    		$monitorEventsAggKeysResult = $this->getMonitorEventsAggKeys($requestsIds);
    		$monitorEventsAggKeys = array();
    		foreach ($monitorEventsAggKeysResult as $aggKey) {
    			$monitorEventsAggKeys[$aggKey['request_id']][] = $aggKey['agg_key'];
    		}
    		
    		$monitorUisMapper = $this->getServiceLocator()->get('MonitorUi\Model\Model');
    		$retryLimit = \Application\Module::config('zray', 'zrayRetryLimit');
    		foreach ($monitorEventsAggKeys as $reqId => $aggKeys) {
    			/// synchronization between monitor and devbar means that some data may be missing for a while after agg_key was registered
    			/// we are forced to poll on the data for a bit to try and retrieve it, hopefully without starving
    				
    			$issues = $monitorUisMapper->getIssues(array('aggKeys' => $aggKeys), 0, 0, 'date', 'asc');
    			
    			if ($performRetry[$reqId]) {
        			$i = 0;
        			while (0 == $issues->count() && ($i * 100) < $retryLimit) {
        				$i ++;
        				usleep(100000); // sleep for 100 ms
        				$issues = $monitorUisMapper->getIssues(array('aggKeys' => $aggKeys), 0, 0, 'date', 'asc');
        			}
    			}
        				
    			if (0 < $issues->count()) {
    				$monitorEvents[$reqId][] = $issues;
    			} else {
    			    // freezed, no need now
    				//Log::notice('Monitor data was missing for request ' . $reqId);
    			}
    		}
    		
    		$eventsMapper = $this->getLocator()->get('EventsGroup\Db\Mapper'); /* @var $eventsMapper \EventsGroup\Db\Mapper */
    		$eventsData = array();
    		foreach ($monitorEvents as $key => $monitorEvent) {
    			foreach ($monitorEvent as $issuesKey => $issues) {
    				$monitorEvents[$key][$issuesKey] = $this->enhanceIssuesSet($issues);
    				try {
    					if ($monitorEvents[$key][$issuesKey]->count() > 0) {
    						$maxEventGroup = $monitorEvents[$key][$issuesKey]->current()->getMaxEventGroup();
    						$eventsData[$key][$issuesKey] = $eventsMapper->getEventGroupsData(array($maxEventGroup->getEventsGroupId()));
    					} else {
    						$eventsData[$key][$issuesKey] = $eventsMapper->getEventGroupsData(array());
    					}
    				} catch (\ZendMonitorUIException $ex) {
    					Log::warn("No eventGroups retrieved: {$ex->getMessage()}");
    				}
    			}
    		}
		}
		
		$runtimeMapper = $this->getLocator()->get('DevBar\Db\RuntimeMapper');
		$runtimes = $runtimeMapper->getRequestsRuntime($requestsIds);
		$runtimeEntries = array();
		foreach ($runtimes as $runtime) {
			$runtimeEntries[$runtime->getRequestId()] = $runtime;
		}
		
		// load number of functions per request
		$functionsMapper = $this->getLocator()->get('DevBar\Db\FunctionsMapper');
		$functionsList = $functionsMapper->getFunctions($requestsIds);
		
		foreach ($functionsList as $reqId => $funcs) {
			$functionsCount[$reqId] = count($funcs);
		}
		
		
		// get SuperGlobals
		$sgMapper = $this->getLocator()->get('DevBar\Db\SuperglobalsMapper');
		
		// for every request get superglobals
		foreach ($requestsIds as $reqId) {
			$isOversized = false;
			$superglobalsData = $sgMapper->getSuperglobals($reqId);
				
			// check if the _SESSION has oversized values
			if (!empty($superglobalsData) && isset($superglobalsData['_SESSION'])) {
				foreach ($superglobalsData['_SESSION'] as $sessionItem) {
					$sessionItemData = $sessionItem->getRawData();
					$isOversized = (strpos($sessionItemData, 'SESSION_PAYLOAD_TOO_LARGE') !== false);
					if ($isOversized) break;
				}
			}
				
			// set info about the superglobals in this request
			$superglobals[$reqId] = array(
				'oversizedSession' => $isOversized ? 1 : 0,
			);
		}
		
		
		return array(
			'requests' => $requests,
			'runtime' => $runtimeEntries,
			'sqlQueries' => $sqlQueries,
			'logEntries' => $logEntries,
			'exceptions' => $exceptions,
			'monitorEvents' => $monitorEvents,
			'eventsData' => $eventsData,
			'functionsCount' => $functionsCount,
			'superglobals' => $superglobals,
		    'hasCustomData' => $hasCustomData,
		);
	}
	
	/**
	 * Get requests by `pageId` (starting from `lastId`)
	 */
	public function devBarGetRequestsInfoAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array('pageId' => '', 'lastId' => ''));
		
		$this->validateMandatoryParameters($params, array('pageId'));
		
		$pageId = $this->validateString($params['pageId'], 'pageId');
		$lastId = '';
		if (! empty($params['lastId'])) {
			$lastId = $this->validateInteger($params['lastId'], 'lastId');
		}
		
		$requestsResult = $this->getRequests($pageId, $lastId);
		
		$hasCustomData = $this->hasCustomData($requestsResult);
		
		return $this->getRequestsInfo($requestsResult, $hasCustomData);
	}
	
	// read memory limit from the server
	public static function getMemoryLimit() {
	    $memory_limit = ini_get('memory_limit');
	    if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
	        if ($matches[2] == 'M') {
	            $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
	        } else if ($matches[2] == 'K') {
	            $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
	        }
	    }
	     
	    return $memory_limit;
	}
	
	/**
	 * Check when memory percentage is used, out of the allowed PHP memory (e.g. default is 128MB)
	 */
	public static function getMemoryUsagePercent() {	   
	    return ceil((memory_get_usage() / self::getMemoryLimit()) * 100); 
	}
	
	protected function validateFilters(array $filters = array()) {
	    
		$availableFilters = array ('severity', 'response', 'method', 'from', 'to', 'freeText');
		foreach ($filters as $key => $value) {
			$this->validateAllowedValues($key, 'filters', $availableFilters);
		}
		return $filters;
	}
	
	/**
	 * Get requests starting from `from_timestamp` (or all the requests)
	 */
	public function devBarGetAllRequestsInfoAction()
    {
        $this->isMethodGet();
        
        $params = $this->getParameters(array(
            'from_timestamp' => 0,
            'limit' => self::DEFAULT_REQUESTS_LIMIT,
            'offset' => 0,
			'order' => null,
			'direction' => null,
            'filters' => array(),
        ));
        
        $filters = $this->validateFilters($params['filters']);
        
        $fromTimestamp = preg_match('%^\d+$%', $params['from_timestamp']) ? $params['from_timestamp'] : 0;
        $limit = $this->validatePositiveInteger($params['limit'], 'limit');
		
        if ($limit > self::MAX_REQUESTS_LIMIT) {
            throw new WebAPI\Exception(_t('`limit` parameter is above %d', array(
                self::MAX_REQUESTS_LIMIT
            )), WebAPI\Exception::INVALID_PARAMETER);
        }
        $offset = $params['offset'] === 0 ? 0 : $this->validatePositiveInteger($params['offset'], 'offset');
		
        $mapper = $this->getDevBarRequestsMapper();
        
		// check the order
		$order = $params['order'];
		if (!is_null($order)) {
			$this->validateString($order, 'order');
			$this->validateAllowedValues(strtolower($order), 'order', $mapper->getDevbarRequestColumnNames());
		}
		
		// check the order direction
		$direction = $params['direction'];
		if (!is_null($direction)) {
			$this->validateAllowedValues(strtolower($direction), 'direction', array('asc', 'desc'));
		}

        
        // read the data in chunks
        $resultArray = array();
        $initialOffset = $offset;
        $iterations = 0;
        while ($iterations * self::MAX_REQUESTS_PER_ITERATION < $limit) {
            
            // calculate the new limit
            $newLimit = ($limit - $iterations * self::MAX_REQUESTS_PER_ITERATION) < self::MAX_REQUESTS_PER_ITERATION ? 
                ($limit - $iterations * self::MAX_REQUESTS_PER_ITERATION) : self::MAX_REQUESTS_PER_ITERATION;
			
            // get requests from the database
            $requestsResult = $mapper->getRequestsFromTimestamp($fromTimestamp, $newLimit, $initialOffset + $iterations * self::MAX_REQUESTS_PER_ITERATION, $order, $direction, $filters);
            
            // if no results and this is not the first iteration, exit the loop. no more data
            if ($requestsResult->count() == 0 && $iterations > 0) {
                break;
            }
            // if count of results is less than limit, exit the loop. no more data
            if ($requestsResult->count() > 0 && $requestsResult->count() < $newLimit) {
            	
            	// add the new records to the result
            	$newResultArray = $this->getRequestsInfo($requestsResult);
            	foreach ($newResultArray as $key => $value) {
            		if (isset($resultArray[$key])) {
            			$resultArray[$key] = $resultArray[$key] + $newResultArray[$key];
            		} else {
            			$resultArray[$key] = $newResultArray[$key];
            		}
            	}
            	break;
            }
            
            // retry if requests were not found
            $retryLimit = \Application\Module::config('zray', 'zrayRetryLimit');
            $i = 0;
            while ($requestsResult->count() == 0 && ($i * 100) < $retryLimit) {
                $i ++;
                usleep(100000); // sleep for 100 ms
                $requestsResult = $mapper->getRequestsFromTimestamp($fromTimestamp, $newLimit, $initialOffset + $iterations * self::MAX_REQUESTS_PER_ITERATION, $order, $direction, $filters);
            }
            
            // add the new records to the result
            $newResultArray = $this->getRequestsInfo($requestsResult);
            foreach ($newResultArray as $key => $value) {
                if (isset($resultArray[$key])) {
                    $resultArray[$key] = $resultArray[$key] + $newResultArray[$key];
                } else {
                    $resultArray[$key] = $newResultArray[$key];
                }
            }
            $iterations++;
            
            // if no more requests, exit the loop
            if ($newLimit < self::MAX_REQUESTS_PER_ITERATION) {
                break;
            }
            
            // check if there's enough memory, if no, return what collected until now
            if (self::getMemoryUsagePercent() > self::MAX_ACCEPTABLE_MEMORY_USAGE_PERCENT - 20) {
                Log::notice('devBarGetAllRequestsInfoAction: high memory usage: '.self::getMemoryUsagePercent().'% (iteration '.($iterations-1).')');
            }
            if (self::getMemoryUsagePercent() > self::MAX_ACCEPTABLE_MEMORY_USAGE_PERCENT) {
                Log::notice('devBarGetAllRequestsInfoAction: reached limit of '.self::MAX_ACCEPTABLE_MEMORY_USAGE_PERCENT.'%');
                break;
            }
        }
        
        // get the total number of requests
        $totalRequests = $mapper->getRequestsCountFromTimestamp(
            $fromTimestamp, 
            $__limit = 0, $__offset = 0, 
            $__order = NULL, $__direction = NULL, 
			$filters
        );
		
		$resultArray['totalRequests'] = $totalRequests;
		
        $viewModel = new ViewModel($resultArray);
        $viewModel->setTemplate('dev-bar/web-api/dev-bar-get-requests-info');
        return $viewModel;
    }
	
	public function devBarGetRequestFunctionsAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array('requestId' => ''));
		
		$this->validateMandatoryParameters($params, array('requestId'));
		$id = $this->validateInteger($params['requestId'], 'requestId');
		
		$request = $this->getDevBarRequestsMapper()->getRequest($id);

		// check if the request ixists
		if (!$request->getId()) {
			$functions = array();
		} else {
			$functionsMapper = $this->getLocator()->get('DevBar\Db\FunctionsMapper');
			$functions = $functionsMapper->getFunctions($request->getId());
		}
		
		return array('request' => $request, 'functions' => $functions);
	}
	
	public function devBarGetBacktraceAction() {
		$this->isMethodGet();
	
		$params = $this->getParameters(array('id' => ''));
	
		$this->validateMandatoryParameters($params, array('id'));
		$id = $this->validateInteger($params['id'], 'id');
	
		$backtraceMapper = $this->getLocator()->get('DevBar\Db\BacktraceMapper');
		$backtrace = $backtraceMapper->getBacktrace($id);
	
		return array('backtrace' => $backtrace->current());
	}
	
	public function devBarGetCustomDataAction() {
		$this->isMethodGet();
	
		$params = $this->getParameters();
	
		$this->validateMandatoryParameters($params, array('requestId'));
		$id = $this->validateInteger($params['requestId'], 'requestId');
	
		$extensionsMapper = $this->getServiceLocator()->get('DevBar\Db\ExtensionsMapper');
		$dataTypes = $extensionsMapper->findRequestDataTypesMap($id);
		$customData = $extensionsMapper->findCustomDataForRequestId($id);
		$extensionsMetadataMapper = $this->getServiceLocator()->get('DevBar\Db\ExtensionsMetadataMapper');
		$metadata = $extensionsMetadataMapper->metadataForRequestId($id);
		foreach ($metadata as $extensionName => $extensionMetadata) {
		    if (! isset($dataTypes[$extensionName])) {
		      $dataTypes[$extensionName] = array();
		    }
		    if (isset($extensionMetadata['logo']) && ! empty($extensionMetadata['logo']) && file_exists($extensionMetadata['logo'])) {
		        $metadata[$extensionName]['logo'] = base64_encode(file_get_contents($extensionMetadata['logo']));
		    }
		}
		
		try {
			$maxTreeDepth = Module::config('zray', 'zend_gui', 'maxTreeDepth');
		} catch (\ZendServer\Exception $e) {
			$maxTreeDepth = 15;
		}
		
		return array('dataTypesMap' => $dataTypes, 'customData' => $customData, 'metadata' => $metadata, 'maxTreeDepth' => $maxTreeDepth);
	}

	public function devBarGetRequestEnvironmentAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('requestId'));
		$requestId = $this->validateInteger($params['requestId'], 'requestId');
		
		$mapper = $this->getDevBarRequestsMapper();
		$request = $mapper->getRequest($requestId);
		
		$mapper = $this->getServiceLocator()->get('DevBar\Db\SuperglobalsMapper');
		$superglobalsMap = $mapper->getSuperglobals($requestId);
		
		$superglobals = array();
		if (count($superglobalsMap) > 0) {
			foreach ($superglobalsMap as $type => $typeGroup) { /* @var $typeGroup array[\DevBar\SuperGlobalContainer] */
				$typeLabel = str_replace('_', '', strtolower($type));
				foreach ($typeGroup as $superglobalRevision) { /* @var $superglobalRevision \DevBar\SuperGlobalContainer */
					$superglobals[$typeLabel][] = $superglobalRevision->getData();
				}
			}
		}
		
		if (! isset($superglobals['session'])) {
			$superglobals['session'] = array(array());
		}
		
		try {
			$maxTreeDepth = Module::config('zray', 'zend_gui', 'maxTreeDepth');
		} catch (\ZendServer\Exception $e) {
			$maxTreeDepth = 15;
		}
		
		return array('requestId' => $requestId, 'superglobals' => $superglobals, 'request' => $request, 'maxTreeDepth' => $maxTreeDepth);
	}
	
	public function devBarGetDebuggerConfigurationsAction() {
		$this->isMethodGet();
		
		$studioMapper = $this->getLocator()->get('StudioIntegration\Mapper'); /* @var $studioMapper \StudioIntegration\Mapper */
		$configurationMapper = $this->getLocator()->get('Configuration\MapperExtensions');
			
		
		$config = $studioMapper->getConfiguration();
		$debugger = $configurationMapper->selectExtension('Zend Debugger');
		
		// get settings string
		$url = 'http://' . $_SERVER['REMOTE_ADDR'] . ':' . $config->getAutoDetectionPort();
        $retryCounter = 0;
        do {
            $handle = curl_init($url);
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT ,1);
            curl_setopt($handle, CURLOPT_TIMEOUT, 2); //timeout in seconds
            $response = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $settingsString = '';
            if($httpCode == 200) {
                $settingsString = trim($response);
            }
		    curl_close($handle);
            $retryCounter++;
            if ($httpCode != 200) {
                usleep(200000); // sleep for 200 ms
            }
        } while ($httpCode != 200 && $retryCounter < 5);
				
		$configurations = array(
			'autoDetect' => $config->getAutoDetect(),
			'useSsl' => $config->getSsl(),
			'autoDetectPort' => $config->getPort(),
			'autoDetectHost' => $config->getHost(),
			'debuggerEnabled' => $debugger->isLoaded(),
			'settingsString' => $settingsString,
		);
		
		return array('configurations' => $configurations);
	}
	
	/**
	 * @param string $iprange
	 * @throws WebAPI\Exception
	 */
	private function validateIpOrRange($iprange) {
		if (preg_match('#^(?P<ip>[0-9\\.]+)(/(?P<range>[0-9]{1,2}))?$#', $iprange, $matches) > 0) {
			if (long2ip(ip2long($matches['ip'])) != $matches['ip']) {
				throw new WebAPI\Exception(_t('Invalid Ip address passed %s', array($matches['ip'])), WebAPI\Exception::INVALID_PARAMETER);
			}
			
			if (isset($matches['range'])) {
				$range = intval($matches['range']);
				if ($range < 8 || $range > 32) {
					throw new WebAPI\Exception(_t('Invalid Ip mask passed %s', array($matches['range'])), WebAPI\Exception::INVALID_PARAMETER);
				}
			}
		} else {
			throw new WebAPI\Exception(_t('Invalid Ip address passed %s', array($iprange)), WebAPI\Exception::INVALID_PARAMETER);
		}
		return $iprange;
	}
	
	private function getMonitorEventsAggKeys($requestId) {
		$monitorEventsMapper = $this->getLocator()->get('DevBar\Db\MonitorEventsMapper');
		$monitorEvents = $monitorEventsMapper->getMonitorEvents($requestId);
		return (is_null($monitorEvents)) ? array() : $monitorEvents->toArray();
	}
	
	private function enhanceIssuesSet($issues) {
		$newIssues = array();
		$issueIds = array();
		foreach ($issues as $issue) { /* @var $issue \Issue\Container */
			$issueIds[] = $issue->getId();
		}
			
		$eventsMapper = $this->getLocator()->get('EventsGroup\Db\Mapper'); /* @var $eventsMapper \EventsGroup\Db\Mapper */
		$orderedLastEventsResults = array();
		$lastEventsResults = $eventsMapper->getIssuesLastEventGroupData($issueIds);
			
		foreach ($lastEventsResults as $lastEventsResult) { /* @var $lastEventsResult \EventsGroup\Container */
			$orderedLastEventsResults[$lastEventsResult->getIssueId()] = $lastEventsResult->toArray();
		}
			
		$lastEvents = new Set($orderedLastEventsResults, '\EventsGroup\Container');
		
		foreach ($issues as $issue) { /* @var $issue \Issue\Container */
			$maxEventGroup = $lastEvents[$issue->getId()]; /* @var $maxEventGroup \EventsGroup\Container */
			$issue->setMaxEventGroup($maxEventGroup);
			if ($maxEventGroup->hasCodetracing()) {
				$issue->setCodeTracingEventGroupId($maxEventGroup->getEventsGroupId());
			} else {
				$issue->setCodeTracingEventGroupId('');
			}
			
			/*
			$wrapper = new \MonitorUi\Wrapper();
			$moreIssueDetails = $wrapper->getIssueData($issue->getId());
			if (isset($moreIssueDetails[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES])) {
				$issue->setErrorString($moreIssueDetails[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES][ZM_DATA_ATTR_ERROR_STRING]);
			}
			*/
			
			$newIssues[] = $issue;
		}
		
		$issues = new Set($newIssues, null);
	
		return $issues;
	}
	
	private function getRequests($pageId, $lastId) {
	    $retryLimit = \Application\Module::config('zray', 'zrayRetryLimit');
	     
	    $mapper = $this->getDevBarRequestsMapper();
	    $requestsResult = $mapper->getRequests($pageId, $lastId);
	     
	    $i = 0;
	    while (empty($lastId) && 0 == $requestsResult->count() && ($i * 100) < $retryLimit) {
	        $i ++;
	        usleep(100000); // sleep for 100 ms
	        $requestsResult = $mapper->getRequests($pageId, $lastId);
	    }
	     
	    return $requestsResult;
	}
	
	private function hasCustomData($requestsResult) {
	    if ($requestsResult->count() > 0) {
	        $extensionsMapper = $this->getServiceLocator()->get('DevBar\Db\ExtensionsMapper');
	        $customData = $extensionsMapper->findCustomDataForRequestId($requestsResult->current()->getId(), 1);
	        if ($customData->count() > 0) {
	            return true;
	        }
	    }
	     
	    return false;
	}
}
