<?php
namespace ZendServer\Validator;

use Zend\Validator\AbstractValidator;
use ZendServer\Log\Log;

abstract class AbstractZendServerValidator extends AbstractValidator {
	
	protected function error($messageKey, $value = null) {
		Log::err("value '".var_export($value,true)."' failed validation");
		parent::error($messageKey, $value);
		$messages = parent::getMessages();
		Log::err(_t($messages[$messageKey]));
		return _t($messages[$messageKey]);
	}
}