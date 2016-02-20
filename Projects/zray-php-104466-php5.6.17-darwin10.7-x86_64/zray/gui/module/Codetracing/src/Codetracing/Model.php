<?php

namespace Codetracing;

use ZendServer\FS\FS,
ZendServer\Exception as ZSException,
Codetracing\Dump\AmfDetails;

class Model {

	/**
	 * @var string
	 */
	protected $directiveEnable = 'zend_codetracing.enable';
	/**
	 * @var Zend_Db_Adapter_Pdo_Abstract
	 */
	private static $dbAdapter = null;
	/**
	 * @var ZwasComponents_CodeTracing_TraceFiles_Table
	 */
	private static $traceFilesTable = null;

	/**
	 * @var \Configuration\MapperDirectives
	 */
	private $directivesMapper;
	
	/**
	 * @param string $dumpId
	 * @return AmfDetails
	 * @throws ZSException
	 */
	public function getDumpFileAmfDetails($dumpId) {
		$file = $this->getDumpFileById($dumpId);
		$fileObj = null;
		try {
			// if there is an already AMF cached copy of this file - return that
			$fileObj = FS::getFileObject($file . '.amf');
		} catch (ZSException $e) {
			$codeTracingWrapper = new Dump\Wrapper();
			$amfData = $codeTracingWrapper->getDumpFileData($file);
			if (! empty($amfData)) {
				// if the file can't be opened, the exception will go up
				$fileObj = FS::getFileObject($file . '.amf', 'w+');
				$fileObj->fwrite($amfData);
				$fileObj->rewind();
			} else {
				throw new ZSException(_t('Could not read the dump file'));
			}
				
		}
	
		return self::generateAmfFileResult($fileObj);
	}
	
	/**
	 * @param string $dumpId
	 * @throws ZSException if the file doesn't exist
	 */
	public function getDumpFileById($dumpId) {
		$dumpsPrefix = $this->getDumpFilePrefix();
	
		$filename = "$dumpsPrefix.$dumpId";
		try {
			FS::getFileObject($filename);
		} catch (ZSException $e) {
			throw new ZSException(_t('Unable to locate the dump file %s', array($filename)));
		}
	
		return $filename;
	}
	
	/**
	 * Get the ID part of the dump from its file
	 * @param string $dumpFile
	 * @throws ZSException if the file doesn't exist
	 */
	public function getDumpIdByFile($dumpFile) {
		$dumpsPrefix = $this->getDumpFilePrefix();
	
		// normalize the path as it may contain different directory seperators (bug 30066)
		$dumpFile = FS::createPath($dumpFile);
	
		if (0 === strpos($dumpFile, $dumpsPrefix)) {
			// +1 is for the extra . (dot) between the prefix and the dump ID
			$dumpId = substr($dumpFile, (strlen($dumpsPrefix) + 1));
				
			if (false !== strpos($dumpId, '.amf')) {
				$dumpId = substr($dumpId, 0, (strlen($dumpId) - 4));
			}
			return $dumpId;
		}
		throw new ZSException(_t('Failed to retrieve the trace file ID from the file'));
	}
	
	/**
	 * @param string $dumpId
	 * @return ZwasComponents_CodeTracing_TraceFiles_Element
	 * @throws ZSException
	 */
	public static function getDumpFileDetails($dumpId) {
		return self::getTraceFilesTable()->getDumpsListByDumpIds(array($dumpId))->current();
	}
	
	/**
	 * @return string
	 * @throws ZSException
	 */
	protected function getDumpFilePrefix() {
		$dumpsPrefix = $this->getDirectivesMapper()->getDirectiveValue('zend_codetracing.dump_file');
	
		if (empty($dumpsPrefix)) {
			throw new ZSException(_t('The directive %s holds an invalid value', array('zend_codetracing.dump_file')));
		}
		
		$dataDir = $this->getDirectivesMapper()->getDirectiveValue('zend.data_dir');
		return FS::createPath($dataDir, $dumpsPrefix);
	}
	
	protected static function generateAmfFileResult(\SplFileObject $fileObject) {
		$result = new AmfDetails();
		return $result->setFile($fileObject);
	}
	
	/**
	 * @return \Configuration\MapperDirectives $directivesMapper
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}

	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 * @return Model
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
		return $this;
	}

}