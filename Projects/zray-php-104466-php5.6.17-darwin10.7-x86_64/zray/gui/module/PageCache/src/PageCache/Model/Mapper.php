<?php
namespace PageCache\Model;
use Deployment\IdentityApplicationsAwareInterface;
use Deployment\IdentityFilterException;
use Deployment\IdentityFilterInterface;
use PageCache\Model\RulesSet;

use Zend\Db\Sql;
use Configuration\MapperAbstract;

use ZendServer\Exception;
use PageCache\Model\Rule;
use ZendServer\Log\Log;

class Mapper extends MapperAbstract implements IdentityApplicationsAwareInterface {

	const RULE_ID = "RULE_ID";
	const RULE_NAME = "NAME";
	const RULE_APP_ID = "APP_ID";
	const RULE_URL = "URL";
	const RULE_META_XML = "META_XML";

    /**
     * @var IdentityFilterInterface
     */
    private $identityFilter;
	
	/**
	 * 
	 * @param \PageCache\Rule $rule
     * @throws Exception
	 */
	public function createRule($rule) {
		
		if (!$rule->getXml()) {
			throw new \ZendServer\Exception("Cannot create a rule without a valid xml");	
		}

        if (! in_array($rule->getAppId(), $this->filterApplications(array()))) {
            throw new Exception(_t('You are not allowed to create a rule for this application'), Exception::ERROR); 
        }
		
		if (!$this->getTableGateway()->insert(array(
					self::RULE_ID => NULL
					, self::RULE_APP_ID => $rule->getAppId()
					, self::RULE_NAME => $rule->getName()
				    , self::RULE_URL => $rule->getUrl()
					, self::RULE_META_XML => $rule->getXmlContents()				
				))) {
			
			return -1;
		}
		
		$rule->setId($this->getTableGateway()->getLastInsertValue());
		
		return $rule->getId();
	}
	
	/**
	 * Creates or updates a rule (depending if ruleId is provided)
	 *
	 * @param \PageCache\Rule $rule
     * @throws Exception
	 * @return integer ruleId
	 */
	function saveRule($rule) {

		if ($rule->getId() == -1) {
			$this->createRule($rule);
		} else {
			$this->updateRule($rule);
		}
		
		return $rule->getId();
	}
	
	
	/**
	 *
	 * @param \PageCache\Rule $rule
     * @throws Exception
	 * @return integer ruleId
	 */
	protected function updateRule($rule) {

        if (! in_array($rule->getAppId(), $this->filterApplications(array()))) {
            throw new Exception(_t('You are not allowed to change a rule for this application'), Exception::ERROR); 
        }

		if (!$rule->getXml()) {
			throw new \ZendServer\Exception("Cannot update a rule without a valid xml");
		}
		
		
		$changed = $this->getTableGateway()->update(
				array( self::RULE_NAME => $rule->getName()
					, self::RULE_APP_ID => $rule->getAppId()
					, self::RULE_URL => $rule->getUrl()
					, self::RULE_META_XML => $rule->getXmlContents()
				), 
				array(self::RULE_ID => $rule->getId())
		);
		
		if ($changed) {
			return $rule->getId();
		} else {
			return -1;
		}
	}
	
	static public function sortRules($rule1, $rule2) {
		
		return strcasecmp($rule1->getName(), $rule2->getName());
		
	}

	
	/**
	 * 
	 * @param array $ids
	 * @param array $apps
	 * @param string $nameStr - substring, freeText for Search mechanizm
	 * @param string $nameFull - full string
	 * @return multitype:\PageCache\Rule
	 */
	public function getRules($ids = array(), $apps = array(), $nameStr = '', $nameFull = '') {
		
		//Log::debug("getRules with " . var_export($ids, true) . var_export($apps, true) . $nameStr . $nameFull);

        if (0 < count($apps) && (array_search(-1, $apps) === false)) {
            $this->identityFilter->setAddGlobalAppId(false);
        }
        try {
            $apps = $this->identityFilter->filterAppIds($apps,true);
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return array();
            }
        }

		$sql = new Sql\Select();
		$sql->from($this->getTableGateway()->getTable());
		
		$where = array();
		if ($ids) {
			$sql->where->in(self::RULE_ID, $ids);
		}
		if ($nameStr) {
			$sql->where->like(self::RULE_NAME, "%{$nameStr}%");
		}
		if ($nameFull) {
			$sql->where(array(self::RULE_NAME => $nameFull));
		}
		if ($apps) {
			$sql->where->in(self::RULE_APP_ID, $apps);
		}		
			
		$rows = $this->getTableGateway()->selectWith($sql)->toArray();
		$list = array();
		foreach ($rows as $row) {

			$rule = new \PageCache\Rule();
			$rule->loadXml($row['META_XML'], $row['APP_ID']);
			$rule->setId($row['RULE_ID']);
			$list[] = $rule;						
		}

		
		usort($list, "PageCache\Model\Mapper::sortRules");
		
		Log::debug("Page Cache mapper: getRules returned " . var_export($list, true)  );

		return $list;
	}
	
	public function deleteRules(array $rulesIds) {
	
		$sql = new Sql\Delete();
		$sql->from($this->getTableGateway()->getTable());
		$sql->where(self::RULE_ID . " IN (" . implode("," , $rulesIds) . ")");
				
		$deleted = $this->getTableGateway()->deleteWith($sql);
		Log::debug("Deleted $deleted Page Cache rules");		
	}
	
	public function deleteRulesByApplicationId($appId) {
		Log::debug("Deleting page cache rules for app $appId");

        if (! in_array($appId, $this->filterApplications(array()))) {
            throw new Exception(_t('You are not allowed to remove a rule for this application'), Exception::ERROR); 
        }

		$sql = new Sql\Delete();
		$sql->from($this->getTableGateway()->getTable());
		$sql->where(array(self::RULE_APP_ID . " = ?" => $appId));
	
		$deleted = $this->getTableGateway()->deleteWith($sql);
		Log::debug("Deleted $deleted Page Cache rules");
		
		return $deleted;
	}
	
	public function ruleUrlExists($url) {
	
		$sql = new Sql\Select();
		$sql->from($this->getTableGateway()->getTable());
		
		$where = array();
		$sql->where(array(self::RULE_URL . " = ?" => $url));
					
		$rows = $this->getTableGateway()->selectWith($sql)->toArray();
		if ($rows) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return array
	 */
	public function getMatchTypeDictionary() {
		return array(
			'exactMatch'	=> _t('is exactly'),
			'regexMatch'	=> _t('matches RegEx'),
			'regexIMatch'	=> _t('matches RegEx (case-insensitive)'));
	}
	
	/**
	 * @return array
	 */
	public function getSuperGlobalsDictionary() {
		return array(	'_GET',
						'_SERVER',
						'_SESSION',
						'_COOKIE');
	}

	public function getSplitSuperGlobalsDictionary() {
		return array(	'entire' => 'Entire query string',
						'uri' => 'Request URI',
						'_GET',
						'_SERVER',
						'_SESSION',
						'_COOKIE');
	}
	
	/**
	 * @return array
	 */
	public function getSuperGlobalMatchDictionary() {
		return array(	
					'equals'			=> _t('equals'),
					'not_equals'		=> _t('does not equal'),
					'regex_match'		=> _t('matches (regex)'),
					'regex_not_match' 	=> _t('does not match (regex)'),
					'exists'			=> _t('exists'),
					'not_exists'		=> _t('does not exist'),
				);
	}

    /**
     * @param IdentityFilterInterface $filter
     */
    public function setIdentityFilter(IdentityFilterInterface $filter)
    {
        $filter->setAddGlobalAppId(true);
        $this->identityFilter = $filter;
        return $this;
    }

    private function filterApplications($applications) {
        try {
            return $this->identityFilter->filterAppIds($applications, true);
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return array();
            }
        }
    }
}