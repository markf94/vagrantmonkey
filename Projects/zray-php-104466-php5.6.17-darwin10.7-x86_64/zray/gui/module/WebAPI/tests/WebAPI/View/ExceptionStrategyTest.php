<?php
namespace WebAPI\Mvc\View;

use ZendServer\PHPUnit\TestCase;

use PHPUnit_Framework_TestCase,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Application,
    Zend\Stdlib\ResponseDescription as Response,
	WebAPI\Mvc\View\Http\ExceptionStrategy;

require_once 'tests/bootstrap.php';

class ExceptionStrategyTest extends TestCase
{
    public function testPrepareNotFoundViewModel() {
        
        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new \WebAPI\Exception('exception message', \WebAPI\Exception::UNKNOWN_METHOD));
        
        $strategy = new ExceptionStrategy();
        $strategy->prepareExceptionViewModel($event);
        
        $model = $event->getResult();
        self::assertTrue($model instanceof \Zend\View\Model\ViewModel);
        $variables = $model->getVariables();
        
        self::assertArrayHasKey('errorCode', $variables);
        self::assertArrayHasKey('errorMessage', $variables);
        self::assertEquals('unknownMethod', $variables['errorCode']);
        self::assertEquals('exception message', $variables['errorMessage']);
        
        $response = $event->getResponse();
        self::assertTrue($model instanceof \Zend\View\Model\ViewModel);
        self::assertEquals('400', $response->getStatusCode());
    }

}