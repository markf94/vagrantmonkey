<?php
namespace Vhost\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Vhost\Entity\VhostNode;
use Vhost\Entity\Vhost;
use Deployment\Application\Container;
class VhostFullInfoXml extends AbstractHelper {
	
	/**
	 * @param Vhost $vhost
	 * @param VhostNode $vhostsNodes
	 * @return string
	 */
	public function __invoke(Vhost $vhost, array $vhostsNodes = array()) {
		$xml = $this->getView()->VhostInfoXml($vhost, $vhostsNodes, true) . PHP_EOL;
		$xml .= $this->getExtendedDetails($vhost);
		return $xml;
	}

	/**
	 * @param Vhost $vhost
	 * @return string
	 */
	private function getExtendedDetails(Vhost $vhost) {
		
		$applications = '<vhostApplications>';
		foreach($vhost->getApplications() as $appId => $application) { /* @var $application Container */
			$applications .= "
			<vhostApplication>
				<applicationId>{$application->getApplicationId()}</applicationId>
				<applicationName>{$this->getView()->escapeHtml($application->getApplicationName())}</applicationName>
				<userApplicationName>{$this->getView()->escapeHtml($application->getApplicationName())}</userApplicationName>
				<baseUrl><![CDATA[{$this->getView()->applicationUrl($application->getBaseUrl())}]]></baseUrl>
				<installedLocation><![CDATA[{$application->getInstallPath()}]]></installedLocation>
			</vhostApplication>
		";
		}
		$applications .= '</vhostApplications>';

		$template = $vhost->getTemplate();
		if (empty($template)) {
			$template = $vhost->getText();
		}
		
		return <<<XML
	<vhostExtended>
		<text><![CDATA[{$vhost->getText()}]]></text>
		<template><![CDATA[{$template}]]></template>
		<docRoot><![CDATA[{$vhost->getDocRoot()}]]></docRoot>
		<sslCertificatePath><![CDATA[{$vhost->getCertificatePath()}]]></sslCertificatePath>
		<sslCertificateKeyPath><![CDATA[{$vhost->getCertificateKeyPath()}]]></sslCertificateKeyPath>
		<sslCertificateChainPath><![CDATA[{$vhost->getCertificateChainPath()}]]></sslCertificateChainPath>
		<sslAppName><![CDATA[{$vhost->getAppName()}]]></sslAppName>
		$applications
	</vhostExtended>
XML;
	}
}