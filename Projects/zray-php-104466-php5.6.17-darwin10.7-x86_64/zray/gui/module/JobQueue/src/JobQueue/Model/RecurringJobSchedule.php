<?php

namespace JobQueue\Model;

use ZendServer\Log\Log;

class RecurringJobSchedule {

    public $type;
    
    public $every = 'schedule-every-hours';

    public $everyMinutes = '';

    public $everyHours = '';
    
    public $everyDays = '';

    public $hourlyMinute = '';

    public $dailyMinute = '';

    public $dailyHour = '';

    public $monthlyMinute = '';

    public $monthlyHour = '';

    public $monthlyDay = '';

    public $weeklyMinute = '';

    public $weeklyHour = '';

    public $weeklyDay = array();

    function __construct($schedule) {
        $this->parseSchedule($schedule);
    }
    
    
    /**
     * Get the $type
     */
    public function getType() {
        return $this->type;
    }

	/**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

	/**
     * Get the $every
     */
    public function getEvery() {
        return $this->every;
    }

	/**
     * Get the $everyMinutes
     */
    public function getEveryMinutes() {
        return $this->everyMinutes;
    }

    /**
     * Get the $everyDays
     */
    public function getEveryDays() {
    	return $this->everyDays;
    }
    
	/**
     * Get the $everyHours
     */
    public function getEveryHours() {
        return $this->everyHours;
    }

	/**
     * Get the $hourlyMinute
     */
    public function getHourlyMinute() {
        return $this->hourlyMinute;
    }

	/**
     * Get the $dailyMinute
     */
    public function getDailyMinute() {
        return $this->dailyMinute;
    }

	/**
     * Get the $dailyHour
     */
    public function getDailyHour() {
        return $this->dailyHour;
    }

	/**
     * Get the $monthlyMinute
     */
    public function getMonthlyMinute() {
        return $this->monthlyMinute;
    }

	/**
     * Get the $monthlyHour
     */
    public function getMonthlyHour() {
        return $this->monthlyHour;
    }

	/**
     * Get the $monthlyDay
     */
    public function getMonthlyDay() {
        return $this->monthlyDay;
    }

	/**
     * Get the $weeklyMinute
     */
    public function getWeeklyMinute() {
        return $this->weeklyMinute;
    }

	/**
     * Get the $weeklyHour
     */
    public function getWeeklyHour() {
        return $this->weeklyHour;
    }

	/**
     * Get the $weeklyDay
     */
    public function getWeeklyDay() {
        return $this->weeklyDay;
    }

	/**
     * @param string $every
     */
    public function setEvery($every) {
        $this->every = $every;
    }

	/**
     * @param string $everyMinutes
     */
    public function setEveryMinutes($everyMinutes) {
        $this->everyMinutes = $everyMinutes;
    }

	/**
     * @param string $everyHours
     */
    public function setEveryHours($everyHours) {
        $this->everyHours = $everyHours;
    }
    
    /**
     * @param string $everyDays
     */
    public function setEveryHDays($everyDays) {
    	$this->everyDays = $everyDays;
    }

	/**
     * @param string $hourlyMinute
     */
    public function setHourlyMinute($hourlyMinute) {
        $this->hourlyMinute = $hourlyMinute;
    }

	/**
     * @param string $dailyMinute
     */
    public function setDailyMinute($dailyMinute) {
        $this->dailyMinute = $dailyMinute;
    }

	/**
     * @param string $dailyHour
     */
    public function setDailyHour($dailyHour) {
        $this->dailyHour = $dailyHour;
    }

	/**
     * @param string $monthlyMinute
     */
    public function setMonthlyMinute($monthlyMinute) {
        $this->monthlyMinute = $monthlyMinute;
    }

	/**
     * @param string $monthlyHour
     */
    public function setMonthlyHour($monthlyHour) {
        $this->monthlyHour = $monthlyHour;
    }

	/**
     * @param string $monthlyDay
     */
    public function setMonthlyDay($monthlyDay) {
        $this->monthlyDay = $monthlyDay;
    }

	/**
     * @param string $weeklyMinute
     */
    public function setWeeklyMinute($weeklyMinute) {
        $this->weeklyMinute = $weeklyMinute;
    }

	/**
     * @param string $weeklyHour
     */
    public function setWeeklyHour($weeklyHour) {
        $this->weeklyHour = $weeklyHour;
    }

	/**
     * @param multitype: $weeklyDay
     */
    public function setWeeklyDay($weeklyDay) {
        $this->weeklyDay = $weeklyDay;
    }

	/**
       * Parses cron command and prepares list of variale/value pairs for GUI
       *
       * @return object
       */
    public function parseSchedule($schedule) {
    	
        if($schedule == '') {
            $this->type = 'schedule-every';
            $this->every = 'schedule-every-hours';
        } elseif ($schedule[0] == 'D') {
        	$this->type = 'schedule-every';
        	$this->every = 'schedule-every-days';
        	$exploded = explode(' ', $schedule);
        	$this->everyDays = $exploded[1];
        } elseif ($schedule[0] == 'H') {
        	$this->type = 'schedule-every';
        	$this->every = 'schedule-every-hours';
        	$exploded = explode(' ', $schedule);
        	$this->everyHours = $exploded[1];
        } elseif ($schedule[0] == 'M') {
        	$this->type = 'schedule-every';
        	$this->every = 'schedule-every-minutes';
        	$exploded = explode(' ', $schedule);
        	$this->everyMinutes = $exploded[1];
        } elseif(preg_match('/^\*(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*)?)?)?)?$/', $schedule)) {
            $this->type = 'schedule-every';
            $this->every = 'schedule-every-minutes';
            $this->everyMinutes = '1';
        } elseif(preg_match('/^\*\/([\d]+)(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*)?)?)?)?$/', $schedule, $match)) {
            $this->type = 'schedule-every';
            $this->every = 'schedule-every-minutes';
            $this->everyMinutes = $match[1];
            
        } elseif(preg_match('/^[\d]+[ \t]+[\d]+[ \t]+\*\/([\d]+)(?:[ \t]+\*(?:[ \t]+\*)?)?$/', $schedule, $match)) {  //format: \d \d /*x like  '0 0 */2 * *' every 2 days
            $this->type = 'schedule-every';
            $this->every = 'schedule-every-days';
            $this->everyDays = $match[1];
            
        } elseif(preg_match('/^0+(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*)?)?)?)?$/', $schedule, $match)) {
            $this->type = 'schedule-every';
            $this->every = 'schedule-every-hours';
            $this->everyHours = '1';
        } elseif(	preg_match('/^[\d]+[ \t]+[\d]+[ \t]+\*\/([\d]+)(?:[ \t]+\*(?:[ \t]+\*)?)?$/', $schedule, $match) ||  //format: 1 /*x or 0 /*x
        			preg_match('/^[0\*]+[ \t]+\*\/([\d]+)(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*)?)?)?$/', $schedule, $match)) {
            $this->type = 'schedule-every';
            $this->every = 'schedule-every-hours';
            $this->everyHours = $match[1];
            
        } elseif(preg_match('/^([\d]+)(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*)?)?)?)?$/', $schedule, $match)) {
            $this->type = 'schedule-hourly';
            $this->hourlyMinute = $match[1];
        } elseif(preg_match('/^([\d]+)[ \t]+([\d]+)(?:[ \t]+\*(?:[ \t]+\*(?:[ \t]+\*)?)?)?$/', $schedule, $match)) {
            $this->type = 'schedule-daily';
            $this->dailyMinute = $match[1];
            $this->dailyHour = $match[2];
        } elseif(preg_match('/^([\d]+)[ \t]+([\d]+)[ \t]+([\d,]+)(?:[ \t]+\*(?:[ \t]+\*)?)?$/', $schedule, $match)) {
            $this->type = 'schedule-monthly';
            $this->monthlyMinute = $match[1];
            $this->monthlyHour = $match[2];
            $this->monthlyDay = $match[3];
        } elseif(preg_match('/^([\d]+)[ \t]+([\d]+)[ \t]+\*[ \t]+\*[ \t]+([\d,]+)$/', $schedule, $match)) {
            $this->type = 'schedule-weekly';
            $this->weeklyMinute = $match[1];
            $this->weeklyHour = $match[2];
            $this->weeklyDay = explode(",", $match[3]);
        } else {
            $this->type = 'schedule-custom';
        }
    }
}