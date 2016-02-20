<?php

namespace Zsd\View\Helper;


use Messages\MessageContainer;
use Zend\View\Helper\AbstractHelper;

class DaemonMessageJson extends AbstractHelper{
    public function __invoke(MessageContainer $message) {
    	return $this->getView()->json(array(
                'msgId' => $message->getMessageId(),
                'nodeId' => $message->getMessageId(),
                'context' => $this->getView()->messageLabels()->context($message),
                'key' => $message->getMessageKey(),
                'severity' => $this->getView()->messageLabels()->severity($message),
                'details' => $message->getMessageDetails(),
                'type' => $this->getView()->messageLabels()->type($message),
            ));
    }
}