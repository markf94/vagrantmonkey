<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;

class UrlInsightZraySnapshotsJson extends AbstractHelper {
	
	public function __invoke($snapshots = array()) {
		$snapshotsList = array();
		foreach ($snapshots as $snapshot) {
			$snapshotsList[] = $this->getZraySnapshotJSON($snapshot);
		}
		return $this->getView()->json($snapshotsList);
	}
	
	protected function getZraySnapshotJSON(\UrlInsight\ZraySnapshotContainer $snapshot) {
		return array(
			'pageId' => $snapshot->getPageId(),
			'requestTime' => $snapshot->getRequestTime(),
		);
	}
}