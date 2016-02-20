<?php
namespace WebAPI\Mvc\View\Http;

use ZendServer\Log\Log;

use Zend\Mvc\View\Http\ExceptionStrategy as baseExceptionStrategy,
    WebAPI\Exception,
    Zend\Http\Response as HttpResponse,
    Zend\Mvc\Application,
    Zend\Mvc\MvcEvent,
    Zend\Stdlib\ResponseInterface as Response,
    Zend\View\Model as ViewModel;

class ExceptionStrategy extends baseExceptionStrategy {
    
	const ERRORCODE_MISSING_HTTP_HEADER 			= 'missingHttpHeader';
	const ERRORCODE_UNEXPECTED_HTTP_METHOD		 	= 'unexpectedHttpMethod';
	const ERRORCODE_INVALID_PARAMETER 			 	= 'invalidParameter';
	const ERRORCODE_MISSING_PARAMETER 			 	= 'missingParameter';
	const ERRORCODE_UNEXPECTED_PARAMETERS		 	= 'unexpectedParameters';
	const ERRORCODE_UNKNOWN_METHOD 				 	= 'unknownMethod';
	const ERRORCODE_MALFORMED_REQUEST 			 	= 'malformedRequest';
	const ERRORCODE_AUTH_ERROR 					 	= 'authError';
	const ERRORCODE_TIMES_SKEW_ERROR 			 	= 'timeSkewError';
	const ERRORCODE_NOT_IMPLEMENTED_BY_EDITION 	 	= 'notImplementedByEdition';
	const ERRORCODE_INTERNAL_SERVER_ERROR 		 	= 'internalServerError';
	const ERRORCODE_NO_SUCH_SERVER 				 	= 'noSuchServer';
	const ERRORCODE_CANT_CONNECT_TO_SERVER 		 	= 'cantConnectToServer';
	const ERRORCODE_INVALID_SERVER				 	= 'invalidServer';
	const ERRORCODE_WRONG_PASSWORD				 	= 'wrongPassword';
	const ERRORCODE_SERVER_NOT_DISABLED				= 'serverNotDisabled';
	const ERRORCODE_RESTART_FAILED				 	= 'restartFailed';
	const ERRORCODE_APPLICATION_DOES_NOT_EXISTS	 	= 'noSuchApplication';
	const ERRORCODE_TEMPORARILY_LOCKED			 	= 'temporarilyLocked';
	const ERRORCODE_INVALID_SERVER_RESPONSE		 	= 'invalidServerResponse';
	const ERRORCODE_API_VERSION_NOT_SUPPORTED	 	= 'unsupportedApiVersion';
	const ERRORCODE_INSUFFICIENT_ACCESS_LEVEL	 	= 'insufficientAccessLevel';
	const ERRORCODE_CONFIGURATION_IMPORT_FAILED	 	= 'importFailed';
	const ERRORCODE_CONFIGURATION_SYSTEM_MISMATCH 	= 'systemMismatch';
	const ERRORCODE_SERVER_ALREADY_CONNECTED		= 'alreadyConnected';
	const ERRORCODE_NO_ACTIVE_SERVERS			 	= 'noActiveServers';
	const ERRORCODE_IMPORT_FAILED				 	= 'importFailed';
	const ERRORCODE_SERVER_NOT_LICENSED			 	= 'serverNotLicensed';
	const ERRORCODE_SERVER_NOT_CONFIGURED		 	= 'serverNotConfigured';
	const ERRORCODE_DIRECT_ACCESS_FORBIDDEN		 	= 'directAccessForbidden';
	const ERRORCODE_BASE_URL_CONFLICT			 	= 'baseUrlConflict';
	const ERRORCODE_NO_SUCH_APPLICATION			 	= 'noSuchApplication';
	const ERRORCODE_APPLICATION_CONFLICT			= 'applicationConflict';
	const ERRORCODE_MISSING_VIRTUAL_HOST			= 'missingVirtualHost';
	const ERRORCODE_SERVER_VERSION_MISMATCH		 	= 'serverVersionMismatch';
	const ERRORCODE_NO_ROLLBACK_AVAILABLE		 	= 'noRollbackAvailable';
	const ERRORCODE_NO_SUCH_TRACE				 	= 'noSuchTrace';
	const ERRORCODE_NO_SUCH_ISSUE				 	= 'noSuchIssue';
	const ERRORCODE_NO_SUCH_EVENTGROUP			 	= 'noSuchEventGroup';
	const ERRORCODE_NO_SUCH_EXTENSION			 	= 'noSuchExtension';
	const ERRORCODE_NO_SUCH_MONITOR_RULE			= 'noSuchMonitorRule';
	const ERRORCODE_NO_SUCH_AUDIT_MESSAGE		 	= 'noSuchAuditMessage';
	const ERRORCODE_SMTP_HOST_IS_MISSING		    = 'smtpHostIsMissing';
	const ERRORCODE_SMTP_PORT_IS_MISSING		    = 'smtpPortIsMissing';
	const ERRORCODE_SMTP_CONNECTION_CLASS_IS_MISSING= 'smtpConnectionClassIsMissing';
	const ERRORCODE_EMAIL_SEND_TRANSPORT_IS_MISSING = 'emailSendTransportIsMissing';
	const ERRORCODE_LOG_FILE_NOT_READABLE 		 	= 'logsFileNotReadable';
	const ERRORCODE_OUTPUT_TYPES_LIMITED 		 	= 'outputTypesLimited';
	const ERRORCODE_NO_SUCH_FILTER				 	= 'noSuchFilter';
	const ERRORCODE_SERVER_NOT_READY				= 'serverNotReady';
	const ERRORCODE_WEBAPI_KEY_REMOVE_FAILED		= 'webAPIKeyRemoveFailed';
	const ERRORCODE_DB_CREATION_LOCKED				= 'dbCreationLocked';
	const NOT_SUPPORTED_BY_EDITION					= 'notSupportedByEdition';
	const PARAMETER_VALUE_NOT_SUPPORTED_BY_EDITION	= 'parameterValueNotSupportedByEdition';
	const SNAPSHOT_ALREADY_EXISTS					= 'snapshotAlreadyExists';
	const ERRORCODE_SERVER_NON_MATCHING_PROFILE     = 'serverNonMatchingProfile';
	const ERRORCODE_CLUSTER_NOT_ALLOWED     		= 'clusterNotAllowed';
	const ERRORCODE_NO_SUCH_LIBRARY 				= 'noSuchLibrary';
	const ERRORCODE_LIBRARY_ALREADY_EXISTS 			= 'libraryAlreadyExists';
	const ERRORCODE_NO_SUCH_LIBRARY_VERSION 		= 'noSuchLibraryVersion';
	const ERRORCODE_UNMET_DEPENDENCY 		        = 'unmetDependency';
	const ERRORCODE_SERVER_ALREADY_BOOTSTRAPPED		= 'alreadyBootstrapped';
	const ERRORCODE_WEBSERVER_CONFIGURATION_ERROR	= 'webserverConfigurationError';
	const ERRORCODE_VIRTUAL_HOST_ALREADY_EXISTS	    = 'virtualHostAlreadyExists';
	const ERRORCODE_VIRTUAL_HOST_INVALID			= 'virtualHostInvalid';
	const ERRORCODE_NO_SUCH_VHOST					= 'noSuchVhost';
	const ERRORCODE_VIRTUAL_HOST_IS_NOT_MANAGED		= 'virtualHostIsNotManaged';
	const ERRORCODE_VIRTUAL_HOST_HAS_DEPENDENTS		= 'virtualHostHasDependents';
	const DEPLOYMENT_DOWNLOAD_ALREADY_EXISTS		= 'deploymentDownloadAlreadyExists';
	const DEPLOYMENT_DOWNLOAD_NOT_EXISTS			= 'deploymentDownloadNotExists';
	const ERRORCODE_VIRTUAL_HOST_SSL_ALREADY_EXISTS	= 'virtualHostSslAlreadyExists';
	const ERRORCODE_NO_SUCH_REQUEST					= 'noSuchRequest';
	const ERRORCODE_NO_SUCH_ACCESS_TOKEN			= 'noSuchAccessToken';
	const ERRORCODE_URL_INSIGHT_RULE_ALREADY_EXISTS	= 'urlinsightRuleAlreadyExists';
	const ERRORCODE_URL_INSIGHT_NO_SUCH_RULE	    	= 'urlinsightNoSuchRule';
	const ERRORCODE_NO_SUCH_DIRECTIVE	    		= 'noSuchDirective';
	const ERRORCODE_URL_INSIGHT_NO_SUCH_URL		    = 'urlinsightNoSuchUrl';
	const ERRORCODE_NO_SUCH_ASSET		    		= 'noSuchAsset';
	const ERRORCODE_NO_SUCH_PLUGIN		    		= 'noSuchPlugin';
	const ERRORCODE_DUPLICATE_RECORD		    	= 'duplicateRecordError';
	const ERRORCODE_MISSING_RECORD		    	    = 'missingRecordError';
	const ERRORCODE_MISSING_FILE		    	    = 'missingFileOrFolderError';
	const ERRORCODE_PERMISSIONS_ERROR		        = 'permissionsError';
	const ERRORCODE_DATABASE_LOCKED		        	= 'databaseIsLocked';
	

	/**
     * Name of exception template
     * @var string
     */
    protected $exceptionTemplate = 'error/index';
	
    private static $errorCodes = array(
			Exception::MISSING_HTTP_HEADER 			 => self::ERRORCODE_MISSING_HTTP_HEADER ,
			Exception::UNEXPECTED_HTTP_METHOD		 => self::ERRORCODE_UNEXPECTED_HTTP_METHOD,
			Exception::INVALID_PARAMETER 			 => self::ERRORCODE_INVALID_PARAMETER ,
			Exception::MISSING_PARAMETER 			 => self::ERRORCODE_MISSING_PARAMETER ,
			Exception::UNEXPECTED_PARAMETERS 		 => self::ERRORCODE_UNEXPECTED_PARAMETERS ,
			Exception::UNKNOWN_METHOD 				 => self::ERRORCODE_UNKNOWN_METHOD,
			Exception::MALFORMED_REQUEST 			 => self::ERRORCODE_MALFORMED_REQUEST ,
			Exception::AUTH_ERROR 					 => self::ERRORCODE_AUTH_ERROR ,
			Exception::TIMES_SKEW_ERROR 			 => self::ERRORCODE_TIMES_SKEW_ERROR ,
			Exception::NOT_IMPLEMENTED_BY_EDITION 	 => self::ERRORCODE_NOT_IMPLEMENTED_BY_EDITION ,
			Exception::INTERNAL_SERVER_ERROR 		 => self::ERRORCODE_INTERNAL_SERVER_ERROR ,
			Exception::NO_SUCH_SERVER 				 => self::ERRORCODE_NO_SUCH_SERVER,
			Exception::CANT_CONNECT_TO_SERVER 		 => self::ERRORCODE_CANT_CONNECT_TO_SERVER ,
			Exception::INVALID_SERVER				 => self::ERRORCODE_INVALID_SERVER,
			Exception::WRONG_PASSWORD				 => self::ERRORCODE_WRONG_PASSWORD,
			Exception::SERVER_NOT_DISABLED			 => self::ERRORCODE_SERVER_NOT_DISABLED,
			Exception::RESTART_FAILED				 => self::ERRORCODE_RESTART_FAILED,
			Exception::APPLICATION_DOES_NOT_EXISTS	 => self::ERRORCODE_APPLICATION_DOES_NOT_EXISTS,
			Exception::TEMPORARILY_LOCKED			 => self::ERRORCODE_TEMPORARILY_LOCKED,
			Exception::INVALID_SERVER_RESPONSE		 => self::ERRORCODE_INVALID_SERVER_RESPONSE,
			Exception::API_VERSION_NOT_SUPPORTED	 => self::ERRORCODE_API_VERSION_NOT_SUPPORTED,
			Exception::INSUFFICIENT_ACCESS_LEVEL	 => self::ERRORCODE_INSUFFICIENT_ACCESS_LEVEL,
			Exception::CONFIGURATION_IMPORT_FAILED	 => self::ERRORCODE_CONFIGURATION_IMPORT_FAILED,
			Exception::CONFIGURATION_SYSTEM_MISMATCH => self::ERRORCODE_CONFIGURATION_SYSTEM_MISMATCH,
			Exception::SERVER_ALREADY_CONNECTED		 => self::ERRORCODE_SERVER_ALREADY_CONNECTED,
			Exception::NO_ACTIVE_SERVERS			 => self::ERRORCODE_NO_ACTIVE_SERVERS,
			Exception::IMPORT_FAILED				 => self::ERRORCODE_IMPORT_FAILED,
			Exception::SERVER_NOT_LICENSED			 => self::ERRORCODE_SERVER_NOT_LICENSED,
			Exception::SERVER_NOT_CONFIGURED		 => self::ERRORCODE_SERVER_NOT_CONFIGURED,
			Exception::DIRECT_ACCESS_FORBIDDEN		 => self::ERRORCODE_DIRECT_ACCESS_FORBIDDEN,
			Exception::BASE_URL_CONFLICT			 => self::ERRORCODE_BASE_URL_CONFLICT,
			Exception::NO_SUCH_APPLICATION			 => self::ERRORCODE_NO_SUCH_APPLICATION,
			Exception::APPLICATION_CONFLICT			 => self::ERRORCODE_APPLICATION_CONFLICT,
			Exception::MISSING_VIRTUAL_HOST			 => self::ERRORCODE_MISSING_VIRTUAL_HOST,
			Exception::SERVER_VERSION_MISMATCH		 => self::ERRORCODE_SERVER_VERSION_MISMATCH,
			Exception::NO_ROLLBACK_AVAILABLE		 => self::ERRORCODE_NO_ROLLBACK_AVAILABLE,
			Exception::NO_SUCH_TRACE				 => self::ERRORCODE_NO_SUCH_TRACE,
			Exception::NO_SUCH_ISSUE				 => self::ERRORCODE_NO_SUCH_ISSUE,
			Exception::NO_SUCH_EVENTGROUP			 => self::ERRORCODE_NO_SUCH_EVENTGROUP,
    		Exception::NO_SUCH_EXTENSION			 => self::ERRORCODE_NO_SUCH_EXTENSION,
    		Exception::NO_SUCH_MONITOR_RULE			 => self::ERRORCODE_NO_SUCH_MONITOR_RULE,
    		Exception::NO_SUCH_AUDIT_MESSAGE		 => self::ERRORCODE_NO_SUCH_AUDIT_MESSAGE,
            Exception::SMTP_HOST_IS_MISSING		     => self::ERRORCODE_SMTP_HOST_IS_MISSING,
            Exception::SMTP_PORT_IS_MISSING		     => self::ERRORCODE_SMTP_PORT_IS_MISSING,
            Exception::SMTP_CONNECTION_CLASS_IS_MISSING  => self::ERRORCODE_SMTP_CONNECTION_CLASS_IS_MISSING ,
            Exception::EMAIL_SEND_TRANSPORT_IS_MISSING  => self::ERRORCODE_EMAIL_SEND_TRANSPORT_IS_MISSING ,
    		Exception::LOG_FILE_NOT_READABLE 		 => self::ERRORCODE_LOG_FILE_NOT_READABLE ,
    		Exception::OUTPUT_TYPES_LIMITED 		 => self::ERRORCODE_OUTPUT_TYPES_LIMITED ,
    		Exception::NO_SUCH_FILTER				 => self::ERRORCODE_NO_SUCH_FILTER,
    		Exception::SERVER_NOT_READY				 => self::ERRORCODE_SERVER_NOT_READY,
    		Exception::WEBAPI_KEY_REMOVE_FAILED		 => self::ERRORCODE_WEBAPI_KEY_REMOVE_FAILED,
    		Exception::DB_CREATION_LOCKED			 => self::ERRORCODE_DB_CREATION_LOCKED,
    		Exception::NOT_SUPPORTED_BY_EDITION		 => self::NOT_SUPPORTED_BY_EDITION,
    		Exception::PARAMETER_VALUE_NOT_SUPPORTED_BY_EDITION		 => self::PARAMETER_VALUE_NOT_SUPPORTED_BY_EDITION,   
    		Exception::SNAPSHOT_ALREADY_EXISTS		 => self::SNAPSHOT_ALREADY_EXISTS,
    		Exception::SERVER_NON_MATCHING_PROFILE   => self::ERRORCODE_SERVER_NON_MATCHING_PROFILE,
    		Exception::CLUSTER_NOT_ALLOWED   		=> self::ERRORCODE_CLUSTER_NOT_ALLOWED,
    		Exception::NO_SUCH_LIBRARY   			=> self::ERRORCODE_NO_SUCH_LIBRARY,
    		Exception::LIBRARY_ALREADY_EXISTS		=> self::ERRORCODE_LIBRARY_ALREADY_EXISTS,
    		Exception::NO_SUCH_LIBRARY_VERSION		=> self::ERRORCODE_NO_SUCH_LIBRARY_VERSION,
    		Exception::UNMET_DEPENDENCY		        => self::ERRORCODE_UNMET_DEPENDENCY,
    		Exception::SERVER_ALREADY_BOOTSTRAPPED	=> self::ERRORCODE_SERVER_ALREADY_BOOTSTRAPPED,
    		Exception::WEBSERVER_CONFIGURATION_ERROR=> self::ERRORCODE_WEBSERVER_CONFIGURATION_ERROR,
    		Exception::VIRTUAL_HOST_ALREADY_EXISTS  => self::ERRORCODE_VIRTUAL_HOST_ALREADY_EXISTS,
    		Exception::VIRTUAL_HOST_INVALID		 => self::ERRORCODE_VIRTUAL_HOST_INVALID,
    		Exception::NO_SUCH_VHOST			 => self::ERRORCODE_NO_SUCH_VHOST,
    		Exception::VIRTUAL_HOST_IS_NOT_MANAGED => self::ERRORCODE_VIRTUAL_HOST_IS_NOT_MANAGED,
    		Exception::VIRTUAL_HOST_HAS_DEPENDENTS => self::ERRORCODE_VIRTUAL_HOST_HAS_DEPENDENTS,
    		Exception::DEPLOYMENT_DOWNLOAD_ALREADY_EXISTS => self::DEPLOYMENT_DOWNLOAD_ALREADY_EXISTS,
    		Exception::DEPLOYMENT_DOWNLOAD_NOT_EXISTS => self::DEPLOYMENT_DOWNLOAD_NOT_EXISTS,
    		Exception::VIRTUAL_HOST_SSL_ALREADY_EXISTS => self::ERRORCODE_VIRTUAL_HOST_SSL_ALREADY_EXISTS,
    		Exception::NO_SUCH_REQUEST			 => self::ERRORCODE_NO_SUCH_REQUEST,
    		Exception::NO_SUCH_ACCESS_TOKEN			 => self::ERRORCODE_NO_SUCH_ACCESS_TOKEN,
    		Exception::URL_INSIGHT_RULE_ALREADY_EXISTS		 => self::ERRORCODE_URL_INSIGHT_RULE_ALREADY_EXISTS,
    		Exception::URL_INSIGHT_NO_SUCH_RULE		 		 => self::ERRORCODE_URL_INSIGHT_NO_SUCH_RULE,
    		Exception::NO_SUCH_DIRECTIVE		 	 => self::ERRORCODE_NO_SUCH_DIRECTIVE,
    		Exception::URL_INSIGHT_NO_SUCH_URL		 	 	 => self::ERRORCODE_URL_INSIGHT_NO_SUCH_URL,
    		Exception::NO_SUCH_ASSET		 	 	 => self::ERRORCODE_NO_SUCH_ASSET,
    		Exception::NO_SUCH_PLUGIN		 	 	 => self::ERRORCODE_NO_SUCH_PLUGIN,
    		Exception::DUPLICATE_RECORD		 	 	 => self::ERRORCODE_DUPLICATE_RECORD,
    		Exception::MISSING_RECORD		 	 	 => self::ERRORCODE_MISSING_RECORD,
    		Exception::MISSING_FILE		 	 		 => self::ERRORCODE_MISSING_FILE,
    		Exception::PERMISSIONS_ERROR		 	 => self::ERRORCODE_PERMISSIONS_ERROR,
    		Exception::DATABASE_LOCKED		 	 	 => self::ERRORCODE_DATABASE_LOCKED,
    	);
	
	
	private static $httpCodes = array(
			Exception::MISSING_HTTP_HEADER 			=> 400,
			Exception::UNEXPECTED_HTTP_METHOD	 	=> 400,
			Exception::INVALID_PARAMETER 			=> 400,
			Exception::UNEXPECTED_PARAMETERS 		=> 400,
			Exception::MISSING_PARAMETER 			=> 400,
			Exception::UNKNOWN_METHOD 				=> 400,
			Exception::MALFORMED_REQUEST 			=> 400,
			Exception::AUTH_ERROR 					=> 401,
			Exception::TIMES_SKEW_ERROR 			=> 401,
			Exception::NOT_IMPLEMENTED_BY_EDITION 	=> 405,
			Exception::INTERNAL_SERVER_ERROR 		=> 500,
			Exception::NO_SUCH_SERVER 				=> 404,
			Exception::CANT_CONNECT_TO_SERVER 		=> 500,
			Exception::INVALID_SERVER				=> 500,
			Exception::WRONG_PASSWORD				=> 400,
			Exception::SERVER_NOT_DISABLED			=> 400,
			Exception::RESTART_FAILED				=> 500,
			Exception::APPLICATION_DOES_NOT_EXISTS	=> 404,
			Exception::TEMPORARILY_LOCKED			=> 503,
			Exception::INVALID_SERVER_RESPONSE		=> 500,
			Exception::API_VERSION_NOT_SUPPORTED	=> 406,
			Exception::INSUFFICIENT_ACCESS_LEVEL	=> 401,
			Exception::CONFIGURATION_IMPORT_FAILED	=> 500,
			Exception::CONFIGURATION_SYSTEM_MISMATCH=> 409,
			Exception::SERVER_ALREADY_CONNECTED		=> 400,
			Exception::NO_ACTIVE_SERVERS			=> 500,
			Exception::IMPORT_FAILED				=> 500,
			Exception::SERVER_NOT_LICENSED			=> 500,
			Exception::SERVER_NOT_CONFIGURED		=> 500,
			Exception::DIRECT_ACCESS_FORBIDDEN		=> 403,
			Exception::BASE_URL_CONFLICT			=> 409,
			Exception::NO_SUCH_APPLICATION			=> 404,
			Exception::APPLICATION_CONFLICT			=> 409,
			Exception::MISSING_VIRTUAL_HOST			=> 400,
			Exception::SERVER_VERSION_MISMATCH		=> 500,
			Exception::NO_ROLLBACK_AVAILABLE		=> 404,
			Exception::NO_SUCH_TRACE				=> 404,
			Exception::NO_SUCH_ISSUE				=> 404,
			Exception::NO_SUCH_EVENTGROUP			=> 404,
			Exception::NO_SUCH_EXTENSION			=> 404,
			Exception::NO_SUCH_MONITOR_RULE			=> 404,
			Exception::NO_SUCH_AUDIT_MESSAGE		=> 404,
	        Exception::SMTP_HOST_IS_MISSING		    => 500,
	        Exception::SMTP_PORT_IS_MISSING		    => 500,
	        Exception::SMTP_CONNECTION_CLASS_IS_MISSING => 500,
	        Exception::EMAIL_SEND_TRANSPORT_IS_MISSING => 500,
			Exception::LOG_FILE_NOT_READABLE => 404,
			Exception::OUTPUT_TYPES_LIMITED 		=> 406,
			Exception::NO_SUCH_FILTER				=> 404,
			Exception::SERVER_NOT_READY				=> 406,
			Exception::WEBAPI_KEY_REMOVE_FAILED		=> 407,
			Exception::DB_CREATION_LOCKED			=> 409,
			Exception::NOT_SUPPORTED_BY_EDITION		 => 410,
			Exception::PARAMETER_VALUE_NOT_SUPPORTED_BY_EDITION		 => 406,			
			Exception::SNAPSHOT_ALREADY_EXISTS		 => 400,
			Exception::SERVER_NON_MATCHING_PROFILE   => 500,
			Exception::CLUSTER_NOT_ALLOWED   => 500,
			Exception::NO_SUCH_LIBRARY   => 404,
			Exception::LIBRARY_ALREADY_EXISTS        => 409,
			Exception::NO_SUCH_LIBRARY_VERSION       => 404,
			Exception::UNMET_DEPENDENCY              => 400,
			Exception::SERVER_ALREADY_BOOTSTRAPPED   => 406,
			Exception::WEBSERVER_CONFIGURATION_ERROR => 406,
			Exception::VIRTUAL_HOST_ALREADY_EXISTS   => 409,
			Exception::VIRTUAL_HOST_INVALID		     => 500,
			Exception::NO_SUCH_VHOST			     => 404,
			Exception::VIRTUAL_HOST_IS_NOT_MANAGED   => 409,
			Exception::VIRTUAL_HOST_HAS_DEPENDENTS   => 409,
			Exception::DEPLOYMENT_DOWNLOAD_ALREADY_EXISTS => 400,
			Exception::DEPLOYMENT_DOWNLOAD_NOT_EXISTS => 404,
			Exception::VIRTUAL_HOST_SSL_ALREADY_EXISTS => 409,
			Exception::NO_SUCH_REQUEST			     => 404,
			Exception::NO_SUCH_ACCESS_TOKEN		     => 404,
			Exception::URL_INSIGHT_RULE_ALREADY_EXISTS 	=> 400,
			Exception::URL_INSIGHT_NO_SUCH_RULE	      => 404,
			Exception::NO_SUCH_DIRECTIVE		      => 404,
			Exception::URL_INSIGHT_NO_SUCH_URL	      => 404,
			Exception::NO_SUCH_ASSET			      => 404,
			Exception::NO_SUCH_PLUGIN			      => 404,
			Exception::DUPLICATE_RECORD               => 400,
			Exception::MISSING_RECORD                 => 400,
			Exception::MISSING_FILE                	  => 400,
			Exception::PERMISSIONS_ERROR              => 400,
			Exception::DATABASE_LOCKED                => 500,
	);
    
    /**
     * Create an exception view model, and set the HTTP status code
     * 
     * @todo   dispatch.error does not halt dispatch unless a response is 
     *         returned. As such, we likely need to trigger rendering as a low
     *         priority dispatch.error event (or goto a render event) to ensure
     *         rendering occurs, and that munging of view models occurs when
     *         expected.
     * @param  MvcEvent $e 
     * @return void
     */
    public function prepareExceptionViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }
        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                // Specifically not handling these
                return;

            case Application::ERROR_EXCEPTION:
            default:
                $exception = $e->getParam('exception');
                Log::err($exception);
                if (! $exception instanceof Exception) {
                	Log::notice("WebAPI action '{$e->getParam('webapi-action')}' threw an exception of type " . get_class($exception));
                	$exception = new Exception($exception->getMessage(), Exception::INTERNAL_SERVER_ERROR, $exception);
                }
                
                $model     = new ViewModel\ViewModel(array(
                    'errorCode'     => self::$errorCodes[$exception->getCode()],
                    'errorMessage'  => $exception->getMessage(),
                ));
                $model->setTemplate($this->getExceptionTemplate());
                $e->setResult($model);

                $response = $e->getResponse();
                if (!$response) {
                    $response = new HttpResponse();
                    $e->setResponse($response);
                }
                $response->setStatusCode(self::$httpCodes[$exception->getCode()]);
                break;
        }
    }
}