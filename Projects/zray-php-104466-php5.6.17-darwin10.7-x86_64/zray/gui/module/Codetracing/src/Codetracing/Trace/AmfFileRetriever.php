<?php

namespace Codetracing\Trace;

use Zend\Uri\Uri;

use WebAPI\SignatureGenerator;

use Zend\Json\Json;

use Zend\Uri\UriFactory;

use WebAPI\Db\Mapper;

use WebAPI\WebapiRequestCreatorInterface;

use ZendServer\Edition;

use ZendServer\Log\Log;

use Application\Module;

use ZendServer\Exception;
use ZendServer\FS\FS;
use Servers\Db\ServersAwareInterface;

class AmfFileRetriever implements WebapiRequestCreatorInterface, ServersAwareInterface {
	/**
	 * @var \Servers\Db\Mapper
	 */
	private $serversMapper;
	
	/**
	 * @var \Codetracing\Dump\Wrapper
	 */
	private $wrapper;
	
	/**
	 * @var \WebAPI\Db\Mapper
	 */
	private $webapiKeyMapper;
	
	/**
	 * @param string $traceFilePath
	 * @throws \ZendServer\Exception
	 */
	public function deleteTrace($traceFilePath) {
		$traceIdInfo = $this->extractTraceIdFromPath($traceFilePath);
		$serverId = $traceIdInfo['serverId'];		 
		$edition = new Edition();		
		if ($edition->getServerId() == $serverId) {// if we are in the same server as of the code-trace itself, just delete the local files
			return $this->deleteLocalTrace($traceFilePath);
		}
		 
		$traceId = $traceIdInfo[0];
		$serverSet = $this->getServersMapper()->findServersById(array($serverId));
		 
		if (0 == $serverSet->count()) {
			throw new Exception(_t("Server id %s was not found", array($serverId)));		
		}
		 
		$server = $serverSet->current(); /* @var $server \Servers\Container */
		$baseUrl = Module::config('baseUrl');
		 
		$uri = UriFactory::factory("http://{$server->getNodeIp()}");
		$uri->setPath("{$baseUrl}/Api/codetracingDelete");
		$uri->setPort(Module::config('installation', 'defaultPort'));
		$uri->setScheme('http');
		 
		
		$http = new \Zend\Http\Client();
		$http->setMethod('post');
		 
		$response = $this->propagateWebapiRequest($http, $uri, $traceId);
		
		if ($response->getStatusCode() != 200) {
			try {
				$responseDecoded = Json::decode($response->getBody(),Json::TYPE_ARRAY);
				if (
						isset($responseDecoded['errorData'])
						&& isset($responseDecoded['errorData']['errorMessage'])) {
			 		
					$errorMessage = $responseDecoded['errorData']['errorMessage'];
				} else {
					$errorMessage = 'Bad response format';
				}
			} catch (\Exception $e) {
				$errorMessage = "Bad response format, not a json ({$e->getMessage()})";
			}
			throw new Exception(_t("Could not retrieve code trace file from %s: %s ",array ($server->getNodeIp(),$errorMessage)));
		}
	}
	
	protected function deleteLocalTrace($traceFilePath) {
		try {
			FS::getFileObject($traceFilePath, 'r');
			FS::unlink($traceFilePath);
		} catch (\Exception $e) {
			Log::warn("Unable to locate binary trace file '{$traceFilePath}'");
		}
		try {
			$traceFilePath = "$traceFilePath.amf";
			FS::getFileObject($traceFilePath, 'r');
			FS::unlink($traceFilePath);
		} catch (\Exception $e) {
			Log::info("Unable to locate amf trace file '{$traceFilePath}'"); // this may very well be the case
		}	
	}
	
	/**
	 * @param string $traceFilePath
	 * @return \SplFileObject
	 * @throws \ZendServer\Exception
	 */
	public function retrieveAmf($traceFilePath) {		
		try {
			$tracefileObject = FS::getFileObject("{$traceFilePath}.amf", 'r');
		} catch (\Exception $e) {
			$tracefileObject = $this->procureAmfData($traceFilePath);
		}
		
		return $tracefileObject;
	}
	
	/**
	 * @return \Codetracing\Dump\Wrapper $wrapper
	 */
	public function getWrapper() {
		return $this->wrapper;
	}

	/**
	 * @param \Codetracing\Dump\Wrapper $wrapper
	 * @return \Codetracing\Trace\AmfFileRetriever
	 */
	public function setWrapper($wrapper) {
		$this->wrapper = $wrapper;
		return $this;
	}

	/**
	 * @return \Servers\Db\Mapper $serversMapper
	 */
	public function getServersMapper() {
		return $this->serversMapper;
	}
	
	/**
	 * @param \Servers\Db\Mapper $serversMapper
	 * @return \Codetracing\Trace\AmfFileRetrieverCM
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
		return $this;
	}

	/**
	 * @param string $traceFilePath
	 * @return SplFileObject
	 */
	protected function procureAmfData($traceFilePath) {
		$traceIdInfo = $this->extractTraceIdFromPath($traceFilePath);
		$serverId = $traceIdInfo['serverId'];
		
		try {
			$amfDetailsPath = $this->getWrapper()->getDumpFileAmfPath($traceFilePath);
			return FS::getFileObject($amfDetailsPath, 'r');
		} catch (\Exception $e) {
			/// if we are in the same server as of the code-trace itself, end the process
			$edition = new Edition();
			if ($edition->getServerId() == $serverId) {
				Log::warn("Could not write AMF file to {$traceFilePath}, check file permissions");
				throw new Exception(_t("Could not write AMF file to {$traceFilePath}, check file permissions"), Exception::WARNING, $e);
			}
			Log::info("Could not retrieve file {$traceFilePath} locally, attempting to retrieve from cluster");
		}
		
		
		
		$traceId = $traceIdInfo[0];
		$serverSet = $this->getServersMapper()->findServersById(array($serverId));
		
		if (0 == $serverSet->count()) {
			throw new Exception(_t("Server ID %s was not found",array($serverId)));
		}
		
		$server = $serverSet->current(); /* @var $server \Servers\Container */
		$baseUrl = Module::config('baseUrl');
		 
		$uri = UriFactory::factory("http://{$server->getNodeIp()}");
		$uri->setPath("{$baseUrl}/Api/codetracingDownloadTraceFile");
		$uri->setPort(Module::config('installation', 'defaultPort'));
		$uri->setScheme('http');
		 
		$http = new \Zend\Http\Client();
		$response = $this->propagateWebapiRequest($http, $uri, $traceId);
		
		if ($response->getStatusCode() != 200) {
			try {
				$responseDecoded = Json::decode($response->getBody(),Json::TYPE_ARRAY);
				if (
						isset($responseDecoded['errorData'])
						&& isset($responseDecoded['errorData']['errorMessage'])) {
						
					$errorMessage = $responseDecoded['errorData']['errorMessage'];
				} else {
					$errorMessage = 'Bad response format';
				}
			} catch (\Exception $e) {
				$errorMessage = "Bad response format, not a json ({$e->getMessage()})";
			}
			throw new Exception(_t("Could not retrieve code trace file from %s: %s ",array($server->getNodeIp(),$errorMessage)));
		}
		
		try {
			/// we received an amf file, not the original binary
			/// open for writing and reading - we return this file object
			$tracefileObject = FS::getFileObject("$traceFilePath.amf", 'w+');
			$tracefileObject->fwrite($response->getBody());
			$tracefileObject->rewind();
		} catch (\Exception $e) {
			throw new Exception(_t("Could not cache AMF information in %s",array($traceFilePath)));
		}
		
		return $tracefileObject;
	}
	

	/**
	 * @param string $traceFilePath
	 * @throws \ZendServer\Exception
	 * @return array
	 */
	public static function extractTraceIdFromPath($traceFilePath) {
		if (0 < preg_match('#(?<serverId>\d+)\.(?<process>\d+)\.(?<counter>\d+)#', $traceFilePath, $matches)) {
			return $matches;
		} else {
			throw new Exception(_t("Trace ID not found in path '%s'", array($traceFilePath)));
		}
	}
	

	/* (non-PHPdoc)
	 * @see \WebAPI\WebapiRequestCreatorInterface::setWebapiKeyMapper()
	*/
	public function setWebapiKeyMapper(Mapper $keyMapper) {
		$this->webapiKeyMapper = $keyMapper;
	}
	
	/**
	 * @param \Zend\Http\Client $httpClient
	 * @param Uri $uri
	 * @param string $traceId
	 * @throws \ZendServer\Exception
	 * @return \Zend\Http\Response
	 */
	private function propagateWebapiRequest(\Zend\Http\Client $httpClient, Uri $uri, $traceId) {
	
		$httpClient->setUri($uri);
	
		$key = $this->webapiKeyMapper->findKeyByName(Mapper::SYSTEM_KEY_NAME);
		if (! $key) {
			throw new Exception(_t("Could not retrieve the webapi key to connect to cluster member"));
		}
	
		$userAgent = 'Zend Server AmfRetriever';
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
			
		$signatureGenerator = new SignatureGenerator();
		$signature = $signatureGenerator
		->setHost("{$uri->getHost()}:{$uri->getPort()}")
		->setUserAgent($userAgent)
		->setDate($date)
		->setRequestUri($uri->getPath())
		->generate($key->getHash());
			
		$httpClient->setHeaders(array(
				'Accept' => 'application/vnd.zend.serverapi+json;version=1.3',
				'User-Agent'        => $userAgent,
				'Date'              => $date,
				'X-Zend-Signature'  => Mapper::SYSTEM_KEY_NAME . ';' . $signature,
		));
	
		if (strtolower($httpClient->getMethod()) == 'post') {
			$httpClient->setParameterPost(array('traceFile' => $traceId));
		} else {
			$httpClient->setParameterGet(array('traceFile' => $traceId));
		}
	
		try {
			Log::debug("Propagate webapi trace action to {$uri}");
			$response = $httpClient->send();
		} catch (\Zend\Http\Exception\ExceptionInterface $e) {
			throw new Exception(_t("HTTP webapi request to %s failed: %s", array($uri, $e->getMessage())), null, $e);
		}
	
		return $response;
	}
	
}

