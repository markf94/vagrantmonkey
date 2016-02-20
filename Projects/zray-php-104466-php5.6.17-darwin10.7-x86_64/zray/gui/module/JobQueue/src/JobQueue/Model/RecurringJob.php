<?php

namespace JobQueue\Model;
use ZendJobQueue;
use JobQueue\Model\RecurringJobSchedule;

class RecurringJob {
    /**
     * @var integer
     */
    private $id = null;
    
    /**
     * @var integer
     */
    private $type = null;
    
    /**
     * @var string
     */
    private $script = null;
    
    /**
     * @var string
     */
    private $name = null;
    
    /**
     * @var integer
     */
    private $priority = null;
    
    /**
     * @var bool
     */
    private $persistent = false;
    
    /**
     * @var integer
     */
    private $status = null;
    
    /**
     * @var string
     */
    private $vars = null;
    
    /**
     * @var string
     */
    private $schedule = null;
    
    /**
     * 
     * @var \JobQueue\Model\RecurringJobSchedule
     */
    private $parsedSchedule = null;
    
    /**
     * 
     * @var
     */
    //private $parsedSchedule = null;
    
    /**
     * @var string
     */
    private $httpHeaders = null;
    
    /**
     * @var string
     */
    private $lastRun = null;
    
    /**
     * @var string
     */
    private $nextRun = null;
    
    /**
     * Creates a job with given properties
     */
    public function __construct(array $jobProperties = null) {
        if (is_array($jobProperties)) {
            foreach ($jobProperties as $key => $val) {
                switch($key) {
                    case 'id':
                        $this->setId($val);
                        break;
                    case 'type':
                        $this->setType($val);
                        break;
                    case 'status':
                        $this->setStatus($val);
                        break;
                    case 'script':
                        $this->setScript($val);
                        break;
                    case 'name':
                        $this->setName($val);
                        break;
                    case 'priority':
                        $this->setPriority($val);
                        break;
                    case 'persistent':
                        $this->setPersistent($val);
                        break;
                    case 'vars':
                        $this->setVars($val);
                        break;
                    case 'schedule':
                        $this->setSchedule($val);
                        $this->setParsedSchedule(new RecurringJobSchedule($val));
                        break;
                    case 'last_run':
                        $this->setLastRun($val);
                        break;
                    case 'next_run':
                        $this->setNextRun($val);
                        break;
                    case 'http_headers':
                        $this->setHttpHeaders($val);
                        break;
                }
            }
        }
    }
    
    /**
     * Get the $parsedSchedule
     */
    public function getParsedSchedule() {
        return $this->parsedSchedule;
    }
    
    /**
     * @param stdClass $parsedSchedule
     */
    public function setParsedSchedule($parsedSchedule) {
        $this->parsedSchedule = $parsedSchedule;
    }
    
    /**
     * @param integer
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * @param integer
     */
    public function setType($type) {
        $this->type = $type;
    }
    
    /**
     * @return integer
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * @param integer
     */
    public function setStatus($status) {
        $this->status = $status;
    }
    
    /**
     * @return integer
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * @return bool
     */
    public function isSuspended() {
        return $this->status === \JobQueue\JobQueueInterface::STATUS_SUSPENDED;
    }
    
    /**
     * @param string
     */
    public function setScript($script) {
        $this->script = $script;
    }
    
    /**
     * @return string
     */
    public function getScript() {
        return $this->script;
    }
    
    /**
     * @param string
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * @param integer
     */
    public function setPriority($priority) {
        $this->priority = $priority;
    }
    
    /**
     * @return integer
     */
    public function getPriority() {
        return $this->priority;
    }
    
    /**
     * @param bool
     */
    public function setPersistent($persistent) {
        $this->persistent = $persistent;
    }
    
    /**
     * @return bool
     */
    public function getPersistent() {
        return $this->persistent;
    }
    
    /**
     * @param string
     */
    public function setVars($vars) {
        $this->vars = $vars;
    }
    
    /**
     * @return string
     */
    public function getVars() {
        return $this->vars;
    }
    
    /**
     * @param string
     */
    public function setSchedule($schedule) {
        $this->schedule = $schedule;
    }
    
    /**
     * @return string
     */
    public function getSchedule() {
        return $this->schedule;
    }
    
    /**
     * @param string
     */
    public function setLastRun($lastRun) {
        $this->lastRun = $lastRun;
    }
    
    /**
     * @return string
     */
    public function getLastRun() {
        return $this->lastRun;
    }
    
    /**
     * @param string
     */
    public function setNextRun($nextRun) {
        $this->nextRun = $nextRun;
    }
    
    /**
     * @return string
     */
    public function getNextRun() {
        return $this->nextRun;
    }
    
    /**
     * @param string
     */
    public function setHttpHeaders($httpHeaders) {
        $this->httpHeaders = $httpHeaders;
    }
    
    /**
     * @return string
     */
    public function getHttpHeaders() {
        return $this->httpHeaders;
    }
    
    /**
     * @return string
     */
    public function getTypeDisplay() {
        // TRANLSATE
        $display = array(
                \JobQueue\JobQueueInterface::TYPE_HTTP				=> _t('HTTP'),
                \JobQueue\JobQueueInterface::TYPE_HTTP_RELATIVE	=> _t('HTTP'),
                \JobQueue\JobQueueInterface::TYPE_SHELL			=> _t('SHELL'),
        );
        $type = $this->getType();
        if (isset($display[$type])) {
            return $display[$type];
        } else {
            return _t("Unknown (%s)", array($type));
        }
    }
    
    /**
     * @return string
     */
    public function getPriorityDisplay() {
        // TRANLSATE
        $display = array(
                \JobQueue\JobQueueInterface::PRIORITY_LOW		=> _t('low'),
                \JobQueue\JobQueueInterface::PRIORITY_NORMAL	=> _t('normal'),
                \JobQueue\JobQueueInterface::PRIORITY_HIGH		=> _t('high'),
                \JobQueue\JobQueueInterface::PRIORITY_URGENT	=> _t('urgent'),
        );
        $priority = $this->getPriority();
        if (isset($display[$priority])) {
            return $display[$priority];
        } else {
            return _t("Unknown (%s)", array($priority));
        }
    }
}