<?php
namespace Deployment;
use ZendServer\Exception as baseException;
use ZendDeployment_Exception;

class Exception extends baseException {

	const UNKNOWN_ERROR					= 10;
	const INTERNAL_SERVER_ERROR 		= 11;
	const FILE_SYSTEM_ERROR				= 12;
	const DATABASE_ERROR				= 13;
	const DATABASE_MISCONFIGURED		= 14;
	const VHOST_NOT_FOUND				= 15;
	const EXISTING_BASE_URL_ERROR		= 16;
	const INVALID_PACKAGE				= 17;
	const INVALID_PACKAGE_DESCRIPTOR	= 18;
	const APPLICATION_NOT_ROLLBACKABLE	= 19;
	const ALREADY_IN_PROGRESS			= 20;
	const WRONG_TYPE					= 21;
		
	/**
	 * @param ZendDeployment_Exception $zde
	 * @return ZwasComponents_Deployment_Api_Exception
	 */
	public static function fromZendDeploymentException(ZendDeployment_Exception $zde) {
		switch ($zde->getCode()) {
			case ZendDeployment_Exception::DATABASE_ERROR:
				$message = _t('Zend Deployment database error: %s', array($zde->getMessage()));
				$code = self::DATABASE_ERROR;
				break;

			case ZendDeployment_Exception::DATABASE_MISCONFIGURED:	
				$message = _t('Zend Deployment database error: %s', array($zde->getMessage()));
				$code = self::DATABASE_MISCONFIGURED;
				break;
								
			case ZendDeployment_Exception::EXISTING_BASE_URL_ERROR:
				$message = _t('The provided base URL is already in use. Either upgrade an existing application or select a different base URL');
				$code = self::EXISTING_BASE_URL_ERROR;
				break;
								
			case ZendDeployment_Exception::FILE_SYSTEM_ERROR:		
				$message = _t('Zend Deployment file system error: %s', array($zde->getMessage()));
				$code = self::FILE_SYSTEM_ERROR;
				break;
								
			case ZendDeployment_Exception::INTERNAL_SERVER_ERROR:		
				$message = _t('Zend Deployment has encountered an internal error. Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center'); 
				$code = self::INTERNAL_SERVER_ERROR;
				break;
								
			case ZendDeployment_Exception::INVALID_PACKAGE:				
				$message = _t('The provided application package is invalid: %s', array($zde->getMessage()));
				$code = self::INVALID_PACKAGE;
				break;
								
			case ZendDeployment_Exception::INVALID_PACKAGE_DESCRIPTOR:			
				$message = _t('The provided application package is invalid: %s', array($zde->getMessage())); 
				$code = self::INVALID_PACKAGE_DESCRIPTOR;
				break;
								
			case ZendDeployment_Exception::VHOST_NOT_FOUND:			
				$message = _t('The requested virtual host was not found on the system', array($zde->getMessage()));
				$code = self::VHOST_NOT_FOUND;
				break;

			case ZendDeployment_Exception::APPLICATION_NOT_ROLLBACKABLE:			
				$message = _t('The application cannot be rolled back: %s', array($zde->getMessage()));
				$code = self::APPLICATION_NOT_ROLLBACKABLE;
				break;

			case ZendDeployment_Exception::ERROR_ALREADY_IN_PROGRESS:			
				$message = _t('This action cannot be cancelled: %s', array($zde->getMessage()));
				$code = self::ALREADY_IN_PROGRESS;
				break;
			case ZendDeployment_Exception::ERROR_ALREADY_IN_PROGRESS:			
				$message = _t('This action cannot be cancelled: %s', array($zde->getMessage()));
				$code = self::ALREADY_IN_PROGRESS;
				break;
								
			default:		
				$message = _t('Zend Deployment has encountered an internal error. Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center'); 
				$code = self::UNKNOWN_ERROR;
				break;
				
		}
		return new self($message, $code);
	}
	
}