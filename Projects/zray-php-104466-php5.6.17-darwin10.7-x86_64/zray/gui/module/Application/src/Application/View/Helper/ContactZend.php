<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Configuration\License\License;

class ContactZend extends AbstractHelper {
	/**
	 * @var License
	 */
	private $license;
	
	/**
	 * @param string $container
	 * @return string
	 */
	public function __invoke($url = '') {
		$edition = strtoupper($this->license->getEdition());
		
		$isEvaluation = $this->license->isEvaluation();
		if (in_array($edition, array('ENTERPRISE', 'PROFESSIONAL', 'SMALL BUSINESS', 'DEVELOPER')) && !$isEvaluation) {
			$urlEdition = 'paid';
		} elseif ($isEvaluation) {
			$urlEdition = 'trial';
		} else {
			$urlEdition = 'free';
		}
		
		return 'http://www.zend.com/go/' . $urlEdition . '/' . $url;
	}
	
	/*
	 * (non-PHPdoc)
	* @see \ZendServer\License\LicenseAwareInterface::setLicense()
	*/
	public function setLicense(License $license) {
		$this->license = $license;
		return $this;
	}
}