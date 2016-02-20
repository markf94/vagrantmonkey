<?php

namespace Configuration;

use Zend\EventManager\EventManagerInterface;

use Zend\EventManager\EventManager;

use Zend\EventManager\EventManagerAwareInterface;

use Zend\Db\Sql\Predicate\Predicate;

use Zend\Db\Sql\Where;

use ZendServer\Ini\IniWriter;

use \ZendServer\FS\FS,
	Zend\Db\Sql\Select;

use ZendServer\Log\Log;
use Zsd\Db\TasksMapper;

class MapperDirectives extends MapperAbstract implements EventManagerAwareInterface {

	protected $setClass='\Configuration\DirectiveContainer';

	/**
	 * @var Mapper
	 */
	protected $tasksMapper;
	
	/**
	 * @var EventManager
	 */
	protected $eventManager;
	
	/**
	 * @param string $directive directive name
	 * @return boolean
	 */
	public function directiveExists($directive) {
		$where = new Where();
		$where->equalTo('NAME', $directive);
		$result = $this->select($where);
		return $result->count() > 0;
	}
	
	/**
	 * @param array $newDirectives associative array of directive names and new values
	 * @param integer $auditId
	 */
	public function setDirectives($newDirectives) {
		$predicate = new Predicate();
		$directivesArray = $this->select(new Where(array($predicate->in('NAME', array_keys($newDirectives)))))->toArray();
		$directivesAssociative = array();
		
		foreach ($directivesArray as $directive) {
			$directivesAssociative[$directive['NAME']] = $directive['DISK_VALUE'];
		}
		
		$directives = array();
		foreach ($newDirectives as $name => $value) {
			$directives[] = array('name' => $name, 'value' => strval($value));
		}
		
		$this->tasksMapper->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_SAVE_AND_APPLY_BLUEPRINT, $directives);
		$this->eventManager->trigger('setDirectives', $this, array('directives' => $directivesAssociative, 'newDirectives' => $newDirectives));
	}
	
	/**
	 * @param string $license
	 * @param string $user
	 */
	public function writeLicenseDirectivesToIni($license, $user) {
		$writer = new IniWriter();
		$iniFile = FS::getGlobalDirectivesFile();
		$iniFile === 'php.ini' ? $section = 'Zend' : $section = null; // php.ini is sectioned, while ZendGlobalDirectives is not
		$directives = array('zend.serial_number' => $license, 'zend.user_name' => $user);
		return $writer->updateDirectives($iniFile, $directives, $section);
	}
	
	public function insertLicenseDetails($license, $user) {
		$commonValues = array('EXTENSION' => 'Zend Global Directives', 'INI_FILE' => FS::getGlobalDirectivesFile(), 'MEMORY_ONLY' => 0); // in particular, INI_FILE is important, as there's a unique index over NAME+INI_FILE, thus when node is added to the cluster, we ensure having only 1 set of directives of zend.serial_number+zend.user_name
		$this->getTableGateway()->insert(array('NAME' => 'zend.serial_number', 'TYPE' => 1, 'MEMORY_VALUE' => $license, 'DISK_VALUE' => $license) + $commonValues);
		$this->getTableGateway()->insert(array('NAME' => 'zend.user_name', 'TYPE' => 1, 'MEMORY_VALUE' => $user, 'DISK_VALUE' => $user) + $commonValues);
	}
	
	/**
	 * 
	 * @return multitype:Ambigous <NULL, \Configuration\ResultSet> Ambigous <NULL, \Configuration\ResultSet, \ZendServer\Set>
	 */
	public function getLicenseDetails(){
		$table = $this->getTableGateway()->getTable();
		
		$select = new Select($table);
		$select->where(array('NAME' => 'zend.serial_number'));
		$serialNumber = $this->selectWithRetries($select);
		$serialNumberArray = $serialNumber->toArray();
		
		$selectUser = new Select($table);
		$selectUser->where(array('NAME' => 'zend.user_name'));	
		$userName = $this->selectWithRetries($selectUser);
		$userNameArray = $userName->toArray();
		
		if(isset($userNameArray[0]) && isset($serialNumberArray[0])){
			return array('user_name' => $userNameArray[0]['DISK_VALUE'],'serial_number' => $serialNumberArray[0]['DISK_VALUE']);
		} else {
			return array();
		}
	}
	
	/**
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function selectAllDirectives($direction='ASC') { 
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->order(array('NAME' => $direction));
		return $this->selectWithRetries($select);
	}	
	
	/**
	 * @param array $directives
	 * @return array
	 */
	public function getGroupedExtensionsByDirectives(array $directives) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->where($this->getSqlInStatement('NAME', $directives));
		$select->group('EXTENSION');
		$select->columns(array('EXTENSION'));

		$extensionsArray = $this->selectWithRetries($select, false);		
		$extensions = array();
		foreach ($extensionsArray as $extArray) {
			foreach($extArray as $extValue) {
				$extensions[] = $extValue;
			}
		}
		
		return $extensions;
	}	
	
	/**
	 * @param array $directivesBlacklist
	 * @return array sql queries
	 */
	public function getExportData(array $directivesBlacklist = array()) {	
		$data = array();		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$res = $this->selectWith($select)->toArray();		
		
		if (!$directivesBlacklist) { // if empty, then we will fetch the blacklist from the configuration
			$blackList = \Application\Module::config()->get('export');
			if (is_object($blackList) && array_key_exists('directivesBlacklist', $blackListArray=$blackList->toArray())) {
				$directivesBlacklist = $blackListArray['directivesBlacklist'];
			}			
		}
				
		foreach ($res as $row) {			
			if (in_array($row['NAME'], $directivesBlacklist)) {
				log::debug("exportConfiguration: skipping {$row['NAME']} as it's blacklisted");
				continue;
			}

			$stringToInt = array('true'=>1, 'false'=>0); // in sqlite, the MEMORY_ONLY colukmn is stored as 'true', 'false' while on MySQL it is stored as 1,0
			foreach ($row as $key => $val) {
				if (isset($stringToInt[$row[$key]])) {
					$row[$key] = $stringToInt[$row[$key]];
				} else {
					$row[$key] = "'" . trim($row[$key], "' ") . "'"; // first trimming single quotes before adding ones
				}
			}		
			$line = "REPLACE INTO " . (string) $this->getTableGateway()->getTable() . " (" . implode(",", array_keys($row)) . ') VALUES (' . implode(",", array_values($row)) . ");";
			$data[] = $line; 
		}		
		
		return $data; 
	}

	/**
	 * @return \ZendServer\Set
	 */
	public function selectSpecificDirectives(array $directives) {
		return $this->select(array('NAME' . ' IN ("' . implode('","', $directives) . '")')); // in values, should look like: ("bcmath.scale","assert.callback")
	}
	
	/**
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function selectAllExtensionDirectives($component, $direction='ASC') {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->order(array('NAME' => $direction));
		$select->where(array('EXTENSION' => $component));
		return $this->selectWithRetries($select);
	}			

	/**
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function selectAllDaemonDirectives($daemon, $direction='ASC') {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->order(array('NAME' => $direction));
		$select->where(array('DAEMON' => $daemon));
		return $this->selectWithRetries($select);
	}
	
	/**
	 * param array $directivesNames
	 * @return array
	 */
	public function getDirectivesValues(array $directivesNames) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->where($this->getSqlInStatement('NAME', $directivesNames));		
		$directivesContainers = $this->selectWithRetries($select);
		
		$values = array();
		foreach ($directivesContainers as $directiveContainer) {
			$values[$directiveContainer->getName()] = $directiveContainer->getFileValue();
		}
	
		return $values;
	}
	
	/**
	 * @param string $directiveName
	 * @param boolean $clean
	 * @return string
	 */
	public function getDirectiveMemoryValue($directiveName, $clean = false) {
		$resultSet = $this->select(array('NAME' => $directiveName)); 
		// @todo add protection for case where no directive of that name is found
		$directiveContainer = $resultSet[0]; /* @var $directiveContainer \Configuration\DirectiveContainer */
		if ($clean && $directiveContainer->getType() == DirectiveContainer::TYPE_STRING) {
			return preg_replace('#^"(.+)"$#', '$1', $directiveContainer->getFileValue());
		}
		return $directiveContainer->getDefaultValue();
	}
	
	/**
	 * @param string $directiveName
	 * @param boolean $clean
	 * @return string
	 */
	public function getDirectiveValue($directiveName, $clean = false) {
		$resultSet = $this->select(array('NAME' => $directiveName)); 
		// @todo add protection for case where no directive of that name is found
		$directiveContainer = $resultSet[0]; /* @var $directiveContainer \Configuration\DirectiveContainer */
		if ($clean && $directiveContainer->getType() == DirectiveContainer::TYPE_STRING) {
			return preg_replace('#^"(.+)"$#', '$1', $directiveContainer->getFileValue());
		}
		return $directiveContainer->getFileValue();
	}
	
	/**
	 * param string $directiveName
	 * @return \Configuration\DirectiveContainer
	 */
	public function getDirective($directiveName) {
		$resultSet = $this->select(array('NAME' => $directiveName)); //@todo - 2 directives with the same name
		$directiveContainer = $resultSet[0]; /* @var $directiveContainer \Configuration\DirectiveContainer */
	
		return $directiveContainer;
	}

	/**
	 * param string $directiveName
	 * @return string
	 */
	public function getDirectiveExtension($directiveName) {
		$resultSet = $this->select(array('NAME' => $directiveName)); //@todo - 2 directives with the same name
	
		$directiveContainer = $resultSet[0]; /* @var $directiveContainer \Configuration\DirectiveContainer */		
		return $directiveContainer->getExtension();
	}
	/**
	 * @param TasksMapper $tasksMapper
	 * @return MapperDirectives
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasksMapper = $tasksMapper;
		return $this;
	}
	/**
	 * @param EventManagerInterface $eventManager
	 * @return \Configuration\MapperDirectives
	 */
	public function setEventManager(EventManagerInterface $eventManager) {
		$this->eventManager = $eventManager;
		return $this;
	}
	/**
	 * @return \Zend\EventManager\EventManager $eventManager
	 */
	public function getEventManager() {
		return $this->eventManager;
	}
	
}

