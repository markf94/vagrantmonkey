<?php

namespace ZendServer\FS;

use ZendServer\Log\Log;

class ZipArchive extends \ZipArchive {

	/**
	 * We wrap the addFile() method as it silently return true when adding non-existing files, which will later cause the close() function to fail
	 * 
	 * (non-PHPdoc)
	 * @see ZipArchive::addFile()
	 */
	public function addFile($filename, $localname = null, $start = null, $length = null) {
		if (!is_readable($filename)) {
			Log::notice("file '{$filename}' does not exist - cannot add it to archive");
			return false;	
		}
		
		return parent::addFile($filename, $localname, $start, $length);
	}
}
