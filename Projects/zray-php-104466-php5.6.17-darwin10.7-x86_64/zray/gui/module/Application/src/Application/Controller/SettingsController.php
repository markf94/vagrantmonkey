<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	Application\Forms\Settings,
	ZendServer\Configuration\Ui\Directives\Container,
	Audit\Db\ProgressMapper,
	Audit\Db\Mapper as auditMapper,
	Zsd\Db\TasksMapper,
	ZendServer\Configuration\Manager,
	ZendServer\Log\Log,
	\Servers\Configuration\Mapper as serversMapper;
use ZendServer\FS\FS;
use ZendServer\Exception;

class SettingsController extends ActionController
{
    public function indexAction() {
    	// Get the restart strategy settings
    	$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
    	$restartStrategy = $directivesMapper->selectSpecificDirectives(array('zend_server_daemon.restart_strategy'));
    	$restartStrategyValue = $directivesMapper->selectSpecificDirectives(array('zend_server_daemon.restart_strategy_value'));
    	$guiHostName = $directivesMapper->selectSpecificDirectives(array('zend_monitor.gui_host_name'));
    	$webserverMessagesLevel = $directivesMapper->getDirective('zend_server_daemon.webserver.error_reporting_level');
    	
    	$generalSettingsForm = new \Application\Forms\Settings\General(array('guiHostName' => $guiHostName->current()));

    	$notificationAction = $this->getNotificationsActionMapper()->getNotification('restartRequired'); // get email of the restartRequired type as representative
    	$notificationAction = $notificationAction[0];
    	$notificationCenterForm = new \Application\Forms\Settings\NotificationCenter(array('notificationAction' => $notificationAction));
    	
    	$auditEmail = $this->getAuditSettingsMapper()->getEmail();
    	$auditScriptUrl = $this->getAuditSettingsMapper()->getScriptUrl();
    	$auditForm = new \Application\Forms\Settings\Audit(array('auditEmail' => $auditEmail, 'auditScriptUrl' => $auditScriptUrl));
    	
    	if (! $this->isAclAllowed('data:useCustomAction')) {
    		$notificationCenterForm->disableFormElement($notificationCenterForm->get('notificationsCustomAction'));
    		$options = $notificationCenterForm->get('notificationsCustomAction')->setAttribute('description', _t('This option is not available in your Zend Server edition.')); 
    		$auditForm->disableFormElement($auditForm->get('auditCustomAction'));
    		$options = $auditForm->get('auditCustomAction')->setAttribute('description', _t('This option is not available in your Zend Server edition.')); 
    	}
    	
    	$mailForm = new \Application\Forms\Settings\Mail();
    	
    	$allowToEditSettings = $this->isAclAllowed('data:editSettings');
    	$allowToEditRestart = $this->isAclAllowed('data:editRestartSettings');
    	
    	if (! $allowToEditSettings) {
	    	$mailForm->disableForm();
	    	$this->setDisabledByEditionMessage($mailForm);
    	}
    	
    	if (! $allowToEditSettings) {
    		$auditForm->disableForm();
    		$this->setDisabledByEditionMessage($auditForm);
    	} elseif (! $this->isAclAllowed('data:editAuditSettings')) {
    		$auditForm->disableFormElement($auditForm->get('auditEmail'));
    		$auditForm->disableFormElement($auditForm->get('auditCustomAction'));
    		$this->setDisabledByEditionMessage($auditForm);
    	}
    	
    	if (! $allowToEditSettings) {
    		$notificationCenterForm->disableForm();
    		$this->setDisabledByEditionMessage($notificationCenterForm);
    	} elseif (! $this->isAclAllowed('data:editNotificationsSettings')) {
    		$notificationCenterForm->disableFormElement($notificationCenterForm->get('notificationsCustomAction'));
    		$this->setDisabledByEditionMessage($notificationCenterForm);
    	}
    	
    	$serversMapper = new serversMapper();
    	
    	$licenseType = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseType();
    	$licenseType = ucfirst(strtolower($licenseType));
    	
    	$manager = new Manager();

    	$gracefulRestartAvailableOs = FS::isLlinux();
    	$gracefulRestartAvailableWebServer = $this->getDirectivesMapper()->getDirectiveValue('zend.webserver_type') == 'apache';
    	$gracefulRestart = $this->getDirectivesMapper()->getDirectiveValue('zend_utils.use_graceful_restart');
    	if ($gracefulRestart == '') {
	    	$gracefulRestart = $this->getDirectivesMapper()->getDirectiveMemoryValue('zend_utils.use_graceful_restart');
    	}
    	
		return array('pageTitle' => 'Settings',
					 'pageTitleDesc' => '',  /* Daniel */
					'allowToEditRestart' => $allowToEditRestart,
					 'allowToEditSettings' => $allowToEditSettings,
				 	 'mailForm' => $mailForm, 
					 'auditForm' => $auditForm, 
					 'notificationCenterForm' => $notificationCenterForm, 
					 'generalSettingsForm' => $generalSettingsForm, 
		             'serverMode' => Module::config('package', 'zend_gui', 'serverProfile'),
					 'restartStrategy' => ($manager->getOsType() == Manager::OS_TYPE_IBMI) ? false : $restartStrategy->current(), 
					 'restartStrategyValue' => $restartStrategyValue->current(),
					 'webserverMessagesLevel' => $webserverMessagesLevel,
					 'isClusterSupport' => $serversMapper->isClusterSupport(),
					 'licenseType' => $licenseType,
					 'gracefulRestartAvailableOs' => $gracefulRestartAvailableOs,
					 'gracefulRestartAvailableWebServer' => $gracefulRestartAvailableWebServer,
					 'gracefulRestart' => $gracefulRestart,
					 'isCluster' => Module::isCluster(),
		);
	}
	
	
	public function generalAction() {
		$result = array('success' => true);
		
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
		$guiHostName = $directivesMapper->selectSpecificDirectives(array('zend_monitor.gui_host_name'));
		
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		if ($request->isPost()) {
			$form = new \Application\Forms\Settings\General(array('guiHostName' => $guiHostName->current()));
			$originalForm = clone $form;
			
			$params = $this->getParameters();
			$form->setData($params);
			if ($form->isValid()){
				$parameters = $params->toArray();
				$directivesToSave = array();
				$externalDirectivesToSave = array();
				foreach ($parameters as $key => $parameter) {
					$formValue = $originalForm->get($key)->getValue();
					if ($formValue != $parameter) {
						$section = $originalForm->get($key)->getAttribute('section');
						if (empty($section)) { // no section - go to external directives
							$externalDirectivesToSave[$key] = $parameter;
						} else {
							$directivesToSave[$key] = $parameter;
						}
					}
				}
				
				if (count($directivesToSave) > 0) {
					if (isset($directivesToSave['defaultServer']) && !$directivesToSave['defaultServer']) { // was set, but empty - server to <default-server>
						$directivesToSave['defaultServer'] = '<default-server>';
					}
					try { // TODO - use special audit type rather than AUDIT_DIRECTIVES_MODIFIED
						$extraData = $this->getLocator()->get('Configuration\Audit\ExtraData\DirectivesParser');
						$extraData->setExtraData($directivesToSave);
						
						$auditMessage = $this->auditMessage(auditMapper::AUDIT_DIRECTIVES_MODIFIED,	ProgressMapper::AUDIT_PROGRESS_REQUESTED, $extraData); /* @var $auditMessage \Audit\Container */
						$this->getGuiConfigurationMapper()->setGuiDirectives($directivesToSave);
						$result = array('success' => true);
					} catch (\Exception $e) {
						$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
						Log::err("Set UI directives failed: " . $e->getMessage());
						$result = array('success' => false, 'error' => $e->getMessage());
					}
				} else {
					$result = array('success' => true);
				}
				
				if (count($externalDirectivesToSave) > 0) {
					if (isset($externalDirectivesToSave['externalUrl'])) {
						// update the directive
						try { // TODO - use special audit type rather than AUDIT_DIRECTIVES_MODIFIED
							$extraData = $this->getLocator()->get('Configuration\Audit\ExtraData\DirectivesParser');
							$extraData->setExtraData($externalDirectivesToSave);
							
							$auditMessage = $this->auditMessage(auditMapper::AUDIT_DIRECTIVES_MODIFIED,	ProgressMapper::AUDIT_PROGRESS_REQUESTED, $extraData); /* @var $auditMessage \Audit\Container */
							$directivesMapper->setDirectives(array('zend_monitor.gui_host_name' => $externalDirectivesToSave['externalUrl']));
						} catch (\Exception $e) {
							$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
							Log::err("Set directives failed: " . $e->getMessage());
							$result = array('success' => false, 'error' => $e->getMessage());
						}
							
						// call reload configuration ZSRV-9601
						try { // TODO - use special audit type rather than AUDIT_DIRECTIVES_MODIFIED
							$serversIds = $this->getServersIds();
							$this->getTasksMapper()->insertTasksServers($serversIds, TasksMapper::COMMAND_RELOAD_CONFIGURATION);
						} catch (\Exception $e) {
							$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
							Log::err("Reload configuration failed: " . $e->getMessage());
							$result = array('success' => false, 'error' => $e->getMessage());
						}
					}
					
					$result = array('success' => true);
				}
			} else {
				$error = $this->getFormErrorMessage($form);
				$result = array('success' => false, 'error' => $error);
			}			
		} else {
			$result = array('success' => false, 'error' => 'Must be post request');
		}
		
		
		$viewModel = new ViewModel();
		$viewModel->setTerminal (true);
		$viewModel->setTemplate('application/settings/settings-response');
		$viewModel->setVariable('result', $result);
		
		return $viewModel;
	}
	
	public function mailAction() {
		$result = array('success' => true);
	
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		if ($request->isPost()) {
			$form = new \Application\Forms\Settings\Mail();
			$originalForm = clone $form;
				
			$params = $this->getParameters();
			$form->setData($params);
			if ($form->isValid()){
				$parameters = $params->toArray();
				$directivesToSave = array();
				foreach ($parameters as $key => $parameter) {
					$formValue = $originalForm->get($key)->getValue();
					if ($formValue != $parameter) {
						$directivesToSave[$key] = $parameter;
					}
				}
	
				$directivesToSaveAudit = $directivesToSave;
				/// scrub the mail password from the audit entry, if more entries need to be scrubbed, look into creating a generalized solution in auditMessage
				if (isset($directivesToSaveAudit['mail_password'])) {
					unset($directivesToSaveAudit['mail_password']);
				}
				
				$extraData = $this->getLocator()->get('Configuration\Audit\ExtraData\DirectivesParser');
				$extraData->setExtraData($directivesToSaveAudit);
				
				$auditMessage = $this->auditMessage(auditMapper::AUDIT_DIRECTIVES_MODIFIED,	ProgressMapper::AUDIT_PROGRESS_REQUESTED,  array($directivesToSaveAudit)); /* @var $auditMessage \Audit\Container */
				if (count($directivesToSave) > 0) {
					try { // TODO - use special audit type rather than AUDIT_DIRECTIVES_MODIFIED
						$this->getGuiConfigurationMapper()->setGuiDirectives($directivesToSave);
						$result = array('success' => true);
					} catch (\Exception $e) {
						$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
						Log::err("Set UI directives failed: " . $e->getMessage());
						$result = array('success' => false, 'error' => $e->getMessage());
					}
				} else {
					$result = array('success' => true);
					$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
				}
			} else {
				$error = $this->getFormErrorMessage($form);
				$result = array('success' => false, 'error' => $error);
			}
		} else {
			$result = array('success' => false, 'error' => 'Must be post request');
		}
	
		$viewModel = new ViewModel();
		$viewModel->setTerminal (true);
		$viewModel->setTemplate('application/settings/settings-response');
		$viewModel->setVariable('result', $result);
	
		return $viewModel;
	}
	
	public function notificationCenterAction() {
		$result = array('success' => true);
		
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		if ($request->isPost()) {
			$notificationAction = $this->getNotificationsActionMapper()->getNotification('restartRequired'); // get email of the restartRequired type as representative
			$notificationAction = $notificationAction[0];
			
			$form = new \Application\Forms\Settings\NotificationCenter(array('notificationAction' => $notificationAction));
			$originalForm = clone $form;
				
			$params = $this->getParameters();
			$form->setData($params);
			if ($form->isValid()){
				$parameters = $params->toArray();
				$directivesToSave = array();				
				foreach ($parameters as $key => $parameter) {
					$formValue = $originalForm->get($key)->getValue();
					if ($formValue != $parameter) {
						$directivesToSave[$key] = $parameter;
					}
				}
				
				if (count($directivesToSave) > 0) {

					$extraData = $this->getLocator()->get('Configuration\Audit\ExtraData\DirectivesParser');
					$extraData->setExtraData($directivesToSave);
					
					$auditMessage = $this->auditMessage(auditMapper::AUDIT_DIRECTIVES_MODIFIED,	ProgressMapper::AUDIT_PROGRESS_REQUESTED,  $extraData); /* @var $auditMessage \Audit\Container */
					if (isset($directivesToSave['notificationsEmail'])) {
						$this->getNotificationsActionMapper()->updateTypesEmail($directivesToSave['notificationsEmail']);
					}
					if (isset($directivesToSave['notificationsCustomAction'])) {
						$this->getNotificationsActionMapper()->updateTypesCustomAction($directivesToSave['notificationsCustomAction']);
					}
					$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($directivesToSave));
				}
				
				$result = array('success' => true);
			} else {
				$error = $this->getFormErrorMessage($form);
				$result = array('success' => false, 'error' => $error);
			}
		} else {
			$result = array('success' => false, 'error' => 'Must be post request');
		}
	
		$viewModel = new ViewModel();
		$viewModel->setTerminal (true);
		$viewModel->setTemplate('application/settings/settings-response');
		$viewModel->setVariable('result', $result);
		
		return $viewModel;
	}
	
	public function auditAction() {
		$result = array('success' => true);
	
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		if ($request->isPost()) {
			$auditEmail = $this->getAuditSettingsMapper()->getEmail();
    		$auditScriptUrl = $this->getAuditSettingsMapper()->getScriptUrl();
    		$form = new \Application\Forms\Settings\Audit(array('auditEmail' => $auditEmail, 'auditScriptUrl' => $auditScriptUrl));
    	
			$originalForm = clone $form;
	
			$params = $this->getParameters();
			$form->setData($params);
			if ($form->isValid()){
				$parameters = $params->toArray();
				$directivesToSave = array();
				foreach ($parameters as $key => $parameter) {
					$formValue = $originalForm->get($key)->getValue();
					if ($formValue != $parameter) {
                        $directivesToSave[$key] = $parameter;
					    switch($key){
					        case 'auditEmail':
					            $key = _t('Audit Email');
					            break;
					        case 'auditCustomAction':
					            $key = _t('Audit Callback URL');
					            break;
					    }
						$directivesToSaveAudit[$key] = $parameter;
					}
				}
	
				if (count($directivesToSave) > 0) {
					$extraData = $this->getLocator()->get('Configuration\Audit\ExtraData\DirectivesParser');
					$extraData->setExtraData($directivesToSave);
						
					$auditMessage = $this->auditMessage(auditMapper::AUDIT_DIRECTIVES_MODIFIED,	ProgressMapper::AUDIT_PROGRESS_REQUESTED,  array($directivesToSaveAudit)); /* @var $auditMessage \Audit\Container */
					
					if (isset($directivesToSave['auditEmail'])) {
						$this->getAuditSettingsMapper()->setEmail($directivesToSave['auditEmail']);
					}
					if (isset($directivesToSave['auditCustomAction'])) {
						$this->getAuditSettingsMapper()->setURL($directivesToSave['auditCustomAction']);
					}
					$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($directivesToSave));
				}
	
				$result = array('success' => true);
			} else {
				$error = $this->getFormErrorMessage($form);
				$result = array('success' => false, 'error' => $error);
			}
		} else {
			$result = array('success' => false, 'error' => 'Must be post request');
		}
	
		$viewModel = new ViewModel();
		$viewModel->setTerminal (true);
		$viewModel->setTemplate('application/settings/settings-response');
		$viewModel->setVariable('result', $result);
	
		return $viewModel;
	}
	
	/**
	 * @param Form\Form $form
	 * @return string
	 */
	private function getFormErrorMessage($form) {
		$errors = array();
		foreach ($form->getMessages() as $key => $errorMessages) {
			if (!$errorMessages) continue;
			foreach ($errorMessages as $errorMessage) {
				return $form->get($key)->getLabel() . ': ' . $errorMessage;
			}
		}
	
		return '';
	}
	
	private function setDisabledByEditionMessage(\Zend\Form\Form $form) {
		// check if all elements in the form are disabled
		$allDisabled = true;
		foreach ($form->getElements() as $element) { /* @var $element Element */
			if ($element->getAttribute('type') != 'submit' && ! $element->hasAttribute('disabled')) {
				$allDisabled = false;
			}
		}
		 
		$licenseType = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseType();
		$licenseType = ucwords(strtolower($licenseType));
		 
		// all form elements are disabled - show general message
		if ($allDisabled) {
			// add global message
			$description = '';
			if ($form->hasAttribute('description')) {
				$description = $form->getAttribute('description');
			}
			$description .= '<div class="restart-server-note global-restart-server-note">Not available in this edition</div>';
			$form->setAttribute('description', $description);
	
			// remove the submit button
			foreach ($form->getElements() as $element) { /* @var $element Element */
				if ($element->getAttribute('type') == 'submit') {
					$form->remove($element->getName());
				}
			}
		} else { // not all form element disabled - show specific element message
			foreach ($form->getElements() as $element) { /* @var $element Element */
				if ($element->hasAttribute('disabled')) {
					$element->setAttribute('description', '<div class="restart-server-note">Not available in this edition</div>');
				}
			}
		}
		 
		return $form;
	}
	
	/**
	 * @return array
	 */
	private function getServersIds() {	
		$servers = $this->getServersMapper()->findAllServers();
		return array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());
	}
}