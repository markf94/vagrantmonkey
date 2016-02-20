<?php

namespace StudioIntegration;

use Zend\Stdlib\Hydrator\HydratorInterface;

class ConfigurationHydrator implements HydratorInterface {
	public function extract($object) {
		if (! $object instanceof Configuration) {
			throw new \InvalidArgumentException(_t('Expected class StudioIntegration\Configuration'));
		}
		
		return array(
					'studioAutoDetection' => $object->getAutoDetect(),
					'studioAutoDetectionEnabled' => $object->getBrowserDetect(),
					'studioHost' => $object->getHost(),
					'studioPort' => $object->getPort(),
					'studioUseSsl' => $object->getSsl(),
					'studioBreakOnFirstLine' => $object->getBrakeOnFirstLine(),
					'studioUseRemote' => $object->getUseRemote(),
				);
	}
	
	public function hydrate(array $data, $object) {/* @var $object Configuration */
		$object->setAutoDetect($data['studioAutoDetection']);
		$object->setBrowserDetect($data['studioAutoDetection']);
		$object->setConfiguration($data['studioHost'],$data['studioPort'],$data['studioUseSsl'], $data['studioBreakOnFirstLine'], $data['studioUseRemote']);
		return $object;
	}
}
