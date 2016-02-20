<?php

namespace UrlInsight\Controller;
use ZendServer\Mvc\Controller\WebAPIActionController,
	WebAPI;
use Zend\View\Model\ViewModel;
use WebAPI\Exception;
use Zsd\Db\TasksMapper;
use ZendServer\Log\Log;
use Audit\Db\Mapper as auditMapper;
use Audit\Db\ProgressMapper;
use Zend\Server\Method\Prototype;

class WebAPIController extends WebAPIActionController
{
	/**
	 * predefined filters
	 */
	protected $filters = array(
		1 => 'MOST_TIME_CONSUMING',
		2 => 'SLOWEST_RESPONSE',
		3 => 'MOST_VISITED',
	);

	/**
	 * @return \UrlInsight\Db\RequestsMapper
	 */
	protected function getUrlInsightRequestsMapper() {
		return $this->getLocator()->get('UrlInsight\Db\RequestsMapper');
	}
	
	/**
	 * @return \UrlInsight\Db\ZraySnapshotsMapper
	 */
	protected function getUrlInsightZraySnapshotsMapper() {
		return $this->getLocator()->get('UrlInsight\Db\ZraySnapshotsMapper');
	}

	/**
	 *
	 * @return multitype:unknown Ambigous <\ZendServer\Set, multitype:, NULL, \Zend\Db\ResultSet\ResultSetInterface, \Zend\Db\ResultSet\ResultSet, multitype:NULL multitype: Ambigous <\ArrayObject, multitype:, \Zend\Db\ResultSet\mixed, unknown> >
	 */
	public function urlinsightGetUrlsAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array(
			'limit' => 10,
			'offset' => 0,
			'applicationId' => 0, // All
			'filter' => array_search('MOST_TIME_CONSUMING', $this->filters),
			'period' => 24  // 24 hours is the default
		));
		
		// validate params
		$limit = $this->validateLimit($params['limit']);
		$offset = $this->validateOffset($params['offset']);
		$applicationId = $this->validateApplicationId($params['applicationId'], 'applicationId');
		$filter = $this->validateAllowedValues($params['filter'], 'filter', array_keys($this->filters));
		$period = $this->validateAllowedValues($params['period'], 'period', array(-1, 2, 24, 48, 168, 720, 2160, 4320, 8640, 43200));

		// get UrlInsight DB
		$mapper = $this->getUrlInsightRequestsMapper();
		$urls = $mapper->getUrls(array(
			'limit' => $limit,
			'offset' => $offset,
			'applicationId' => $applicationId,
			'filter' => $filter,
			'period' => $period,
		));
		$uncutUrls = $mapper->getUrls(array(
			'applicationId' => $applicationId,
			'filter' => $filter,
			'period' => $period,
		));
		
		// count total time consumption
		$totalTimeConsumption = 0;
		// count total rows (not incl. "other" urls) and total memory comsumption
		$totalCount = 0;
		if ($uncutUrls->count() > 0) foreach ($uncutUrls as $uncutUrl) {
		    $totalTimeConsumption += $uncutUrl->getTimeConsumption();
		    if ($uncutUrl->getResourceId() != \UrlInsight\Db\RequestsMapper::UrlInsight_OTHER_URLS_RESOURCE_ID) {
                $totalCount++;
		    }
		}
		
		return array(
		    'urls' => $urls,
		    'totalTimeConsumption' => $totalTimeConsumption,
		    'totalCount' => $totalCount,
		);
	}
	
	/**
	 * 
	 * @throws Exception
	 * @return multitype:mixed Ambigous <\ZendServer\Set, multitype:, NULL, \Zend\Db\ResultSet\ResultSetInterface, \Zend\Db\ResultSet\ResultSet, multitype:NULL multitype: Ambigous <\ArrayObject, multitype:, \Zend\Db\ResultSet\mixed, unknown> >
	 */
	public function urlinsightGetUrlInfoAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array(
		    'id' => '',
		    'order' => '',
		    'period' => 24,
		));
		
		$this->validateMandatoryParameters($params, array('id'));
		$id = $this->validateInteger($params['id'], 'id');
		
		$order = $params['order'];
		if (!empty($order)) $order = $this->validateString($order, 'order');
		
		$period = $this->validatePositiveInteger($params['period'], 'period');
		
		$mapper = $this->getUrlInsightRequestsMapper();
		$urls = $mapper->getUrls(array('ids' => $id));
		
		if ($urls->count() == 0) {
			throw new Exception(_t('The url does not exist'), Exception::URL_INSIGHT_NO_SUCH_URL);
		}
		
		// period comes with number of hours
		$fromTimestamp = time() - ($period * 60 * 60);
		$requests = $mapper->getRequests($id, 0, 0, $order, $fromTimestamp);
		
		return array('url' => $urls->current(), 'requests' => $requests);
	}

	/**
	 * Validate that the application really exists
	 * @param int $appId
	 * @param string $paramName
	 * @return int
	 */
	protected function validateApplicationId($appId, $paramName) {
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applications = $deploymentModel->getAllApplicationsInfo();
		$appIds = array(0, -1); // put 0 as the default value to `all applications`, -1 for URLs without defined application 
		foreach ($applications as $app) {
			$appIds[] = $app['applicationId'];
		}

		return $this->validateAllowedValues($appId, $paramName, $appIds);
	}
	
	/**
	 * get zray snapshots by resource id (resource_id)
	 * @return array
	 */
	public function urlinsightGetZraySnapshotsAction() {
		$this->isMethodGet();
		
		// validate input
		$params = $this->getParameters(array(
			'resource_id' => 0, 
		));
		$this->validateMandatoryParameters($params, array('resource_id'));
		$resourceId = $this->validatePositiveInteger($params['resource_id'], 'resource_id');

		// get zray snapshots
		$zraySnapshots = $this->getUrlInsightZraySnapshotsMapper()->getZraySnapshots($resourceId);
		
		// get page ids by zray request ids
		$zrayIds = array();
		$zrayMapper = $this->getLocator()->get('DevBar\Db\RequestsMapper');
		foreach ($zraySnapshots as $snapshot) {
			$zrayIds[] = $snapshot['zray_request_id'];
		}
		$map = $zrayMapper->getPageIDsByRequestIds($zrayIds);
		
		// prepare the response
		$retArray = array();
		foreach ($zraySnapshots as $snapshot) {
			if (isset($map[$snapshot['zray_request_id']])) {
				$retArray[] = new \UrlInsight\ZraySnapshotContainer(array(
					'pageId' => $map[$snapshot['zray_request_id']],
					'requestTime' => $snapshot['zray_request_time'],
				));
			}
		}
		
		return array('snapshots' => $retArray);
	}
	
}
