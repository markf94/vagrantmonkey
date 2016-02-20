<?php
namespace Codetracing\Dump;

use ZendServer\Exception as ZSException;

class Wrapper {

	/**
	 * Check if the Code Tracing extension is loaded
	 * @throws ZSException
	 */
	public function isAPIAvailable() {
		if (! function_exists('zend_codetracing_dump_inspect')) {
			throw new ZSException(_t('Zend Trace extension is not loaded'));
		}
		// TODO: check the module 'Zend Code Tracing' is loaded with error
	}
	
	/**
	 * Get the dump file information
	 * @param string $file
	 * @param string $dumpId
	 * @return array
	 * @throws ZSException
	 */
	public function getDumpFileDetails($file, $dumpId) {
		if (function_exists('zend_codetracing_dump_inspect')) {
			$result = zend_codetracing_dump_inspect($file, $dumpId, false);
			if (false === $result) {
				return array();
			}
			return $result;
		}
		throw new ZSException(_t('API function zend_codetracing_dump_inspect() doesn\'t exist'));
	}
	
	/**
	 * Get the dump file data in AMF format
	 * @param string $file Dump filename
	 * @return string
	 */
	public function getDumpFileData($file) {
		if (function_exists('zend_codetracing_dump_amf')) {
			return zend_codetracing_dump_amf($file);
		}
		throw new ZSException(_t('API function zend_codetracing_dump_amf() doesn\'t exist'));
	}
	
	/**
	 * Convert dump data into AMF format and return the new file path
	 * @param string $file Dump filename
	 * @return string
	 */
	public function getDumpFileAmfPath($file) {
		if (function_exists('zend_codetracing_dump_amf_file')) {
			return zend_codetracing_dump_amf_file($file);
		}
		throw new ZSException(_t('API function zend_codetracing_dump_amf_file() doesn\'t exist'));
	}
	
}
