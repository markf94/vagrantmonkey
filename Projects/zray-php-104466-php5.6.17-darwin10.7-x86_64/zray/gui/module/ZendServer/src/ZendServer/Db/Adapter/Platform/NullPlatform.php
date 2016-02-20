<?php
namespace ZendServer\Db\Adapter\Platform;

use Zend\Db\Adapter\Platform\PlatformInterface;

class NullPlatform implements PlatformInterface {
	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::getName()
	*/
	public function getName() {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::getQuoteIdentifierSymbol()
	*/
	public function getQuoteIdentifierSymbol() {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::quoteIdentifier()
	*/
	public function quoteIdentifier($identifier) {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::quoteIdentifierChain()
	*/
	public function quoteIdentifierChain($identifierChain) {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::getQuoteValueSymbol()
	*/
	public function getQuoteValueSymbol() {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::quoteValue()
	*/
	public function quoteValue($value) {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::quoteTrustedValue()
	*/
	public function quoteTrustedValue($value) {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::quoteValueList()
	*/
	public function quoteValueList($valueList) {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::getIdentifierSeparator()
	*/
	public function getIdentifierSeparator() {

	}

	/* (non-PHPdoc)
	 * @see \Zend\Db\Adapter\Platform\PlatformInterface::quoteIdentifierInFragment()
	*/
	public function quoteIdentifierInFragment($identifier, array $additionalSafeWords = array()) {

	}


}