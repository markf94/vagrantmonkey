<?php

namespace Audit\Controller\Plugin;

interface InjectAuditMessageInterface {
	/**
	 * @param AuditMessage $auditMessage
	 */
	public function setAuditMessage($auditMessage);
	
}

