<?php
namespace Acl\License;

use ZendServer\Permissions\AclQuery;
use ZendServer\Permissions\AclQuerierInterface;
use ZendServer\Log\Log;

class Mapper implements AclQuerierInterface {
	
	/**
	 * @var AclQuery
	 */
	private $acl;
	
	public function isValid($from) {
		
		//$from = 1352980800; // 15 nov 2012
		//$from = 1351771200; // 1 nov 2012
		//$from = 1349092800; // 1 oct 2012
		//$from = 1338552000; // 1 june 2012
		
	    $maxTime = $this->getMaxAuditFrom();
	    
	    if($maxTime === false){
	        return true;
	    }else{
		  return ($maxTime < $from);
	    }
	    
		return false;
	}
	

	public function getMaxAuditFrom() {
	    
		//False = Unlimited
		
	    if ($this->acl->isAllowed('dataRentention:timelimit', 'unlimited')) {
	        return false;
	    }
	
	    if ($this->acl->isAllowed('dataRentention:timelimit', '3month')) {
	        return strtotime("-3 month  -1 minute");
	    }
	
	    if ($this->acl->isAllowed('dataRentention:timelimit', '2weeks')) {
	        return strtotime("-2 week  -1 minute");
	    }
	
	    return strtotime("-2 hour -1 minute");
	}
	
	
	/**
	 * @param AclQuery $acl
	 * @return AclQuerierInterface
	 */
	
	/* (non-PHPdoc)
	 * @see \ZendServer\Permissions\AclQuerierInterface::setAcl()
	*/
	public function setAcl(\ZendServer\Permissions\AclQuery $acl) {
		$this->acl = $acl;
		return $this;
	}

}