<?php

namespace Application\Controller\Plugin;


use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use Zend\Permissions\Acl\Acl;

use Zend\Config\Config;

class CapabilitiesList extends AbstractPlugin
{
	/**
	 * @var Acl
	 */
	private $licenseAcl = null;
	/**
	 * @var boolean
	 */
	private $populated = false;
	/**
	 * @var array
	 */
	private $capabilitiesList = array();
	/**
	 * @var array
	 */
	private $changesMatrix = array();
	
	/**
	 * @var Config
	 */
	private $licenseAclConfig = null;
	
	/**
	 * @return \Application\Controller\Plugin\InjectCapabilitiesList
	 */
	public function __invoke() {
		if (!$this->populated) {
			$this->prepareCapabilitiesList();
		}
		return $this;
	}
	
	private function prepareCapabilitiesList() {
		$licenseAcl = $this->getLicenseAcl();
		 
		$capabilitiesList = array();
		$licenseAclData = $this->getLicenseAclConfig()->toArray();
		$licenseTypes = array_keys($licenseAclData);
		/// create a full capabilities list
		foreach ($licenseAclData as $licenseType => $resources) {
			foreach ($resources as $resource => $actions) {
				if (! strstr($resource, 'route:')) {
					$capabilitiesList[$resource] = $actions;
				}
			}
		}
		
		/// special cases to indicate specific features
		$capabilitiesList['route:ServersWebAPI'] = false;
		$capabilitiesList['route:DevBarWebApi'] = false;
		$capabilitiesList['route:VhostWebAPI'] = false;
		 
		/// create a full capabilities map for all editions
		$this->capabilitiesList = array();
		foreach ($licenseTypes as $type) {
			foreach ($capabilitiesList as $resource => $actions) {
				/// if resource has a resource-level priv
				/// or if the actions list has a first "boolean value" (which sets a reousrce-level priv but then allows to set specific actions)
				if (is_bool($actions) || (is_array($actions) && is_bool(current($actions)))) {
					$this->capabilitiesList[$type][$resource] = $licenseAcl->isAllowed("edition:$type", $resource);
				}
				 
				/// Cover all other cases - actions is an array
				if (is_array($actions)) {
					foreach ($actions as $action => $value) {
						if (! is_bool($action)) {
							$this->capabilitiesList[$type]["$resource:$action"] = $licenseAcl->isAllowed("edition:$type", $resource, $action);
						}
					}
				}
			}
		}
		
		$this->changesMatrix = array();
		foreach ($licenseTypes as $type) {
			foreach ($licenseTypes as $type2) {
				$currentMatrix = array();
				foreach ($this->capabilitiesList[$type] as $resource => $allowed) {
					if ($this->capabilitiesList[$type2][$resource] != $this->capabilitiesList[$type][$resource]) {
						/// store value in the new edition
						$currentMatrix[$resource] = $this->capabilitiesList[$type2][$resource];
					}
				}
				$this->changesMatrix[$type][$type2] = $currentMatrix;
			}
		}
	}
	
	/**
	 * @return Acl
	 */
	public function getLicenseAcl() {
		return $this->licenseAcl;
	}

	/**
	 * @return array
	 */
	public function getCapabilitiesList() {
		return $this->capabilitiesList;
	}

	/**
	 * @return array
	 */
	public function getChangesMatrix() {
		return $this->changesMatrix;
	}

	/**
	 * @return Config
	 */
	public function getLicenseAclConfig() {
		return $this->licenseAclConfig;
	}

	/**
	 * @param \Zend\Config\Config $licenseAclConfig
	 * @return CapabilitiesList
	 */
	public function setLicenseAclConfig($licenseAclConfig) {
		$this->licenseAclConfig = $licenseAclConfig;
		return $this;
	}

	/**
	 * @param array
	 * @return CapabilitiesList
	 */
	public function setCapabilitiesList($capabilitiesList) {
		$this->capabilitiesList = $capabilitiesList;
		return $this;
	}

	/**
	 * @param array
	 * @return CapabilitiesList
	 */
	public function setChangesMatrix($changesMatrix) {
		$this->changesMatrix = $changesMatrix;
		return $this;
	}

	/**
	 * @param \Zend\Permissions\Acl\Acl $licenseAcl
	 * @return CapabilitiesList
	 */
	public function setLicenseAcl($licenseAcl) {
		$this->licenseAcl = $licenseAcl;
		return $this;
	}

	
	
}