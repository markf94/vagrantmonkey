<?php

namespace Issue\View\Helper;

use Zend\ServiceManager\Config;

use ZendServer\PHPUnit\TestCase;

use Zend\View\HelperPluginManager;

use Zend\View\Renderer\PhpRenderer;

use Deployment\Application\InfoContainer;

use Issue\Container;

use Zend\Json\Json;

use Issue\View\Helper\IssueXml;

use PHPUnit_Framework_TestCase, Zend, XMLReader;
use Zend\Log\Logger;
use ZendServer\Log\Log;
use Zend\Log\Writer\Mock;

require_once 'tests/bootstrap.php';

class IssueXmlTest extends TestCase
{
	public function test__invoke() {
		
		$broker = new HelperPluginManager(new Config(array(
				'invokables' => array(
						'webapiDate' => 'WebAPI\View\Helper\WebapiDate',
						'phpErrorType' => 'ZendServer\View\Helper\PhpErrorType',
						'issueSeverity' => 'Issue\View\Helper\IssueSeverity',
						'issueStatus' => 'Issue\View\Helper\IssueStatus',
						'eventsGroupXml' => 'EventsGroup\View\Helper\EventsGroupXml',
					))));
		
		$renderer = new PhpRenderer();
		$renderer->setHelperPluginManager($broker);
		
		$helper = new IssueXml();
		$helper->setView($renderer);
		
		$issue = new Container(array(
					'cluster_issue_id' => 0,
					'event_type' => 1,
					'rule_name' => '',
					'repeats' => 0,
					'last_timestamp' => 0,
					'status' => 0,
					'tracer_dump_file' => '',
					'full_url' => '',
					'file_name' => '',
					'line' => 0,
					'function_name' => '',
					'agg_hint' => '',
					'severity' => 0,
				), 0);
		$application = new InfoContainer(array('applicationId' => 0), 0);
		
		$result = $helper($issue, $application);
		$reader = new \SimpleXMLElement($result);
		
		self::assertArrayHasKeys(array(
				'id', 'eventType', 'rule', 'count', 'lastOccurance', 'severity', 'status', 'hasCodetracing',
				'codeTracingEventGroupId', 'ruleId', 'generalDetails', 'whatHappenedDetails', 'routeDetails', 'appName', 'appId'
		), (array)$reader);
		
		self::assertArrayHasKeys(array(
				'url', 'baseUrl', 'sourceFile', 'sourceLine', 'function', 'customEventClass', 'aggregationHint',
				'errorString', 'errorType'
		), (array)$reader->generalDetails);
		
		$expectedHelpers = array('eventsgroupxml', 'webapidate', 'issueseverity', 'issuestatus');
		/// check dependent helpers are registered
		$services = $broker->getRegisteredServices();
		self::assertArrayHasKeys($expectedHelpers, array_flip($services['instances']));
	}
	
	public function test__invokeNoApplication() {
		
		$broker = new HelperPluginManager(new Config(array(
				'invokables' => array(
						'webapiDate' => 'WebAPI\View\Helper\WebapiDate',
						'phpErrorType' => 'ZendServer\View\Helper\PhpErrorType',
						'issueSeverity' => 'Issue\View\Helper\IssueSeverity',
						'issueStatus' => 'Issue\View\Helper\IssueStatus',
						'eventsGroupXml' => 'EventsGroup\View\Helper\EventsGroupXml',
					))));
		
		$renderer = new PhpRenderer();
		$renderer->setHelperPluginManager($broker);
		
		$helper = new IssueXml();
		$helper->setView($renderer);
		
		$issue = new Container(array(
					'cluster_issue_id' => 0,
					'event_type' => 1,
					'rule_name' => '',
					'repeats' => 0,
					'last_timestamp' => 0,
					'status' => 0,
					'tracer_dump_file' => '',
					'full_url' => '',
					'file_name' => '',
					'line' => 0,
					'function_name' => '',
					'agg_hint' => '',
					'severity' => 0,
				), 0);
		
		$result = $helper($issue);
		$reader = new \SimpleXMLElement($result);
		
		self::assertArrayHasKeys(array(
				'id', 'eventType', 'rule', 'count', 'lastOccurance', 'severity', 'status', 'hasCodetracing',
				'codeTracingEventGroupId', 'ruleId', 'generalDetails', 'whatHappenedDetails', 'routeDetails', 'appName', 'appId'
		), (array)$reader);
		
		self::assertArrayHasKeys(array(
				'url', 'baseUrl', 'sourceFile', 'sourceLine', 'function', 'customEventClass', 'aggregationHint',
				'errorString', 'errorType'
		), (array)$reader->generalDetails);
		
		$expectedHelpers = array('eventsgroupxml', 'webapidate', 'issueseverity', 'issuestatus');
		/// check dependent helpers are registered
		$services = $broker->getRegisteredServices();
		self::assertArrayHasKeys($expectedHelpers, array_flip($services['instances']));
	}
}

