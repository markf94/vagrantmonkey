<?php
namespace EventsGroup\View\Helper;

use Zend\View\Helper\AbstractHelper,
EventsGroup\Container,
Deployment\Application\InfoContainer;

/**
 * @uses \WebApi\View\Helper\WebapiDate
 */
class EventsGroupJson extends AbstractHelper {
	/**
	 * @param EventsGroup\Container $eventsGroup
	 * @param array $serverIdstoNames
	 * @return string
	 */
	public function __invoke(Container $eventsGroup, $serverIdstoNames=array()) {
		$eventsGroupArray = array(
				'eventsGroupId'	=> $eventsGroup->getEventsGroupId(),
				'eventsCount'	=> $eventsGroup->getEventsCount(),
				'startTime' 	=> $eventsGroup->getstartTime(),
				'startTimeTimesatmp' => $eventsGroup->getstartTime(),
				'serverId' 		=> $eventsGroup->getServerId(),
				'serverName' 	=> $this->getServerName($serverIdstoNames, $eventsGroup->getServerId()),
				'class' 		=> $eventsGroup->getClass(),
				'hasCodetracing'=> $eventsGroup->hasCodetracing(),
				'userData' 		=> $eventsGroup->getUserData(),
				'javaBacktrace' => $eventsGroup->getJavaBacktrace(),
				'execTime' 		=> $eventsGroup->getExecTime(),
				'avgExecTime' 	=> $eventsGroup->getAvgExecTime(),
				'relExecTime' 	=> $eventsGroup->getRelExecTime(),				
				'memUsage' 		=> $eventsGroup->getMemUsage(),
				'avgMemUsage' 	=> $eventsGroup->getAvgMemUsage(),
				'relMemUsage' 	=> $eventsGroup->getRelMemUsage(),
				'avgOutputSize' => $eventsGroup->getAvgOutputSize(),
				'relOutputSize' => $eventsGroup->getRelOutputSize(),
				'load' 			=> $eventsGroup->getLoad(),
		);
		$eventsGroupArray['startTime'] = $this->getView()->webapiDate($eventsGroupArray['startTime']);
		
		return $this->getView()->json($eventsGroupArray, array());
	}
	
	private function getServerName($serverIdstoNames, $serverId) {		
		if (is_array($serverIdstoNames) && isset($serverIdstoNames[$serverId])) {
			return $serverIdstoNames[$serverId];
		}
		
		return '';
	}
}

