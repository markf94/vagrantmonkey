<?php

namespace DevBar\Db;

use DevBar\AccessTokenContainer;
use Zsd\Db\TasksMapper;
use Zend\Db\Sql\Select;

class AccessTokensMapper extends TokenMapper {
	
	/**
	 * @param string $allowedHosts
	 * @param string $baseUrl
	 * @param integer $ttl
	 * @param string $title
	 * @return AccessTokenContainer
	 */
	public function createToken($allowedHosts, $baseUrl, $ttl, $title, $token='', $actions='', $inject='') {
	    
	    $actions = $actions ? '1' : '0';
	    $inject = $inject ? '1' : '0';
	    
		$tokenId = $this->insert(array(
			'id' => null,
			'token' => $token,
			'ttl' => time()+$ttl, 'base_url' => $baseUrl, 'allowed_hosts' => $allowedHosts, 'name' => $title,
			'run_actions' => $actions, 'inject' => $inject,
		));
		
		if (!isAzureEnv() && !isZrayStandaloneEnv()) {
			$this->tasks->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_UPDATED_ACCESS_TOKENS);
		} elseif (isZrayStandaloneEnv()) {
			// in addition to the database record, JSON file has to be updated for Z-Ray standalone 
			$jsonOpResult = $this->appendTokenToJsonFile(array(
				'id' => $tokenId,
				'token' => $token,
				'ttl' => time() + $ttl, 
				'base_url' => $baseUrl, 
				'allowed_hosts' => $allowedHosts, 
				'name' => $title,
		        'run_actions' => $actions, 
				'inject' => $inject,
			));
			
			if (is_string($jsonOpResult)) {
				return $jsonOpResult;
			}
		}
		
		return $this->findTokenById($tokenId);
	}
	
	/**
	 * @return string
	 */
	public function findAllowedHosts() {
	    $select = new Select($this->getTableGateway()->getTable());
	    
	    $allowedHosts = array();
	    $result = $this->selectWith($select);
	    foreach ($result as $row) { /* @var $row \DevBar\AccessTokenContainer */
	        $rowAllowedHosts = $row->getAllowedHosts();
	        if (! empty($rowAllowedHosts)) {
	            $allowedHosts = array_merge($allowedHosts, explode(',', $rowAllowedHosts));
	        }
	    }
	    $allowedHosts = array_unique($allowedHosts);
	    return implode(',', $allowedHosts);
	}
	
}
