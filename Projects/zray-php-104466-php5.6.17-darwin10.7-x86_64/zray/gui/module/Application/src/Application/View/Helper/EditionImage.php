<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	ZendServer\Exception,
	Application\Module;
use Configuration\License\License;
use ZendServer\Log\Log;

class EditionImage extends AbstractHelper {
	
	const SERVER_TYPE_STANDALONE_GUI = 1;
	const SERVER_TYPE_SERVER = 2;
	const SERVER_TYPE_CLUSTER_MEMBER = 3;
	
	const LICENSE_EDITION_FREE = 'free';
	const LICENSE_EDITION_FREE_IBMI = 'basic';
	const LICENSE_EDITION_BASIC = 'smb';
	const LICENSE_EDITION_PRO = 'professional';
	const LICENSE_EDITION_ENTERPRISE = 'enterprise';
	const LICENSE_EDITION_ENTERPRISE_TRIAL = 'enterprise_trial';
	
	const LICENSE_EDITION_DEVELOPER = 'developer_standard';
	const LICENSE_EDITION_DEVELOPER_ENTERPRISE = 'developer_enterprise';

	/**
	 * @var integer
	 */
	private $serverType;
	
	/**
	 * @param integer $serverType one of EditionImage::SERVER_TYPE_STANDALONE_GUI, EditionImage::SERVER_TYPE_SERVER, EditionImage::SERVER_TYPE_STANDALONE
	 */
	public function __construct($serverType = self::SERVER_TYPE_SERVER) {
		$this->serverType = $serverType;
	}
	
	public function __invoke($image, $edition = '', $topframe=false, $trial=false, $isI5=false) {
		$class = str_replace(strstr($image, '.'), '', $image);	
		$id = str_replace(strstr($image, '.'), '', $image);
		$baseUrl = $this->getView()->basePath();
		
		$licenseImage = '';
		if($edition && $edition != 'Unknown') {
			$edition = strtolower($edition);
			
			$editionsMap = array_change_key_case(array(
				License::EDITION_BASIC 					=> self::LICENSE_EDITION_BASIC,
				License::EDITION_DEVELOPER 				=> self::LICENSE_EDITION_DEVELOPER,
				License::EDITION_DEVELOPER_ENTERPRISE 	=> self::LICENSE_EDITION_DEVELOPER_ENTERPRISE,
				License::EDITION_ENTERPRISE 			=> self::LICENSE_EDITION_ENTERPRISE,
				License::EDITION_FREE 					=> self::LICENSE_EDITION_FREE,
				License::EDITION_PROFESSIONAL 			=> self::LICENSE_EDITION_PRO,
			), CASE_LOWER);
			
			if (isset($editionsMap[$edition])) {
				$edition = $editionsMap[$edition];
			} else {
				$edition = self::LICENSE_EDITION_BASIC;
			}
			
			if ($edition == self::LICENSE_EDITION_ENTERPRISE && $trial) {
				$edition = self::LICENSE_EDITION_ENTERPRISE_TRIAL;
			}
			
			if ($isI5 && $edition == self::LICENSE_EDITION_FREE) {
				$edition = self::LICENSE_EDITION_FREE_IBMI;
			}
			
			$imgName = "{$edition}.png";
			if ($topframe) {
				$imgName = "{$edition}-login.png";
			}
			
			$licClass = $licId = 'edition';
			
			$licenseImage = '<img id="' . $licId . '" class="' . $licClass .'" src="' . $baseUrl . "/images/editions/{$imgName}" . '"/>';
		}
		
		if ($topframe) {
			return $licenseImage;
		}		

		return '<a href="' .  $baseUrl . '/" class="logo-placeholder" title="Zend Server edition">' .
				$licenseImage .
				'</a>';
	}
	
	/**
	 * @return string
	 */
	private function getEditionFolder() {
		return 'editions'; // showing cluster info at logo is no longer desired
		
/* 		switch ($this->serverType) {
			case self::SERVER_TYPE_SERVER:
				return 'single';
				break;
			case self::SERVER_TYPE_CLUSTER_MEMBER:
				return 'cluster';
				break;
			case self::SERVER_TYPE_STANDALONE_GUI:
				return 'manager';
				break;
			default:
				throw new Exception("edition type was not set!");
		} */
		
	}
}

