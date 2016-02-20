<?php

namespace ZendServer\Http\Header;

use Zend\Http\Header\SetCookie as baseSetCookie;
use ZendServer\Log\Log;

class SetCookie extends baseSetCookie {
	/**
	 * Created to work around empty-cookie issue in zf2
	 * @see https://github.com/zendframework/zf2/issues/5620
	 * @see \Zend\Http\Header\SetCookie::fromString()
	 */
	public static function fromString($headerLine, $bypassHeaderFieldName = false) {
		$headerLine = str_replace('=;', '= ;', $headerLine);
		return parent::fromString($headerLine, $bypassHeaderFieldName);
	}

	
}

