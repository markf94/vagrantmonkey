<?php
namespace WebAPI\View\Helper;

use Zend\View\Helper\AbstractHelper;

class strtotimeAddTZOffset extends AbstractHelper {
	
	/**
	 * @var string
	 * The offset string to use in the strtotime
	 */
	private $tzOffset = null;
	
	/**
	 * @param integer $timestamp
	 * @return string
	 * BUG ZSRV-7679
	 * This view helper takes a string with no timestamp and convert it into timestamp that includes the
	 * currect timezone offset of the db server
	 */
	public function __invoke($timestamp) {
		// if the offset is not initialized
		if (is_null($this->tzOffset)) {
			$tz = @date_default_timezone_get();
			$tz = $this->getTimezoneOffset($tz);
			
			// because strtotime function adds the timezone offset to given time string and also
			// the timestamp need to be offseted to the db server time we need to add/desc the offset twice
			if ($tz >= 0) {
				$this->tzOffset = ' -' . (2 * $tz) .' hours';
			} else {
				$this->tzOffset = ' +' . abs(2 * $tz) .' hours';
			}
		}

		return strtotime($timestamp . $this->tzOffset);
	}
	
	private function getTimezoneOffset($remote_tz, $origin_tz = null) {
		if($origin_tz === null) {
			if(!is_string($origin_tz = date_default_timezone_get())) {
				return false; // A UTC timestamp was returned -- bail out!
			}
		}
		$origin_dtz = new \DateTimeZone($origin_tz);
		$remote_dtz = new \DateTimeZone($remote_tz);
		$origin_dt = new \DateTime("now", $origin_dtz);
		$remote_dt = new \DateTime("now", $remote_dtz);
		$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
	
		if (strpos($remote_tz, '+') !== false) {
			$extraOffset = (int) substr($remote_tz, strpos($remote_tz, '+') + 1);
			$offset += $extraOffset;
		} elseif (strpos($remote_tz, '-') !== false) {
			$extraOffset = (int) substr($remote_tz, strpos($remote_tz, '-') + 1);
			$offset -= $extraOffset;
		}
	
		return $offset;
	}
}


