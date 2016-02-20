<?php
namespace Servers\View\Helper;

use Servers\Container;

use Zend\View\Helper\AbstractHelper;


class serverInfoJson extends AbstractHelper {
	/**
	 * @param array $directive
	 * @return string
	 */
	public function __invoke(Container $server) {
		
		$dirArray = array(	'id'			=> $server->getNodeId(),
							'name'			=> $server->getNodeName(),
							'address'		=> $server->getNodeIp(),
							'status'		=> $this->getView()->ServerStatus($server->getStatusCode()),
							'messageList' 	=> $this->getView()->serverErrorMessageJson($server->getMessageList()),
							'debugModeEnabled' => $server->isDebugModeEnabled(),
						);
				
		return $this->getView()->json($dirArray, array());
	}
}
