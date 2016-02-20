<?php
namespace Configuration;

use ZendServer\PHPUnit\TestCase;

use Zend\Validator\ValidatorInterface;

use PHPUnit_Framework_TestCase,
Configuration\DdMapper,
Zend\Json\Json,
ZendServer\Set,
ZendServer\Exception,
Configuration\ExtensionContainer,
Configuration\DirectiveContainer;
use Zend\InputFilter\Input;

require_once 'tests/bootstrap.php';

class DdMapperTest extends TestCase {
	
	public function testDirectiveValidatorNullValidator() {
		$ddMapFileContent = $this->getDdMapFileContent();
		$dd = new DdMapper($ddMapFileContent);
		$validator = $dd->directiveValidator('pdo_oci.null_validation');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\NullValidator', current(current($validator->getValidatorChain()->getValidators())));
		self::assertTrue($validator->allowEmpty());
	}
	
	public function testgetComponentsDisplayMap() {
		$ddMapFileContent = json_encode($this->arrayToObject(array(
			"Zend OPcache" => array (
				'name' => "Zend OPcache",
				'zemExtension' => true,
			),
			"pdo_mysql" => array (
				'name' => "pdo_mysql",
				'zemExtension' => false,
			)
		)));
		$dd = new DdMapper($ddMapFileContent);
		$displayMap = $dd->getComponentsDisplayMap();
		self::assertEquals(2, count($displayMap));
		self::assertArrayHasKey('Zend OPcache', $displayMap);
		self::assertTrue($displayMap['Zend OPcache']);
		self::assertArrayHasKey('pdo_mysql', $displayMap);
		self::assertFalse($displayMap['pdo_mysql']);
	}
	
	public function testDirectiveValidatorMinMaxValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 4,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => "1",
												"maxValue"=> "3",
												"regex"=> "",
												"listValues"=>  "")),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\Integer', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorIntegerValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 4,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									'email' => false,
									'uri' => false,
									'host' => false,
									'defaultServer' => false)),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\Integer', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorFloatValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 9,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									'email' => false,
									'uri' => false,
									'host' => false,
									'defaultServer' => false,
									)),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\FloatValidator', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorBooleanValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 2,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									'email' => false,
									'uri' => false,
									'host' => false,
									'defaultServer' => false,
									)),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\Boolean', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorMinValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 4,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => "1",
												"maxValue" => null,
												"regex"=> "",
												"listValues"=>  "")),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\Integer', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorMaxValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 4,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => null,
												"maxValue"=> "1",
												"regex"=> "",
												"listValues"=>  "")),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\Integer', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorRegexValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 4,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => null,
												"maxValue" => null,
												"regex"=> "/[0-9]+/",
												"listValues"=>  "")),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('Zend\Validator\RegEx', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorListValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
				'shortDescription' => "A driver that implements the PHP Data Objects",
				'iniFileName' => "pdo_oci.ini",
				'subPath' => "",
				'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
						"type" => 4,
						"section"=>  "",
						"visible" => "0",
						"units"=>  "",
						"validation" => array ( "minValue" => null,
												"maxValue" => null,
												"regex"=> "",
												"listValues"=>  serialize(array('in'=>'this string will be displayed, actual value is "ini"', 'my'=>0, 'belly!'=>'belly!')))),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('Zend\Validator\InArray', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorHtmlValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array ('directives' => array (
						"pdo_oci.cache_size"	=> array (
							"type" => 4,
							"validation" => array (
									"minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> true,
						)),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('Zend\Validator\RegEx', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorError_reportingValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array ('directives' => array (
						"error_reporting"	=> array (
							"type" => 1,
							"validation" => array (
									"minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									
						)),
		
		))));
		
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('error_reporting');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\ErrorReporting', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorEmailValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array ('directives' => array (
						"pdo_oci.cache_size"	=> array (
							"type" => 1,
							"validation" => array (
									"minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									'email' => true,
									
						)),
		
		))));
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('Zend\Validator\EmailAddress', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorUriValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array ('directives' => array (
						"pdo_oci.cache_size"	=> array (
							"type" => 1,
							"validation" => array (
									"minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									'email' => false,
									'uri' => true,
									
						)),
		
		))));
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('Zend\Validator\Uri', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorHostValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array ('directives' => array (
						"pdo_oci.cache_size"	=> array (
							"type" => 1,
							"validation" => array (
									"minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									'email' => false,
									'uri' => false,
									'host' => true,
									
						)),
		
		))));
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('ZendServer\Validator\HostWithPort', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testDirectiveValidatorDefaultServerValidator() {
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array ('directives' => array (
						"pdo_oci.cache_size"	=> array (
							"type" => 1,
							"validation" => array (
									"minValue" => null,
									"maxValue" => null,
									"regex"=> "",
									"listValues"=> "",
									"html"=> false,
									'email' => false,
									'uri' => false,
									'host' => false,
									'defaultServer' => true,
									
						)),
		
		))));
		$dd = new DdMapper(Json::encode($mapc));
		$validator = $dd->directiveValidator('pdo_oci.cache_size');
		self::assertTrue($validator instanceof Input);
		self::assertInstanceOf('Application\Validators\DefaultServer', current(current($validator->getValidatorChain()->getValidators())));
	}
	
	public function testAddExtensionDataEmpty() {
		$dd = new DdMapper(Json::encode(array()));
		try {
			$dd->addExtensionData(new ExtensionContainer(array()));
		} catch (\Exception $e) {
			self::assertEquals('Provided extension array should contain the \'NAME\' field', $e->getMessage());		
		}
	}
	
	public function testAddDirectiveDataEmpty() {
		$dd = new DdMapper(Json::encode(array()));
		try {
			$dd->addDirectiveData(new DirectiveContainer(array()));
		} catch (\Exception $e) {
			self::assertEquals('Provided directive array should contain the \'NAME\' field', $e->getMessage());
		}
	}
	
	public function testAddExtensionsDataEmpty() {
		$dd = new DdMapper(Json::encode(array()));
		try {
			$dd->addExtensionsData(array());
		} catch (\Exception $e) {
			self::assertEquals('Provided extension array should contain the \'NAME\' field', $e->getMessage());
		}
	}
	
	public function testAddDirectivesDataEmpty() {
		$dd = new DdMapper(Json::encode(array()));
		try {
			$dd->addDirectivesData(array());
		} catch (\Exception $e) {
			self::assertEquals('Provided directive array should contain the \'NAME\' field', $e->getMessage());
		}
	}
	
	public function testAddExtensionDataExist() {		
		$ddMapFileContent = $this->getDdMapFileContent();
		$dd = new DdMapper($ddMapFileContent);
		$fullExtData = $dd->addExtensionData(new ExtensionContainer($this->getExtensionDbArray('PDO_OCI')));
		
		$ddMapFileContent = Json::decode($ddMapFileContent, Json::TYPE_ARRAY);
		$expectedExtData = $ddMapFileContent['PDO_OCI'];
		unset($expectedExtData['directives']);
		$expectedExtData = array_merge($this->getExtensionDbArray('PDO_OCI'), $expectedExtData);
	
		self::assertEquals($fullExtData, $expectedExtData);
	}
	
	public function testAddExtensionsDataExist() {	
		$ddMapFileContent = $this->getDdMapFileContent();
		$dd = new DdMapper($ddMapFileContent);
		$fullExtsData = $dd->addExtensionsData(array ($this->getExtensionDbContainer('PDO_OCI'), $this->getExtensionDbContainer('pdo_mysql')));
		
		$ddMapFileContent = Json::decode($ddMapFileContent, Json::TYPE_ARRAY);
		$expectedData_OCI = $ddMapFileContent['PDO_OCI'];
		unset($expectedData_OCI['directives']);
		$expectedData_OCI = array_merge($this->getExtensionDbArray('PDO_OCI'), $expectedData_OCI);
		
		$expectedData_Mysql = $ddMapFileContent['pdo_mysql'];
		unset($expectedData_Mysql['directives']);
		$expectedData_Mysql = array_merge($this->getExtensionDbArray('pdo_mysql'), $expectedData_Mysql);
		
		$expectedSet = new Set(array($expectedData_OCI, $expectedData_Mysql), '\Configuration\ExtensionContainer');
		
		self::assertEquals($fullExtsData, $expectedSet);
	}
	
	public function testAddDirectiveDataExist() {
		$ddMapFileContent = $this->getDdMapFileContent();
		$dd = new DdMapper($ddMapFileContent);
		$fullExtData = $dd->addDirectiveData($this->getDirectiveDbContainer('pdo_oci.cache_size'));
		self::assertInstanceOf('Configuration\DirectiveContainer', $this->getDirectiveDbContainer('pdo_oci.cache_size'));
		self::assertInternalType('array', $fullExtData);
		self::assertArrayHasKeys(array_keys($fullExtData), $this->getDirectiveDbContainer('pdo_oci.cache_size')->toArray());
	}
	
	public function testAddDirectivesDataExist() {	
		$ddMapFileContent = $this->getDdMapFileContent();
		$dd = new DdMapper($ddMapFileContent);
		$fullExtData = $dd->addDirectivesData(array ($this->getDirectiveDbContainer('pdo_oci.cache_size'), $this->getDirectiveDbContainer('pdo_oci.socket')));
	
		self::assertInstanceOf('ZendServer\Set', $fullExtData);
		
		foreach ($fullExtData as $key => $extData) {
			self::assertInstanceOf('Configuration\DirectiveContainer', $extData);
			self::assertArrayHasKeys(array_keys($extData->toArray()), $this->getDirectiveDbContainer($key)->toArray());
		}
	}
	
	public function testAddDirectiveDataOnlyDbExist() {	
		$ddMapFileContent = $this->getDdMapFileContentEmpty();
		$dd = new DdMapper($ddMapFileContent);
		$fullDirData = $dd->addDirectiveData($this->getDirectiveDbContainer('pdo_oci.cache_size'));
		self::assertEquals($fullDirData, $this->getDirectiveDbArray('pdo_oci.cache_size'));
	}
	
	public function testAddExtensionDataOnlyDbExist() {	
		$ddMapFileContent = $this->getDdMapFileContentEmpty();
		$dd = new DdMapper($ddMapFileContent);
		$fullExtData = $dd->addExtensionData(new ExtensionContainer($this->getExtensionDbArray('PDO_OCI')));
		self::assertEquals($fullExtData, $this->getExtensionDbArray('PDO_OCI'));
	}
	
	private function getDdMapFileContentEmpty() {
		$mapc =  new \stdClass(array());
		return Json::encode($mapc);
	}
	
	private function getDdMapFileContent() {		
		$mapc = $this->arrayToObject(array(	"PDO_OCI" => array (	'name' => "PDO_OCI",
																	'shortDescription' => "A driver that implements the PHP Data Objects",
																	'iniFileName' => "pdo_oci.ini",
																	'subPath' => "",
																	'directives' => array ( "pdo_oci.cache_size"	=> array (	"shortDescription" => "PDO OCI cache desc",
																														        "type" => 4,
																														        "section"=>  "",
																														        "visible" => "0",
																														        "units"=>  "",
																														        "validation" => array ( "minValue" => null,
																																				          "maxValue" => null,
																																				          "regex"=> "",
																														        		
																								"pdo_oci.null_validation" => array ( "shortDescription" => "I AM NULL",
																														"type" => 4, // string
																														"section" => "",
																														"visible"=> "0",
																														"units" => "",
																														"validation" => array ( 
																																		        "regex"=> "",
																																		        "listValues"=>  "")),																																	          "listValues"=>  "")),
																						
																							"pdo_oci.socket" => array ( "shortDescription" => "Default socket name",
																														"type" => 1,
																														"section" => "",
																														"visible"=> "0",
																														"units" => "",
																														"validation" => array ( "minValue" => null,
																																		        "maxValue" => null,
																																		        "regex"=> "",
																																		        "listValues"=>  "")))),
				
				
											"pdo_mysql" => array (	'name' => "pdo_mysql",
																	'shortDescription' => 'Allows access to MySQL 3.x/4.0 databases',
																	'iniFileName' => "pdo_mysql.ini",
																	'subPath' => "",
																	'directives' => array ( "pdo_mysql.cache_size"	=> array (	"shortDescription" => "If mysqlnd is used: Number of cache slots for the internal result set cache",
																														        "type" => 4,
																														        "section"=>  "",
																														        "visible" => "0",
																														        "units"=>  "",
																														        "validation" => array ( "minValue" => null,
																																				          "maxValue" => null,
																																				          "regex"=> "",
																																				          "listValues"=>  "")),
																							
																							"pdo_mysql.default_socket" => array ( "shortDescription" => "Default socket name for local MySQL connects.  If empty, uses the built-in MySQL defaults",
																																	"type" => 1,
																																	"section" => "",
																																	"visible"=> "0",
																																	"units" => "",
																																	"validation" => array ( "minValue" => null,
																																					        "maxValue" => null,
																																					        "regex"=> "",
																																					        "listValues"=>  ""))))
		));
														        
   
		
		
		return Json::encode($mapc);
	}
	
	private function getExtensionDbContainer($name) {
		return new ExtensionContainer($this->getExtensionDbArray($name));
	}
		
	private function getExtensionDbArray($name) {
		return array (	"NAME" => $name,
						"EXT_VERSION" => NULL,
						"IS_INSTALLED" => "true",
						"IS_LOADED" => "false",
						"INI_FILE" => "$name.ini");
	}
	
	private function getDirectiveDbArray($name) {
		return array (	"NAME" => $name,
						"TYPE" => "1",
						"MEMORY_VALUE" => NULL,
						"DISK_VALUE" => "Off",
						"EXTENSION" => "global",
						"DAEMON" => NULL);
	}

	private function getDirectiveDbContainer($name) {
		return new DirectiveContainer($this->getDirectiveDbArray($name));
	}
		
	private function arrayToObject($array) {
	    if(! is_array($array)) {
	        return $array;
	    }
	    
	    $object = new \stdClass();
	    if (is_array($array) && count($array) > 0) {
	      foreach ($array as $name=>$value) {
	         if (! empty($name)) {
	            $object->$name = $this->arrayToObject($value);
	         }
	      }
	      return $object;
	    } else {
	      return FALSE;
	    }
	}
}