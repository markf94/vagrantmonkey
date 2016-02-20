<?php
namespace Audit\Controller;

use Acl\License\Exception as LicenseException;

use WebAPI\Exception;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use Application\Module;

use ZendServer\Mvc\Controller\WebAPIActionController,
	ZendServer\Set,
	Zend\Mvc\Controller\ActionController,
	ZendServer\Log\Log,
	\Audit\Container,
	WebAPI;
use ZendServer\FS\FS;
use Zend\Http\Response\Stream;
use Zend\Http\Headers;

class WebAPIController extends WebAPIActionController {
	
    /**
     * filters may include:
     *     from
     *     to
     *     auditTypes
     *     freeText
     */
	public function auditGetListAction() {		
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array(
                'limit' => Module::config('list', 'resultsPerPage'),
                'offset' => 0, 
                'order' => 'audit_id', 
                'direction' => 'DESC',
			    'filters' => array(),
            ));
			$limit = $this->validateLimit($params['limit']);
			$offset = $this->validateOffset($params['offset']);
			$order = strtoupper($this->validateOrder($params['order']));
			$direction = $this->validateDirection($params['direction']);
			$filters = $this->validateFilters($params['filters']);
		} catch (\WebAPI\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}	
			
		try {
			$licenseMapper = $this->getLocator()->get('Acl\License\Mapper'); /* @var $licenseMapper \Acl\License\Mapper */
			if (isset($filters['from']) && $filters['from'] > 0) {
				if (!$licenseMapper->isValid($filters['from']) || $filters['from'] > $filters['to']) {
					throw new LicenseException('Not valid time range');
				}
			}else{
			    $filters['from']=$licenseMapper->getMaxAuditFrom();
			    if($filters['from']===false){
			         unset($filters['from']);
			    }
			}
			$auditMessages = $this->getAuditMapper()->findAuditMessagesPaged($limit, $offset, $order, $direction, $filters);
			$totalCount = $this->getAuditMapper()->countAuditMessages($filters);			
		} catch (\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INVALID_SERVER_RESPONSE, $e);
		}
			
		$auditMessages = $this->resolveMessagesOutcome($auditMessages);
		
		return array('auditMessages' => $auditMessages, 'totalCount' => $totalCount);
	}
	
	
	public function auditExportAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array('filters' => array()));
		
		$auditMessages = $this->getAuditMapper()->findAuditMessagesFiltered($params['filters']);
		$auditMessages = $this->resolveMessagesOutcome($auditMessages);
		$viewRenderer = $this->getLocator()->get('ViewRenderer');
		
		$auditLogString = '';
		foreach ($auditMessages as $messageContainer) { /* @var $messageContainer \Audit\Container */
			$extraData = urlencode($messageContainer->getRawExtradata());
			
			$auditLogString .= 
			"{$messageContainer->getAuditId()},{$messageContainer->getUsername()},{$messageContainer->getRequestInterface()},{$messageContainer->getRemoteAddr()},".
			"{$viewRenderer->auditType($messageContainer->getAuditType())},{$messageContainer->getOutcome()},{$messageContainer->getbaseUrl()},{$messageContainer->getCreationTime()},".
			"{$extraData}\r\n";
		}
		
		$archive = \ZendServer\FS\FS::getGuiTempDir() . DIRECTORY_SEPARATOR . 'compressed.zip';
		$filter = new \Zend\Filter\Compress\Zip(array(
				'adapter' => 'Zip',
				'target' => 'audit.csv',
				'options' => array(
						'archive' => $archive,
				),
		));
		
		$filter->compress($auditLogString);
		
		$logFilename = $traceFileName = "auditLog-" . date('dMY-HisO') . ".zip";
		$archive = FS::getFileObject($archive);

		$response = new Stream();
		$response->setStream(fopen($archive->getPathname(), 'r'));
		$response->setStreamName($logFilename);
		$response->setStatusCode(200);
		$response->setContentLength($archive->getSize());
		$this->response = $response;
		
		$this->getEvent()->setParam('do-not-compress', true);
		
		$headers = new Headers();
		$headers->addHeaderLine('Content-Disposition', "attachment; filename=\"{$logFilename}\"");
		$headers->addHeaderLine('Content-type', "application/zip");
		$headers->addHeaderLine('Content-Length', $archive->getSize());
		$response->setHeaders($headers);
		return $response;
	}

	public function auditGetDetailsAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('auditId'));
			$auditId = $this->validateInteger($params['auditId'], 'auditId');
		} catch (\WebAPI\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
			
		try {
			$auditMessages = $this->getAuditMapper()->findAuditMessage($auditId);			
			$auditProgressList = $this->getAuditProgressMapper()->findMessageDetailsErrorOnly($auditId);
		} catch (\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INVALID_SERVER_RESPONSE, $e);
		}
		
		if ($auditMessages->count() < 1) {
			throw new WebAPI\Exception("No Audit message with auditId: '{$auditId}'", WebAPI\Exception::NO_SUCH_AUDIT_MESSAGE);
		}
		
		return array('auditMessage' => $auditMessages->current(), 'auditProgressList' => $auditProgressList);
	}	

	public function auditSetSettingsAction() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array('history' => 30, 'email' => '', 'callbackUrl' => '', 'auditTriggers' => array()));
			$this->validateMandatoryParameters($params, array('history'));
			$history = $this->validateInteger($params['history'], 'history');
			$email = $this->validateString($params['email'], 'email');
			$callbackUrl = $this->validateString($params['callbackUrl'], 'callbackUrl');
			$this->validateArray($params['auditTriggers'], 'auditTriggers');			
		} catch (\WebAPI\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$settingsForm = $this->getLocator('Audit\Forms\Settings');
		$settingsForm->setData($params);
		$nonValidElements = '';
		if (! $settingsForm->isValid()) {
			foreach ($settingsForm->getMessages() as $field => $errors) {
				if (!$errors) continue;
				if (is_array($errors)) {
					foreach ($errors as $type => $error) {
						$nonValidElements .= $field . ': ' . $error;
					}
				}
			}
			$errorMsg = _t("Invalid parameters: " . $nonValidElements);
			throw new \WebAPI\Exception($errorMsg, \WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$audit = $this->auditMessage(Mapper::AUDIT_GUI_AUDIT_SETTINGS_SAVE);
		try {
			$this->getAuditSettingsMapper()->setHistory($history);
			$this->getAuditSettingsMapper()->setEmail($email);
			$this->getAuditSettingsMapper()->setURL($callbackUrl);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INVALID_SERVER_RESPONSE, $e);
		}
		
		$historyData = $this->getAuditSettingsMapper()->getHistory();
		$emailData = $this->getAuditSettingsMapper()->getEmail();
		$scriptUrlData = $this->getAuditSettingsMapper()->getScriptUrl();
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);

		return array(	'history' => $historyData,
						'email' => $emailData,
						'url' => $scriptUrlData);
	}

	protected function resolveMessagesOutcome($auditMessages) {
		$messagesWithOutCome = array();
		foreach ($auditMessages as $idx=>$auditMessage) { /* @var $auditMessage \Audit\Container */
			$auditMessage->setOutcome($this->resolveOutcome($auditMessage));
			$messagesWithOutCome[] = $auditMessage;
		}
	
		return new Set($messagesWithOutCome, null);
	}
	
	protected function resolveOutcome(\Audit\Container $auditMessage) {
		$progressValues = array_count_values($auditMessage->getProgress());
	
		if (!isset($progressValues['AUDIT_PROGRESS_ENDED_SUCCESFULLY']) && !isset($progressValues['AUDIT_PROGRESS_ENDED_FAILED'])) {
			return 'In Progress';
		}
	
		if (!isset($progressValues['AUDIT_PROGRESS_ENDED_FAILED'])) {
			return 'OK';
		} else {
			return 'Failed';
		}
	
		return 'Failed';
	}
	
	/**
	 * @param string $order
	 * @throws WebAPI\Exception
	 */
	protected function validateOrder($order) {
		return $this->validateAllowedValues($order, 'order', array('audit_id', 'creation_time', 'username'));
	}

	protected function validateFilters(array $filters = array()) {
	    $availableFilters = array ('from', 'to', 'freeText', 'auditGroups','outcome');
	    foreach ($filters as $key => $value) {
	        $this->validateAllowedValues($key, 'filters', $availableFilters);
	    }
	    return $filters;
	}
}
