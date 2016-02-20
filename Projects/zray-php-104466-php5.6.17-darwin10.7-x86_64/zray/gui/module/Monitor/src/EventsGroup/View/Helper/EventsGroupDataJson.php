<?php
namespace EventsGroup\View\Helper;

use Zend\View\Helper\AbstractHelper,
EventsGroup\Container,
EventsGroup\DataContainer,
Deployment\Application\InfoContainer;

/**
 * @uses \WebApi\View\Helper\WebapiDate
 */
class EventsGroupDataJson extends AbstractHelper {
	/**
	 * @param EventsGroup\DataContainer $eventsGroup
	 * @return string
	 */
	public function __invoke(Container $eventsGroup) {
		$eventsGroupArray = array(
				'eventsGroupId'	=> $eventsGroup->getEventsGroupId(),
				'eventsCount'	=> $eventsGroup->getEventsCount(),
				'startTime' 	=> $eventsGroup->getstartTime(),
				'startTimeTimestamp' => $eventsGroup->getstartTime(),
				'serverId' 		=> $eventsGroup->getServerId(),
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
				'email'			=> $eventsGroup->getEmailAction(),
				'actionUrl'		=> $eventsGroup->getUrlAction(),
		);
		$eventsGroupArray['startTime'] = $this->getView()->webapiDate($eventsGroupArray['startTime']);
		
		return $this->getView()->json($eventsGroupArray, array());
	}
}

