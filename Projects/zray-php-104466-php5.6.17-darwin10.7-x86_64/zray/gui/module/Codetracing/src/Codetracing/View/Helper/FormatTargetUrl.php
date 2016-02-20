<?php

namespace Codetracing\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Uri\UriFactory;

class FormatTargetUrl extends AbstractHelper {
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke($url) {
		$uri = UriFactory::factory($url);
		$query = $uri->getQueryAsArray();
		if(isset($query['dump_data'])) {
			unset($query['dump_data']);
			$uri->setQuery($query);
		}
		if ($uri->isValid()) {
			return $uri->toString();
		}
		return $url;
	}
}

