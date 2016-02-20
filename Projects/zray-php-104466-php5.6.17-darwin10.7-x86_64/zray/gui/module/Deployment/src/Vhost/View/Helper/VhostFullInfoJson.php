<?php
namespace Vhost\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Vhost\Entity\Vhost;
use ZendServer\Log\Log;
use Deployment\Application\Container;

class VhostFullInfoJson extends AbstractHelper {

	/**
	 * @param Vhost $vhost
	 * @param array $vhostsNodes
	 * @return string
	 */
	public function __invoke(Vhost $vhost, array $vhostsNodes = array()) {
		return array(
			'vhostInfo' => $this->getView()->VhostInfoJson($vhost, $vhostsNodes),
			'vhostExtended' => $this->getExtendedDetails($vhost),
		);
	}
	
	/**
	 * @param Vhost $vhost
	 * @return string
	 */
	private function getExtendedDetails(Vhost $vhost) {
		
		$applications = array();
		
		foreach($vhost->getApplications() as $application) { /* @var $application Container */
			$applications[] = array(
							'applicationId' => $application->getApplicationId(),
				    		'baseUrl' => $this->getView()->applicationUrl($application->getBaseUrl()),
				    		'applicationName' => $application->getApplicationName(),
				    		'userApplicationName' => $application->getUserApplicationName(),
				    		'installedLocation' => $application->getInstallPath(),
					);
		}
		
		$template = $vhost->getTemplate();
		if (empty($template)) {
			$template = $vhost->getText();
		}
		
		return array(
			'text' => $vhost->getText(),
			'template' => $template,
			'docRoot' => $vhost->getDocRoot(),
			'sslCertificatePath' => $vhost->getCertificatePath(),
			'sslCertificateKeyPath' => $vhost->getCertificateKeyPath(),
			'sslCertificateChainPath' => $vhost->getCertificateChainPath(),
			'sslAppName' => $vhost->getAppName(),
			'vhostApplications' => $applications
		);
	}	
}