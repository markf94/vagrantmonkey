<?php

namespace PageCache\Controller;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use ZendServer\Mvc\Controller\WebAPIActionController;

use ZendServer\Log\Log,
	ZendServer;

class WebAPIController extends WebAPIActionController
{
	public function pagecacheClearRulesCacheAction() {
		$mapper = $this->getLocator()->get('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
		
		try {
			$params = $this->getParameters();
			
			$auditData = $params['rules'];
			
			$audit = $this->auditMessage(Mapper::AUDIT_CLEAR_PAGE_CACHE_CACHE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(
					'rules' => $auditData
			))); /* @var $audit \Audit\Container */
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, array(array(
					'rules' => $auditData)));
			
			$this->isMethodPost();
			$this->validateMandatoryParameters($params, array('rules'));
			$this->validateArray($params['rules'], 'rules');
			foreach ($params['rules'] as $key => $ruleId) {
				$this->validatePositiveInteger($ruleId, "rules[$key]");
			}
		} catch (ZendServer\Exception $e) {
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
				$e->getMessage())));			
			
			$this->handleException($e, 'Input validation failed');
		}
		
		Log::debug("Clearing rules cache " . var_export($params['rules'], true));
		
		$imploded = $params;
		$imploded['rules'] = implode(",", $imploded['rules']);
		$this->getLocator()->get('PageCache\Model\Tasks')->clearCache($imploded);
		return array();
	}
	
}