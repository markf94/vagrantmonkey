<?php
namespace PageCache\Controller;
use Zend\Json\Json;

use ZendServer\Mvc\Controller\ActionController,
	PageCache\Rule,
	ZendServer\Log\Log;

class EditController extends ActionController {
	
	public function indexAction() {
		$this->getLocator('Navigation')->findByLabel('Page Cache')->setActive(true);
		$params = $this->getParameters(array('id' => '', 'appId' => '', 'duplicate' => 'FALSE'));

		// validation
		if ($params['appId']) {
			$this->validateInteger($params['appId'], 'appId');
		}
		if ($params['id']) {
			$this->validateInteger($params['id'], 'id');
		}
		
		$this->validateBoolean($params['duplicate'], 'duplicate');
		
		$mapper = $this->getLocator()->get('PageCache\Model\Mapper');  /* @var $mapper \PageCache\Model\Mapper */
		$rules = $mapper->getRules(array($params['id'])); // null in case not existing
		$rule = (isset($rules[0])) ? $rules[0] : null;
		
		$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
		$applications = $deploymentModel->getMasterApplications();
		$applications->setHydrateClass('\Deployment\Application\Container');
		
		//$cacheRule = new Rule
		return array(	'dictionaryMatchType' 				=> $mapper->getMatchTypeDictionary(),
						'dictionaryGlobalTypeJson' 			=> Json::encode($mapper->getSuperGlobalsDictionary()),
						'dictionarySplitGlobalTypeJson' 	=> Json::encode($mapper->getSplitSuperGlobalsDictionary()),
						'dictionaryGlobalMatchTypeJson' 	=> Json::encode($mapper->getSuperGlobalMatchDictionary()),
						'applications' 						=> $applications,
						'ruleContainer' 					=> $rule,
						'appId'								=> $params['appId'],
						'duplicate'							=> (strtolower($params['duplicate']) == 'true'));
	}
	
}
