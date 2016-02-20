<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\BacktraceContainer;

class UrlInsightZraySnapshotsXml extends AbstractHelper {
	
	public function __invoke(array $snapshots) {
		$snapshotsXMLs = array();
		foreach ($snapshots as $snapshot) {
			$snapshotsXMLs[] = $this->getSnapshotXml($snapshot);
		}
		
		return implode(PHP_EOL, $snapshotsXMLs);
	}
	
	protected function getSnapshotXml(\UrlInsight\ZraySnapshotContainer $snapshot) {
		return <<<XML
<zraySnapshot>
	<pageId>{$snapshot->getPageId()}</pageId>
	<requestTime>{$snapshot->getRequestTime()}</requestTime>
</zraySnapshot>
XML;
	}
}