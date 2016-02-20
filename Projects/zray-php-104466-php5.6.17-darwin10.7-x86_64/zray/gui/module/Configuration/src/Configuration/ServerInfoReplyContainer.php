<?php
namespace Configuration;
use Zend\Json\Json;
use ZendServer\Exception as ZSException;
use ZendServer\Log\Log;

class ServerInfoReplyContainer extends ReplyContainer {
	
	private $data;
	
	/**
	 * @param array $reply
	 */
	public function __construct(array $reply) {
		parent::__construct($reply);
		if (parent::hasReply()) {
			$this->data = Json::decode(gzuncompress(parent::getReply()), Json::TYPE_ARRAY);
			$this->data['phpinfo'] = str_replace('/UserServer/zsd_php_info.php', '/ZendServer/index.php', $this->data['phpinfo']);
		} else {
			Log::notice('No server info available, try again later');
			$this->data = array(
					'zsversion' => '',
					'zfversion' => '',
					'phpinfo' => '',
					'zf2version' => '',
					'phpversion' => '',
			);
		}
	}
	
	/**
	 * @return array
	 */
	public function toArray() {
		return $this->data;	
	}
	
	/**
	 * @return string
	 */
	public function getPhpinfo() {
		return $this->data['phpinfo'];
	}
	
	/**
	 * @return string
	 */
	public function getZSVersion() {
		return $this->data['zsversion'];
	}
	
	/**
	 * @return string
	 */
	public function getZFVersion() {
		return $this->data['zfversion'];
	}

	/**
	 * @return string
	 */
	public function getZF2Version() {
		return $this->data['zf2version'];
	}
	
	/**
	 * @return string
	 */
	public function getPHPVersion() {
		return $this->data['phpversion'];
	}
	
}