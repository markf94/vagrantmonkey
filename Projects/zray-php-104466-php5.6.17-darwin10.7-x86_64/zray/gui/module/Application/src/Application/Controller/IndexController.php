<?php

namespace Application\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Deployment\Model,
	ZendServer\Configuration\Manager,
	Application\Module,
	\Servers\Configuration\Mapper;

class IndexController extends ActionController {
	
    public function indexAction() {    
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\Model */
		$applicationsSet = $deploymentModel->getMasterApplications();
		$applicationsSet->setHydrateClass('\Deployment\Application\Container');
		$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
		$deploymentSupportedByWebServer = $deploymentModel->isDeploySupportedByWebserver();
		
		$statsModel = $this->getLocator()->get('Statistics\Model'); /* @var $statsModel \Statistics\Model */
		
		if (! defined('ZEND_STATS_TYPE_AVG_MEMORY_USAGE')) {
			throw new \Exception('Statistics type not found');
		}
		
		$eventsPieContainer = $statsModel->createContainer();
		$eventsPieContainer->setTitle(_t('Events Breakdown'))
				->setName('EventsPie')
				->setChartType(\Statistics\Container::TYPE_PIE)
				->setValueType('%')
				->setCounterId(\Statistics\Model::STATS_EVENTS_PIE);
		
		$browsersPieContainer = $statsModel->createContainer();
		$browsersPieContainer->setTitle(_t('Browsers Distribution'))
			->setName('BrowsersPie')
			->setChartType(\Statistics\Container::TYPE_PIE)
			->setValueType('%')
			->setCounterId(\Statistics\Model::STATS_BROWSERS_PIE);
		
		$osPieContainer = $statsModel->createContainer();
		$osPieContainer->setTitle(_t('OS Distribution'))
			->setName('OsPie')
			->setChartType(\Statistics\Container::TYPE_PIE)
			->setValueType('%')
			->setCounterId(\Statistics\Model::STATS_OS_PIE);
		
		$mobileOsPieContainer = $statsModel->createContainer();
		$mobileOsPieContainer->setTitle(_t('OS Distribution'))
			->setName('MobileOsPie')
			->setChartType(\Statistics\Container::TYPE_PIE)
			->setValueType('%')
			->setCounterId(\Statistics\Model::STATS_MOBILE_OS_PIE);
		
		$processingTimeLayeredContainer = $statsModel->createContainer();
		$processingTimeLayeredContainer->setTitle(_t('Processing Breakdown'))
			->setName('processingTime')
			->setChartType(\Statistics\Container::TYPE_LINE)
			->setValueType('ms')
			->setCounterId(\Statistics\Model::TYPE_AVG_PROC_TIME);

        $mobileProcessingTimeLayeredContainer = $statsModel->createContainer();
        $mobileProcessingTimeLayeredContainer->setTitle(_t('Processing Breakdown'))
            ->setName('processingTime')
            ->setChartType(\Statistics\Container::TYPE_LINE)
            ->setValueType('ms')
            ->setCounterId(\Statistics\Model::TYPE_MOBILE_AVG_PROC_TIME);

		$NumOfEventsLayeredContainer = $statsModel->createContainer();
		$NumOfEventsLayeredContainer->setTitle(_t('Number of Events'))
		->setName('Events')
		->setChartType(\Statistics\Container::TYPE_LINE)
		->setValueType('')
		->setCounterId(\Statistics\Model::TYPE_NUMBER_OF_EVENTS_LAYERED);
		
		$trendOfMobileUsage = $statsModel->createContainer();
		$trendOfMobileUsage->setTitle(_t('Mobile vs Desktop'))
			->setName('TrendMobileUsage')
			->setChartType(\Statistics\Container::TYPE_LINE)
			->setValueType('%%')
			->setCounterId(\Statistics\Model::TYPE_TREND_MOBILE_USAGE_LAYERED);
		
		$manager = new Manager();
		
		$mapper = new Mapper();
		
		$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
		$serversSet = $serversMapper->findRespondingServers();

		// get IDE configurations
		$ideConfigMapper = $this->getLocator()->get('StudioIntegration\Mapper');
		$ideConfig = $ideConfigMapper->getConfiguration();
		
		return array(
					 'pageTitle' => 'Dashboard',
					 'pageTitleDesc' => '',  /* Daniel */
					 'applications' => $applicationsSet,
					 'perPage' => Module::config('list', 'resultsPerPage'),
					 'statEvents' => $eventsPieContainer,
					 'statBrowsers'	=> $browsersPieContainer,
					 'statOs' => $osPieContainer,
					 'statMobileOs' => $mobileOsPieContainer,
					 'statMobileReqPerSec' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_OS_DISTRIBUTION),
					 'statReqPerSec' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_NUM_REQUESTS_PER_SECOND),
					 'statAvgCpuUsage' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_AVG_CPU_USAGE),
					 'statAvgReqProcTime' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_AVG_REQUEST_PROCESSING_TIME),
					 'statAvgDbTime' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_AVG_DATABASE_TIME),
					 'statAvgMemUsage' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_AVG_MEMORY_USAGE),
					 //'statNumEvents' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_MON_NUM_OF_EVENTS),
                     'mobileStatProcessingTime' => $mobileProcessingTimeLayeredContainer,
					 'statNumEvents' => $NumOfEventsLayeredContainer,
					 'statSessReused' => $statsModel->getContainer(array(), ZEND_STATS_TYPE_ACTIVE_SESSIONS),
					 'statProcessingTime' => $processingTimeLayeredContainer,
					 'osType' => $manager->getOsType(),
					 'trendOfMobileUsage' => $trendOfMobileUsage,
					 'deploymentSupportedByWebServer' => $deploymentSupportedByWebServer,
					 'isClusterSupport' => $mapper->isClusterSupport(),
					 'timeout'	=> Module::config('studioIntegration', 'zend_gui', 'studioClientTimeout'),
					 'servers' => $serversSet,
					 'ideConfig' => $ideConfig,
				);
	}
}
