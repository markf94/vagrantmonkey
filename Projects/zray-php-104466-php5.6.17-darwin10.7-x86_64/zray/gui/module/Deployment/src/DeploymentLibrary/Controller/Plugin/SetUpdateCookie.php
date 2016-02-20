<?php

namespace DeploymentLibrary\Controller\Plugin;

use Zend\Http\Header\SetCookie;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZendServer\Log\Log;
use ZendServer\Permissions\AclQuery;
use DeploymentLibrary\Mapper as librariesMapper;
use DeploymentLibrary\Db\Mapper as updatesMapper;

class SetUpdateCookie extends AbstractPlugin {
	
	/**
	 * @var AclQuery
	 */
	private $acl;
	
	/**
	 * @var librariesMapper
	 */
	private $librariesMapper;

    /**
     * @var updatesMapper
     */
    private $updatesMapper;

    public function __invoke() {
        if ($this->getAcl()->isAllowedEdition('route:DeploymentLibraryWebAPI', 'libraryDeploy') &&
                (! isset($this->getController()->getRequest()->getCookie()->ZSLIBRARIES))) {

            $mapper = $this->getLibrariesMapper();
            $dbMapper = $this->getUpdatesMapper();

            // get all update urls
            $libraries = $mapper->getAllLibrariesUpdateUrl(true);

            // get new versions and merge them with current url versions
            $updatesResult = $dbMapper->getUpdates()->toArray();

            $updates = array();
            foreach ($updatesResult as $update) {
                $updates[$update['NAME']] = $update;
            }

            foreach ($libraries as $libraryName => $library) {
                if (isset($updates[$libraryName]) && version_compare($updates[$libraryName]['VERSION'], $library['version'])) {
                    $libraries[$libraryName]['version'] = $updates[$libraryName]['VERSION'];
                }
            }

			$this->setCookieContent($libraries);
        }
    }

    /**
     * @param \DeploymentLibrary\Db\Mapper $updatesMapper
     */
    public function setUpdatesMapper($updatesMapper)
    {
        $this->updatesMapper = $updatesMapper;
    }

    /**
     * @return \DeploymentLibrary\Db\Mapper
     */
    public function getUpdatesMapper()
    {
        return $this->updatesMapper;
    }

    /**
     * @param \ZendServer\Permissions\AclQuery $acl
     */
    public function setAcl($acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return \ZendServer\Permissions\AclQuery
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param \DeploymentLibrary\Mapper $librariesMapper
     */
    public function setLibrariesMapper($librariesMapper)
    {
        $this->librariesMapper = $librariesMapper;
    }

    /**
     * @return \DeploymentLibrary\Mapper
     */
    public function getLibrariesMapper()
    {
        return $this->librariesMapper;
    }

    /**
     * @param $libraries
     * @return bool
     */
    protected function setCookieContent($libraries) {
        return setcookie("ZSLIBRARIES", Json::encode($libraries), time()+(24*3600), '/');
    }

}