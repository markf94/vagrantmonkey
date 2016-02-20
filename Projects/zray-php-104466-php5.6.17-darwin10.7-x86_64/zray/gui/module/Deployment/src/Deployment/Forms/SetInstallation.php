<?php
namespace Deployment\Forms;

use Deployment;
use Vhost\Mapper\Vhost;
use Zend\Form,
	Deployment\SessionStorage,
	Deployment\Model,
	Deployment\Application\Package,
	Deployment\Forms\ApplicationsAwareForm,
	ZendServer\Log,
	ZendServer\Text,
	ZendServer\Exception,
	Deployment\InputFilter\Factory as DeploymentInputFactory;
use Vhost\Validator\VhostValidForDeploy;
use Zend\Validator\Uri;
use Vhost\Entity\Vhost as VhostEntity;
use Zend\Uri\UriFactory;
	

class SetInstallation extends ApplicationsAwareForm {
	
	/**
	 * @var Form\Element\Select
	 */
	private $vhosts;
	/**
	 * @var Form\Element\Text
	 */
	private $displayName;
	/**
	 * @var Form\Element\Text
	 */
	private $path;
	/**
	 * @var Form\Element\Hidden
	 */
	private $newVhost;

    /**
     * @var Vhost
     */
    private $vhostMapper;

	/**
	 * @var SessionStorage
	 */
	private $sessionStorage = null;
	
	/**
	 * @var VhostValidForDeploy
	 */
	private $vhostValidator;
	
	public function __construct($options, $deploymentModel) {
		parent::__construct($options, $deploymentModel);

		$this->setAttribute('id', 'deployment-base-url');
		$this->setAttribute('class', 'deploy-wizard-form');

		$this->add(array(
				'name' => 'displayName',
				'options' => array(
						'label' => _t('Display Name')
				),
				'attributes' => array(
						'onkeypress' => 'return event.keyCode!=13', // disable enter click on submit
						'id' => 'displayName',
				)
		));

		$defaultServer = \Application\Module::config('deployment' , 'defaultServer');
		$defaultPort = $deploymentModel->getDefaultServerPort();
		if ($defaultServer == '<default-server>') {
			if ($defaultPort != 80) {
				$defaultServer = "default server:$defaultPort";
			} else {
				$defaultServer = "default server";
			}
		}
		
		$defaultServerUrl = \Deployment\Model::DEFAULT_SERVER;
		// if baseUrl is passed as default server we add the default port to the deploy application baseUrl
		if (strstr($defaultServerUrl, '<default-server>')) {
			$port = $defaultPort;
			if (intval($port) != 80) {
				$defaultServerUrl = str_replace('<default-server>', "<default-server>:$port", $defaultServerUrl);
			}
		}
	
		$filteredDefaultServer = str_replace('<default-server>', 'defaultserver', $defaultServerUrl);
		$this->add(array(
				'name' => 'vhosts',
				'options' => array(
						'label' => _t('Virtual Host'),
						'options' => array( "http://{$filteredDefaultServer}" => $defaultServer),
				),
				'type' => 'Zend\Form\Element\Select',
				'attributes' => array(
						'id' => 'vhosts',
						'onchange' => 'changeVHost()'
				)
		));

		$this->add(array(
				'name' => 'path',
				'options' => array(
						'label' => _t('Path'),
				),
				'attributes' => array(
						'onkeypress' => 'return event.keyCode!=13', // disable enter click on submit
						'id' => 'path',
						'onkeyup' => 'changeVHost()'
				)
		));
		
		$this->add(array(
				'name' => 'addNew',
				'type' => 'Zend\Form\Element\Button',
				'attributes' => array(
						'value' => _t('Add New'),
						'onclick' => 'addNewVHost()'
				)
		));

		$this->add(array(
				'name' => 'newVhost',
				'type' => 'Zend\Form\Element\Hidden',
				'attributes' => array(
						'id' => 'newVhost',
				)
		));
		
		$inputFactory = new DeploymentInputFactory();
		$inputFactory->setDeploymentModel($deploymentModel);
		$validators = $inputFactory->createInputFilter(array());
		$validators->setValidationGroup(array('vhosts', 'path', 'displayName'));
		
		$this->setInputFilter($validators);
	}

    public function init() {
        $vhostsResult = $this->getVhostMapper()->getVhosts();
        $vhostsIds = array();
        $vhostsResult->buffer();
        foreach($vhostsResult as $vhostContainer) {
            $vhostsIds[] = $vhostContainer->getId();
        }
        $vhostNodes = $this->getVhostMapper()->getFullVhostNodes($vhostsIds);
        $vhosts = array();
        foreach ($vhostsResult as $vhost) {
        	/// collect all managed vhosts, filter out the default vhost
            if ($vhost->isManagedByZend() && (! $vhost->isDefault())) {
                foreach ($vhostNodes[$vhost->getId()] as $vhostNodeContainer) {
                    // if not all nodes have the error status of vhost, we attach it to the list
                    if ($vhostNodeContainer->getStatus() != \Vhost\Entity\Vhost::STATUS_ERROR) {
                    	$vhostString = "{$vhost->getName()}:{$vhost->getPort()}";
                    	$vhostScheme = $vhost->isSsl() ? 'https://' : 'http://';
                        $vhosts[$vhostScheme.$vhostString] = $vhostScheme.$vhostString;
                        break;
                    }
                }
            }
        }

        $this->fillWithGeneralDeploymentData($vhosts);
    }
	
	/**
	 * @param Package $package
	 * @return SetInstallation
	 */
	public function fillWithPackageData(Package $package) {
		$this->get('displayName')->setAttribute('value', $package->getName());
		
		return $this;
	}
	
	/**
	 * @param array $virtualHosts
	 * @return SetInstallation
	 */
	public function fillWithGeneralDeploymentData(array $virtualHosts) {
		$options = $this->get('vhosts')->getOptions();
	
		foreach ($virtualHosts as $uri => $vhost) {
			$options['options'][$uri] = $vhost;
		}

		$this->get('vhosts')->setOptions($options);
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Form::isValid()
	*/
	public function setData($data) {
		// As the new vhost is not part of the original drop down,
		// validation will fail unless the new vhost is added
		$newVhost = '';
		if (isset($data[ $this->get('newVhost')->getName() ])) {
			$newVhost = $data[ $this->get('newVhost')->getName() ];
		}

		if ('' != $newVhost) {
			$options = $this->get('vhosts')->getValueOptions() ;
			$options = $options + array($data['vhosts'] => $data['vhosts']);
			$this->get('vhosts')
				->setValueOptions($options)
				->setAttribute('value', $data['vhosts']);
		}
	
		// Normalize path for validation, make sure it has only a single / at the begining
		if (isset($data[ $this->get('path')->getName() ])) {
			$data[ $this->get('path')->getName() ] = '/' . ltrim($data[ $this->get('path')->getName() ], '/');
		}
	
		return parent::setData($data);
	}
	
	public function isValid() {
		$result = parent::isValid();
		if ($result) {
			$this->sessionStorage->setBaseUrlDetails($this->getData());
		}
		return $result;
	}
	
	/**
	 * @param SessionStorage $sessionStorage
	 */
	public function setSessionStorage(SessionStorage $sessionStorage) {
		$this->sessionStorage = $sessionStorage;
	}
	
	/**
	 * @param array $values
	 */
	public function populateValid(array $values) {
		$values = $this->getValidValues($values);
		foreach ($values as $key => $value) {
			if (! empty($value)) {
				$element = $this->get($key);
				if ($element) {
					$element->setAttribute('value', $value);
				}
			}
		}
	
		// Update the drop down with the new vhost
		$newVhost = '';
		if (isset($values[ $this->get('newVhost')->getName() ])) {
			$newVhost = $values[ $this->get('newVhost')->getName() ];
		}
	
		if ('' != $newVhost) {
			$this->get('vhosts')
			->setAttribute('options', $this->get('vhosts')->getAttribute('options') + array($newVhost => $newVhost))
			->setAttribute('value', $newVhost);
		}
	}
	// @todo process should be moved to model/controller
	/**
	 * IMPORTANT: phpDoc and method don't match as strict standards do not allow changing a methods signature
	 * @param ZwasComponents_Deployment_Mapper_Abstract $mapper
	 * @param string $filePath - the path on Zend Server filesystem where the uploaded package file is
	 * @return boolean true on success
	 * @throws Zwas_Exception
	 * @throws ZwasComponents_Deployment_Api_Exception
	 */
	public function process() {
		$model = null;
		$filePath = null;
		if (func_num_args() == 2) {
			$model = func_get_arg(0);
			$filePath = (string)func_get_arg(1);
		}
		
		if (strlen($filePath) == 0) {
			$message = new Text('%s::%s expects a string file path as second parameter', array(__CLASS__, __METHOD__));
			throw new Exception($message);
		}
		if (! ($model instanceof Model)) {
			$message = new Text('%s::%s expects a Deployment\Model as first parameter', array(__CLASS__, __METHOD__));
			throw new Exception($message);
		}
		
		if ('' == $this->get('newVhost')->getValue()) {
			$createVhost = false;
		} else {
			$createVhost = true;
		}
		
		$requestedVhost = $this->get('vhosts')->getValue();
        if (0 < preg_match('#^http://defaultserver(:[[:digit:]]+)?$#', $requestedVhost)) {
        	$defaultPort = $model->getDefaultServerPort();
        	if ($defaultPort != 80) {
	            $requestedVhost = 'http://'.\Deployment\Model::DEFAULT_SERVER.':'. $defaultPort;
        	} else {
	            $requestedVhost = 'http://'.\Deployment\Model::DEFAULT_SERVER;
        	}
            $defaultServer = true;
            $createVhost = false;
            $foundVhost = $this->getVhostMapper()->getDefaultServerVhost();
            $vhostId = $foundVhost->getId();
        } else {
			$uriValidator = new Uri(array('allowAbsolute' => true, 'allowRelative' => false));
        	if (! $uriValidator->isValid($requestedVhost)) {
        		throw new Exception(_t('Virtual host is invalid: %s', array(current($this->getVhostValidator()->getMessages()))));
        	}
        	
        	$requestedUri = UriFactory::factory($requestedVhost);
            $foundVhost = $this->getVhostMapper()->getVhostByName("{$requestedUri->getHost()}:{$requestedUri->getPort()}");
        	if ($foundVhost instanceof VhostEntity) {
	            $vhostId = $foundVhost->getId();
        	} else {
        		$createVhost = true;
        		$vhostId = 0;
        	}
            $defaultServer = false;
        }
		
        // add check to see if the vhost is already exists
        if ($createVhost && (! is_null($foundVhost))) {
       		$createVhost = false;
        }
        
		$requestedPath = ltrim($this->get('path')->getValue(), '/');
		
		$url = "{$requestedVhost}/{$requestedPath}";
		
		$baseUrls = $this->_deploymentModel->getDeployedBaseUrls();
		if (in_array($url, $baseUrls)) {
			$message = new Text('Base URL %s already exists', array($url));
			throw new Exception($message);		
		}
		
		$zendParams = $model->createZendParams(
				$this->get('displayName')->getValue(),
				false,
				$url,
				$createVhost,
				$defaultServer,
                false,
                $vhostId
		);
		
		$model->cancelPendingDeployment($url);
		$model->storePendingDeployment(
				$filePath,
				array(),
				$zendParams
		);
		
		$this->sessionStorage->setBaseUrl($url);
		
		return true;
	}


    /**
     * @param \Vhost\Mapper\Vhost $vhostMapper
     */
    public function setVhostMapper($vhostMapper)
    {
        $this->vhostMapper = $vhostMapper;
    }

    /**
     * @return \Vhost\Mapper\Vhost
     */
    public function getVhostMapper()
    {
        return $this->vhostMapper;
    }
    
	/**
	 * @return VhostValidForDeploy
	 */
	public function getVhostValidator() {
		return $this->vhostValidator;
	}

	/**
	 * @param \Vhost\Validator\VhostValidForDeploy $vhostValidator
	 */
	public function setVhostValidator($vhostValidator) {
		$this->vhostValidator = $vhostValidator;
	}

}

