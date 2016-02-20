<?php

namespace Vhost\Mapper;
use Exception as baseException;

class Exception extends baseException {
	const VHOST_OPERATION_FAILED = 1000;
	const APACHE_CONFIGURATION_INVALID = 1001;
}

