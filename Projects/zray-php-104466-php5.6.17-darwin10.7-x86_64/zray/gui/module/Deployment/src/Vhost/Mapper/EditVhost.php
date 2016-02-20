<?php

namespace Vhost\Mapper;

class EditVhost extends AbstractVhostAction {
	/**
	 * @var integer
	 */
	private $vhostId;
	
	/* (non-PHPdoc)
	 * @see \Vhost\Mapper\AbstractVhostAction::modifyVhost()
	 */
	protected function modifyVhost() {
		return $this->getVhostMapper()->editVhost($this->getVhostId(), $this->getTemplate(), $this->getSslCertificatePath(), $this->getSslCertificateKeyPath(), $this->getSslCertificateChainPath(), $this->getSslAppName(), $this->isForceCreate());
	}
	
	/* (non-PHPdoc)
	 * @see \Vhost\Mapper\AbstractVhostAction::postOperationCheck()
	 */
	protected function postOperationCheck() {
		$vhostsResult = $this->getVhostMapper()->getVhostById($this->getVhostId());
		if (is_null($vhostsResult)) {
			throw new Exception(_t("Post operation check failed, the virtual host was not set"), Exception::VHOST_OPERATION_FAILED);
		}
		return $vhostsResult;
	}
	
	/**
	 * @return integer
	 */
	public function getVhostId() {
		return $this->vhostId;
	}

	/**
	 * @param number $vhostId
	 */
	public function setVhostId($vhostId) {
		$this->vhostId = $vhostId;
	}


}

