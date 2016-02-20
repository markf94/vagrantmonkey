<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper,
Deployment\Db\Mapper,
ZendServer\Log\Log;

class DownloadStatus extends AbstractHelper {
	public function __invoke($status) {
		switch ($status) {
			case Mapper::STATUS_INITIALIZED:
				return 'initialized';
			case Mapper::STATUS_DOWNLOADING:
				return 'downloading';
			case Mapper::STATUS_ERROR:
				return 'error';
			case Mapper::STATUS_OK:
				return 'ok';
			default:
				Log::notice('Invalid status ' . $status);
				return 'unknown';
		}
	}
}