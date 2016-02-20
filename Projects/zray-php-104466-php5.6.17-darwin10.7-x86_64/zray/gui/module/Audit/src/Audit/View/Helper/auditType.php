<?php
namespace Audit\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Audit\Container;

class auditType extends AbstractHelper {

    /**
     * 
     * @var \Audit\Dictionary
     */
    protected $dictionary;
    
    public function setDictionary(\Audit\Dictionary $dictionary) {
        $this->dictionary = $dictionary;
    }
    
    /**
     * @return \Audit\Dictionary
     */
    public function getDictionary() {
        return $this->dictionary;
    }
    
    public function __invoke($auditTypeId) {
	    $auditTypeStrings = $this->getDictionary()->getAuditTypeStrings();
        if (isset($auditTypeStrings[$auditTypeId])) {
	        return $auditTypeStrings[$auditTypeId];
        } else {
	        return $auditTypeId;
	    }
	}
}
