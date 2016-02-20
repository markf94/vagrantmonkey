<?php

namespace PageCache;

use PageCache\Model\RuleCondition;
use PageCache\Model\SplitByCondition;
use ZendServer\Log\Log;
use ZendServer\Exception;


class Rule {
	
	const MATCH_EXACT = "exactMatch";
	const MATCH_REGEX_SENSITIVE = "regexMatch";
	const MATCH_REGEX_INSENSITIVE = "regexIMatch";
	
	const MATCH_EXACT_XML = "exact";
	const MATCH_REGEX_SENSITIVE_XML = "regex_match";
	const MATCH_REGEX_INSENSITIVE_XML = "regex_match_i";
	
		
	protected $xml;
	protected $id;
	protected $appId;
	protected $name;
	protected $urlScheme;
	protected $urlHost;
	protected $urlPath;
	protected $appName;
	protected $matchType;
	protected $conditionsType;
	protected $conditions;
	protected $splitBy;
	protected $lifetime;
	protected $compress;
		
	
	/**
	 * @return the $urlScheme
	 */
	public function getUrlScheme() {
		return $this->urlScheme;
	}

	/**
	 * @param field_type $urlScheme
	 */
	public function setUrlScheme($urlScheme) {
		
		$xml = simplexml_load_string($this->xml);
		if ($xml) {
			$xml->attributes()->schema = $urlScheme;
			$this->xml = strstr($xml->asXML(), "<url");
		}
				
		$this->urlScheme = $urlScheme;
	}

	public function __construct() {
		$this->conditions = array();
		$this->splitBy = array();
		$this->appName = "";
		$this->appId = -1;
	}
	
	/**
	 * @return the $splitBy
	 */
	public function getSplitBy() {
		return $this->splitBy;
	}

	/**
	 * @param field_type $splitBy
	 */
	public function setSplitBy($splitBy) {
		$this->splitBy = $splitBy;
	}

	public function getSplitByArray() {
		$splitByArray = array();
		foreach($this->splitBy as $key => $splitBy) {
			$splitByArray[$key] = $splitBy->toArray();
		}
		return $splitByArray;
	}
	
	/**
	 * @return string $matchType
	 */
	public function getMatchType() {
		return $this->matchType;
	}

	/**
	 * @param string $matchType
	 */
	public function setMatchType($matchType) {
		$this->matchType = $matchType;
	}

	static function cleanupElement($element) {
		if (strpos($element, "[") === 0) {
			$close = strpos($element, "]");
			if ($close === strlen($element) - 1) {
				return substr($element, 1, strlen($element) - 2);
			}
		}		

		return $element;
	}
	
	
	public function loadXml($xml, $appId) {
		if(is_object($xml) && (get_class($xml) == 'SimpleXMLElement')){
			$this->xml = $xml->asXML();
			$el = $xml;
		} else {
			$this->xml = $xml;
			$el = simplexml_load_string($xml);
		}
		
		if (!$el) {
			throw new \ZendServer\Exception("Failed to load xml contents of page cache rule");
		}
		$this->setLifetime((int) $el['lifetime']);
		$this->setAppId((int) $appId);
		$this->setCompress(((string)$el['compress'] ==="true") ? true : false);
		$this->setUrlScheme((string) $el['schema']);
		
		$this->setUrlHost( (string) $el['host']);
		
		$this->setUrlPath((string)$el['path']);
		switch ((string)$el['match_type']) {
			case "exact":
				$this->setMatchType(self::MATCH_EXACT);
				break;
			case 'regex_match':
				$this->setMatchType(self::MATCH_REGEX_SENSITIVE);
				break;
			default:
				$this->setMatchType(self::MATCH_REGEX_INSENSITIVE);
				break;
		};
		
		$this->setName((string) $el->name);
		
		$conds = array();
		
		if ($el->conditions_OR_block) {
			$orBlock = $el->conditions_OR_block;
			if (count($orBlock->conditions_AND_block) > 1) {
				$this->setConditionsType("or");
				foreach ($orBlock->conditions_AND_block as $id => $andBlock) {
					$ruleCond = new RuleCondition();
					preg_match('#(^[^\[]+)(.*)#', (string) $andBlock->condition['global'], $matches);
					$ruleCond->setSuperGlobal($matches[1]);
					$ruleCond->setElement($matches[2]);
					$ruleCond->setMatchType((string) $andBlock->condition['type']);
					$ruleCond->setValue((string) $andBlock->condition['value']);
					$conds[] = $ruleCond;
				}	
				
				$this->setConditions($conds);
			} else {
				$this->setConditionsType("and");
				foreach ($orBlock->conditions_AND_block->condition as $id => $condition) {
					$ruleCond = new RuleCondition();
					preg_match('#(^[^\[]+)(.*)#', (string)$condition['global'], $matches);
					$ruleCond->setSuperGlobal($matches[1]);
					$ruleCond->setElement($matches[2]);
					$ruleCond->setMatchType((string) $condition['type']);
					$ruleCond->setValue((string) $condition['value']);
					$conds[] = $ruleCond;
				}
				
				$this->setConditions($conds);
			}
		}
		
		$conds = array();
		
		if (isset($el->split_by)) {
			$splitByBlock = $el->split_by;
			foreach ($splitByBlock->split_condition as $id => $condition) {
				$ruleCond = new SplitByCondition();
				preg_match('#(^[^\[]+)(.*)#', (string)$condition['global'], $matches);
				$ruleCond->setSuperGlobal($matches[1]);
				if (isset($matches[2])) {
					$ruleCond->setElement($matches[2]);
				}
				$conds[] = $ruleCond;		
			}
			$this->setSplitBy($conds);
		}
	}
	
	public function loadArray($ruleArr, $appId) {
		
		//Log::Debug("PageCache loading array" . var_export($ruleArr, true));
		
		$this->xml = simplexml_load_string("<url/>");
		
		$this->setAppId((int) $appId);
		
		$this->setLifetime((int) $ruleArr['lifetime']);
		$this->xml->addAttribute('lifetime',(int)  $ruleArr['lifetime']);
		
		$this->setMatchType($ruleArr['matchType']);
		switch ($ruleArr['matchType']) {
			case self::MATCH_EXACT:
				$str = self::MATCH_EXACT_XML;
				break;
			case self::MATCH_REGEX_SENSITIVE:
				$str = self::MATCH_REGEX_SENSITIVE_XML;
				break;
			default:
				$str = self::MATCH_REGEX_INSENSITIVE_XML;
				break;
		};
		$this->xml->addAttribute('match_type', $str);
		
		$this->setUrlScheme($ruleArr['urlScheme']);
		$this->xml->addAttribute('schema', $ruleArr['urlScheme']);
		
		$host = $ruleArr['urlHost'];
		$this->setUrlHost($ruleArr['urlHost']);
		$this->xml->addAttribute('host', $host);
				
		$this->setUrlPath($ruleArr['urlPath']);
		$path = trim($ruleArr['urlPath'], "/");
		$this->xml->addAttribute('path', $path);
		
		$this->setId((int) $ruleArr['ruleId']);
		$this->xml->addAttribute("id", ""); // database row id will be used instead
		
		$this->setCompress($ruleArr['compress']==="TRUE" ? true : false);
		$this->xml->addAttribute('compress', strtoupper($ruleArr['compress'])==="TRUE"?"true":"false");
		
		$this->xml->addAttribute('app_id', $appId);
		
		$this->setName($ruleArr['name']);
		
		// create the name in the XML in CDATA
		$this->xml->name = NULL;
		$nameNode = dom_import_simplexml($this->xml->name);
		$ownerDoc = $nameNode->ownerDocument;
		$nameNode->appendChild($ownerDoc->createCDATASection($ruleArr['name']));		
			
		$this->setConditionsType($ruleArr['conditionsType']);
		
		$conds = array();
		if ($ruleArr['conditions']) {
			$mainOrBlock = $this->xml->addChild("conditions_OR_block");
			if ($ruleArr['conditionsType'] == "and") {
				$andBlock = $mainOrBlock->addChild("conditions_AND_block");
				foreach ($ruleArr['conditions'] as $cond) {
					$ruleCond = new RuleCondition();
					$ruleCond->setSuperGlobal($cond['global']);
					$ruleCond->setElement($cond['element']);
					$ruleCond->setMatchType($cond['type']);
					$ruleCond->setValue($cond['value']);
					$conds[] = $ruleCond;
						
					$cond = $andBlock->addChild("condition");
					$cond->addAttribute("global", $ruleCond->getSuperGlobal() . $ruleCond->getElement());
					$cond->addAttribute("type", $ruleCond->getMatchType());
					$cond->addAttribute("value", $ruleCond->getValue());
				}
				$this->setConditions($conds);
			} else {
				$conds = array();
				foreach ($ruleArr['conditions'] as $cond) {
					$ruleCond = new RuleCondition();
					$ruleCond->setSuperGlobal($cond['global']);
					$ruleCond->setElement($cond['element']);
					$ruleCond->setMatchType($cond['type']);
					$ruleCond->setValue($cond['value']);
					$conds[] = $ruleCond;
					
					$andBlock = $mainOrBlock->addChild("conditions_AND_block");
					$cond = $andBlock->addChild("condition");
					$cond->addAttribute("global", $ruleCond->getSuperGlobal() . $ruleCond->getElement());
					$cond->addAttribute("type", $ruleCond->getMatchType());
					$cond->addAttribute("value", $ruleCond->getValue());
				}
				
				$this->setConditions($conds);
			}
		}
		
		if ($ruleArr['splitBy']) {
			$splitBy = array();
			$splitByBlock = $this->xml->addChild("split_by");
			foreach ($ruleArr['splitBy'] as $cond) {
				$ruleCond = new SplitByCondition();
				$ruleCond->setSuperGlobal($cond['global']);
				$ruleCond->setElement($cond['element']);
				$splitBy[] = $ruleCond;
				$singleSplit = $splitByBlock->addChild("split_condition");
				if (strpos($cond['element'], '[') === 0) {
					$singleSplit->addAttribute("global", $cond['global'] . $cond['element']);
				} else {
					$singleSplit->addAttribute("global", $cond['global']. '[' . $cond['element'] . ']');
				}
			}
			$this->setSplitBy($splitBy);		
		}

		Log::debug("Validating rule xml:" . var_export($this->xml->asXML(), true));
		$dom=new \DOMDocument;
		$dom->loadXML($this->xml->asXML());
		if (!$dom->schemaValidate(getCfgVar("zend.conf_dir") . "/pagecache_rules_schema.xsd")) {
			throw new \ZendServer\Exception(_t("Rule failed validation"), \WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 * 
	 * @param string $type
	 */
	public function setConditionsType($type) {
		$this->conditionsType = $type;
		
	}
	
	/**
	 *
	 * @param ConditionsSet $conds
	 */
	public function setConditions($conds) {
		$this->conditions = $conds;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getConditionsType() {
		return $this->conditionsType;
	
	}
	
	/**
	 *
	 * @return ConditionsSet
	 */
	public function getConditions() {
		return $this->conditions;
	}
	
	/**
	 *
	 * @return ConditionsSet
	 */
	public function getConditionsArray() {
		$conditionsArray = array();
		foreach($this->conditions as $key =>$condition) {
			$conditionsArray[$key] = $condition->toArray();
		}
		return $conditionsArray;
	}
	
	/**
	 * @return the $appName
	 */
	public function getAppName() {
		return $this->appName;
	}

	/**
	 * @param field_type $appName
	 */
	public function setAppName($appName) {
		$this->appName = $appName;
	}

	/**
	 * @return integer $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param integer $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return \SimpleXmlElement
	 */
	public function getXml() {
		return $this->xml;
	}

	/**
	 * @return integer $appId
	 */
	public function getAppId() {
		return $this->appId;
	}

	/**
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return integer $lifetime
	 */
	public function getLifetime() {
		return $this->lifetime;
	}

	
	/**
	 * @return boolean $compress
	 */
	public function getCompress() {
		return $this->compress;
	}

	
	/**
	 * @param integer $appId
	 */
	public function setAppId($appId) {
		$this->appId = $appId;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param integer $lifetime
	 */
	public function setLifetime($lifetime) {
		$this->lifetime = $lifetime;
	}

	/**
	 * @param string $url
	 */
	public function setUrlHost($host) {
		
		$xml = simplexml_load_string($this->xml);
		if ($xml) {
			$xml->attributes()->host = $host;
			$this->xml = strstr($xml->asXML(), "<url");
		}
				
		$this->urlHost = $host;
	}

	/**
	 * @return the $urlHost
	 */
	public function getUrlHost() {
		return $this->urlHost;
	}

	/**
	 * @return the $urlPath
	 */
	public function getUrlPath() {
		return $this->urlPath;
	}

	/**
	 * @param field_type $urlPath
	 */
	public function setUrlPath($urlPath) {
		
		$xml = simplexml_load_string($this->xml);
		if ($xml) {
			$xml->attributes()->path = $urlPath;
			$this->xml = strstr($xml->asXML(), "<url");
		}
			
		$this->urlPath = $urlPath;
	}

	/**
	 * @param boolean $compress
	 */
	public function setCompress($compress) {
		$this->compress = $compress;
	}

	public function getUrl() {
		return $this->urlScheme . "://" . $this->urlHost . "/" . $this->urlPath;
	}
	
	public function getXmlContents() {
		if($this->xml instanceof \SimpleXMLElement){
			return strstr($this->xml->asXML(), "<url");
		}
		return strstr($this->xml, "<url");
	}
	
}

?>