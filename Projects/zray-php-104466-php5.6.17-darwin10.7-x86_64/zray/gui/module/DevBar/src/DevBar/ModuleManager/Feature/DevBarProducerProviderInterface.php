<?php

namespace DevBar\ModuleManager\Feature;

use Zend\EventManager\EventInterface;
interface DevBarProducerProviderInterface {
	/**
	 * @return array[\Zend\View\Model\ViewModel]
	 */
	public function getDevBarProducers(EventInterface $e);
}

