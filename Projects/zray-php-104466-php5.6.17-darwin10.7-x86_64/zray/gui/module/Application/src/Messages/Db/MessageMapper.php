<?php

namespace Messages\Db;

use ZendServer\Log\Log,
Configuration\MapperAbstract,
Messages\MessageContainer;
use Zend\Db\Sql\Where;

class MessageMapper extends MapperAbstract {
	
	const CONTEXT_EXTENSION			= 0;
	const CONTEXT_DIRECTIVE			= 1;
	const CONTEXT_DAEMON			= 2;
	const CONTEXT_MONITOR_RULE		= 3;
	const CONTEXT_PAGECACHE_RULE	= 4;
	const CONTEXT_JOBQUEUE_RULE		= 5;
	const CONTEXT_VHOST				= 6;

	const SEVERITY_INFO		= 0;
	const SEVERITY_WARNING	= 1;
	const SEVERITY_ERROR	= 2;

	// INFO TYPES
	const TYPE_EXTENSION_ENABLED		= 0;
	const TYPE_EXTENSION_DISABLED		= 1;
	const TYPE_DIRECTIVE_MODIFIED		= 2;
	const TYPE_MONITOR_RULES_UPDATED	= 8;
	const TYPE_JOBQUEUE_RULES_UPDATED	= 9;
	const TYPE_PAGECACHE_RULES_UPDATED	= 10;
	const TYPE_RELOADABLE_DIRECTIVE_MODIFIED	= 12;

	// WARNING TYPES
	const TYPE_MISSMATCH				= 3;
	const TYPE_NOT_LICENSED				= 11;
	const TYPE_INVALID_LICENSE			= 13;
	const TYPE_LICENSE_ABOUT_TO_EXPIRE	= 14;
	const TYPE_SC_SESSION_HANDLER_FILES	= 23;

	// ERROR TYPES
	const TYPE_MISSING					= 4;
	const TYPE_NOT_LOADED				= 5;
	const TYPE_NOT_INSTALLED			= 6;
	const TYPE_OFFLINE					= 7;
	const TYPE_WEBSERVER_NOT_RESPONDING	= 15;
	const TYPE_SCD_STDBY_MODE			= 24;
	const TYPE_SCD_ERROR_MODE			= 25;
	const TYPE_SCD_SHUTDOWN_ERROR		= 26;
	
	const TYPE_VHOST_ADDED				= 27;
	const TYPE_VHOST_REMOVED			= 28;
	const TYPE_VHOST_MODIFIED			= 29;
	const TYPE_VHOST_REDEPLOYED			= 30;
	const TYPE_VHOST_WRONG_OWNER		= 31;

	protected $setClass = '\Messages\MessageContainer';


	/**
	 * Check if a specific daemon is offline. Specify a serverId to check on a single server, otherwise it is a cluster-wide check
	 * @param string $daemon
	 * @param integer $serverId
	 * @return boolean
	 */
	public function isDaemonOffline($daemon, $serverId = null) {
		$where = new Where();
		$where->equalTo('CONTEXT', self::CONTEXT_DAEMON);
		$where->equalTo('TYPE', self::TYPE_OFFLINE);
		$where->equalTo('MSG_KEY', $daemon);
		if (! is_null($serverId)) {
			$where->equalTo('NODE_ID', $serverId);
		}
		return $this->count('*', $where) > 0;
	}
	
	/**
	 * @return Set[MessageContainer]
	 */
	public function findAllMessages() {
		return $this->select();
	}

	/**
	 * @return Set[MessageContainer]
	 */
	public function findServerMessages($serverId) {
		$predicate = 'NODE_ID IN ("' . implode('","', array($serverId, -1)) . '")'; // -1 is relevant for all
		return $this->select(array($predicate));
	}

	/**
	 * @return Set[MessageContainer]
	 */
	public function findServersMessages($serverIds) {
		$predicate = 'NODE_ID IN ("' . implode('","', $serverIds) . '")'; // -1 is relevant for all
		return $this->select(array($predicate));
	}

	/**
	 * @return Set[MessageContainer]
	 */
	public function findAllVhostMessages($serverIds) {
		$predicates = new Where();
		$predicates->in('NODE_ID', $serverIds);
		$predicates->equalTo('CONTEXT', self::CONTEXT_VHOST);
		return $this->select($predicates);
	}

	/**
	 * @return Set[MessageContainer]
	 */
	public function findAllExtensionsMessages($serverIds) {
		$predicates = new Where();
		$predicates->in('NODE_ID', $serverIds);
		$predicates->equalTo('CONTEXT', self::CONTEXT_EXTENSION);
		return $this->select($predicates);
	}

	/**
	 * @return Set[MessageContainer]
	 */
	public function findAllDirectivesMessages($serverId = null) {		
		$bind = array('CONTEXT' => self::CONTEXT_DIRECTIVE);		
		if (is_numeric($serverId)) {
			$bind += array('NODE_ID' => $serverId);
		}
		
		return $this->select($bind);
	}

	/**
	 * @return Boolean
	 */
	public function isDirectivesAwaitingRestart(array $directives, $serverId = null) {
		$directivesMessages = $this->findAllDirectivesMessages($serverId);
		foreach ($directivesMessages as $directivesMessage) {/* @var $directivesMessage MessageContainer */
			if (in_array($directivesMessage->getMessageKey(), $directives)) {
				return true; // we assume that if there's a message about a certain directive, then a restart is required, this might not be 100% accurate
			}
		}
		
		return false;
	}

	/**
	 * @return Set[MessageContainer]
	 */
	public function findAllDaemonsMessages() {
		return $this->select(array('CONTEXT' => self::CONTEXT_DAEMON));
	}
}
