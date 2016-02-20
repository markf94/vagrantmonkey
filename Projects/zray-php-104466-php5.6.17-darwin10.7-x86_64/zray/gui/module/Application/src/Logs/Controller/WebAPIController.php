<?php
namespace Logs\Controller;

use Zend\Http\Headers;

use ZendServer\Edition;

use ZendServer\FS\FS;

use Logs\LogReader,
	WebAPI\Exception,
	Audit\Db\ProgressMapper,
	Audit\Db\Mapper,
	Application\Module,
	ZendServer\Mvc\Controller\WebAPIActionController,
	ZendServer\Set,
	Zend\Mvc\Controller\ActionController,
	Zend\Json\Json,
	ZendServer\Log\Log;
use Zend\Http\Response\Stream;

class WebAPIController extends WebAPIActionController {
	
	public function logsGetLogfileAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('serverId' => 0, 'linesToRead'=>Module::config('logReader', 'defaultLineChunk'), 'filter'=>''));
			$this->validateMandatoryParameters($params, array('logName'));
			$logName = $this->validateStringNonEmpty($params['logName'], 'logName');
			$serverId = $this->validateInteger($params['serverId'], 'serverId');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$guiTemp = FS::getGuiTempDir();
		$edition = new Edition();
		if ($this->isRemoteServer($serverId)) {
			$paramsToRemote = $params->toArray();
			unset($paramsToRemote['serverId']);
			try {
				$response = $this->execWebAPIRequestOnClusterMember($serverId, $this->getCmdName(), true, $paramsToRemote);
			} catch (\Exception $e) {
				Log::err("Log Reading Failed from Remote serverId '$serverId': " . $e->getMessage());
				Log::debug($e);
				throw new Exception(_t('Log Reading Failed from Remote serverId %s: %s', array($serverId, $e->getMessage())), Exception::INTERNAL_SERVER_ERROR, $e);
			}

			$contentDisposition = $response->getHeaders()->get('Content-Disposition'); /* @var $contentDisposition \Zend\Http\Header\ContentDisposition */
			if (0 < preg_match('#;\s*filename\s*=\s*"?(?P<filename>[^"]+)"?$#', $contentDisposition->getFieldValue(), $matches)) {
				$zipPath = FS::createPath($guiTemp, $matches['filename'] .'-'. uniqid() .'.zip');
			} else {
				$zipPath = FS::createPath($guiTemp, 'log-'. uniqid() .'.zip');
			}
			$zipFile = FS::getFileObject($zipPath, 'w');
			$zipFile->fwrite($response->getBody());
			$logFilename = basename($zipPath);
		} else {
			try {
				$logReader = $this->getLocator('Logs\LogReader'); /* @var $logReader \Logs\LogReader */
				$logFile = $logReader->getFileObj($logName);
			} catch (\Exception $e) {
				Log::err("Log Reading Failed: " . $e->getMessage());
				throw new Exception(_t('Log Reading Failed: %s', array($e->getMessage())), Exception::LOG_FILE_NOT_READABLE);
			}
			
			$logRealFilename = $logFile->getFilename();
			$logFilename = $logRealFilename .'-'. uniqid() .'.zip';
			$zip = new \ZipArchive();
			$zipPath = FS::createPath($guiTemp, $logFilename);
			$zip->open($zipPath, \ZipArchive::CREATE);
			$zip->addFile($logFile->getPathname(), $logRealFilename);
			$zip->close();
		}
		
		
		$package = FS::getFileObject($zipPath);
		
		$response = new Stream();
		$response->setStream(fopen($package->getPathname(), 'r'));
		$response->setStreamName($logFilename);
		$response->setStatusCode(200);
		$response->setContentLength($package->getSize());
		$this->response = $response;
		
		$this->getEvent()->setParam('do-not-compress', true);
		
		$headers = new Headers();
		$headers->addHeaderLine('Content-Disposition', "attachment; filename=\"{$logFilename}\"");
		$headers->addHeaderLine('Content-type', "application/zip");
		$headers->addHeaderLine('Content-Length', $package->getSize());
		$response->setHeaders($headers);
		return $response;
	}
	
	public function logsReadLinesAction() {
		$logReader = $this->getLocator('Logs\LogReader');
		
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('serverId' => 0, 'linesToRead'=>Module::config('logReader', 'defaultLineChunk'), 'filter'=>''));
			$this->validateMandatoryParameters($params, array('logName'));
			$logMapper = $this->getLocator('Logs\Db\Mapper'); /* @var $logReader \Logs\Db\Mapper */
			$logName = $this->validateAllowedValues($params['logName'], 'logName', array_keys($logMapper->findAllEnabledLogFiles()));
			$serverId = $this->validateInteger($params['serverId'], 'serverId');
			$linesToRead = $this->validatePositiveInteger($params['linesToRead'], 'linesToRead');
			$filter = $this->validateString($params['filter'], 'filter');
			$this->validateMaxInteger($linesToRead, Module::config('logReader', 'maxLineChunk'), 'linesToRead');		
		}catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		if ($this->isRemoteServer($serverId)) {
			$paramsToRemote = $params->toArray();
			unset($paramsToRemote['serverId']);
			try {
				$response = $this->execWebAPIRequestOnClusterMember($serverId, $this->getCmdName(), true, $paramsToRemote);
				$responseDecoded = Json::decode($response->getBody(), Json::TYPE_ARRAY);
				$responseData = $responseDecoded['responseData'];
			} catch (\Exception $e) {
				Log::err("Log Reading Failed from Remote serverId '$serverId': " . $e->getMessage());
				throw new Exception(_t('Log Reading Failed from Remote serverId %s: %s', array($serverId, $e->getMessage())), Exception::INTERNAL_SERVER_ERROR);
			}

			return array(
					'logLines'		=> $responseData['logLines'],
					'lineNumber'	=> $responseData['logFileMetaData']['lineNumber'],
					'fileSize'		=> $responseData['logFileMetaData']['fileSize'],
					'lastModified'	=> strtotime($responseData['logFileMetaData']['lastModified']),
			);			
		}
		
		try {
			$logLines = $logReader->readLog($logName, $linesToRead, $filter);						
		} catch (\Exception $e) {
			Log::err("Log Reading Failed: ({$e->getCode()}) {$e->getMessage()}");
			if (strstr($e->getMessage(), 'No such file or directory')) {
				throw new Exception(_t('Log Reading Failed: file not found'), Exception::LOG_FILE_NOT_READABLE, $e);
			} else {
				throw new Exception(_t('Log Reading Failed: please verify your log file permissions'), Exception::LOG_FILE_NOT_READABLE, $e);
			}
		}		

		return array(
				'logLines'		=> $logLines,
				'lineNumber'	=> $logReader->getLineNumber(),
				'fileSize'		=> $logReader->getFileSize(),
				'lastModified'	=> $logReader->getLastModified(),
		);
	}
	
	protected function isRemoteServer($serverId) {
		if (! $serverId) return false;
		
		$edition = new Edition();
		return $serverId != $edition->getServerId();
	}

}
