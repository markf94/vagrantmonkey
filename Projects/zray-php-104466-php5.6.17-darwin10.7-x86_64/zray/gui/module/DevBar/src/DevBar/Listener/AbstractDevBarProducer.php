<?php
namespace DevBar\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;
use ZendServer\Log\Log;

abstract class AbstractDevBarProducer implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var MapperDirectives
     */
    protected $directivesMapper;
    
    /**
     * @param \Configuration\MapperDirectives $directivesMapper
     */
    public function setDirectivesMapper($directivesMapper) {
    	$this->directivesMapper = $directivesMapper;
    }
    
    public function getDirectivesMapper() {
    	return $this->directivesMapper;
    }
    
    /* (non-PHPdoc)
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('devbar', 'DevBarModules', $this, 100);
    }

    /* (non-PHPdoc)
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
    	$sharedEvents      = $events->getSharedManager();
        foreach ($this->listeners as $index => $listener) {
            if ($sharedEvents->detach('devbar', $listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    /**
     * @return \Zend\View\Model\ViewModel
     */
    abstract public function __invoke();
}

