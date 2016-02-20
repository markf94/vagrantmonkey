<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class TestEmail extends AbstractPlugin {
	public function __invoke($params) {
		return array();
	}
}