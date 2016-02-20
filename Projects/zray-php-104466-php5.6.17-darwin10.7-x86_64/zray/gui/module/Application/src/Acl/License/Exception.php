<?php
namespace Acl\License;

class Exception extends \Exception {
	const ASSERT	= 1;
	const WARNING	= 2;
	const ERROR		= 4;
	
	const LICENSE_PERMISSION_DENIED = 101;
}

