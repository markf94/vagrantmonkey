<?php
namespace ZendServer\Configuration\Directives;

use Configuration\DirectiveContainer;
use ZendServer\Log\Log;

class Translator {
	
	/**
	 * Return a normalized value of an element.
	 * e.g. for a boolean directive with value '1' or 'On' - return boolean true
	 *
	 * @param DirectiveContainer $directive
	 * @return mixed
	 */
	static public function getRealFileValue(DirectiveContainer $directive) {
		return self::getTranslatedValue($directive, $directive->getFileValue());
	}
	
	/**
	 * Return the string representation of a directive's value.
	 * e.g. for a boolean directive with value '1' or 'On' - return boolean true
	 *
	 * @param DirectiveContainer $directive
	 * @return string
	 */
	static public function getStringFileValue(DirectiveContainer $directive) {
		return self::getAsStringFactory($directive, $directive->getFileValue());
	}
	
	/**
	 * Return the given value translated according to the directive type (
	 * i.e. In case of boolean directive, the following values will be converted to a boolean true: 1, 'on', 'yes', 'true'
	 *
	 * @param DirectiveContainer $directive
	 * @param mixed $value
	 * @return mixed
	 */
	static private function getTranslatedValue(DirectiveContainer $directive, $value) {
		if ('error_reporting' === $directive->getName()) {
			Log::debug('error_reporting!int!'.$value);
			return self::getErrorReportingAsInteger($value);
		}
		
		switch ($directive->getType()) {
			case DirectiveContainer::TYPE_BOOLEAN:
				return self::getAsBoolean($value);
				break;
			case DirectiveContainer::TYPE_INT_BOOLEAN:
				return self::getIntBoolean($value);
				break;
			case DirectiveContainer::TYPE_SELECT:
				if (empty($value)) {
					return '0';
				}
				break;
			case DirectiveContainer::TYPE_SHORTHAND:
				return self::getShorthandAsInteger($value);
				break;
		}
		
		// default, if the value doesn't need translation
		return $value;
	}
	
	static private function getAsStringFactory(DirectiveContainer $directive, $value) {
		if ('error_reporting' === $directive->getName()) {
			Log::debug('error_reporting!str!'.$value);
			return self::getErrorReportingAsString($value);
		}

		switch ($directive->getType()) {
			case DirectiveContainer::TYPE_BOOLEAN:
				return self::getBooleanAsString($value);
				break;
			case DirectiveContainer::TYPE_INT_BOOLEAN:
				return self::getIntBooleanAsString($value);
				break;
			case DirectiveContainer::TYPE_SELECT:
				return self::getSelectAsString($directive, $value);
				break;
			case DirectiveContainer::TYPE_SHORTHAND:
				return self::getShorthandAsString($value);
				break;
		}
		
		// default, if the value doesn't need translation
		return $value;
	}
	
	/**
	 * Returns a boolean representation value of a string value
	 * @param string $value
	 * @return boolean
	 */
	static private function getAsBoolean($value) {
		if (is_bool($value)) {
			return $value;
		}
		if (is_numeric($value)) {
			return ($value) ? true : false;
		}
		$value = strtolower($value);
		return in_array($value, array('yes','on','1', 'true'));
	}
	
	/**
	 * Returns a string representation of a boolean value directive.
	 * i.e. translate true to 'On' and false to 'Off'
	 *
	 * @param boolean $value
	 */
	static private function getBooleanAsString($value) {
		if (!is_bool($value)) {
			$value = self::getAsBoolean($value);
		}
		
		if (true === $value) {
			return _t('On');
		} else {
			return _t('Off');
		}
	}

	static private function getIntBoolean($value) {
		$value = strtolower($value);
		if ('off' == $value) {
			return 0;
		}
		if ('on' == $value) {
			return 1;
		}

		return $value;
	}
	
	static private function getIntBooleanAsString($value) {
		if (0 == $value) {
			return _t('Off');
		}
		
		return $value;
	}
	
	static private function getSelectAsString(DirectiveContainer $directive, $value) {
		 $options = $directive->getListValues();
		 
		 if (isset($options[$value])) {
		 	return (string)$options[$value];
		 }
		 return (string)$value;
	}
	
	static private function getShorthandAsInteger($value) {
		$matches = array();
		if (preg_match('/^[0-9]+(K|B|M|G)$/i', $value, $matches)) {
			if (isset ($matches[1])) {
				switch (strtoupper($matches[1])) {
					case 'G':
						$value *= 1024;
						// fallthrough
					case 'M':
						$value *= 1024;
						// fallthrough
					case 'K':
						$value *= 1024;
						// fallthrough
				}
			}
		}
		return sprintf('%u', $value);
	}
		
	static private function getShorthandAsString($value) {
		if (is_numeric($value)) {
			$units = array('B', 'K', 'M', 'G');
	    	for ($i = 0; $value >= 1024 && $i < 4; $i++) {
				$value /= 1024;
	    	}
	    	return round($value, 2) . $units[$i];
		}
		return strtoupper($value);
	}	
	
	/**
	 * Get the string representation value of error_reporting from its integer value
	 * Note that an integer value may have more than one string representation, so there is
	 * no way to know the user's prefered notation of it
	 *
	 * @param integer $code
	 * @return string
	 */
	public static function getErrorReportingAsString($code) {
		$code = (int)$code;
		$allOnes = E_ALL | E_STRICT;	// E_ALL is no longer all 1, so this is the reference
		
		// input safety
		if (($code < 0) || ($code > $allOnes)) {
			return '';
		}

		$resultString = '';
		
		$errorsCombined = array();	// E_* pieces for E_ERROR | E_NOTICE type of string
		$errorsMinus = array();		// E_* pieces for E_ALL &~(E_NOTICE | E_WARNING) type of string
		
		// as our base of reference is both E_ALL and E_STRICT they will both need to serve
		// as the base for substructing elements
		$baseMinus = array(E_ALL => 'E_ALL', E_STRICT => 'E_STRICT');
		
		$errorCodes = array(
			E_ERROR				=> 'E_ERROR',
			E_WARNING			=> 'E_WARNING',
			E_PARSE				=> 'E_PARSE',
			E_NOTICE			=> 'E_NOTICE',
			E_CORE_ERROR		=> 'E_CORE_ERROR',
			E_CORE_WARNING		=> 'E_CORE_WARNING',
			E_COMPILE_ERROR		=> 'E_COMPILE_ERROR',
			E_COMPILE_WARNING	=> 'E_COMPILE_WARNING',
			E_USER_ERROR		=> 'E_USER_ERROR',
			E_USER_WARNING		=> 'E_USER_WARNING',
			E_USER_NOTICE		=> 'E_USER_NOTICE',
			E_STRICT			=> 'E_STRICT',
			E_RECOVERABLE_ERROR	=> 'E_RECOVERABLE_ERROR',
		);
		
		// Additional PHP 5.3 type
		if (defined('E_DEPRECATED')) {
			$errorCodes[E_DEPRECATED] = 'E_DEPRECATED';
		}
		
		// Additional PHP 5.3 type
		if (defined('E_USER_DEPRECATED')) {
			$errorCodes[E_USER_DEPRECATED] = 'E_USER_DEPRECATED';
		}
		
		// check the simple option where the code is a single code piece
		if (isset($errorCodes[$code])) {
			return $errorCodes[$code];
		}
		if ($code === E_ALL) {
			return 'E_ALL';
		}
		
		// construct the combined error types that match the code
		foreach ($errorCodes as $errorNumber => $errorString) {
			if ($code & $errorNumber) {
				$errorsCombined[] = $errorString;
			}
			if (($allOnes - $code) & $errorNumber) {
				if (isset($baseMinus[$errorNumber])) {
					unset($baseMinus[$errorNumber]);
				} else {
					$errorsMinus[] = $errorString;
				}
			}
		}
		
		// choose if to display E_ALL&~(...) or E_WARNING | E_NOTICE - depends on where
		// there will be less elements
		$errorsMinusCount = count($errorsMinus);
		$errorsCombinedCount = count($errorsCombined);
		
		if (($errorsMinusCount + 1) < $errorsCombinedCount) {
			// create a string which looks like E_ALL &~E_NOTICE
			$baseMinusCount = count($baseMinus);
			
			// check if the first part before the &~ should be E_ALL, E_STRICT or (E_ALL|E_STRICT)
			if (0 == $baseMinusCount) {
				$resultString = '';
			} elseif(1 == $baseMinusCount) {
				$resultString = current($baseMinus);
			} else {
				$resultString = '(' . implode(' | ', $baseMinus) . ')';
			}
			
			
			// build the second part of the string which will come after the &~
			if ($errorsMinusCount) {
				$errorElements = '';
				if (1 === $errorsMinusCount) {
					$errorElements = $errorsMinus[0];
				} elseif (1 < $errorsMinusCount) {
					$errorElements = '(' . implode(' | ', $errorsMinus) . ')';
				}
				$resultString .= ' & ~' . $errorElements;
			}
			
		} else {
			// create a string which looks like E_ALL | E_STRICT
			if (1 <= $errorsCombinedCount) {
				$resultString = implode(' | ', $errorsCombined);
			}
		}
		return (string)$resultString;
	}

	/**
	 * Get the integer value of error_reporting from its string representation
	 *
	 * @param string $value
	 * @return integer
	 */
	static private function getErrorReportingAsInteger($value) {
		// sometime we get the value as integer (e.g. 6143) sometimes as string (E_ALL)
		if (is_numeric($value)) {
			return (int)$value;
		}
		
		$evalValue = 0;
		
		if (preg_match('/^(?:\ |\||\~|\&|\(|\)|E_ERROR|E_WARNING|E_PARSE|E_NOTICE|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING|E_USER_ERROR|E_USER_WARNING|E_USER_NOTICE|E_ALL|E_STRICT|E_RECOVERABLE_ERROR|E_DEPRECATED|E_USER_DEPRECATED)+$/', $value)) {
			@eval('$evalValue=' . $value . ';');
		}
		return (int)$evalValue;
	}
}