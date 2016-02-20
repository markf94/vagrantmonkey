<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class QueuePriority extends AbstractHelper {

    /**
     * 
     * @param integer $priority
     */
	public function __invoke($priority) {
	    $prioNames = array('Low', 'Below Normal', 'Normal', 'Above Normal', 'High');
	    if (!in_array($priority, array_keys($prioNames))) {
	        return $priority;
	    }
	    
	    return  $prioNames[$priority];
	}
	
	
}

