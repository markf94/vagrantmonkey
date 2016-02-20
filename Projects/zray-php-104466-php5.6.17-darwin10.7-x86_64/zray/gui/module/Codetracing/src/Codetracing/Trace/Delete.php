<?php

namespace Codetracing\Trace;

use Application\Module;

use ZendServer\Edition;

class Delete {
	
	/**
	 * @var integer
	 */
	private $traceId;
	
	/**
	 * @param $traceId
	 * @return \Codetracing\Trace\AmfFileRetriever
	 */
	public static function factory($traceId) {
		$traceIdInfo = AmfFileRetriever::extractTraceIdFromPath($traceId);
	
		$edition = new Edition();
		if (Module::isSingleServer() || ($traceIdInfo['serverId'] == $edition->getServerId())) {
			$delete = new self();
		} else {
			$delete = new self();
		}
	
		$delete->setTraceId($traceId);
		return $delete;
	}
	
	/**
	 * @return number $traceId
	 */
	public function getTraceId() {
		return $this->traceId;
	}

	/**
	 * @param number $traceId
	 * @return \Codetracing\Trace\Delete
	 */
	public function setTraceId($traceId) {
		$this->traceId = $traceId;
		return $this;
	}

	
}
