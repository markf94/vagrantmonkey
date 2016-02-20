<?php

namespace Zsd\View\Helper;


use Messages\MessageContainer;
use Zend\View\Helper\AbstractHelper;

class DaemonMessageXml extends AbstractHelper{
    public function __invoke(MessageContainer $message) {
    	return <<<DAEMON_MESSAGE
    	<daemonMessage>
			<msgId>{$message->getMessageId()}</msgId>
			<nodeId>{$message->getMessageNodeId()}</nodeId>
			<context>{$this->getView()->messageLabels()->context($message)}</context>
			<key>{$message->getMessageKey()}</key>
			<severity>{$this->getView()->messageLabels()->severity($message)}</severity>
			<details><![CDATA[{$this->getView()->json($message->getMessageDetails())}]]></details>
			<type>{$this->getView()->messageLabels()->type($message)}</type>
		</daemonMessage>
DAEMON_MESSAGE;
    }
}