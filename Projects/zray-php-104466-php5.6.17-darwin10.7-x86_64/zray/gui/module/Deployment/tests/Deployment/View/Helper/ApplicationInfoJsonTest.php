<?php

namespace Deployment\View\Helper;

use ZendServer\PHPUnit\TestCase;

use Zend\Config\Config;

use Application\Module;

use Zend\View\HelperPluginManager;

use Zend\View\Renderer\PhpRenderer;

use Deployment\Application\Container;
use ZendDeployment_Application;

use Zend\Json\Json;
use Deployment\View\Helper\ApplicationInfoJson;

use PHPUnit_Framework_TestCase, Zend;
use Zend\Log\Logger;
use ZendServer\Log\Log;
use Zend\Log\Writer\Mock;

require_once 'tests/bootstrap.php';

class ApplicationInfoJsonTest extends TestCase
{
	public function test__invoke() {
		
		$broker = new HelperPluginManager(new \Zend\ServiceManager\Config(array(
					'invokables' => array(
						'AppHealthCheckStatus' => 'Deployment\View\Helper\AppHealthCheckStatus',
						'applicationUrl' => 'Deployment\View\Helper\ApplicationUrl',
						'appMessages' => 'Deployment\View\Helper\AppMessages',
						'appStatus' => 'Deployment\View\Helper\AppStatus',
						'webapidate' => 'WebAPI\View\Helper\WebapiDate',
					))));
		
		$renderer = new PhpRenderer();
		$renderer->setHelperPluginManager($broker);
		
		$helper = new ApplicationInfoJson();
		$helper->setView($renderer);
		$application = new ZendDeployment_Application();
                $application->setErrors(array('error message'));
                $application->setStatus(\ZendDeployment_Application_Interface::STATUS_ACTIVE);
                $application->setCreationTime(1);
                
                $application = new Container($application, 1);
                
		$result = (array)Json::decode($helper($application));

		self::assertArrayHasKeys(array(
					'id', 'baseUrl', 'appName', 'userAppName', 'installedLocation', 'status', 'isRollbackable', 
					'isRedeployable', 'servers', 'deployedVersions', 'messageList', 'creationTime', 'lastUsed', 'vhostId'
				), ($result));
		
		/// check dependent helpers are registered
		$services = $broker->getRegisteredServices();
		self::assertArrayHasKeys(array(
				'appstatus', 'webapidate', 'json'
		), array_flip($services['instances']));
                
                self::assertEquals('deployed', $result['status']);
                self::assertNotEquals(1, $result['creationTime']);
                $messageResult = current((array) $result['messageList']);
                self::assertEquals(array('type' => 'error', 'message' => 'error message'), (array)$messageResult);
	}
	
}

