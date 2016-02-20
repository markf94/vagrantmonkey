<?php
namespace ZendServer\Validator;

use Zend\Uri\Http,
	Zend\Uri\Uri,
	ZendServer\Log\Log,
	Zend\Validator\AbstractValidator,
	Zend\Uri\Exception;

class AbsoluteUriPath extends AbstractValidator {
	
	const INVALID_PATH = 'invalidPath';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::INVALID_PATH  => "'%value%' is not a valid absolute HTTP path",
	);
		
	public function __construct($options = array()) {
		parent::__construct($options);
	}
	
	public function isValid($value) {
		$this->setValue($value);

		$result = true;
		try {
			$uri = new \Zend\Uri\Uri($value);
			$result = $uri->isValid();
			$result = 	$result && 
						$uri->isAbsolute() &&
						Uri::validateHost($uri->getHost()) &&
						Uri::validateScheme($uri->getScheme()) &&
						Uri::validatePort($uri->getPort()) &&
						Uri::validatePath($uri->getPath());

		} catch (Exception $e) {
			$this->error(self::INVALID_PATH);
			return false;
		}
		
		if (false === $result) {
			$this->error(self::INVALID_PATH);
			return false;
		}
		return true;
	}
	
}