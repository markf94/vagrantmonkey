<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yonni
 * Date: 5/21/13
 * Time: 11:49 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Zsd\View\Helper;


use Messages\Db\MessageMapper;
use Messages\MessageContainer;
use Zend\View\Helper\AbstractHelper;

class MessageLabels extends AbstractHelper{
    public function __invoke() {
        return $this;
    }

    public function severity(MessageContainer $message) {
        switch ($message->getMessageSeverity()) {
            case MessageMapper::SEVERITY_INFO:
                return 'info';
                break;
            case MessageMapper::SEVERITY_WARNING:
                return 'warning';
                break;
            case MessageMapper::SEVERITY_ERROR:
                return 'error';
                break;
            default:
                return 'unknown';
        }
    }

    /**
     * @param MessageContainer $message
     * @return string
     */
    public function context(MessageContainer $message) {
        switch ($message->getMessageContext()) {
            case MessageMapper::CONTEXT_DAEMON:
                return 'daemon';
                break;
            case MessageMapper::CONTEXT_DIRECTIVE:
                return 'directive';
                break;
            case MessageMapper::CONTEXT_EXTENSION:
                return 'extension';
                break;
            case MessageMapper::CONTEXT_JOBQUEUE_RULE:
                return 'jobqueueRule';
                break;
            case MessageMapper::CONTEXT_MONITOR_RULE:
                return 'monitorRule';
                break;
            case MessageMapper::CONTEXT_PAGECACHE_RULE:
                return 'pagecacheRule';
                break;
            case MessageMapper::CONTEXT_VHOST:
                return 'vhost';
                break;
            default:
                return 'unknown';
        }
    }

    /**
     * @param MessageContainer $message
     * @return string
     */
    public function type(MessageContainer $message) {
        switch ($message->getMessageType()) {
            case MessageMapper::TYPE_DIRECTIVE_MODIFIED:
                return 'directiveModified';
                break;
            case MessageMapper::TYPE_EXTENSION_DISABLED:
                return 'extensionDisabled';
                break;
            case MessageMapper::TYPE_EXTENSION_ENABLED:
                return 'extensionEnabled';
                break;
            case MessageMapper::TYPE_MISSMATCH:
                return 'missmatch';
                break;
            case MessageMapper::TYPE_MISSING:
                return 'missing';
                break;
            case MessageMapper::TYPE_NOT_LOADED:
                return 'notLoaded';
                break;
            case MessageMapper::TYPE_NOT_INSTALLED:
                return 'notInstalled';
                break;
            case MessageMapper::TYPE_OFFLINE:
                return 'offline';
                break;
            case MessageMapper::TYPE_MONITOR_RULES_UPDATED:
                return 'monitorRulesUpdated';
                break;
            case MessageMapper::TYPE_JOBQUEUE_RULES_UPDATED:
                return 'jobqueueRulesUpdated';
                break;
            case MessageMapper::TYPE_PAGECACHE_RULES_UPDATED:
                return 'pagecacheRulesUpdated';
                break;
            case MessageMapper::TYPE_NOT_LICENSED:
                return 'notLicensed';
                break;
            case MessageMapper::TYPE_RELOADABLE_DIRECTIVE_MODIFIED:
                return 'reloadableDirectiveModified';
                break;
            case MessageMapper::TYPE_INVALID_LICENSE:
                return 'invalidLicense';
                break;
            case MessageMapper::TYPE_LICENSE_ABOUT_TO_EXPIRE:
                return 'licenseAboutToExpire';
                break;
            case MessageMapper::TYPE_WEBSERVER_NOT_RESPONDING:
                return 'webserverNotResponding';
                break;
            case MessageMapper::TYPE_SC_SESSION_HANDLER_FILES:
                return 'scSessionHandlerFiles';
                break;
            case MessageMapper::TYPE_SCD_STDBY_MODE:
                return 'scdStandbyMode';
                break;
            case MessageMapper::TYPE_SCD_ERROR_MODE:
                return 'scdErrorMode';
                break;
            case MessageMapper::TYPE_SCD_SHUTDOWN_ERROR:
                return 'scdShutdownError';
                break;
            case MessageMapper::TYPE_VHOST_ADDED:
                return 'vhostAdded';
                break;
            case MessageMapper::TYPE_VHOST_MODIFIED:
                return 'vhostModified';
                break;
            case MessageMapper::TYPE_VHOST_REDEPLOYED:
                return 'vhostRedployed';
                break;
            case MessageMapper::TYPE_VHOST_REMOVED:
                return 'vhostRemoved';
                break;
            case MessageMapper::TYPE_VHOST_WRONG_OWNER:
                return 'vhostWrongOwner';
                break;
            default:
                return 'unknown';
        }
    }
}