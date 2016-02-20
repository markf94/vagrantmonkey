<?php
namespace EventsGroup\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZendServer\Exception;

class EventEmail extends AbstractPlugin {
	
	/**
	 * @var \EventsGroup\Db\Mapper
	 */
	private $eventGroupMapper;
	
	/**
	 * @var \Issue\Db\Mapper
	 */
	private $issueMapper;
	
	public function __invoke($params) {
		$eventId = $params['eventGroupId'];
		
		try {
			$eventGroupData = $this->eventGroupMapper->getEventGroupData($eventId);
			$issue = $this->issueMapper->getIssue($eventGroupData->getIssueId());
		} catch (\Exception $ex) {
			throw new Exception(_t('Could not retrieve information of requested eventGroupId'), Exception::ERROR, $ex);
		}

		return array('issue' => $issue, 'eventGroup' => $eventGroupData);
	}
	
	/**
	 * @param \Issue\Db\Mapper $issueMapper
	 * @return \Notifications\Plugin\EventEmail
	 */
	public function setIssueMapper(\Issue\Db\Mapper $issueMapper) {
		$this->issueMapper = $issueMapper;
		return $this;
	}
	
	/**
	 * @param \EventsGroup\Db\Mapper $eventGroupMapper
	 * @return \Notifications\Plugin\EventEmail
	 */
	public function setEventGroupMapper(\EventsGroup\Db\Mapper $eventGroupMapper) {
		$this->eventGroupMapper = $eventGroupMapper;
		return $this;
	}
}