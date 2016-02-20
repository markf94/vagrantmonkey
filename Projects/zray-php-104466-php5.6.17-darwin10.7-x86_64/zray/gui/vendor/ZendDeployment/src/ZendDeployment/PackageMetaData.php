<?php

require_once 'PackageMetaData/Interface.php';
require_once dirname ( __FILE__ ) . '/PackageFile.php';
use ZendServer\Log\Log;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

class ZendDeployment_PackageMetaData implements ZendDeployment_PackageMetaData_Interface {
	
	private $_appName;
	private $_appVersion;
	private $_appEula;
	private $_appLogo;
	private $_appPrerequisites;
	private $_appRequiredParamsIni;
	private $_packageDescriptor;
	private $_packagePath;
	private $_healthCheckPath;
	private $_packageId;
	private $_monitorRules;
	private $_pageCacheRules;
	private $_updateUrl;
	private $_releaseDate;

	private $_monitorRulesFileExists = false;
	private $_pageCacheRulesFileExists = false;
	
	
	/**
	 * @return the $_releaseDate
	 */
	public function getReleaseDate() {
		return $this->_releaseDate;
	}

	/**
	 * @param field_type $_releaseDate
	 */
	public function setReleaseDate($_releaseDate) {
		$this->_releaseDate = $_releaseDate;
	}

	/**
	 * @return the $_updateUrl
	 */
	public function getUpdateUrl() {
		return $this->_updateUrl;
	}
	
	/**
	 * @param field_type $_updateUrl
	 */
	public function setUpdateUrl($_updateUrl) {
		$this->_updateUrl = $_updateUrl;
	}
	
	/**
	 * @param field_type $_healthCheckPath
	 */
	public function setHealthCheckPath($_healthCheckPath) {
		$this->_healthCheckPath = $_healthCheckPath;
	}

	public function __construct() {
		$this->_packageId = -1;
	}
	
	public function setAppName($name) {
		$this->_appName = $name;
	}
	
	public function setAppVersion($version) {
		$this->_appVersion = $version;
	}
	
	public function setAppEula($eula) {
		$this->_appEula = $eula;
	}
	
	public function setAppLogo($logo) {
		$this->_appLogo = $logo;
	}
	
	public function setAppPrerequisites($preqs) {
		$this->_appPrerequisites = $preqs;
	}
	
	public function setMonitorRules($rules) {
		$this->_monitorRules = $rules;
	}
	
	public function getMonitorRules() {
		return $this->_monitorRules;
	}
	
	public function setPackageDescriptor($descXml) {
		if (!function_exists('simplexml_load_string')) {
			Log::warn(_t('the function simplexml_load_string is not defined (%s)', array(__METHOD__)));
			return;
		}
		
		if ($descXml) {
			$this->_packageDescriptor = $descXml;
			
			$xml = @simplexml_load_string($descXml);
			
			if ($xml !== false) {
    			$this->_appRequiredParamsIni = self::createPackageParams ( $xml->parameters );
    			//ZDBG3("Required params:");
    			//ZDBG3(implode(PHP_EOL,$this->_appRequiredParamsIni ));
    			$this->_appPrerequisites = $xml->dependencies?$xml->dependencies->asXML():"";
    			
    			if ($xml->healthcheck) {
    				$this->_healthCheckPath = (string) $xml->healthcheck;
    			} else {
    				$this->_healthCheckPath = NULL;
    			}
			} else {
			    $json = json_decode($descXml);
			    
			    // take the "<dependencies>" json part, convert to XML, save it
			    if (property_exists($json, 'dependencies')) {
    			    $dependencies = (array)$json->dependencies;
    			    $dependenciesXML = new SimpleXMLElement('<dependencies/>');
    			    $required = $dependenciesXML->addChild('required');
    			    $xml = ZendDeployment_PackageFile::arrayToXml($dependencies, $required); // should be XML object
    			    $this->_appPrerequisites = $dependenciesXML->asXML();
			    }
			}		
		}
	}
	
	
	/**
	 * Create a ini input that will be used as input for Zend Form
	 * @param unknown_type $paramsXml
	 * @return array ini contents
	 */
	static public function createPackageParams($paramsXml) {
		$spec = array ();
		
		$paramsCount = count ( $paramsXml->parameter );
		$currentGroup = "";
		$currentGroupId = "";
		$currentFieldset = NULL;
		$currentFieldsetValidators = NULL;
		$inputFilters = array();
				
		$spec['input_filter'] = array();
		
		for($i = 0; $i < $paramsCount; $i ++) {
			$param = $paramsXml->parameter [$i];
			$elValidators = array();
			$elId = ( string ) $param ['id'];
			
			$display = ( string ) $param ['display'];
			$displayGroup = NULL;
			if (strstr($display, ".")) {
				$displayParts = explode ( ".", $display );
				//$displayParts = array_pop($displayParts); ///// workaround to not handling fieldsets
				//$displayGroup = array_shift($displayParts);	
				$elLabel = array_pop($displayParts);
			} else {
				$elLabel = $display;	
			}			
			
			if ($displayGroup){
				if ($currentGroup != $displayGroup) {
					//new group
					
					if ($currentFieldset) {
						$spec['fieldsets'][] = $currentFieldset;
						//$inputFilters[$displayGroup] = array("validators" => $currentFieldsetValidators);
					}
					
					
					$currentGroup = $displayGroup;
					$currentGroupId = str_replace(" ", "",strtolower($displayGroup));
					$currentFieldset = array( 
						'spec' => array (
										'attributes' => array(
														'name' => $currentGroup
														),
										'elements' => array()
										),
								
						);
					$currentFieldsetValidators = array ();
				}
			}				
			
			$elIsRequired = (( string ) $param ['required'] == "true");
									
			$elDescription = trim ( ( string ) $param->description );
			$elDefaultValue = trim ( ( string ) $param->defaultvalue );
			
			$elIsReadOnly = false;
			if (isset($param['readonly'])) {
				$readonly = (string) $param['readonly'];
				if ($readonly == "true") {
					$elIsReadOnly = true;
				}
			}
			
			$type = ( string ) $param ['type'];
			$elType = "";
			$elOptions = array();
			$elAttrOptions = array();
			$elValidator = NULL;
			switch ($type) {
				case "string" :
					$elType = 'Zend\Form\Element\Text';
					break;
				case "hostname" :
					$elType = 'Zend\Form\Element\Text';
					
					$elValidator = new Validator\Hostname();
					$elValidator->setAllow(7);
										
					break;
					
				case "number" :
					$elType = 'Zend\Form\Element\Text';		
					$elValidator = new Validator\Digits();			
					break;
				
				case "email" :
					$elType = 'Zend\Form\Element\Email';
					$elValidator = new Validator\EmailAddress();
										
					break;
					
				case "choice" :
					$elType = 'Zend\Form\Element\Select';
					
					// fill in the enum options
					
					if ($param->validation) {
						if ($param->validation->enums) {
							$enumsCount = count ( $param->validation->enums->enum );
							for($j = 0; $j < $enumsCount; $j ++) {
								$enum = ( string ) $param->validation->enums->enum [$j];
								$elAttrOptions[$enum] = $enum;
							}
						}
					}
					
					break;
				
				case "password" :
					$elType = 'Zend\Form\Element\Password';
					
					
					//check if it needs to be identical to other field
					if (isset($param['identical'])) {
						$identical = (string) $param['identical'];
						$elValidator = new Validator\Identical($identical);												
					}					
					
					break;
				
				case "checkbox" :
					$elType = 'Zend\Form\Element\Checkbox';
					
					if (strval($param->defaultvalue) === 'false') {
						$elDefaultValue = '0';
					} elseif (strval($param->defaultvalue) === 'true') {
						$elDefaultValue = '1';
					}

					break;
				
				default :
					throw new ZendDeployment_Exception ( "Invalid parameter type $type", ZendDeployment_Exception_Interface::INVALID_PACKAGE_DESCRIPTOR );
			
			}

			$elAttributes = array (
					'readonly' => $elIsReadOnly?'readonly':'',
					'value' => $elDefaultValue					
				);
			
			if ($elDescription) {
				$elAttributes['description'] = $elDescription;
			}
			
			if ($elAttrOptions) {
				$elAttributes['options'] = $elAttrOptions;
			}
			
			$elAttributes['required'] = $elIsRequired;
			
			
			$elOptions['label'] = $elLabel;
			
			
			$newElement = array('spec'=>array (
				'type' => $elType,
				'name' => $elId,
				'attributes' => $elAttributes,
				'options' => $elOptions,
				//'validators' => array($elValidator)
				));		
			
			if ($elDescription) {
				// do something here
			}
				
			if ($currentFieldset) {
				$currentFieldset['spec']['elements'][] = $newElement;
			} else {
				$spec['elements'][] = $newElement;				
			}
			
			
			if ($elValidator) {
				$elValidators[] = $elValidator;
			}
						
			
			//if ($elValidators) {
				if ($currentGroup) {
					$inputFilters[$currentGroup] = array ('name' => $elId, 'required' => $elIsRequired, 'allow_empty' => $elIsRequired?false:true, 'validators' => $elValidators?$elValidators:array());
				} else {
					$inputFilters[$elId] = array ('name' => $elId, 'required' => $elIsRequired, 'allow_empty' => $elIsRequired?false:true, 'validators' => $elValidators?$elValidators:array());
				}
			//}
		}
		
		$spec['input_filter'] = $inputFilters;
				
		if ($currentFieldset) {
			$spec['fieldsets'][] = $currentFieldset;
		}		
		
		
		return $spec;

	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getPackageDescriptor()
	 */
	public function getPackageDescriptor() {
		return $this->_packageDescriptor;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getName()
	 */
	public function getName() {
		return $this->_appName;
		
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getVersion()
	 */
	public function getVersion() {
		return $this->_appVersion;
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getEula()
	 */
	public function getEula() {
		return $this->_appEula;		
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getLogo()
	 */
	public function getLogo() {
		return $this->_appLogo;		
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getPrerequisites()
	 */
	public function getPrerequisites() {
		return $this->_appPrerequisites;		
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getRequiredParams()
	 */
	public function getRequiredParams() {
		return $this->_appRequiredParamsIni;		
	}	
	
	public function setPackagePath($path) {
		$this->_packagePath = $path;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getPackagePath()
	 */
	public function getPackagePath() {
		return $this->_packagePath;		
	}	
	
	public function setPackageId($id) {
		$this->_packageId = $id;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getPackagePath()
	 */
	public function getPackageId() {
		return $this->_packageId;		
	}	
	
	public function isNull() {
		return ($this->_packageId == -1);
	}	
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getHealthCheckPath()
	 */
	public function getHealthCheckPath() {
		return $this->_healthCheckPath;
	}
	
	/**
	 * @return the $_pageCacheRules
	 */
	public function getPageCacheRules() {
		return $this->_pageCacheRules;
	}
	
	/**
	 * @param field_type $_pageCacheRules
	 */
	public function setPageCacheRules($_pageCacheRules) {
		$this->_pageCacheRules = $_pageCacheRules;
	}
	/**
	 * @param boolean $_monitorRulesFileExists
	 */
	public function setMonitorRulesFileExists($_monitorRulesFileExists) {
		$this->_monitorRulesFileExists = $_monitorRulesFileExists;
	}

	/**
	 * @param boolean $_pageCacheRulesFileExists
	 */
	public function setPageCacheRulesFileExists($_pageCacheRulesFileExists) {
		$this->_pageCacheRulesFileExists = $_pageCacheRulesFileExists;
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getMonitorRulesFileExists()
	 */
	public function isMonitorRulesFileExists() {
		return $this->_monitorRulesFileExists;
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageMetaData_Interface::getPageCacheRulesFileExists()
	 */
	public function isPageCacheRulesFileExists() {
		return $this->_pageCacheRulesFileExists;
	}
	
}
