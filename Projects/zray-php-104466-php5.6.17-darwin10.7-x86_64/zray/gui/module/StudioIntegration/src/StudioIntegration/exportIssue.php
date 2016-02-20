<?php
namespace StudioIntegration;

use Codetracing\Trace\AmfFileRetriever;

use ZendServer\Exception as ZSException,
ZendServer\Log\Log,
ZendServer\FS\FS,
ZendServer\Validator\XmlStructure,
Codetracing\Model as codetracingModel,
MonitorUi\Model\Model as monitorUiModel,
\Issue\Filter\Dictionary;

class exportIssue {
	
	/**
	 * @var codetracingModel
	 */
	private $codetracingModel;

	/**
	 * @var monitorUiModel
	 */
	private $monitorUiModel;	
	
	
	public function __construct($monitorUiModel) {
		$this->monitorUiModel = $monitorUiModel;
	}
	
	/**
	 * @param string $issueId
	 * @param string $groupId
	 * @param string $traceDumpId
	 * @throws ZSException
	 * @return string path to where the file is located at
	 */
	public function createFile($issueId = null, $groupId = null, $traceFilepath = null) {
		// cleanup on erroneous input
		if ((('' == $issueId) && ('' != $groupId)) || (('' != $issueId) && ('' == $groupId))) {
			$issueId = '';
			$groupId = '';
		} 

		$issueDetailsPath = null;
		if (('' != $issueId) && ('' != $groupId)) {
			$issueDetailsPath = $this->createIssueDetailsFile($issueId, $groupId);
		}

		if (is_null($traceFilepath) && is_null($issueDetailsPath)) {
			throw new ZSException(_t('No parameters received for export'));
		}
		
		try {
			$zipFilepath = FS::createPath(
				FS::getGuiTempDir(), 
				$this->generateArchiveFilename($issueId, $groupId, $traceFilepath)
			);
		} catch (ZSException $e) {
			throw new ZSException(_t('Could not create an archive file because of incorrect input'. $e->getMessage()));
		}
		
		try {
			$fileObject = FS::getFileObject($zipFilepath);
			/// file already exists, return it
			return $fileObject->getPathname();
		} catch (\Exception $e) {
			Log::info("{$e->getMessage()} ({$e->getPrevious()->getMessage()})");
		}
		
		try {
			$zip = FS::getZipArchive($zipFilepath);
		} catch (ZSException $e) {
			throw new ZSException(_t('Failed to create an archive file in \'%s\': %s'), array($zipFilepath, $e->getMessageObject()));
		}

		if ($traceFilepath) {
			$this->addTraceAmfFile($traceFilepath, $zip);
		}
		
		if (! is_null($issueDetailsPath)) {
			$zip->addFile($issueDetailsPath, basename($issueDetailsPath));
			$issueDetailsXsdPath = FS::createPath(__DIR__, 'Resources', 'eventGroup.xsd');
			$zip->addFile($issueDetailsXsdPath, basename($issueDetailsXsdPath));
		}
		$zip->setArchiveComment(_t('This file is intended for import into IDE. Created by Zend Server, a Zend Technologies LTD. product'));
		
		if (false === $zip->close()) {
			throw new ZSException(_t('Failed to store the zip archive in \'%s\'',array($zipFilepath)));
		}
		
		if (! is_null($issueDetailsPath)) {
			FS::unlink($issueDetailsPath);
		}
		
		return $zipFilepath;
	}
	
	/**
	 * @param codetracingModel $codetracingModel
	 * @return exportIssue
	 */
	public function setcodetracingModel(codetracingModel $codetracingModel) {
		$this->codetracingModel = $codetracingModel;
		return $this;
	}
	
	/**
	 * Ensure an AMF file exists and retrieve its full filepath.
	 * Will throw a ZSException if the original dump file is 
	 * inaccessible or the traceId doesn't exist
	 * Add to the zip
	 * 
	 * @param string $traceFilePath
	 * @param ZipArchive $zip
	 * @return string
	 * @throws ZSException
	 */
	protected function addTraceAmfFile($traceFilePath, $zip) {
		$codetracingModel = $this->getcodetracingModel();
		$traceId = current(AmfFileRetriever::extractTraceIdFromPath($traceFilePath));
		$traceAmfDetails = $codetracingModel->getDumpFileAmfDetails($traceId);
		$fileObject = $traceAmfDetails->getFile();
		if (! ($fileObject instanceof \SplFileObject)) {
			Log::err('Failed to create the trace AMF details file');
			throw new ZSException(_t('Failed to create the trace AMF details file'));
		}
		
		$zip->addFile($fileObject->getPathname(), 'trace.amf');
		
		return $zip;
	}
	
	
	/**
	 * @return codetracingModel
	 */
	protected function getcodetracingModel() {
		if(is_null($this->codetracingModel)) {
			$this->codetracingModel = new codetracingModel();
		}
		
		return $this->codetracingModel;
	}
	
	/**
	 * @return monitorUiModel
	 */	
	protected function getMonitorUiModel() {
		return $this->monitorUiModel;
	}
	
	/**
	 * @param string $issueId
	 * @param string $groupId
	 * @throws ZSException
	 * @return string path to where the file is located at
	 */
	protected function createIssueDetailsFile($issueId, $groupId) {	
		$monitorUiModel = $this->getMonitorUiModel();
		$eventGroupData	= $monitorUiModel->getEventGroupData($groupId);
				
		// validate that the groupId belongs to this issueId
		if ($eventGroupData->getIssueId() != $issueId) {
			Log::err(_t('The group ID %s does not match the issue ID %s', array($groupId, $issueId)));
			throw new ZSException(_t('Invalid input provided: The issue details do not match'));
		}
		
		$issueData = $monitorUiModel->getIssue($issueId);
		$eventGroupStatistics = $monitorUiModel->getEventsGroup($groupId);
		$dictionary = new \Issue\Filter\Dictionary();
		$uri = $this->getUri($issueData->getUrl());
		
		$data = new \SimpleXMLElement('<eventGroup />');
		$data->addAttribute('version', '1.0.0');
		
		$details = $data->addChild('details');
		$details->addChild('issueId', 			$issueData->getId());
		$details->addChild('groupId', 			$eventGroupStatistics->getEventsGroupId());
		$details->addChild('ruleName',			$issueData->getRuleName());
		$details->addChild('eventType',			$dictionary->eventTypeToStudioText(intval($issueData->getEventType())));
		$details->addChild('totalCount',		$issueData->getCount());
		$details->addChild('firstOccurrence',	$issueData->getFirstOccurance());
		$details->addChild('lastOccurrence',	$issueData->getLastOccurance());
		$details->addChild('schema', 			$uri->getScheme());
		$details->addChild('server', 			$uri->getHost());
		$details->addChild('port', 				$uri->getPort());
		$details->addChild('path', 				$uri->getPath());
		$details->addChild('functionName', 		$issueData->getFunction());
		$details->addChild('file', 				$issueData->getFilename());
		$details->addChild('line', 				$issueData->getLine());
		$details->addChild('severity', 			$dictionary->severityToText(intval($issueData->getSeverity())));
		$details->addChild('aggregationHint', 	$issueData->getAggregationHint());
		
		$groupData = $data->addChild('groupData');
		
		$functionDetails = $groupData->addChild('functionDetails');
		$functionDetails->addChild('name',		$eventGroupData->getFunctionName());
		/// Function arguments is returned as an actual associative array - it is not a string and therefore we cannot 
		/// rely on the serializedToJson method
		$functionDetails->addChild('arguments',	htmlspecialchars(json_encode($eventGroupData->getFunctionArgs()), ENT_NOQUOTES)); // @todo - Zend_Json::encode($eventGroupData->getFunctionArgs()
		
		$superGlobals = $groupData->addChild('superGlobals');
		$superGlobals->addChild('get',		htmlspecialchars(self::serializedToJson($eventGroupData->getSuperGlobalGet()), ENT_NOQUOTES));
		$superGlobals->addChild('post',		htmlspecialchars(self::serializedToJson($eventGroupData->getSuperGlobalGet()), ENT_NOQUOTES));
		$superGlobals->addChild('cookie',	htmlspecialchars(self::serializedToJson($eventGroupData->getSuperGlobalPost()), ENT_NOQUOTES));
		$superGlobals->addChild('session',	htmlspecialchars(self::serializedToJson($eventGroupData->getSuperGlobalCookie()), ENT_NOQUOTES));
		$superGlobals->addChild('env',		self::serializedToJson($eventGroupData->getSuperGlobalEnv()));
		$superGlobals->addChild('server',	htmlspecialchars(self::serializedToJson($eventGroupData->getSuperGlobalServer()), ENT_NOQUOTES));
		$superGlobals->addChild('rawData',	htmlspecialchars(self::serializedToJson($eventGroupData->getSuperGlobalRawPost()), ENT_NOQUOTES));
		
		$backtrace = $groupData->addChild('backtrace');
		foreach ($eventGroupData->getBacktrace() as $key => $backtraceRow) { // @todo - remove constants
			$row = $backtrace->addChild('row');
			$row->addAttribute('id',		(int)$key);
			$row->addChild('className',		(string)$backtraceRow[ZM_DATA_BACKTRACE_CLASS_NAME]);
			$row->addChild('objectName',	(string)$backtraceRow[ZM_DATA_BACKTRACE_OBJECT_NAME]);
			$row->addChild('functionName',	(string)$backtraceRow[ZM_DATA_BACKTRACE_FUNCTION_NAME]);
			$row->addChild('lineNumber',	(int)$backtraceRow[ZM_DATA_BACKTRACE_LINE_NUMBER]);
			$row->addChild('file',			(string)$backtraceRow[ZM_DATA_BACKTRACE_FILE]);
			$row->addChild('isStatic',		(int)$backtraceRow[ZM_DATA_BACKTRACE_IS_STATIC]);
		}
		
		$additionalData = $groupData->addChild('additionalData');
		
		$additionalData->addChild('groupCount',			(int)$eventGroupStatistics->getEventsCount());
		$additionalData->addChild('errorString',		(string)$eventGroupData->getErrorString());
		$additionalData->addChild('classIdentifier',	(string)$eventGroupData->getClass());
		$additionalData->addChild('userData',			(string)self::serializedToJson($eventGroupData->getUserData()));
		$additionalData->addChild('javaBacktrace',		(string)$eventGroupData->getJavaBacktrace());
		
		$execTime = $additionalData->addChild('execTime');
		$execTime->addChild('absolute',	(int)$eventGroupStatistics->getExecTime());
		$execTime->addChild('average',	(float)$eventGroupStatistics->getAvgExecTime());
		
		$memoryUsage = $additionalData->addChild('memoryUsage');
		$memoryUsage->addChild('absolute',	(int)$eventGroupStatistics->getMemUsage());
		$memoryUsage->addChild('average',	(float)$eventGroupStatistics->getAvgMemUsage());
		
		$outputSize = $additionalData->addChild('outputSize');
		$outputSize->addChild('absolute',	(int)$eventGroupStatistics->getOutputSize());
		$outputSize->addChild('average',	(float)$eventGroupStatistics->getAvgOutputSize());
		
		$additionalData->addChild('issueLoad',	(float)$eventGroupStatistics->getLoad());
		
		$domDocument = $this->createDomFromSimplex($data);
		$domDocument->formatOutput = true; // have a nicely indented xml file		
		$this->wrapNodeValueInCdata($domDocument, '//functionDetails/arguments');// protect XML integrity from potentially variable values

		$xmlOutput = $domDocument->saveXML();

		$validator = new XmlStructure();
		$validator->setXsdFile(FS::createPath(__DIR__, 'Resources', 'eventGroup.xsd'));
		if (! $validator->isValid($xmlOutput)) {
			Log::err("Failed to to generate the xml file with the following errors: " . print_r($validator->getMessages(), true));
			throw new ZSException(_t('The generated output is invalid'));
		}
		
		$filepath =  FS::createPath(FS::getGuiTempDir(), 'event.xml');
		try {
			FS::getFileObject($filepath, 'w')->fwrite($xmlOutput);
		} catch (ZSException $e) {
			Log::logException('Failed to store the issue xml', $e);
			throw new ZSException(_t('The application could not store the data'));
		}
		
		return $filepath;
	}
	
	/**
	 * @param SimpleXMLElement $simpleXml
	 * @return DOMDocument
	 */
	protected function createDomFromSimplex(\SimpleXMLElement $simpleXml) {
		$node = dom_import_simplexml($simpleXml);
		$domDocument = new \DOMDocument('1.0', 'UTF-8');
		$node = $domDocument->importNode($node, true);
		$domDocument->appendChild($node);
		return $domDocument;
	}
	
	/**
	 * Retrieve a node's text value and wrap it in "cdata"
	 * @param DOMDocument $domDocument
	 * @param string $query
	 * @throws DOMException
	 */
	protected function wrapNodeValueInCdata(\DOMDocument $domDocument, $query) {
		$xpath = new \DOMXpath($domDocument);
		$nodes = $xpath->query($query);
		if (! is_null($nodes)) {
			foreach ($nodes as $node) {/* @var $node DOMElement */
				$value = $node->nodeValue;
				$node->nodeValue = '';
				$cdata = $domDocument->createCDATASection($value);
				$node->appendChild($cdata);
			}
		}
	}
	
	/**
	 * @param string $uri
	 * @throws ZSException
	 * @return Zend_Uri
	 */
	protected function getUri($uri) {
		// break the URI to pieces
		try {
			/**
			 * Zend_Uri has problems with URIs which have strange unencoded characters in them, like
			 * the Zend Monitor's <BLOCKED_VALUE> so superfluous GET variables are dropped
			 */
			if (false !== strpos($uri, '?')) {
				$uri = substr($uri, 0, strpos($uri, '?'));
			}
			
			$result = $uri = new \Zend\Uri\Http($uri);
			
			if (! $result->getPort()) {
				$result->setPort(80);
			}
			
			return $result;
		} catch (\Exception $e) {
			throw new ZSException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * @param string $string - serialized data
	 * @return string
	 */
	private static function serializedToJson($value) {
		if ($value) { // no need to encode empty string/array and such
			$value = json_encode($value);
		}
		
		return $value; // @todo - does json_encode covers ok the functioanlity of ZS5: Zend_Json::encode(unserialize($string)) ?
	}

	/**
	 * @param string $issueId
	 * @param string $groupId
	 * @param string $traceFilePath
	 * @return string
	 * @throws ZSException
	 */
	protected function generateArchiveFilename($issueId, $groupId, $traceFilePath = null) {
		
		if ($traceFilePath) {
			$traceId = implode('.', AmfFileRetriever::extractTraceIdFromPath($traceFilePath));
		} else {
			$traceId = '';
		}
		
		$monitorUiModel = $this->getMonitorUiModel();
		$issueData = $monitorUiModel->getIssue($issueId);
		
		// normalizing the input
		if ('' == $traceId) {
			$traceId = '0.0.0';
		}
		if ('' == $issueId) {
			$issueId = '0';
		}
		if ('' == $groupId) {
			$groupId = '0';
		}
		
		if (('0' == $issueId) && ('0' == $groupId) && ('0.0.0' == $traceId)) {
			throw new ZSException(_t('Invalid input provided'));
		}
		
		if (('0' != $issueId) && ('0' != $groupId)) {
			$ruleName = $issueData->getRuleName();
			$ruleName = preg_replace('/\s+/', '_', $ruleName);
			$ruleName = preg_replace('/[^a-zA-Z0-9]+/', '_', $ruleName);
			$filename = trim($ruleName, '_') . '-' . $issueId . '-' . $groupId;
		} else {
			$filename = 'trace-' . str_replace('.', '-', $traceId);
		}
		return $filename . '-' . date('Ymd') . '.zsf';
	}	
}