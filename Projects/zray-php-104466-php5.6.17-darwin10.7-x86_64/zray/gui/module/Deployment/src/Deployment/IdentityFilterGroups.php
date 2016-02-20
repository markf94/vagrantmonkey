<?php

namespace Deployment;

use ZendServer\Permissions\AclQuerierInterface;

use ZendServer\Permissions\AclQuery;

use Zend\Acl\Acl;

use Application\Module as appModule;
use ZendServer\Log\Log;

use Acl\Db\MapperGroups;

use Users\Identity;

use Zend\EventManager\Event;

use Users\IdentityAwareInterface;

class IdentityFilterGroups extends IdentityFilterSimple implements IdentityAwareInterface, AclQuerierInterface {

	/**
	 * @var Identity
	 */
	private $identity;
	/**
	 * @var MapperGroups
	 */
	private $mapperGroups;
	
	/**
	 * @var Acl
	 */
	private $acl;

    /**
     * @param array $applications
     * @param bool $emptyIsAll
     * @return array
     * @throws IdentityFilterException
     */
    public function filterAppIds(array $applications, $emptyIsAll = false) {
		$result = array();
		// if the current role is allowed access to all applications, use the simple filter instead
		if ($this->getAcl()->isAllowed('data:applications')) {
            $result = parent::filterAppIds($applications, $emptyIsAll);
		} else {

            $allowGlobal = $this->getAcl()->isAllowed('data:globalApplication');
            if ((! $applications) && (! $emptyIsAll)) {
                if ($allowGlobal && $this->isAddGlobalAppId()) {
                    $result = array(-1);
                } else {
                    $result = array();
                }
            } else {

                Log::debug('Filter applications for display');
                if ($emptyIsAll && (! $applications)) {
                    $result = $this->retrieveAccountApplications($this->getIdentity());
                } else {
                    $result = array_values(array_intersect($this->retrieveAccountApplications($this->getIdentity()), $applications));
                }

                if ($allowGlobal && $this->isAddGlobalAppId()) {
                    $result[] = -1;
                }

            }

        }

        if (0 == count($result)) {
            throw new IdentityFilterException(_t('Applications array is empty'), IdentityFilterException::EMPTY_APPLICATIONS_ARRAY);
        }

		return $result;
	}

	/**
	 * @return AclQuery $acl
	 */
	public function getAcl() {
		return $this->acl;
	}

	/**
	 * @return Identity $identity
	 */
	public function getIdentity() {
		return $this->identity;
	}
	
	/**
	 * @param Identity $identity
	 * @return IdentityFilterGroups
	 */
	public function setIdentity(Identity $identity) {
		$this->identity = $identity;
		return $this;
	}
	/**
	 * @param AclQuery $acl
	 * @return IdentityFilterGroups
	 */
	public function setAcl(AclQuery $acl) {
		$this->acl = $acl;
		return $this;
	}

	/**
	 * @param \Acl\Db\MapperGroups $mapperGroups
	 * @return Ldap
	 */
	public function setMapperGroups($mapperGroups) {
		$this->mapperGroups = $mapperGroups;
		return $this;
	}

	/**
	 * @param Identity $identity
	 * @return array
	 */
	private function retrieveAccountApplications(Identity $identity) {
		$groups = $identity->getGroups();
		$mappedApplications = $this->mapperGroups->findAllMappedApplications();
		$applications = array_intersect($mappedApplications, $groups);
		return array_keys($applications);
	}
}
