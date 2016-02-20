<?php

namespace DeploymentLibrary\Controller\Plugin;

use ZendServer\PHPUnit\TestCase;
use ZendServer\Mvc\Controller\ActionController;
use ZendServer\Set;
use Zend\Json\Json;
use Zend\Http\Headers;
use Zend\Http\Header\SetCookie;
use Zend\Http\Header\Cookie;

require_once 'tests/bootstrap.php';

class SetUpdateCookieTest extends TestCase {

    public function testSetUpdateCookie() {
        $plugin = new SetUpdateCookieTestable();

        $controller = new ActionController();
        $request = $controller->getRequest(); /* @var $request \Zend\Http\Request */
        $response = $controller->getResponse();
        $plugin->setController($controller);
        
        $acl = $this->getMock('ZendServer\Permissions\AclQuery');
        $acl->expects($this->once())->method('isAllowedEdition')->will($this->returnValue(true));
        $plugin->setAcl($acl);
        
        $librariesMapper = $this->getMock('\DeploymentLibrary\Mapper');
        $librariesMapper->expects($this->once())->method('getAllLibrariesUpdateUrl')
        	->will($this->returnValue(array(
        			'library1' => array('version' => '1.0')
        	)));
        $plugin->setLibrariesMapper($librariesMapper);
        
        $updatesMapper = $this->getMock('\DeploymentLibrary\Db\Mapper');
        $updatesMapper->expects($this->once())->method('getUpdates')
        	->will($this->returnValue(new Set(array(
        			array('NAME' => 'library1', 'VERSION' => '1.1')
        	))));
        $plugin->setUpdatesMapper($updatesMapper);

        $plugin();
        
        $cookie = current($response->getCookie()); /* @var $cookie \Zend\Http\Header\SetCookie */
        self::assertEquals('ZSLIBRARIES', $cookie->getName());
        self::assertEquals('{"library1":{"version":"1.1"}}', $cookie->getValue());
        
    }

    public function testSetUpdateCookieCookieIsSet() {
        $plugin = new SetUpdateCookieTestable();

        $controller = new ActionController();
        $request = $controller->getRequest(); /* @var $request \Zend\Http\Request */
        $response = $controller->getResponse();
        
        $response->getHeaders()->addHeader(new SetCookie('ZSLIBRARIES', '{"library2":{"version":"3.4"}}'));
        
        $headers = new Headers();
        $headers->addHeader(new Cookie(array('ZSLIBRARIES' => '{"library2":{"version":"3.4"}}')));
        $request->setHeaders($headers);
        
        $plugin->setController($controller);
        
        $acl = $this->getMock('ZendServer\Permissions\AclQuery');
        $acl->expects($this->once())->method('isAllowedEdition')->will($this->returnValue(true));
        $plugin->setAcl($acl);

        $plugin();
        
        $setCookie = current($response->getCookie());
        self::assertEquals('ZSLIBRARIES', $setCookie->getName());
        self::assertEquals('{"library2":{"version":"3.4"}}', $setCookie->getValue());
        
    }

    public function testSetUpdateCookieNoPermission() {
        $plugin = new SetUpdateCookieTestable();

        $controller = new ActionController();
        $request = $controller->getRequest(); /* @var $request \Zend\Http\Request */
        $response = $controller->getResponse();
        $plugin->setController($controller);
        
        $acl = $this->getMock('ZendServer\Permissions\AclQuery');
        $acl->expects($this->once())->method('isAllowedEdition')->will($this->returnValue(false));
        $plugin->setAcl($acl);

        $plugin();
        
        self::assertFalse($response->getCookie());
        
    }

}

class SetUpdateCookieTestable extends SetUpdateCookie {
    /**
     * @param $libraries
     * @return bool
     */
    protected function setCookieContent($libraries) {
        $cookie = new SetCookie("ZSLIBRARIES", Json::encode($libraries), time()+(24*3600), '/'); /* expire in 1 day */
        $this->getController()->getResponse()->getHeaders()->addHeader($cookie);
    }
}