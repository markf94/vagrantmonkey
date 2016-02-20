<?php
namespace MonitorRules\Controller;
use Zend\View\Model\ViewModel;


use ZendServer\Mvc\Controller\WebAPIActionController,
ZendServer\Log\Log,
WebAPI
;

class WebAPI17Controller extends WebAPIActionController {
	
	public function monitorExportRulesAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('applicationId' => -1, 'retrieveGlobal' => 'TRUE'));
		
		$resolver = $this->getLocator('ViewTemplatePathStackWebAPI'); /* @var $resolver \WebAPI\View\Resolver\TemplatePathStack */
		$resolver->setWebapiVersion('1.3'); // otherwise viewscripts will be looked using current webapi version (1.4 at the moment)
		
		$retrieveGlobal = $this->validateBoolean($params['retrieveGlobal'], 'retrieveGlobal');
		
		try {
			$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
			$applications = array($params['applicationId']);
		
			if ($retrieveGlobal && (! in_array(-1, $applications))) {
				$applications[] = -1;
			}
			
			/// enable global application only if the input specifically requires -1
			if (in_array(-1, $applications)) {
				$mapper->setAddGlobalAppId(true);
			} else {
				$mapper->setAddGlobalAppId(false);
			}

			$rules = $mapper->findMonitorRules(array('applications' => $applications));
		} catch (\Exception $e) {
			Log::err($e);
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		// prepare the environment for a file download
		$this->setHttpResponseCode('200', 'OK');
		$response = $this->getResponse(); /* @var $response \Zend\Http\PhpEnvironment\Response */
		$response->getHeaders()->addHeaders(array(
			'Content-Type' => 'application/vnd.zend.monitor.rules+xml',
			'Content-Disposition' => 'attachment; filename="monitor_rules.xml"'
		));
		
		// @todo remove temporary fix for view model variables' propagation when setTerminal is true
		//$this->layout('layout/nothing');
		$viewModel = new ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setVariable('rules', $rules);
		return $viewModel;
	}
}