<?php

namespace EventsGroup\View\Helper;

use ZendServer\PHPUnit\TestCase;

use Zend\ServiceManager\Config;

use EventsGroup\Container;

use Zend\View\Renderer\PhpRenderer;

use EventsGroup\View\Helper\EventsGroupJson;

use Zend\View\HelperPluginManager;
use PHPUnit_Framework_TestCase, Zend;
use Zend\Log\Logger;
use ZendServer\Log\Log;
use Zend\Log\Writer\Mock;

require_once 'tests/bootstrap.php';

class EventsGroupJsonTest extends TestCase
{

	public function test__invoke() {
		
		$broker = new HelperPluginManager ( new Config(array (
				'invokables' => array (
								'webapiDate' => 'WebAPI\View\Helper\WebapiDate',
								'phpErrorType' => 'ZendServer\View\Helper\PhpErrorType',
		) )));
		
		$renderer = new PhpRenderer();
		$renderer->setHelperPluginManager($broker);
		
		$helper = new EventsGroupJson();
		$helper->setView ( $renderer );
		
		
		$container = new Container(array(
				ZM_DATA_ATTR => array(
						ZM_DATA_ATTR_LOAD => 0,
						ZM_DATA_ATTR_AVG_OUT_SIZE => 0,
						ZM_DATA_ATTR_AVG_MEM_USAGE => 0,
						ZM_DATA_ATTR_MEM_USAGE => 0,
						ZM_DATA_ATTR_AVG_EXEC_TIME => 0,
						ZM_DATA_ATTR_EXEC_TIME => 0,
						ZM_DATA_ATTR_USER_DATA => '',
						ZM_DATA_ATTR_CLASS => '',
				),
				ZM_DATA_HAS_TRACE_FILES => false,
				'tracer_dump_file' => false,
				ZM_DATA_NODE_ID => 0,
				ZM_DATA_FIRST_TIMESTAMP => 0,
				ZM_DATA_REPEATS => 0,
				ZM_DATA_EVENT_ID => 0,
				
		), 1);
		
		$expectedString = '{"eventsGroupId":"","eventsCount":0,"startTime":"","startTimeTimesatmp":0,"serverId":"","serverName":"","class":"","hasCodetracing":false,"userData":"","javaBacktrace":"","execTime":0,"avgExecTime":0,"relExecTime":0,"memUsage":0,"avgMemUsage":0,"relMemUsage":0,"avgOutputSize":0,"relOutputSize":0,"load":0}';
		$actual = $helper($container);
		$this->assertEquals($expectedString, $actual);
	}
}

