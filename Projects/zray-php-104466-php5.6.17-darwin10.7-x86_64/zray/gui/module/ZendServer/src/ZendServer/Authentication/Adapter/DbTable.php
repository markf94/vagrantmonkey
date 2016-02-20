<?php

namespace ZendServer\Authentication\Adapter;

use Zend\Crypt\Hash;

use ZendServer\Log\Log;

use Zend\Authentication\Adapter\AdapterInterface;

use Users\Identity;

use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter as baseDbTable;
use Zend\Authentication\Result;
use Application\Module;

class DbTable extends baseDbTable {
	
	protected function authenticateCreateAuthResult() {
		
		$identityRow = $this->getResultRowObject(array('NAME', 'ROLE'));

		$identity = new Identity();
		if (is_object($identityRow)) {
			$identity->setIdentity($identityRow->NAME);
			$identity->setRole($identityRow->ROLE);
			$identity->setUsername($identityRow->NAME);
		} else {
			$identity->setIdentity('Unknown');
			$identity->setRole(Module::ACL_ROLE_GUEST);
			$identity->setUsername('');
		}
		
		return new Result(
				$this->authenticateResultInfo['code'],
				$identity,
				$this->authenticateResultInfo['messages']
		);
	}

}
