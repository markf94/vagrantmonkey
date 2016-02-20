<?php

namespace Application\View\Helper;

use Application\Module,
    Application\View\Helper\HelpLink;


use Zend\Mvc\Router\Http\RouteMatch;
use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class HelpLinkTest extends TestCase
{
    public function testLinksMapHome() {
        $helper = new HelpLink();
        $helper->setRequestUri('/ZendServer');

        $routeMatch = new RouteMatch(array('controller' => 'Index', 'action' => 'index'));
        $routeMatch->setMatchedRouteName('home');

        $helper->setRouteMatch($routeMatch);

        self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=dashboard&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/dashboard.htm'), $helper());

    }
    
    public function testLinksMapDashboard() {
        $helper = new HelpLink();
        $helper->setRequestUri('/ZendServer');

        $routeMatch = new RouteMatch(array('controller' => 'index', 'action' => 'index'));
        $routeMatch->setMatchedRouteName('home');
        $routeMatch->setMatchedRouteName('dashboard');

        $helper->setRouteMatch($routeMatch);

        self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=dashboard&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/dashboard.htm'), $helper());

    }
    
    public function testLinksMapDifferentHome() {
    	$helper = new HelpLink();
    	$helper->setRequestUri('/ZendServer');
    
    	$routeMatch = new RouteMatch(array('controller' => 'FakeHome', 'action' => 'index'));
    	$routeMatch->setMatchedRouteName('home');
    	$routeMatch->setMatchedRouteName('fakehome');
    	
    	$helper->setRouteMatch($routeMatch);
    
    	self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=fake_home&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/fake_home.htm'), $helper());
    
    }

    public function testLinksMapSingle() {
        $helper = new HelpLink();

        $routeMatch = new RouteMatch(array('controller' => 'IssueList', 'action' => 'index'));
        $routeMatch->setMatchedRouteName('default');
        $helper->setRouteMatch($routeMatch);

        $helper->setRequestUri('/ZendServer/IssueList');
        self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=issue_list&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/issue_list.htm'), $helper());

        $routeMatch = new RouteMatch(array('controller' => 'Issue', 'action' => 'index'));
        $routeMatch->setMatchedRouteName('default');
        $helper->setRouteMatch($routeMatch);

        $helper->setRequestUri('/ZendServer/Issue');
        self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=issue&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/issue.htm'), $helper());

        $routeMatch = new RouteMatch(array('controller' => 'MonitorEditRule', 'action' => 'index'));
        $routeMatch->setMatchedRouteName('default');
        $helper->setRouteMatch($routeMatch);

        $helper->setRequestUri('/ZendServer/MonitorEditRule');
        self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=monitor_edit_rule&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/monitor_edit_rule.htm'), $helper());

        $routeMatch = new RouteMatch(array('controller' => 'Extensions', 'action' => 'phpExtensions'));
        $routeMatch->setMatchedRouteName('default');
        $helper->setRouteMatch($routeMatch);

        $helper->setRequestUri('/ZendServer/Extensions/phpExtensions/');
        self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=extensions_php_extensions&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/extensions_php_extensions.htm'), $helper());

    }


    public function testLinksMapTwoLevels() {
        $helper = new HelpLink();


        $routeMatch = new RouteMatch(array('controller' => 'Extensions', 'action' => 'phpExtensions'));
        $routeMatch->setMatchedRouteName('default');
        $helper->setRouteMatch($routeMatch);

        $helper->setRequestUri('/ZendServer/Extensions/phpExtensions/');
        self::assertEquals('http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=extensions_php_extensions&url='. urlencode('http://files.zend.com/help/Zend-Server-8/Content/extensions_php_extensions.htm'), $helper());

    }

}

