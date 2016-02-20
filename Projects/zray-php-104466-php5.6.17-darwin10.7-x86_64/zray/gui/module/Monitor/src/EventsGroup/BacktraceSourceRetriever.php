<?php

namespace EventsGroup;

use Zend\Http\Headers;

use Zend\Json\Exception\RuntimeException;

use Zend\Json\Json;

use Zend\Stdlib\Parameters;

use Zend\Http\Client;

use Zend\Uri\UriFactory;

use Application\Module;

use WebAPI\WebapiRequestCreatorInterface;

use Zend\Uri\Uri;

use WebAPI\Db\Mapper;

use WebAPI\SignatureGenerator;

use ZendServer\Exception;

use ZendServer\Edition;

use ZendServer\Log\Log;

use ZendServer\FS\FS;
use Servers\Db\ServersAwareInterface;

class BacktraceSourceRetriever implements WebapiRequestCreatorInterface, ServersAwareInterface {
	
	/**
	 * @var \EventsGroup\Db\Mapper
	 */
	private $eventsDbMapper;
	
	/**
	 * @var \WebAPI\Db\Mapper
	 */
	private $webapiKeyMapper;
	
	/**
	 * @var \Servers\Db\Mapper
	 */
	private $serversMapper;
	
	public function __construct($eventsDbMapper, $webapiKeyMapper, $serversMapper) {
		
		$this->eventsDbMapper = $eventsDbMapper;
		$this->webapiKeyMapper = $webapiKeyMapper;
		$this->serversMapper = $serversMapper;
		
	}
		
	
	/**
	 * @param integer $groupId
	 * @param integer $backtraceNum
	 * @return string
	 * @throws \ZendServer\Exception
	 */
	public function getHighlightedSource($groupId, $backtraceNum) {
				
		$event = $this->eventsDbMapper->getEventGroupData($groupId);
		
		$backtrace = $event->getBacktrace();
		$filename = $backtrace[$backtraceNum][ZM_DATA_BACKTRACE_FILE];
		if (FS::fileExists($filename)) {
			return highlight_file($filename, true);
		} else {
			$edition = new Edition();
			if ($event->getServerId() == $edition->getServerId()) {
				throw new Exception(_t("'%s' not found for highlighting",array($filename)));
			}
			$server = $this->getServersMapper()->findServerById($event->getServerId());
			if (! $server) {
				throw new Exception(_t("Server ID %s was not found",array($event->getServerId())));
			}
			
			$baseUrl = Module::config('baseUrl');
				
			$uri = UriFactory::factory("http://{$server->getNodeIp()}");
			$uri->setPath("{$baseUrl}/Api/monitorGetBacktraceFile");
			$uri->setPort(Module::config('installation', 'defaultPort'));
			
			$request = new \Zend\Http\Request();
			$request->setMethod(\Zend\Http\Request::METHOD_GET);
			$request->setUri($uri);
			$request->setQuery(new Parameters(array(
				'eventsGroupId' => $groupId,
				'backtraceNum' => $backtraceNum
			)));
			$response = $this->propagateWebapiRequest($request);
			
			try {
				$payload = Json::decode($response->getBody(), Json::TYPE_ARRAY);
			} catch (RuntimeException $e) {
				throw new Exception(_t("JSON parsing of a remote response failed: %s", array($e->getMessage())), null, $e);
			}
			
			if (isset($payload['responseData']) && isset($payload['responseData']['sourcePayload'])) {
				return base64_decode($payload['responseData']['sourcePayload']);
			} else {
				throw new Exception(_t("JSON parsing of a remote response failed: Expected sourcePayload structure not found"));
			}
		}
	}
	
	/**
	 * @param integer $groupId
	 * @param integer $backtraceNum
	 * @return integer
	 */
	public function getHighlightedLine($groupId, $backtraceNum) {
		$event = $this->eventsDbMapper->getEventGroupsData(array($groupId))->current();
		$backtrace = $event->getBacktrace();
		
		return $backtrace[$backtraceNum][ZM_DATA_BACKTRACE_LINE_NUMBER];
	}
	
		
	/* (non-PHPdoc)
	 * @see \WebAPI\WebapiRequestCreatorInterface::setWebapiKeyMapper()
	 */
	public function setWebapiKeyMapper(Mapper $keyMapper) {
		$this->webapiKeyMapper = $keyMapper;
	}

	public function setEventsDbMapper($mapper) {
		$this->eventsDbMapper = $mapper;
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
	 * @param \Zend\Http\Request $request
	 * @param Uri $uri
	 * @param string $traceId
	 * @throws \ZendServer\Exception
	 * @return \Zend\Http\Response
	 */
	private function propagateWebapiRequest(\Zend\Http\Request $request) {
	
		$key = $this->webapiKeyMapper->findKeyByName(Mapper::SYSTEM_KEY_NAME);
		if (! $key) {
			throw new Exception(_t("Failed to delete WebAPI key to connect to cluster member"));
		}
	
		$userAgent = 'Zend Server AmfRetriever';
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
		
		$uri = UriFactory::factory($request->getUri());
		
		$signatureGenerator = new SignatureGenerator();
		$signature = $signatureGenerator
			->setHost("{$uri->getHost()}:{$uri->getPort()}")
			->setUserAgent($userAgent)
			->setDate($date)
			->setRequestUri($uri->getPath())
			->generate($key->getHash());

		$headers = new Headers();
		$headers->addHeaders(array(
				'Accept' => 'application/vnd.zend.serverapi+json;version=1.3',
				'User-Agent'        => $userAgent,
				'Date'              => $date,
				'X-Zend-Signature'  => Mapper::SYSTEM_KEY_NAME . ';' . $signature,
		));
		$request->setHeaders($headers);
	
		try {
			Log::debug("Propagate webapi trace action to {$uri}");
			$httpClient = new Client();
			$response = $httpClient->send($request);
		} catch (\Zend\Http\Exception\ExceptionInterface $e) {
			throw new Exception(_t("HTTP WebAPI request to %s failed: %s", array($uri, $e->getMessage())), null, $e);
		}
	
		return $response;
	}
	
}

