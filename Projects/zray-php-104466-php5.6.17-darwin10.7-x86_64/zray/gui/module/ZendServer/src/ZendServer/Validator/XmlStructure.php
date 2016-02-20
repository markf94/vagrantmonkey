<?php

namespace ZendServer\Validator;

use Zend\Validator\AbstractValidator,
\ZendServer\Exception as ZSException;

class XmlStructure extends AbstractValidator {
	
		const SCHEMA_XSD	= 1;
		const SCHEMA_RNG	= 2;
	
		const INVALID      = 'invalidXml';
	
		/**
		 * @var DOMDocument
		 */
		private $document = null;
		/**
		 * @var string
		 */
		private $schemaFile = '';
		/**
		 * @var integer
		 */
		private $schemaType = null;
		/**
		 * @var integer
		 */
		private $problemsCounter = 0;
	
		/**
		 * @var array
		 */
		protected $messageTemplates = array(
				self::INVALID      => 'Invalid input provided',
		);
	
		/**
		 * @param string $xmlFile - a file path
		 * @return XmlStructure
		*/
		public function loadXmlFile($xmlFile) {
			// TODO: (k) Add error handling in case the DOMDocument throws warnings
			$this->document->load($xmlFile);
			return $this;
		}
	
		/**
		 * @param string $xmlString - the content of the xml
		 * @return XmlStructure
		 */
		public function loadXmlString($xmlString) {
			// TODO: (k) Add error handling in case the DOMDocument throws warnings
			$this->document->loadXML($xmlString);
			return $this;
		}
	
		/**
		 * @param string $file - a file path
		 * @return XmlStructure
		 */
		public function setXsdFile($file) {
			$this->schemaType = self::SCHEMA_XSD;
			$this->schemaFile = $file;
			return $this;
		}
	
		/**
		 * @param string $file - a file path
		 * @return XmlStructure
		 */
		public function setRngFile($file) {
			$this->schemaType = self::SCHEMA_RNG;
			$this->schemaFile = $file;
			return $this;
		}
		/**
		 * Validates the file against the schema
		 * @return integer number or errors found
		 */
		public function validate() {
	
			if ('' === $this->schemaFile) {
				throw new ZSException(_t('Missing the schema file to validate against'));
			}
	
			set_error_handler(array($this, "handleErrors"));
			switch ($this->schemaType) {
				case self::SCHEMA_XSD:
					$this->document->schemaValidate($this->schemaFile);
					break;
				case self::SCHEMA_RNG:
					$this->document->relaxNGValidate($this->schemaFile);
					break;
				default:
					throw new ZSException(_t('The provided schema type is invalid'));
			}
			restore_error_handler();
	
			return $this->problemsCounter;
		}
	
	
		/**
		 * @see \Zend\Validator\Validator::isValid()
		 *
		 * @param string $value
		 * @return boolean
		 */
		public function isValid($xmlString) {
			if (empty($xmlString) || (! is_string($xmlString)) || ('<' !== substr(ltrim($xmlString),0,1))) {
				$this->error(self::INVALID);
				$this->problemsCounter++;
				return false;
			}
			$this->loadXmlString($xmlString);
			return (0 === $this->validate());
		}
	
		public function __construct() {
			$this->document = new \DOMDocument();
			$this->abstractOptions['messageTemplates'][self::INVALID] = _t('The provided input is invalid for validation');
		}
	
		/**
		 * Internal, has to be public because it's called by PHP
		 *
		 * @param integer $errno - contains the level of the error raised
		 * @param string $errstr -contains the error message
		 * @param string $errfile - which contains the filename that the error was raised in
		 * @param integer $errline - which contains the line number the error was raised at
		 */
		public function handleErrors($errno, $errstr, $errfile, $errline) {
			if (isset($this->abstractOptions['messageTemplates'][$errno])) {
				$errstr = $this->abstractOptions['messageTemplates'][$errno] . PHP_EOL . $errstr;
			}
			
			$this->abstractOptions['messageTemplates'][$errno] = $errstr;

			$this->error($errno, $errstr);
			++$this->problemsCounter;
		}
	
	}