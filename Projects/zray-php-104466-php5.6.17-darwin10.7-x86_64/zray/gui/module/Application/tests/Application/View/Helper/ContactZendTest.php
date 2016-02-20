<?php

namespace Application\View\Helper;

use Application\Module,
    Application\View\Helper\ContactZend;


use Zend\Mvc\Router\Http\RouteMatch;
use ZendServer\PHPUnit\TestCase;
use Configuration\License\License;

require_once 'tests/bootstrap.php';

class ContactZendTest extends TestCase
{
    public function testContactZendValidFullLicense() {
        $helper = new ContactZend();
        $helper->setLicense(new License(array('serial_number' => 'serialnumber', 'signature_invalid' => false, 'edition' => 2, 'evaluation' => false)));

        self::assertEquals('http://www.zend.com/go/paid/', $helper());
    }
    
    public function testContactZendValidTrialLicense() {
        $helper = new ContactZend();
        $helper->setLicense(new License(array('serial_number' => 'serialnumber', 'signature_invalid' => false, 'edition' => 2, 'evaluation' => true)));
        self::assertEquals('http://www.zend.com/go/trial/', $helper());
    }
    
    public function testContactZendInvalidFullLicense() {
        $helper = new ContactZend();
        $helper->setLicense(new License(array('serial_number' => 'serialnumber', 'signature_invalid' => true, 'edition' => 2, 'evaluation' => false)));
        self::assertEquals('http://www.zend.com/go/paid/', $helper());
    }
    
    public function testContactZendInvalidTrialLicense() {
        $helper = new ContactZend();
        $helper->setLicense(new License(array('serial_number' => 'serialnumber', 'signature_invalid' => true, 'edition' => 2, 'evaluation' => true)));
        self::assertEquals('http://www.zend.com/go/trial/', $helper());
    }
    
    public function testContactZendEmptyLicense() {
        $helper = new ContactZend();
        $helper->setLicense(new License(array('serial_number' => 'serialnumber', 'signature_invalid' => false, 'edition' => 2, 'evaluation' => false)));
        self::assertEquals('http://www.zend.com/go/paid/', $helper());
    }
    
    public function testContactZendBadEditionLicense() {
        $helper = new ContactZend();
        $helper->setLicense(new License(array('serial_number' => 'serialnumber', 'signature_invalid' => false, 'edition' => -1, 'evaluation' => false)));
        self::assertEquals('http://www.zend.com/go/free/', $helper());
    }
    
    public function testContactZendValidFreeLicense() {
        $helper = new ContactZend();
        $helper->setLicense(new License(array('serial_number' => 'serialnumber', 'signature_invalid' => false, 'edition' => 5, 'evaluation' => false)));
        self::assertEquals('http://www.zend.com/go/free/', $helper());
    }

}

