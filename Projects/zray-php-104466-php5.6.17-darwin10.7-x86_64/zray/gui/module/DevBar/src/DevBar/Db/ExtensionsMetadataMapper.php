<?php

namespace DevBar\Db;

use Configuration\MapperAbstract;
use ZendServer\Set;
use ZendServer\Log\Log;
use ZendServer\Exception;
use ZendServer\FS\FS;

class ExtensionsMetadataMapper extends MapperAbstract {
	
	/**
	 * Create a metadata map for extensions for a particular requestId
	 * 
	 * @param integer $requestId
	 * @return array
	 */
	public function metadataForRequestId($requestId) {
	    $metadataArray = $this->select(array('request_id' => $requestId));
	    if (count($metadataArray)) {
	        $metaMap = array();
	        foreach($metadataArray as $metadataRow) {
	            $metaMap[$metadataRow['namespace']] = unserialize($metadataRow['serialized_data']);
	        }
	        return $metaMap;
	    }
		return array();
	}
	
	/**
	 * @param integer $requestId
	 * @param string $extension
	 * @param string $asset
	 * @throws Exception
	 * @return string
	 */
	public function loadAssetFile($requestId, $extension, $asset) {
		$metadata = $this->metadataForRequestId($requestId);
		if (! isset($metadata[$extension]) || ! isset($metadata[$extension]['assets']) || ! isset($metadata[$extension]['assets'][$asset])) {
			throw new Exception(_t('Asset name not found or no assets set by extension'));
		}

		$path = $this->getAssetFilepath($metadata[$extension]['assets'][$asset]);
		$assetFile = FS::getFileObject($path);
		
		// return mime type ala mimetype extension
		$finfo = finfo_open(FILEINFO_MIME);
		
		//check to see if the mime-type starts with 'text'
		if (substr(finfo_file($finfo, $path), 0, 4) == 'text') {
		    return $assetFile->readAll();
		} else {
		    return base64_encode($assetFile->readAll());
		}
	}
	
	/**
	 * @param mixed $asset
	 * @return string
	 */
	private function getAssetFilepath($asset) {
		if (is_array($asset)) {
			return $asset['filepath'];
		} else {
			return $asset;
		}
	}
	
	/**
	 * @param integer $requestId
	 * @param string $extension
	 * @param string $asset
	 * @throws Exception
	 * @return string
	 */
	public function assetMime($requestId, $extension, $asset) {
		$metadata = $this->metadataForRequestId($requestId);
		if (! isset($metadata[$extension]) || ! isset($metadata[$extension]['assets']) || ! isset($metadata[$extension]['assets'][$asset]) || ! isset($metadata[$extension]['assets'][$asset]['mime'])) {
			return 'text/html';
		}
		
		return $metadata[$extension]['assets'][$asset]['mime'];
	}
	

	public function removeDevBarRequests(array $ids) {
		$effected = $this->getTableGateway()->delete(array("request_id IN (" . implode(",", $ids) . ")"));
		Log::debug("Deleted $effected rows from devbar_extension_metadata");
	}
}
