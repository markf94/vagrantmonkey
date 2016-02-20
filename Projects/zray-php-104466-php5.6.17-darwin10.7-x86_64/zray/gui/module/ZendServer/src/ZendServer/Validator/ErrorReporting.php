<?php

namespace ZendServer\Validator;

use ZendServer\Exception;
use ZendServer\Log\Log;

class ErrorReporting extends Integer {

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return boolean
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value) {
        try {
            return parent::isValid($this->normalizeValue($value));
        } catch (Exception $ex) {
            return false;
        }
    }

    private function normalizeValue($value) {
        /// normalize into an integer
        if (is_numeric($value)) {
            return (int)$value;
        }

        $evalValue = 0;
        if (0 < preg_match('/^(?:\ |\||\~|\&|\(|\)|E_ERROR|E_WARNING|E_PARSE|E_NOTICE|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING|E_USER_ERROR|E_USER_WARNING|E_USER_NOTICE|E_ALL|E_STRICT|E_RECOVERABLE_ERROR|E_DEPRECATED|E_USER_DEPRECATED)+$/', $value)) {
            @eval('$evalValue=' . $value . ';');
        } else {
            throw new Exception(_t('Contains illegal symbols or characters for \'error_reporting\' directive')); 
        }
        return (int)$evalValue;
    }
}