<?php
namespace Expired\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	ZendServer\Edition,
	ZendServer\Configuration\Manager,
	Zend\View\Model\ViewModel;

class IndexController extends ActionController
{
	public function indexAction() {
		$this->Layout('layout/login.phtml');
		
		$viewModel = new ViewModel();
		if (! $this->isAclAllowedIdentity('route:ServersWebAPI', 'serverStoreLicense')) {
			$viewModel->setTemplate('expired/index/index-dev');
		}
		
		$licenseMapper = $this->getLocator('Configuration\License\ZemUtilsWrapper'); /* @var $licenseMapper \Configuration\License\ZemUtilsWrapper */
		$licenseInfo = $licenseMapper->getLicenseInfo();
		$isEvaluation = $licenseInfo->isEvaluation();
		
		$isFree = false;
		if ($licenseMapper->getLicenseType() == 'FREE') {
			$isFree = true;
		}
		
		$edition = new Edition();
		$serverId = $edition->getServerId();
		$manager = new Manager();
		
		$viewModel->setVariable('serverId', $serverId);
		$viewModel->setVariable('baseUrl', Module::config('baseUrl'));
		$viewModel->setVariable('currentLicense', $licenseInfo);
		$viewModel->setVariable('isEvaluation', $isEvaluation);
		$viewModel->setVariable('isFree', $isFree);
		$viewModel->setVariable('osType', $manager->getOsType());
		$viewModel->setVariable('extraParams', \Application\Module::config('license', 'zend_gui', 'extra'));
		
		$viewModel->setVariable('capabilitiesMap', $this->capabilitiesList()->getCapabilitiesList());
		$viewModel->setVariable('changesMatrix', $this->capabilitiesList()->getChangesMatrix());
		
		return $viewModel;
	}
}