<?php
namespace ZendServer\Translator\Adapter;

use Zend\Translator\Adapter\AbstractAdapter;

class NullAdapter extends AbstractAdapter {

	/* (non-PHPdoc)
	 * @see Zend\Translator\Adapter.AbstractAdapter::_loadTranslationData()
	 */
	protected function _loadTranslationData($data, $locale, array $options = array()) {
		// load nothing!
	}

	/* (non-PHPdoc)
	 * @see Zend\Translator\Adapter.AbstractAdapter::toString()
	 */
	public function toString() {
		return 'NullAdapter';
	}


}

