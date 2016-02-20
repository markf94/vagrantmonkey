<?php

namespace Bootstrap\Controller;

use Users\Forms\ChangePassword;

use Servers\Configuration\Mapper as ServersConfigurationMapper;

use ZendServer\Configuration\Manager;

use Users\Identity;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use Bootstrap\Model;

use Zend\Form\Factory;

use ZendServer\FS\FS;

use Zend\Json\Json;

use ZendServer\Log\Log;

use ZendServer\Exception;

use Zend\View\Model\JsonModel;

use Zend\Form\Element\Text;

use Zend\Validator\Identical;

use Zend\Form\Element\Password;

use Zend\Form\Element\Checkbox;

use Zend\Form\Element\Textarea;

use Zend\Form\Form;

use Application\Module;

use Zend\View\Model\ViewModel;

use ZendServer\Mvc\Controller\ActionController;

use ZendServer\Ini\IniWriter;

use Servers\Forms\JoinCluster;

class BootstrapController extends ActionController
{
	
    public function createDatabaseAction() {
    	
    	$locator = $this->getLocator();
    	
    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	$viewModel->success = true;
    	
    	$params = $this->getRequest()->getPost();
    	$schema = $params['location']['name'];
    	$host = $params['location']['host'];
    	$port = $params['location']['port'];
    	$user = $params['credentials']['username'];
    	$pass = $params['credentials']['password'];
    	
    	if (!$schema || !$host || !$port) {
    		$viewModel->success = false;
    		$viewModel->message = _t('Define database host, port and name');
    		return $this->getResponse()->setContent(Json::encode($viewModel->getVariables()->getArrayCopy()));
    	}
    	
    	try {
	    	$dbCreator = new \Application\Db\Creator("mysql:host={$host};port={$port}", $user, $pass, $schema);
    		Log::info("Connected correctly to {$host}:{$port}");

	    	if ($dbCreator->zendUserExists()) {
	    		Log::info('Zend User already exists but we cannot retrieve the password');
	    		/// user exists, use the given user
	    		$userCreds = $params['credentials'];
	    	} else {
	    		Log::info('Zend User does not exist create the user \'zend\'');
	    		$userCreds = $dbCreator->createZendUser();
	    	}
	    	
	    	
	    	$viewModel->credentials = $userCreds;
	    	
	    	if (! $dbCreator->schemaExists()) {
	    		Log::debug("schema {$schema} does not seem to exist - will create it");
	    		$dbCreator->createSchema();
	    	} else {
	    		Log::debug("schema {$schema} seems to exist");
	    	}
	    	
	    	$dbCreator->grantPermissions($schema, $userCreds['username']);
	    	$this->writeDbDirectives($schema, $host, $port, $userCreds);    	
    	} catch (\Exception $e) {
    		Log::err("schema creation failed with the following error: " . $e->getMessage());
    		
    		$viewModel->success = false;
    		$viewModel->message = $e->getMessage();
    		$viewModel->code = $e->getCode();
    		
    		if (isset($dbCreator) && $dbCreator instanceof \Application\Db\Creator) {
	    		$dbCreator->cleanUpZend($schema);
    		}    		
    	}
    	
    	if ($viewModel->success === true) {
    		$this->auditMessage()->setIdentity(new Identity(_t('Unknown')));
    		$this->auditMessage(Mapper::AUDIT_GUI_BOOTSTRAP_CREATEDB,ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(
    				'type' => 'MYSQL',
    				'schema' => $schema,
    				'host' => $host,
    				'port' => $port,
    				'user' => $userCreds['username'],
    		));    		
    	}
    	
    	return $this->getResponse()->setContent(Json::encode($viewModel->getVariables()->getArrayCopy()));
    }
    
    public function saveAction() {
    	set_time_limit(120); /// Arbitrarily extend time limit for save action in case of locks or slow response
    	
    	/* @var $response \Zend\Http\PhpEnvironment\Response */
    	$response = $this->getResponse();
    	$defaultHeaders = clone $response->getHeaders();
    	
    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	$viewModel->response = array('success' => true);
    	$params = $this->getRequest()->getPost();
    	
    	if (! isset($params['singleServerFlag']) || ! isset($params['firstServerFlag'])) {
    		//missing js variables we create --> should NEVER get here unless user tried something fishy.
    		throw new Exception(_t('There was an error processing your new server please try again.'));

    	}
    	$singleServerFlag = ($params['singleServerFlag'] == 'true');
    	$firstServerFlag = ($params['firstServerFlag'] == 'true');
    	
    	if($firstServerFlag || $singleServerFlag){ //dont check license and admin pass when joining exisiting cluster
    		$this->auditMessage()->setIdentity(new Identity(_t('Unknown')));
    		$audit = $this->auditMessage(Mapper::AUDIT_GUI_BOOTSTRAP_SAVELICENSE);
    		try {
    			$defaultEmail = (isset($params['administrator']['defaultEmail'])) ? $params['administrator']['defaultEmail'] : '';
    			$defaultServer = (isset($params['administrator']['defaultServer'])) ? $params['administrator']['defaultServer'] : '';
    			$developerPassword = $params['developer']['password'] ? $params['developer']['password'] : ''; // if no password passed, we will generate a random one
    			
	    		$bootstrapModel = $this->getLocator('Bootstrap\Mapper'); /* @var $bootstrapModel \Bootstrap\Mapper */
	    		$bootstrapModel->setAdminPassword($params['administrator']['password']);
	    		$bootstrapModel->setApplicationUrl($defaultServer);
	    		$bootstrapModel->setAdminEmail($defaultEmail);
	    		$bootstrapModel->setDeveloperPassword($developerPassword);
	    		
	    		$bootstrapModel->setProduction(Module::isCluster() ? 'cluster' : $params['mode']);
	    		
	    		$bootstrapModel->bootstrapSingleServer();

    			if (!$this->Authentication()->authenticate('admin', $params['administrator']['password'])){
    				throw new Exception("Authentication with new saved credentials failed");
    			}
				
				\Application\Module::generateCSRF();
				
    		} catch (\Exception $e) {
    			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
    			Log::err("bootstrap failed with the following error: " . $e->getMessage());
    			Log::debug($e);
    			$viewModel->response = array('success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTrace());
    		}
    		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
    	}

    	try { 
    		if (0 < $this->getServersMapper()->countAllServers()) {
    			// if we have any servers, push using storeDirectives so that all synchronisation takes place
    			$this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'restartPhp')); /* @var $bootstrapView \Zend\View\Model\ViewModel */
    		}
    	} catch (\Exception $ex) {
    		/// nothing to be done really...
    	}

    	$response->setHeaders($defaultHeaders);

    	$this->setLibrariesUpdateCookie();
    	$this->setPluginsUpdateCookie();
    	
    	$response->setContent(Json::encode($viewModel->response));    	
    	$this->clearUpgradeFlag();
    	return $response;
    }
    
    public function renderSummaryAction() {
    	$params = $this->getRequest()->getPost();

    	/**
		 * @link ZSRV-7821
		 * Fix for IE due to placeholder passed in submit
    	 */
    	$administrator = $params['administrator'];
    	
    	$upgrade = $this->isUpgrade();
    	
    	$isCloud = $this->getZemUtilsWrapper()->getLicenseInfo()->isCloudLicense();

    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	$viewModel->administrator = $administrator;
    	$viewModel->defaultServer = (isset($params['defaultServer'])) ? $params['defaultServer'] : '';
    	$viewModel->defaultEmail = $params['defaultEmail'];
    	$viewModel->developer = $params['developer'];
    	$viewModel->singleServerFlag = $params['singleServerFlag'];
    	$viewModel->firstServerFlag = $params['firstServerFlag'];
    	$viewModel->profile = $params['profile'];
    	$viewModel->upgrade = $upgrade;
    	$viewModel->isCloud = $isCloud;
    	
    	return $viewModel;
    }
	
	public function indexAction() {
		$this->Layout('layout/login.phtml');
		$baseUrl = Module::config('baseUrl');
		
		/// if users are already set, skip bootstrap
		$bootstrapModel = $this->getLocator('Bootstrap\Mapper'); /* @var $bootstrapModel \Bootstrap\Mapper */
		if (! $bootstrapModel->isBootstrapNeeded()) {
			$bootstrapModel->setBootStrapCompleted();
			$bootstrapModel->setServerUniqueId();
			$bootstrapModel->setServerTimezone();
			$this->getTasksMapper()->waitForTasksComplete();
			Log::debug('Bootstrap is not needed, users are already set');
			return $this->redirect()->toRoute('home');
		}
		
		$formFactory = new Factory();
		
		$useEula = Module::config('bootstrap', 'zend_gui', 'requireEula');
		$eulaForm = null;
		if ($useEula) {
			try {
				$eulaFile = FS::getFileObject(FS::createPath(getCfgVar('zend.install_dir'), 'doc', 'EULA.txt'))->readAll();
				/// Replace strings that end with a single newline with the same string that end with a space.
				/// In other words, replace the trailing *single* newline with a space character.
				/// Do NOT replace the newline if it is immediately followed by another newline.
				$eulaFile = preg_replace("#([^\\n]+)\\n(?!\\n)#m", "$1 ", $eulaFile);
			} catch (\Exception $e) {
				$eulaFile = '';
				Log::notice("Could not retrieve Eula content for display: {$e->getMessage()}");
			}
			
			$eulaForm = $formFactory->createForm(array(
					'attributes' => array('id' => 'bootstrap-eula'),
					'fieldsets' => array(
						array(
							'spec' => array(
								'attributes' => array(
									'name' => 'eula',
								),
								'elements' => array(
									array(
										'spec' => array(
											'name' => 'eulaContent',
											'type' => 'Zend\Form\Element\Textarea',
											'attributes' => array(
												'rows' => 20,
												'readonly' => 'readonly',
												'class' => 'eula',
												'value' => $eulaFile
									))),
									array(
										'spec' => array(
											'name' => 'acceptTerms',
											'type' => 'Zend\Form\Element\Checkbox',
											'options' => array(
												'checked_value' => 1,
												'label' => _t('I have read and agree to the license agreement')
											),
											'attributes' => array(
												'id' => 'accept-terms'
									))))
								)
							)
						)
					));
		}
			
		$passwordForm = $formFactory->createForm(array(
				'attributes' => array('id' => 'bootstrap-password'),
				'fieldsets' => array(
					array(
						'spec' => array(
							'attributes' => array(
								'name' => 'administrator',
							),
							'elements' => array(
								array(
									'spec' => array(
										'name' => 'password',
										'options' => array(
												'label' => _t('Password'),
										),
										'type' => 'Zend\Form\Element\Password',
										'attributes' => array(
											'id' => 'administrator-password',
										)),
								),
								array(
									'spec' => array(
										'name' => 'password-confirm',
										'options' => array(
												'label' => _t('Confirm Password'),
										),
										'type' => 'Zend\Form\Element\Password',
										'attributes' => array(
											'id' => 'administrator-passwordconfirm',
										)),
								),
								array(
									'spec' => array(
										'name' => 'defaultServer',
										'options' => array(
												'label' => _t('Default Application URL'),
										),
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
												'id' => 'default-server',
												'placeholder' => _t('<default>')
										)),
								),
								array(
									'spec' => array(
										'name' => 'defaultEmail',
										'options' => array(
												'label' => _t('Default Email'),
										),
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
												'id' => 'default-email',
										)),
								)),								
							)
						),
						array(
								'spec' => array(
										'attributes' => array(
												'name' => 'developer',
										),
										'elements' => array(
												array(
														'spec' => array(
																'name' => 'password',
																'options' => array(
																		'label' => _t('Password'),
																),
																'type' => 'Zend\Form\Element\Password',
																'attributes' => array(
																		'id' => 'developer-password',
																)),
												),
												array(
														'spec' => array(
																'name' => 'password-confirm',
																'options' => array(
																		'label' => _t('Confirm Password'),
																),
																'type' => 'Zend\Form\Element\Password',
																'attributes' => array(
																		'id' => 'developer-passwordconfirm',
																)),
												)),
								)
						)
					)
				));
		
		
		list($licenseType, $licenseExpiryDate, $licenseEvaluation, $licenseNeverExpires) = $this->getLicenseDetails();// get the license details
		
		$config = $this->getLocator()->get('Configuration');
		
		
		$viewModel = new ViewModel(array(
				'joinCluster' => new JoinCluster(),
				'eulaForm' => $eulaForm, 
				'passwordForm' => $passwordForm, 
				'build' => Module::config('package', 'build'), //get the current build of server
				'version' => Module::config('package', 'version'), //get the current version of server
				'licenseType' => $licenseType,
		        'licenseNeverExpires' => $licenseNeverExpires,
				'licenseExpiryDate' => $licenseExpiryDate,
				'licenseEvaluation' => $licenseEvaluation,
				'profile' => @$config['package']['zend_gui']['serverProfile']?:'',
				'authenticationSource' => Module::config('authentication', 'simple') ? 'simple' : 'extended'
			));
		
		if (Module::isClusterManager()) {
			$dbCredentialsForm = $formFactory->createForm(array(
				'attributes' => array('id' => 'bootstrap-database'),
				'fieldsets' => array(
					array(
						'spec' => array(
							'attributes' => array(
								'name' => 'location',
							),
							'elements' => array(
								array(
									'spec' => array(
										'name' => 'host',
										'options' => array(
												'label' => _t('Database Host'),
										),
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
											'id' => 'location-host',
										)),
								),
								array(
									'spec' => array(
										'name' => 'port',
										'options' => array(
												'label' => _t('Database Port'),
										),
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
											'id' => 'location-port',
										)),
								),
								array(
									'spec' => array(
										'name' => 'name',
										'options' => array(
												'label' => _t('Database Name'),
										),
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
											'id' => 'location-name',
										)),
								),
							)
						)
					),
					array(
						'spec' => array(
							'attributes' => array(
								'name' => 'credentials',
							),
							'elements' => array(
								array(
									'spec' => array(
										'name' => 'username',
										'options' => array(
												'label' => _t('Username'),
										),
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
											'id' => 'credentials-username',
										)),
								),
								array(
									'spec' => array(
										'name' => 'password',
										'options' => array(
												'label' => _t('Password'),
										),
										'type' => 'Zend\Form\Element\Password',
										'attributes' => array(
											'id' => 'credentials-password',
										)),
								),
							)
						)
					)
				)
			));
			
			$viewModel->setVariable('dbForm', $dbCredentialsForm);
			$viewModel->setVariable('edition', 'cm');
		} else {
			$viewModel->setVariable('dbForm', true);
			$viewModel->setVariable('edition', 'zs');
		}
		$viewModel->setVariable('isUpgrade', $this->isUpgrade());
		
		$mapper = new ServersConfigurationMapper();
		/// allow cluster support iff OS supports cluster and license is not expired
		$viewModel->setVariable('isClusterSupport', $mapper->isClusterSupport() && (!$this->getZemUtilsWrapper()->getLicenseInfo()->isLicenseExpired()));
		$manager = new Manager();
		$viewModel->setVariable('osType', $manager->getOsType());
		$viewModel->setVariable('isCluster', Module::isCluster());
		$viewModel->setVariable('useEula', $useEula);
		$viewModel->setVariable('passwordMaximumLength', Module::config('user', 'passwordLengthMax'));
		$viewModel->setVariable('passwordMinimumLength', Module::config('user', 'passwordLengthMin'));
		return $viewModel;
	}
	
	
	protected function getLicenseDetails() {
		$utilsWrapper = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper');		
		return array($utilsWrapper->getLicenseType(), $utilsWrapper->getLicenseFormattedExpiryDate(), $utilsWrapper->getLicenseEvaluation(), $utilsWrapper->getLicenseInfo()->isNeverExpires());
	}
	
	protected function writeDbDirectives($schema, $host, $port, $userCreds) {
		$iniWriter = new iniWriter();
		
		$iniFile = FS::createPath(getCfgVar('zend.conf_dir'), 'zend_database.ini'); // store user in zend_database.ini
		$iniWriter->updateZendDirectives($iniFile, array(
				'zend.database.type' => 'MYSQL',
				'zend.database.name' => $schema,
				'zend.database.host_name' => $host,
				'zend.database.port' => $port,
				'zend.database.user' => $userCreds['username'],
				'zend.database.password' => $userCreds['password'],
		));	
	}

	/**
	 * @return boolean
	 */
	private function clearUpgradeFlag() {
		if ($this->isUpgrade()) {
			$this->getGuiConfigurationMapper()->setGuiDirectives(array('zs_upgrade' => 0));
		}
		
		return true;
	}
	
	/**
	 * @return boolean
	 */
	private function isUpgrade() {
		return (boolean)Module::config('package', 'zs_upgrade');
	}
	
}
