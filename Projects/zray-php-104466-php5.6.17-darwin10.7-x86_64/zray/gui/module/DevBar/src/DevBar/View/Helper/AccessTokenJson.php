<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;

class AccessTokenJson extends AbstractHelper {
	
	public function __invoke($tokens = null, $count = 0) {
		if (is_null($tokens)) {
			return $this;
		}
		
		$tokensForJson = array();
		foreach ($tokens as $token) {
			$tokensForJson[] = array(
			    'tokenId' => $token->getId(), 
			    'hash' => $token->getToken(), 
			    'iprange' => $token->getAllowedHosts(), 
			    'baseUrl' => $token->getBaseUrl(), 
			    'ttl' => $token->getTtl(), 
			    'title' => $token->getTitle(),
			    'actions' => $token->getActions() ? '1' : '0',
			    'inject' => $token->getInject() ? '1' : '0',
			);
		}
		return $this->getView()->json(array('accessTokens' => $tokensForJson, 'totalCount' => $count));
	}
	
	public function single($token) {
		return $this->getView()->json(array(
		    'tokenId' => $token->getId(), 
		    'hash' => $token->getToken(), 
		    'iprange' => $token->getAllowedHosts(), 
		    'baseUrl' => $token->getBaseUrl(), 
		    'ttl' => $token->getTtl(),
		    'title' => $token->getTitle(),
		    'actions' => $token->getActions() ? '1' : '0',
		    'inject' => $token->getInject() ? '1' : '0',
		));
	}
}