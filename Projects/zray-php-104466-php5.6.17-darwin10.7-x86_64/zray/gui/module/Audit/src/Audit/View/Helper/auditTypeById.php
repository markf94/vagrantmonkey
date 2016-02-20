<?php
namespace Audit\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Audit\Container;

class auditTypeById extends AbstractHelper {

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
        // In database we store integer values of audit type
        $reflection = new \ReflectionClass('\Audit\AuditTypeInterface');
        $auditTypes = array_values($reflection->getConstants());
        if (isset($auditTypes[$auditTypeId]) && isset($auditTypeStrings[$auditTypes[$auditTypeId]])) {
            return $auditTypeStrings[$auditTypes[$auditTypeId]];
        } else {
            return $auditTypeId;
        }
	}
}
