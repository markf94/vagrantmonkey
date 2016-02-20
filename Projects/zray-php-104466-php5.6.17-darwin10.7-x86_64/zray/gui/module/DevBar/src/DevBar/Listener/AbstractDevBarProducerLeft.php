<?php
namespace DevBar\Listener;

use Zend\EventManager\EventManagerInterface;

abstract class AbstractDevBarProducerLeft extends AbstractDevBarProducer
{
    /* (non-PHPdoc)
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('devbar', 'DevBarModulesLeft', $this, 100);
    }
}

