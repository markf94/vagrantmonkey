<?php
namespace Vhost\Entity;

use ZendServer\Set;

class Vhost {
	
	const STATUS_UNKNOWN 				= -1;
	const STATUS_OK 					= 0;
	const STATUS_ERROR 					= 1;
	const STATUS_PENDING_RESTART		= 2;
	const STATUS_MODIFIED 				= 3;
	const STATUS_DEPLOYMENT_NOT_ENABLED = 4;
	const STATUS_CREATE_ERROR			= 5;
	
	const STATUS_WARNING 				= 50;
	
	protected $ID;
	protected $NAME;
	protected $PORT;
	protected $TEXT;
	protected $TEMPLATE;
	protected $CREATED_AT;
	protected $LAST_UPDATED;
	protected $OWNER;
	protected $DOCUMENT_ROOT;
	protected $CONFIG_FILE;
	protected $IS_DEFAULT;
	protected $IS_SSL;
	protected $CRT_FILE;
	protected $CRT_KEY_FILE;
	protected $CRT_CHAIN_FILE;
	protected $SSL_APP_NAME;

	/**
	 * @var Set
	 */
	protected $applications;
	
	public function getId() {
		return $this->ID;
	}
	
	public function getName() {
		return $this->NAME;
	}
	
	public function getPort() {
		return $this->PORT;
	}
	
	public function isDefault() {
		return $this->IS_DEFAULT;
	}
	
	public function getTemplate() {
		return $this->TEMPLATE;
	}
	
	public function getText() {
		return $this->TEXT;
	}
	
	public function getConfigFile() {
		return $this->CONFIG_FILE;
	}
	
	public function getDocRoot() {
		return $this->DOCUMENT_ROOT;
	}
	
	public function getCreatedAt() {
		return $this->CREATED_AT;
	}
	
	public function getLastUpdated() {
		return $this->LAST_UPDATED;
	}
	
	public function getOwner() {
		return $this->OWNER;
	}
	
	public function isZendDefined() {
		return $this->OWNER == '1';
	}
	
	public function isManagedByZend() {
		return $this->OWNER == '1' || $this->OWNER == '2';
	}
	
	/**
	 * @return boolean
	 */
	public function isSsl() {
		return (boolean)$this->IS_SSL;
	}
	/**
	 * @return string
	 */
	public function getCertificatePath() {
		return $this->CRT_FILE;
	}
	/**
	 * @return string
	 */
	public function getCertificateKeyPath() {
		return $this->CRT_KEY_FILE;
	}
	/**
	 * @return string
	 */
	public function getCertificateChainPath() {
		return $this->CRT_CHAIN_FILE;
	}
	/**
	 * @return string
	 */
	public function getAppName() {
		return $this->SSL_APP_NAME;
	}
	
	/**
	 * @return Set
	 */
	public function getApplications() {
		return $this->applications;
	}

	/**
	 * @param \ZendServer\Set $applications
	 */
	public function setApplications($applications) {
		$this->applications = $applications;
	}

}
