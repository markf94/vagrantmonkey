<?php

namespace Vhost\Mapper;

use ZendServer;


class AddVhost extends AbstractVhostAction {

	/**
	 * @return number
	 */
	protected function modifyVhost() {
		return $this->getVhostMapper()->insertVhost($this->getVhostName(), $this->getPort(), $this->getTemplate(), $this->getSsl(), $this->getSslCertificatePath(), $this->getSslCertificateKeyPath(), $this->getSslCertificateChainPath(), $this->getSslAppName(), $this->isForceCreate());
	}
	
}