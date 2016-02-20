<?php
namespace Messages\View\Helper;

use Zend\View\Helper\AbstractHelper,
	ZendServer\Log\Log,
	Application\Module,
	Messages\Db\MessageMapper,
	Messages\MessageContainer;
use Zend\Filter\HtmlEntities;

abstract class ServerErrorMessageAbstract extends AbstractHelper {
	
	const JSON_INFO = 'Info';
	const JSON_WARNING = 'Warning';	
	const JSON_ERROR = 'Error';

	const XML_INFO = 'info';
	const XML_WARNING = 'warning';
	const XML_ERROR = 'error';
	
	protected $contextToString = array(
			0 => 'extension',
			1 => 'directive',
			2 => 'daemon',
			3 => 'monitorRule',
			4 => 'pagecacheRule',
			5 => 'jobqueueRule',
			6 => 'vhost',
	);
	
	abstract protected function createInfoMessage($message);
	abstract protected function createWarningMessage($message);	
	abstract protected function createErrorMessage($message);
	
	protected function getMessageText(MessageContainer $message) {
		try {
			$filter = new HtmlEntities();
			$messageArray = $this->extractMessage($message);
			if(isset($messageArray['message']) && $messageArray['message']) {
				$messageArray['message'] = $filter->filter($messageArray['message']);
			} elseif (is_array($messageArray)) { ///TODO remove special case handling for servers list
				$key = current(array_keys($messageArray));
				$message = $filter->filter(current($messageArray));
				$messageArray[$key] = $message;
			} /// messageArray can also be a string! in which case there's no handling
			return $messageArray;
		} 
		catch (\ZendServer\Exception $e) {
			Log::logException("Error in extracting message data", $e);
			return $this->createErrorMessage("Unknown Error");
		}
	}
	
	protected function extractMessage(MessageContainer $message) {
		if ($message->isInfo()) {
			return $this->createComponentInfoMessage($message);
		}
		
		if ($message->isWarning()) {
			return $this->createComponentWarningMessage($message);
		}	
			
		return $this->createComponentErrorMessage($message);
	}

	protected function createComponentWarningMessage(MessageContainer $message) {
		$messageKey = $message->getMessageKey();
		$messageCode = $message->getMessageType();
		$context = $this->getContextAsString($message);
	
		if ($messageCode === MessageMapper::TYPE_MISSMATCH) {
			$messageDetails = $message->getMessageDetails();
			$expectedValue = $messageDetails[1];
			$actualValue = $messageDetails[2];
			return $this->createWarningMessage(_t("The %s '%s' is mismatched: expected '%s', actual '%s'", array($context, $messageKey,$expectedValue,$actualValue))); // @todo - fetch values
		}

		if ($messageCode === MessageMapper::TYPE_NOT_LICENSED) {
			return $this->createWarningMessage(_t("The %s '%s' is not licensed", array($context, $messageKey)));
		}
		
		if ($messageCode === MessageMapper::TYPE_NOT_LOADED) {
			return $this->createWarningMessage(_t("The %s '%s' is not loaded", array($context,$messageKey)));
		}
		
		throw new \ZendServer\Exception(_t("Unknown Warning message: '%s'", array($messageCode)));
	}
	
	protected function getContextAsString(MessageContainer $message) {
		if (isset($this->contextToString[$message->getMessageContext()])) {
			return $this->contextToString[$message->getMessageContext()];
		}
	
		Log::warn("unknown context passed: " . var_export($message->getMessageContext(), true));
		return '';
	}	
	
	protected function createComponentErrorMessage(MessageContainer $message) {
		$messageKey = $message->getMessageKey();
		$messageCode = $message->getMessageType();
		$context = $this->getContextAsString($message);
		$details = $message->getMessageDetails();
		
		if ($messageCode === MessageMapper::TYPE_MISSING) {
			return $this->createErrorMessage(_t("The %s '%s' is missing", array($context,$messageKey)));
		}	
		
		if ($messageCode === MessageMapper::TYPE_OFFLINE) {
			return $this->createErrorMessage(_t("The %s '%s' is offline", array($context,$messageKey)));
		}

		if ($messageCode === MessageMapper::TYPE_NOT_INSTALLED) {
			return $this->createErrorMessage(_t("The %s '%s' seems to be missing", array($context,$messageKey)));
		}

		if ($messageCode === MessageMapper::TYPE_WEBSERVER_NOT_RESPONDING) {
			return $this->createErrorMessage(_t("Webserver is not responding"));
		}

		if ($messageCode === MessageMapper::TYPE_SCD_STDBY_MODE) {
			return $this->createErrorMessage(_t("Session Clustering daemon is inactive"));
		}
		
		if ($messageCode === MessageMapper::TYPE_VHOST_MODIFIED) {
			return $this->createErrorMessage(_t("Web server configuration files have been modified on this server")); 
		}
		
		if ($messageCode === MessageMapper::TYPE_VHOST_ADDED) {
			if ($details) {
				return $this->createErrorMessage(_t("Failed to create the virtual host '%s' on this server: %s", array($messageKey, current($details)))); 
			}
			return $this->createErrorMessage(_t("Failed to create the virtual host '%s' on this server", array($messageKey)));
		}
		
		if ($messageCode === MessageMapper::TYPE_VHOST_REDEPLOYED) {
			if ($details) {
				return $this->createErrorMessage(_t("Failed to redeploy the virtual host '%s' on this server: %s", array($messageKey, current($details))));
			}
			return $this->createErrorMessage(_t("Failed to redeploy the virtual host '%s' on this server", array($messageKey))); 
		}
		
		throw new \ZendServer\Exception(_t("Unknown Error message: '%s'", array($messageCode)));
	}	
	
	protected function createComponentInfoMessage(MessageContainer $message) {
		if ($message->isExtension()) {
			$message->isExtensionEnabled() ? $action = 'enabled' : $action = 'disabled';
			return $this->createInfoMessage(_t("The extension '%s' will be %s once restart is performed",array($message->getMessageKey(),$action)));
		}		
		
		if ($message->isDirective()) {
			$messageDetails = $message->getMessageDetails();
			if (!isset($messageDetails[2])) {
				throw new \ZendServer\Exception("Directive change message is missing details");
			}
			return $this->createInfoMessage(_t("The directive '%s' value has been changed from '%s' to '%s'",array($messageDetails[0],$messageDetails[1],$messageDetails[2])));
		}	
		
		if ($message->isDaemon()) {
			$messageKey = $this->getView()->daemonName($message->getMessageKey());
			return $this->createInfoMessage(_t("{$messageKey} requires a restart"));
		}

		throw new \ZendServer\Exception(_t("Unknown Info message: '%s'", array($message->getMessageType())));
	}
}