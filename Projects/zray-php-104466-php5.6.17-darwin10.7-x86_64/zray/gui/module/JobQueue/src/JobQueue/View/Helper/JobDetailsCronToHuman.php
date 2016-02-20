<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class JobDetailsCronToHuman extends AbstractHelper {
	
	/**
	 * Try to convert the crontab style line to a human readable format.
	 * Supports only formats the user can add through the UI
	 * If the function can't convert it properly, it will return the crontab line
	 * as is.
	 *
	 * @param string $line
	 * @return string
	 */
	public function __invoke($line) {
        
		$parts = explode(' ', $line);
		switch (count($parts)) {
			case 1:
				// hourly at 15 minutes after the hour - 15
				// every 40 minutes - */40
				return self::getTimeByMintues($line);
				break;
			case 2:
				// daily at 11:45 - 45 11
				// every five hours - 0 */5
				return self::getTimeByHours($line);
				break;
			case 3:
				// monthly on the 3rd and 15th at 11:25 - 25 11 3,15
				return self::getTimeMonthly($line);
				break;
			case 5:
				// weekly on Monday,Wednesday,Friday at 15:00 - 00 15 * * 1,3,5
				return self::getTimeWeekly($line);
				break;
			default:
				return $line;
		}
	}
	
	/**
	 * @param string $line
	 * @return Zwas_Text|string
	 */
	private static function getTimeByMintues($line) {
		if (ctype_digit($line)) {
			// hourly at 15 minutes after the hour - 15
			return _t('%s minutes after every given hour', array($line));
		} else if (0 === strpos($line, '*/')) {
			// every 40 minutes - */40
			$time = substr($line, 2, strlen($line)-2);
			if (ctype_digit($time)) {
				return _t('Every %s', array(self::getRepeatedString($time, 'minute')));
			}
		}
		
		return $line;// the line doesn't match the allowed formats
	}
	
	private static function getRepeatedString($value, $unit) {
		if ($value > 1) {
			return "{$value} {$unit}s";
		}
		
		return $unit;
	}
	
	/**
	 * @return string
	 * @return Zwas_Text|string
	 */
	private static function getTimeByHours($line) {
		$parts = explode(' ', $line);
		if (2 === count($parts)) {
			$minutes	= $parts[0];
			$hours		= $parts[1];
			if (ctype_digit($minutes)) {
				if (ctype_digit($hours)) {
					// daily at 11:45 - 45 11
					return _t('Daily at %s',
							array(self::getFormattedTime($hours, $minutes)));
				} 
			}
			
			if (0 === strpos($hours, '*/')) {
				// every five hours - 0 */5
				$time = substr($hours, 2, strlen($line)-2);
				return _t('Every %s', array(self::getRepeatedString($time, 'hour')));
			}
			
			// different format of 'Every x min/hours/days' 'M 7'/'H 8'/'D 9'
			if ($minutes == 'M') {
				return _t('Every %s', array(self::getRepeatedString($hours, 'minute')));
			} else if ($minutes == 'H') {
				return _t('Every %s', array(self::getRepeatedString($hours, 'hour')));
			} else if ($minutes == 'D') {
				return _t('Every %s', array(self::getRepeatedString($hours, 'day')));
			}
		}
				
		
		return $line;// the line doesn't match the allowed formats
	}
	
	/**
	 * @return string
	 * @return Zwas_Text|string
	 */
	private static function getTimeMonthly($line) {
		$parts = explode(' ', $line);
		if (3 === count($parts)) {
			// monthly on the 3rd and 15th at 11:25 - 25 11 3
			$minutes		= $parts[0];
			$hours			= $parts[1];
			$daysOfMonth	= $parts[2];
			if (ctype_digit($minutes) && ctype_digit($hours)) {
				return _t('On the %s of every month at %s',
						array(self::getTextualDaysOfMonth($daysOfMonth), self::getFormattedTime($hours, $minutes)));
			}
		}
		return $line;
	}
	
	/**
	 * @return string
	 * @return Zwas_Text|string
	 */
	private static function getTimeWeekly($line) {
		$parts = explode(' ', $line);
		if (5 === count($parts)) {
			$minutes		= $parts[0];
			$hours			= $parts[1];
			$daysOfMonth	= $parts[2];
			$months			= $parts[3];
			$daysOfWeek		= $parts[4];
				
			// weekly - 00 15 * * 1,3,5
			if (('*' === $daysOfMonth) && ('*' === $months)) {
				if (ctype_digit($minutes) && ctype_digit($hours)) {
					// safety from 00 15 * * 1-3
					if (! preg_match('/^[0-6]{1}(?:\,[0-6]{1})*$/', $daysOfWeek)) {
						return $line;
					}
					return _t('Weekly on %s at %s',
							array(self::getTextualDaysOfWeek($daysOfWeek), self::getFormattedTime($hours, $minutes)));
				}
			}
		}
		return $line;
	}
	
	/**
	 * @param integer $hours
	 * @param integer $minutes
	 * @return string
	 */
	private static function getFormattedTime($hours, $minutes) {
		return sprintf('%02d:%02d', $hours, $minutes);
	}
	/**
	 * @param string $days comma separated cron days notation 0-Sunday, 1-Monday, etc.
	 * @return string comma separated day names e.g. Sunday,Monday,Wednesday
	 */
	private static function getTextualDaysOfWeek($days) {
		$daysOfWeek = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
		$textualDays = array();
		foreach(explode(',', $days) as $day) {
			$textualDays[] = $daysOfWeek[$day];
		}
		return implode(',', $textualDays);
	}
	
	/**
	 * @param string $days comma separated cron days of month notation
	 * @return string comman separated days of the month
	 */
	private static function getTextualDaysOfMonth($days) {
		$textualDays = array();
		foreach(explode(',', $days) as $day) {
			// the unix epoch time is the first day of the month, so in order to convert 2 to the 2nd
			// $day-1 needs to be added to the unix epoch time
			$dayOfMonth = ($day-1) * 86400; // 24*60*60
			$textualDays[] = date('jS', $dayOfMonth);
		}
		return implode(',', $textualDays);
	}
}

