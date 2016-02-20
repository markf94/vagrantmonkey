<?php

namespace WebAPI\Authentication;

use Zend\Authentication\Result as baseResult;

class Result extends baseResult {
	const FAILURE_SIGNATURE_TIMESKEW = -10;
	
	/**
     * Sets the result code, identity, and failure messages
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     */
    public function __construct($code, $identity, array $messages = array())
    {
        parent::__construct($code, $identity, $messages);
        /// Break the limitations on the result code in parent constructor
    	$code = (int) $code;
        $this->code     = $code;
    }
}

