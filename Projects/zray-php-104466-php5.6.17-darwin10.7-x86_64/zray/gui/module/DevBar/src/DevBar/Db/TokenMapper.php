<?php

namespace DevBar\Db;

use Configuration\MapperAbstract;
use Zend\Crypt\Hash;
use DevBar\AccessTokenContainer;
use Zend\Db\Sql\Select;
use ZendServer\Exception;
use Zsd\Db\TasksMapperAwareInterface;
use Zsd\Db\TasksMapper;

class TokenMapper extends MapperAbstract implements TasksMapperAwareInterface {
	
	const TOKEN_FIELD_ID = 'id';
	const TOKEN_FIELD_TITLE = 'title';
	const TOKEN_FIELD_TOKEN = 'token';
	const TOKEN_FIELD_TTL = 'ttl';
	
	const TOKEN_LIMIT_DEFAULT = 5;
	
	/**
	 * @var string
	 */
	protected $setClass = '\DevBar\AccessTokenContainer';
	
	/**
	 * @var TasksMapper
	 */
	protected $tasks;
	/**
	 * @param string $allowedHosts
	 * @param string $baseUrl
	 * @param integer $ttl
	 * @param string $title
	 * @return AccessTokenContainer
	 */
	public function createToken($allowedHosts, $baseUrl, $ttl, $title, $token='', $actions='', $inject='') {
		$tokenId = $this->insert(array('id' => null,
				'token' => Hash::compute('sha256', mt_rand(0, mt_getrandmax())),
				'ttl' => time()+$ttl, 'base_url' => $baseUrl, 'allowed_hosts' => $allowedHosts, 'title' => $title
		));
		if (! isAzureEnv() && !isZrayStandaloneEnv()) {
		  $this->tasks->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_UPDATED_ACCESS_TOKENS);
		}
		return $this->findTokenById($tokenId);
	}
	
	/**
	 * @param integer $tokenId
	 * @return integer
	 */
	public function deleteToken($tokenId) {
		$result = $this->delete(array('id' => $tokenId));
		if (! isAzureEnv() && !isZrayStandaloneEnv()) {
			$this->tasks->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_UPDATED_ACCESS_TOKENS);
		}
		if (isZrayStandaloneEnv()) {
			$jsonOpResult = $this->deleteTokenFromJsonFile($tokenId);
			if (is_string($jsonOpResult)) {
				return $jsonOpResult;
			}
		}
		return $result;
	}
	
	/**
	 * Expire the token immediately by setting the current time
	 * @param integer $tokenId
	 * @return integer
	 */
	public function expireToken($tokenId) {
		$result = $this->update(array('ttl' => time() - 1), array('id' => $tokenId));
		if (! isAzureEnv() && !isZrayStandaloneEnv()) {
			$this->tasks->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_UPDATED_ACCESS_TOKENS);
		}
		if (isZrayStandaloneEnv()) {
			$jsonOpResult = $this->expireTokenInJsonFile($tokenId);
			if (is_string($jsonOpResult)) {
				return $jsonOpResult;
			}
		}
		return $result;
	}
	
	/**
	 * @return integer
	 */
	public function countTokens() {
		return $this->count();
	}
	
	/**
	 * @return Set[AccessTokenContainer]
	 */
	public function findTokens($page = 0, $limit = self::TOKEN_LIMIT_DEFAULT, $order = self::TOKEN_FIELD_TITLE, $direction = 'DESC') {
	    $select = new Select($this->getTableGateway()->getTable());
		$select->limit(intval($limit));
		$select->offset($page * $limit);
		$select->order("$order $direction");
		return $this->selectWith($select);
	}
	
	/**
	 * @param integer $tokenId
	 * @return AccessTokenContainer
	 */
	public function findTokenById($tokenId) {
		return $this->select(array('id' => $tokenId))->current();
	}
	
	/**
	 * @param string $token
	 * @return AccessTokenContainer
	 */
	public function findTokenByHash($token) {
		return $this->select(array('token' => $token))->current();
	}
	
	/**
	 * @return AccessTokenContainer
	 */
	public function findFirstToken() {
		if ($this->count() == 0) {
			throw new Exception(_t('No access tokens exist'));
		}
		$select = new Select($this->getTableGateway()->getTable());
		$select->order('id asc');
		return $this->selectWith($select)->current();
	}
	/**
	 * 
	 * @param TasksMapper $tasksMapper
	 * @return TokenMapper
	 */
	public function setTasksMapper($tasksMapper) {
		$this->tasks = $tasksMapper;
		return $this;
	}
	
	
	protected function getSelectiveAccessJsonFilePath() {
		return getCfgVar('zend.ini_scandir') . DIRECTORY_SEPARATOR . 'zray_selective_access.json';
	}
	
	/**
	 * @brief append the new record to the JSON file with the list of selective accesses
	 * @param array $tokenData 
	 * @return string|bool - true is for success. string is the error.
	 */
	protected function appendTokenToJsonFile(array $tokenData) {
		// convert the data to the `selective access`'s friendly format
		$convertedTokenData = array(
			'_id' => intval($tokenData['id']),
			'_name' => $tokenData['name'],
			'_token' => $tokenData['token'],
			'_runActions' => intval($tokenData['run_actions']), 
			'_inject' => intval($tokenData['inject']),
			'_allowedHosts' => explode(',', $tokenData['allowed_hosts']), 
			'_baseUrl' => $tokenData['base_url'], 
			'_ttl' => intval($tokenData['ttl']), 
		);
		
		// check if the file exists and it's writeable
		$selectiveAccessJsonFileLocation = $this->getSelectiveAccessJsonFilePath();
		if (!file_exists($selectiveAccessJsonFileLocation)) {
			if (!is_writable(dirname($selectiveAccessJsonFileLocation))) {
				// return error string
				return 'Cannot create selective access JSON file - no write permissions ('.dirname($selectiveAccessJsonFileLocation).')';
			} else {
				// create an empty file
				touch($selectiveAccessJsonFileLocation);
				file_put_contents($selectiveAccessJsonFileLocation, json_encode(array()));
			}
		}
		
		if (!is_writable($selectiveAccessJsonFileLocation)) {
			// return error string
			return 'No write permissions to selective access JSON file ('.$selectiveAccessJsonFileLocation.')';
		}
		
		// read the current data
		if (file_exists($selectiveAccessJsonFileLocation)) {
			$currentData = json_decode(file_get_contents($selectiveAccessJsonFileLocation), true);
			if ($currentData === false) {
				return 'Error parsing selective access JSON file ('.$selectiveAccessJsonFileLocation.') contents. '.json_last_error_msg();
			}
		} else {
			$currentData = array();
		}
	
		// add the new token to the data
		$currentData[] = $convertedTokenData;
		
		// write the file back
		$writeResult = file_put_contents($selectiveAccessJsonFileLocation, json_encode($currentData));
		if ($writeResult === false) {
			return 'Cannot write the date back to selective access JSON file ('.$selectiveAccessJsonFileLocation.')';
		}
		
		return true;
	}
	
	protected function deleteTokenFromJsonFile($tokenId) {
		
		$selectiveAccessJsonFileLocation = $this->getSelectiveAccessJsonFilePath();
		
		// check that the file is accessible
		if (!file_exists($selectiveAccessJsonFileLocation)) {
			return 'Selective access JSON file ('.$selectiveAccessJsonFileLocation.') does not exists';
		}
		if (!is_readable($selectiveAccessJsonFileLocation) || !is_writable($selectiveAccessJsonFileLocation)) {
			return 'No permissions to update selective access JSON file ('.$selectiveAccessJsonFileLocation.')';
		}
		
		// get json file contents
		$currentTokens = json_decode(file_get_contents($selectiveAccessJsonFileLocation), true);
		if ($currentTokens === false) {
			return 'selective access JSON file ('.$selectiveAccessJsonFileLocation.') is corrupted';
		}
		
		// create new list without the deleted token
		$newTokensData = array();
		foreach ($currentTokens as $i => $tokenData) {
			if (isset($tokenData['_id']) && $tokenData['_id'] == $tokenId) {
				continue;
			}
			
			$newTokensData[] = $tokenData;
		}
		
		// write the file back
		$writeResult = file_put_contents($selectiveAccessJsonFileLocation, json_encode($newTokensData));
		if ($writeResult === false) {
			return 'Cannot write the date back to selective access JSON file ('.$selectiveAccessJsonFileLocation.')';
		}
	}
	
	protected function expireTokenInJsonFile($tokenId) {
		
		$selectiveAccessJsonFileLocation = $this->getSelectiveAccessJsonFilePath();
		
		// check that the file is accessible
		if (!file_exists($selectiveAccessJsonFileLocation)) {
			return 'Selective access JSON file ('.$selectiveAccessJsonFileLocation.') does not exists';
		}
		if (!is_readable($selectiveAccessJsonFileLocation) || !is_writable($selectiveAccessJsonFileLocation)) {
			return 'No permissions to update selective access JSON file ('.$selectiveAccessJsonFileLocation.')';
		}
		
		// get json file contents
		$currentTokens = json_decode(file_get_contents($selectiveAccessJsonFileLocation), true);
		if ($currentTokens === false) {
			return 'selective access JSON file ('.$selectiveAccessJsonFileLocation.') is corrupted';
		}
		
		// create new list without the deleted token
		$newTokensData = array();
		foreach ($currentTokens as $i => $tokenData) {
			if (isset($tokenData['_id']) && $tokenData['_id'] == $tokenId) {
				$tokenData['_ttl'] = time() - 1;
			}
			
			$newTokensData[] = $tokenData;
		}
		
		// write the file back
		$writeResult = file_put_contents($selectiveAccessJsonFileLocation, json_encode($newTokensData));
		if ($writeResult === false) {
			return 'Cannot write the date back to selective access JSON file ('.$selectiveAccessJsonFileLocation.')';
		}
	}
}
