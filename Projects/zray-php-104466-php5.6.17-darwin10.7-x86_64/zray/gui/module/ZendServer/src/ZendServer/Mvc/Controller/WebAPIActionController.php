<?php
namespace ZendServer\Mvc\Controller;

use ZendServer\Mvc\Controller\ActionController,
Zend\Stdlib,
ZendServer\Mvc\Controller\Plugin\Translate,
ZendServer\Log\Log,
Zend\Validator,
WebAPI;

class WebAPIActionController extends ActionController {

    protected function validateLicenseValid() {
        $licenseMapper = $this->getZemUtilsWrapper(); /* @var $licenseMapper \Configuration\License\ZemUtilsWrapper */
        $licenseInfo = $licenseMapper->getLicenseInfo();
        if (! $licenseInfo->isLicenseOk()) {
            throw new WebAPI\Exception(_t("Zend Server license is expired or no license specified"), WebAPI\Exception::SERVER_NOT_LICENSED); 
        }
        return $licenseInfo;
    }

	/**
	 * same as validateInteger, but for floats as well
	 * @param integer $integer
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateNumber($number, $parameterName) {
		if (!is_numeric($number)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a number",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $number;
	}
	
	/**
	 * same as validateInteger, but for floats as well
	 * @param integer $integer
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validatePositiveNumber($number, $parameterName) {
		$number = $this->validateNumber($number, $parameterName);
		
		if ($number <= 0) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a positive number",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $number;
	}

	/**
	 *
	 * @param integer $integer
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateInteger($integer, $parameterName) {
		if (! is_numeric($integer) || floor($integer) != $integer) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be an integer and not '%s'",array($parameterName, $integer)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return intval($integer);
	}

	/**
	 *
	 * @param integer $integer
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validatePositiveInteger($integer, $parameterName) {
		$integer = $this->validateInteger($integer, $parameterName);
		if ($integer < 0) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a positive integer",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
			
		return $integer;
	}
	
	/**
	 *
	 * @param integer $appId
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateExistingAppId($appId, $parameterName) {
	
		$deploymentMapper = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentMapper \Deployment\Model */
		$apps = $deploymentMapper->getAllApplicationsInfo();
		$apps->setHydrateClass('\Deployment\Application\InfoContainer');
	
		foreach ($apps as $app) {
	
			/* @var $app \Deployment\Application\InfoContainer */
			if ($app->getApplicationId() == intval($appId) ) {
				return;
			}
		}
	
		throw new WebAPI\Exception(_t("Parameter '%s' must be an ID of an existing application",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
	}
	
	/**
	 *
	 * @param integer $serverId
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateExistingServerId($serverId, $parameterName='serverId') {
		$serverId = $this->validateInteger($serverId, $parameterName);
		
		if (! $this->getServersMapper()->isNodeIdExists($serverId)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be an existing serverId - serverId passed '%s' does not seem to be part of the configuration",array($parameterName, $serverId)), WebAPI\Exception::INVALID_PARAMETER);
		}
			
		return $serverId;
	}
	
	/**
	 * validates that the given integer is smaller than a certain passed limit
	 * @param integer $integer
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateMaxInteger($integer, $limit, $parameterName) {
		$integer = $this->validateInteger($integer, $parameterName);
		if ($integer > $limit) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be smaller than limit '%s'",array($parameterName, $limit)), WebAPI\Exception::INVALID_PARAMETER);
		}
			
		return $integer;
	}
	
	/**
	 *
	 * @param integer $percentValue
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validatePercent($percentValue, $parameterName) {
		$percentValue = $this->validateInteger($percentValue, $parameterName);
		
		if ($percentValue < 0 || $percentValue > 100) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid percent integer (0-100)",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return $percentValue;
	}
			
	/**
	 * 
	 * @param string $string
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateStringOrArray($param, $parameterName) {
		if (! is_string($param) && ! is_array($param)) {
			throw new WebAPI\Exception(_t("Parameter '{$parameterName}' must be a string or an array",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $param;
	}

	/**
	 *
	 * @param array $array
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateArray($array, $parameterName) {
		if (! is_array($array)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be an array",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return $array;
	}

	/**
	 *
	 * @param array $array
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateArrayNonEmpty($array, $parameterName) {
		$this->validateArray($array, $parameterName);
		
		if (! $array) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a NON-EMPTY array, an empty array was passed", array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return $array;
	}
			
	/**
	 * 
	 * @param string $string
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateString($string, $parameterName) {
		if (! is_string($string)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a string",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $string;
	}
	
	/**
	 * 
	 * @param string $string
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateName($string, $parameterName) {
		if (!is_string($string)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a string",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
    	// check for regex (start with a letter and continue with 
    	$regex = '%^[A-Za-z0-9_#\/\-\ \.\,\(\)!]+$%i';
		if (!empty($string) && !preg_match($regex, $string)) {
			throw new WebAPI\Exception(_t("Parameter '%s' (%s) contains not allowed characters",array($parameterName, $string)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $string;
	}
	
	/**
	 *
	 * @param string $string
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateStringNonEmpty($string, $parameterName) {
		if (! is_string($string) || strlen($string) == 0) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a NON-EMPTY string",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return $string;
	}

	/**
	 * validate string length
	 * @param string $string
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateStringLength($string, $minLength, $maxLength, $parameterName) {
		if (! is_string($string) || strlen($string) < $minLength || strlen($string) > $maxLength) {
			throw new WebAPI\Exception(_t("Parameter '%s' must contain %d to %d characters", array($parameterName, $minLength, $maxLength)), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return $string;
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
			throw new WebAPI\Exception(_t("Parameter '%s' must be either 'TRUE' or 'FALSE'",array($paramName) ), WebAPI\Exception::INVALID_PARAMETER);
		}
	}

	/**
	 *
	 * @param string $host
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateHost($host, $parameterName) {
		$hostValidator = new Validator\Hostname(array('allow'=>Validator\Hostname::ALLOW_ALL));
		if (! $hostValidator->isValid($host) || ! preg_match('/^[a-z0-9A-Z]+[\.\-_a-z0-9A-Z]*$/i', $host)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid hostname",array($parameterName) ), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $host;
	}
	
	/**
	 *
	 * @param string $host
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateHostWithPort($host, $parameterName) {
		
		$hostToCheck = $host;
		$parts = explode(":", $host);
		if ($parts) {
			$hostToCheck = $parts[0];
			if (isset($parts[1])) {
				$this->validatePositiveInteger($parts[1], 'port specification in ' . $parameterName);				
			}			
		} 
		
		$hostValidator = new Validator\Hostname(array('allow'=>Validator\Hostname::ALLOW_ALL));
		if (! $hostValidator->isValid($hostToCheck)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid hostname",array($parameterName) ), WebAPI\Exception::INVALID_PARAMETER);
		}
	
	
		return $host;
	}
    
	/**
	 * 
	 * @param string $uri
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateUri($uri, $parameterName) {
		$validator = new \Zend\Validator\Uri(array('allowRelative' => false, 'allowAbsolute' => true));
		if (!$validator->isValid($uri)){
		    throw new WebAPI\Exception(_t("Parameter '%s' must be a valid absolute URL",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
	    }
	    
	    return $uri;
	}
	
	/**
	 *
	 * @param string $emailAddress
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 */
	protected function validateEmailAddress($emailAddress, $parameterName) {
		$validator = new \Zend\Validator\EmailAddress();
		
		if (strstr($emailAddress, ',')) {
			$emails = explode(',', $emailAddress);
			foreach ($emails as $email) {
				if ($email) {
					$this->validateEmailAddress($email, $parameterName);
				}
			}
		} else {
			if (! $validator->isValid($emailAddress)) {
				throw new WebAPI\Exception(_t("Parameter '%s' must be a valid email address",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
			}
		}
		
		return $emailAddress;
	}

	/**
	 * @param string $value
	 * @param string $parameterName
	 * @param array $allowedValues
	 * @throws WebAPI\Exception
	 */
	protected function validateAllowedValues($value, $parameterName, array $allowedValues) {
		$lowerCaseValue = strtolower($value);
		$allowedValuesKeys = array_change_key_case(array_flip($allowedValues), CASE_LOWER); // we will now have the values as lower case keys
		
		if (!isset($allowedValuesKeys[$lowerCaseValue])) {		
			$allowedValuesStr = implode(',', $allowedValues);
			throw new WebAPI\Exception(_t("Parameter '%s' must be one of the following values: '%s'. value passed: '%s'",array($parameterName,$allowedValuesStr,$value)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $value;
	}	
	
	/**
	 * @brief Valudate value against the given RegEx
	 * @param string $value 
	 * @param string $regex 
	 * @param string $parameterName
	 * @return 
	 */
	protected function validateRegex($value, $regex, $parameterName) {
		
		// validate the regex first
		$this->validateValidRegex($regex, $parameterName, $useDelimiters = false);
		
		// validate the value
		if (!preg_match($regex, $value)) {
			throw new WebAPI\Exception(_t("Parameter '%s' contains illegal characters", array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 * @brief Check that the $value is a valid regex string
	 * @param <unknown> $value 
	 * @param <unknown> $parameterName 
	 * @return  
	 */
	protected function validateValidRegex($value, $parameterName, $useDelimiters = true) {
		$regex = $useDelimiters ? "#" . $value . "#" : $value;
		if (@preg_match($regex, "") === false) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid regular expression",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 * @brief validate ip addresses in the next format a.b.c.d/r1,e.f.g.h/r2
	 * @param string $value 
	 * @param string $parameterName 
	 * @throws WebAPI\Exception
	 */
	protected function validateIpAddresses($value, $parameterName) {
		if (!preg_match('%^[\\d\\.\\,/]*$%i', $value)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid IPs list",array($parameterName)), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 *
	 * @param array $defaults			
	 * @return \Zend\Stdlib\Parameters
	 */
	protected function getParameters(array $defaults = array()) {
		$parameters = parent::getParameters($defaults);
		$this->printParameters($parameters);
		return $parameters;
	}

	/**
	 *
	 * @return \MonitorUi\Model\Model
	 */
	protected function getMonitorUiModel() {
		return $this->getLocator ()->get('MonitorUi\Model\Model');
	}
	
	/**
	 *
	 * @return \MonitorUi\Model\Model
	 */
	protected function getFilteredMapper() {
		return $this->getLocator()->get('MonitorUi\Model\Model');
	}
	
	/**
	 * 
	 * @param \Zend\Stdlib\ParametersDescription $parameters
	 */
	protected function printParameters($parameters) {
		Log::debug("'{$this->getCmdName()}': the following WebAPI parameters will be used: " . trim(print_r($this->maskParameters($parameters->toArray()), true)));		
	}
	
	protected function maskParameters(array $parameters) {
		$maskedParameters = array();
		foreach ($parameters as $name=>$value) {
			if (is_array($value)) {
				$maskedParameters[$name] = $this->maskParameters($value);
				continue;
			}
			
			if (preg_match('@password@i', $name)) {
				$maskedParameters[$name] = '****';
			} else {
				$maskedParameters[$name] = $value;
			}			
		}
		
		return $maskedParameters;
	}
	
	/**
	 * @brief Check that params has only relevant parameters
	 * @param array $params 
	 * @param array $keys 
	 * @return array
	 */
	public function validateNonRelevantParameters($params, $keys) {
		$nonRelevantParameters = array();
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				if (!in_array($key, $keys)) {
					$nonRelevantParameters[] = $key;
				}
			}
		}
		
		if (!empty($nonRelevantParameters)) {
			$s = count($nonRelevantParameters) > 1 ? 's' : '';
			Log::debug(_t('The parameter'.$s.' %s are not relevant for this action', array('"' . implode('", "', $nonRelevantParameters) . '"')));
			throw new WebAPI\Exception(_t('The parameters %s are not relevant for this action', array('"' . implode('", "', $nonRelevantParameters) . '"')), WebAPI\Exception::UNEXPECTED_PARAMETERS);
		}
		
		return $params;
	}
	
	/**
	 *
	 * @param \Zend\Stdlib\ParametersDescription $request			
	 * @param array $params of parameter names
	 * @throws WebAPI\Exception
	 */
	protected function validateMandatoryParameters(Stdlib\Parameters $requestParams, array $params) {		
		foreach ($params as $param) {
			if ((! isset ( $requestParams [$param] )) || ('' === $requestParams [$param])) {
				Log::debug(_t('This action requires the %s parameter', array($param)));
				throw new WebAPI\Exception(_t('This action requires the %s parameter', array($param)), WebAPI\Exception::MISSING_PARAMETER);
			}
		}
		
		return $params; /// returns the list of defaults!
	}
	
	/**
	 *
	 * @param \Zend\Stdlib\ParametersDescription $request
	 * @param array $params of parameter names
	 * @throws WebAPI\Exception
	 */
	protected function validateExclusiveMandatoryParameters(Stdlib\Parameters $requestParams, array $params) {
		
		foreach ($params as $param) {
			if (isset($requestParams[$param]) && !empty($requestParams[$param])); {
				return true;
			}
		}
		
		Log::debug(_t('This action requires one of these parameters - (%s)', array($param)));
		throw new WebAPI\Exception(_t('This action requires one of these parameters - (%s)', array($param)), WebAPI\Exception::MISSING_PARAMETER);

	}

	/**
	 * @param string $direction
	 * @throws WebAPI\Exception
	 */
	protected function validateDirection($direction) {
		return $this->validateAllowedValues($direction, 'direction', array('asc', 'desc'));
	}
	
	/**
	 * @param integer $offset
	 * @throws WebAPI\Exception
	 */
	protected function validateOffset($offset) {
		return $this->validateInteger($offset, 'offset');
	}
	
	/**
	 * @param integer $limit
	 * @throws WebAPI\Exception
	 */
	protected function validateLimit($limit) {
		return $this->validateInteger($limit, 'limit');
	}
		
	/**
	 * @throws WebAPI\Exception
	 */
	protected function isMethodPost() {
		if (! $this->isMethod('POST')) {
			throw new WebAPI\Exception(_t('This action requires a HTTP POST method'), WebAPI\Exception::UNEXPECTED_HTTP_METHOD );
		}
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	protected function isMethodGet() {
		if (! $this->isMethod ('GET')) {
			throw new WebAPI\Exception(_t('This action requires a HTTP GET method'), WebAPI\Exception::UNEXPECTED_HTTP_METHOD );
		}
	}
		
	/**
	 * @param integer $code
	 * @param string $message 
	 */
	protected function setHttpResponseCode($code, $message = '') {
		$response = $this->getResponse(); /* @var $response  \Zend\Http\Response */
		$response->setStatusCode($code);
		if ($message) {
			$response->setReasonPhrase($message);
		}
	}

	protected function getCmdName() {
		$params = $this->getEvent()->getRouteMatch()->getparams();
		return $params['action'];
	}
	
	/**
	 *
	 * @param \Exception $e
	 * @param string $msg
	 * @throws \Exception
	 */
	protected function handleException($e, $msg) {		
		Log::err("{$this->getCmdName()}: {$msg}");
		Log::debug($e);
		throw $e;
	}	

	/**
	 * 
	 * @param \Exception $e
	 * @param string $msg
	 * @param integer $code
	 * @throws WebAPI\Exception
	 */
	protected function throwWebApiException($e, $msg, $code) {
		Log::err("{$this->getCmdName()}: {$msg}");
		Log::debug($e);		
		throw new WebAPI\Exception(_t("%s: '%s'", array($msg, $e->getMessage())), $code);
	}
		
	/**
	 * @param string $method
	 *			one of POST,GET
	 * @return boolean
	 */
	private function isMethod($method) {
		$request = $this->getRequest (); /* @var $request \Zend\Http\Request */
		return strcasecmp ( $request->getMethod (), $method ) === 0;
	}
	
}

