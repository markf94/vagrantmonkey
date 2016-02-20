<?php
namespace WebAPI\Mvc\View;

use ZendServer\PHPUnit\TestCase;

use PHPUnit_Framework_TestCase,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Application,
    Zend\Stdlib\ResponseDescription as Response,
	WebAPI\Mvc\View\Http\RouteNotFoundStrategy;

require_once 'tests/bootstrap.php';

class RouteNotFoundStrategyTest extends TestCase
{
    public function testPrepareNotFoundViewModel() {
        $response = new \Zend\Http\Response();
        $response->setStatusCode(404);
        
        $event = new MvcEvent();
        $event->setResponse($response);
        
        $strategy = new RouteNotFoundStrategy();
        $strategy->prepareNotFoundViewModel($event);
        
        $model = $event->getResult();
        self::assertTrue($model instanceof \Zend\View\Model\ViewModel);
        $variables = $model->getVariables();
        
        self::assertObjectHasAttribute('errorCode', $variables);
        self::assertEquals('unknownMethod', $variables['errorCode']);
        
        // rewrite status code to 400
        self::assertEquals(400, $response->getStatusCode());
    }

}