<?php

namespace Deployment\View\Helper;

use ZendServer\PHPUnit\TestCase;

use Zend\Config\Config;

use Application\Module;

use Zend\View\HelperPluginManager;

use Zend\View\Renderer\PhpRenderer;

use Deployment\Application\Container;
use ZendDeployment_Application;
use ZendDeployment_Application_Interface;

use Zend\Json\Json;
use Deployment\View\Helper\ApplicationInfoXml;

use PHPUnit_Framework_TestCase, Zend;
use Zend\Log\Logger;
use ZendServer\Log\Log;
use Zend\Log\Writer\Mock;

require_once 'tests/bootstrap.php';

class ApplicationInfoXTest extends TestCase
{
	public function test__invoke() {
		
           $broker = new HelperPluginManager(new \Zend\ServiceManager\Config(array(
					'invokables' => array(
                                            'applicationUrl' => 'Deployment\View\Helper\ApplicationUrl',
                                            'appMessages' => 'Deployment\View\Helper\AppMessages',
                                            'appStatus' => 'Deployment\View\Helper\AppStatus',
                                            'webapidate' => 'WebAPI\View\Helper\WebapiDate',
                                    ))));
		
            $renderer = new PhpRenderer();
            $renderer->setHelperPluginManager($broker);

            $helper = new ApplicationInfoXml();
            $helper->setView($renderer);
            $application = new ZendDeployment_Application();
            $application->setErrors(array('error message'));
            $application->setStatus(ZendDeployment_Application_Interface::STATUS_ACTIVE);
            $application->setCreationTime(1);
            $application->setLastUsed(1);

            $application = new Container($application, 1);

            $result = $helper($application);
            $reader = new \SimpleXMLElement($result);
                
		self::assertArrayHasKeys(array(
					'id', 'baseUrl', 'appName', 'userAppName', 'installedLocation', 'status', 'isRollbackable',
				 	'isRedeployable', 'servers', 'messageList', 'creationTime', 'lastUsed', 'vhostId'
				), (array)$reader);
		
		/// check dependent helpers are registered
		$services = $broker->getRegisteredServices();
		self::assertArrayHasKeys(array(
					'appstatus', 'webapidate'
				), array_flip($services['instances']));
                
        self::assertEquals('deployed', (string)$reader->status);
        self::assertEquals('error message', (string)$reader->messageList->error);
        self::assertNotEquals(1, (string)$reader->creationTime);
        self::assertNotEquals(1, (string)$reader->lastUsed);
	}
	
}

