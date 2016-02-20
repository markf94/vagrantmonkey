<?php
namespace Deployment\Controller;

use Deployment\Validator\ApplicationBaseUrlNotExists;

use Audit\Db\Mapper;

use Zend\Uri\UriFactory;
use ZendServer\Mvc\Controller\WebAPIActionController;

use Zend\Mvc\Controller\ActionController,
ZendServer\Log\Log,
ZendServer\FS\FS,
Zend\Validator,
Deployment\Model,
WebAPI,
Audit\Db\Mapper as auditMapper,
Audit\Db\ProgressMapper,
Deployment\SessionStorage,
Zsd\Db\TasksMapper,
ZendServer\Exception;

use Deployment\Validator\ApplicationNameNotExists;

use Zend\View\Model\ViewModel;
use Deployment\InputFilter\Factory;
use Deployment\Application\Container;
use ZendServer\Set;
use Vhost\Entity\VhostNode;
use Zend\Http\PhpEnvironment\Response;

class WebAPI19Controller extends WebAPIController
{
    /**
     * @param array $validatorMessages
     */
    protected function validateAppnameConflict($validatorMessages) {
        if (isset($validatorMessages[ApplicationNameNotExists::APP_NAME_EXISTS])) {
        	   return false;
        }
        return true;
    }
}
