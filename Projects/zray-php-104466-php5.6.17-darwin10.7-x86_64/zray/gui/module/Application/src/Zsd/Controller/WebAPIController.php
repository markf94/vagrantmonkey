<?php

namespace Zsd\Controller;


use ZendServer\Mvc\Controller\WebAPIActionController;

class WebAPIController extends WebAPIActionController {

    public function daemonsProbeAction() {
        $this->isMethodGet();
        $messages = $this->getMessagesMapper()->findAllDaemonsMessages();
        return array('daemonMessages' => $messages);
    }

}