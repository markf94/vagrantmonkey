<?php
namespace Logs;

use ZendServer\Exception,
	ZendServer\FS\FS;

class LogReader {
	
	protected $fileObj;
	protected $fileName;
	protected $filePath;
	protected $fileStats;
	
	/**
	 * @var \Logs\Db\Mapper
	 */
	protected $logsDbMapper;
	
	protected $logNamesDictionary = array();
	
	public function readLog($logName, $linesToRead, $filter='') {
		$this->init($logName);
		$output='';
		$totalLines = $this->getLineNumber();
		$goToLine = max(0, $totalLines-($linesToRead+1));		
		$this->getFileObj()->seek($goToLine);
		while (!$this->getFileObj()->eof()) {
			$output .= $this->getFilteredLine($this->getFileObj()->fgets(), $filter);
		}	
		
		return $output;		
	}
	
	public function getLineNumber() {
		return $this->getStats()->lineNumber;
	}
		
	public function getFileSize() {
		return $this->getStats()->FileSize;
	}
	
	public function getLastModified() {
		return $this->getStats()->LastModified;
	}

	public function getlogNames() {
		return array_keys($this->logNamesDictionary);
	}

	/**
	 * @return \SplFileObject
	 */
	public function getFileObj($logName = null) {
		if ($logName) {
			$this->init($logName);
		}
		
		if ($this->fileObj) return $this->fileObj;			
			
		return $this->fileObj = new \SplFileObject($this->filePath);
	}
	
	protected function init($logName) {
		$this->logNamesDictionary = $this->getLogsDbMapper()->findAllEnabledLogFiles();
		$this->fileName = $logName;
		$this->filePath = $this->getFilePath();
		$this->isPathReadable();		
	}
	
	protected function getFilteredLine($line, $filter) {
		$line = utf8_decode($line);
		
		if ($filter === '') return $line;
		
		if (stripos($line, $filter)) {
			return $line;
		}
	
		return '';
	}
		
	/**
	 * @return stdClass
	 */
	protected function getStats() {
		if ($this->fileStats) return $this->fileStats;
		
		$this->fileStats = new \stdClass();

		$this->fileStats->lineNumber = $this->countLines();
		$this->fileStats->FileSize = $this->getStatProperty('size');
		$this->fileStats->LastModified = $this->getStatProperty('mtime');		

		return $this->fileStats;
	}

	protected function countLines() {
		$i = 0;
		while (!$this->getFileObj()->eof()) {
			$i++;
			$this->getFileObj()->fgets();
		}
	
		return $i-1;
	}
	
	protected function getStatProperty($property) {
		$stats = $this->getFileObj()->fstat();
	
		return $stats[$property];
	}
		
	protected function getFileName($logName) {
		if (!isset($this->logNamesDictionary[$logName])) {
			throw new Exception("unknown file logName: '$logName'");
		}
	
		return $this->logNamesDictionary[$logName];
	}
	
	protected function getFilePath() {
		return $this->logNamesDictionary[$this->fileName];
	}
	
	protected function isPathReadable() {
		if (! $this->getFileObj()->isFile()) {
			throw new Exception("Path '{$this->filePath}' does not exist");
		}
		
		if (! $this->getFileObj()->isReadable()) {
			throw new Exception("Path '{$this->filePath}' is not readable");			
		}

		return true;
	}
	
	/**
	 * @return \Logs\Db\Mapper $logsDbMapper
	 */
	public function getLogsDbMapper() {
		return $this->logsDbMapper;
	}

	/**
	 * @param \Logs\Db\Mapper $logsDbMapper
	 * @return LogReader
	 */
	public function setLogsDbMapper($logsDbMapper) {
		$this->logsDbMapper = $logsDbMapper;
		return $this;
	}
	
	/**
	 * @param \SplFileObject $fileObj
	 */
	public function setFileObj($fileObj) {
		$this->fileObj = $fileObj;
	}
}
