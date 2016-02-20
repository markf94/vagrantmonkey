<?php
namespace Audit\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
	\Audit\Db\ProgressMapper;

class AuditEmail extends AbstractPlugin {
	
	/**
	 * @var ProgressMapper
	 */
	private $progressMapper;
	
	public function __invoke($params) {
		$resultArray = array();
		
		if ($params['outcome'] != 'Successful') {
			try {
				$progressList = $this->progressMapper->findMessageDetailsErrorOnly($params['id']);
				
				foreach ($progressList as $progressRow) {
					$resultRow['name'] = $progressRow->getNodeName();
					$resultRow['progress'] = ($progressRow->getProgress() == 'AUDIT_PROGRESS_ENDED_SUCCESFULLY') ? 'Successful' : 'Failed';
					$resultRow['timestamp'] = $progressRow->getCreationTime();
					$resultRow['extra'] = $this->parseExtraData($progressRow->getExtraData());
					
					$resultArray['progressList'][] = $resultRow; 
				}
				
			} catch (\Exception $e) {
				// do nothing
			}
		}
		
		return $resultArray;
	}
	
	/**
	 * @param ProgressMapper $progressMapper
	 * @return \Notifications\Plugin\auditEmail
	 */
	public function setProgressMapper(ProgressMapper $progressMapper) {
		$this->progressMapper = $progressMapper;
		return $this;
	}
	
	protected function parseExtraData($extraData) {
		$messages = array();
		if (! $extraData || (! is_array($extraData))) { // covering also the case where we get NULL
			return $messages;
		}
	
		foreach ($extraData as $idx=>$paramater) {
			if (is_array($paramater) || is_object($paramater)) {
				foreach ($paramater as $key=>$value) {
					$messages[$idx][] = array('name' => $key, 'value' => $value);
				}
			}
		}
		return $messages;
	}
}