<?php
namespace Deployment\Validator;

use Zend\Validator\AbstractValidator,
\Application\Module;
use Zend\Uri\UriFactory;
use ZendServer\Log\Log;

class VirtualHostPort extends AbstractValidator {
	
	const INVALID		= 'invalid';
	const INVALID_PORT	= 'invalidPort';
	
	/**
     * @var array
     */
    protected $messageTemplates = array();
    /**
     * @var array
     */
    private $blockedPorts = array();
    /**
     * @var array
     */
    private $directives = array();
    
	public function __construct($options = null) {
		
	    // @todo: Get real directives
	    $this->directives = array();
		
		$this->blockedPorts = array(
			'10063', '20080', '10137', '10001'
		);
		
		$this->blockedPorts[] = Module::config('installation', 'defaultPort');
		$this->blockedPorts[] = Module::config('installation', 'securedPort');
		$this->blockedPorts[] = Module::config('installation', 'enginePort');
		
		$this->blockedPorts[] = $this->getDirectiveFileValue('zend_sc.network.tcp_port_remote', '10060');
		$this->blockedPorts[] = $this->getDirectiveFileValue('zend_sc.network.tcp_port_local', '10062');
		$this->blockedPorts[] = $this->getDirectiveFileValue('zend_sc.ha.udp_port', '10070');
		$this->blockedPorts[] = $this->getDirectiveFileValue('zend_sc.message_server_port', '10061');
		$this->blockedPorts[] = $this->getDirectiveFileValue('zend_deployment.database.port', '3306');
		$this->blockedPorts[] = $this->getDirectiveFileValue('zend_monitor.database.port', '3306');
		
		$jobqueue = $this->getDirectiveFileValue('zend_jobqueue.default_binding', '10085');
		if (! is_numeric($jobqueue)) {
			try {
				$uri = UriFactory::factory($jobqueue);
				if ($uri->getScheme() == 'unix') {
					$jobqueue = '10085';
				} else {
				    $jobqueue = (string)$uri->getPort();
				}
			} catch (\Zend\URI\Exception $e) {
				$jobqueue = '10085';
			}
		}
		
		if (! empty($jobqueue)) {
			$this->blockedPorts[] = $jobqueue;
		}
		
		// remove all empty ports to be safe
		foreach ($this->blockedPorts as $key => $blockedPort) {
			if (empty($blockedPort)) {
				unset($this->blockedPorts[$key]);
			}
		}
		
		$this->blockedPorts = array_values(array_unique($this->blockedPorts));
		
		$this->messageTemplates = array(
			self::INVALID		=> 'The provided virtual host is invalid',
			self::INVALID_PORT	=> 'The provided port is reserved for use by Zend Server. The following ports are reserved: ' . implode(', ', $this->blockedPorts),
		);
		
		parent::__construct($options);
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\Validator::isValid()
	 */
	public function isValid($value) {
		if (! is_numeric($value)) {
			/// not a port number, try to retrieve port from URI
			$uri = UriFactory::factory($value);
			$value = $uri->getPort();
		}
		
		$this->setValue($value);

		if (in_array($value, $this->blockedPorts)) {
			$this->error(self::INVALID_PORT);
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param string $name
	 * @param string $default
	 */
	private function getDirectiveFileValue($name, $default) {
		
		if (isset($this->directives[$name])) {
			return $this->directives[$name]->getFileValue();
		} else {
			return $default;
		}
		
	}
	
	/**
	 * @return array
	 */
	public function getBlockedPorts() {
		return $this->blockedPorts;
	}

	
}
