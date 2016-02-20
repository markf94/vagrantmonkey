<?php

namespace JobQueue;

interface JobQueueInterface {

	const TYPE_HTTP_RELATIVE				= "0";
	const TYPE_HTTP 						= "1";
	const TYPE_SHELL 						= "2";
	const JOB_TYPE_HTTP_RELATIVE 			= "1";
	const JOB_TYPE_HTTP 					= "2";
	const JOB_TYPE_SHELL 					= "4";
	const PRIORITY_LOW 						= "0";
	const PRIORITY_NORMAL 					= "1";
	const PRIORITY_HIGH 					= "2";
	const PRIORITY_URGENT 					= "3";
	const JOB_PRIORITY_LOW 					= "1";
	const JOB_PRIORITY_NORMAL 				= "2";
	const JOB_PRIORITY_HIGH 				= "4";
	const JOB_PRIORITY_URGENT 				= "8";
	const STATUS_RULE_ACTIVE				= "0";
	const STATUS_PENDING 					= "0";
	const STATUS_WAITING_PREDECESSOR 		= "1";
	const STATUS_RUNNING 					= "2";
	const STATUS_COMPLETED 					= "3";
	const STATUS_OK 						= "4";
	const STATUS_FAILED 					= "5";
	const STATUS_LOGICALLY_FAILED 			= "6";
	const STATUS_TIMEOUT 					= "7";
	const STATUS_REMOVED 					= "8";
	const STATUS_SCHEDULED 					= "9";
	const STATUS_SUSPENDED 					= "10";
	const SORT_NONE 						= "0";
	const SORT_BY_ID 						= "1";
	const SORT_BY_TYPE 						= "2";
	const SORT_BY_SCRIPT 					= "3";
	const SORT_BY_APPLICATION 				= "4";
	const SORT_BY_NAME 						= "5";
	const SORT_BY_PRIORITY 					= "6";
	const SORT_BY_STATUS 					= "7";
	const SORT_BY_PREDECESSOR 				= "8";
	const SORT_BY_PERSISTENCE 				= "9";
	const SORT_BY_CREATION_TIME 			= "10";
	const SORT_BY_SCHEDULE_TIME 			= "11";
	const SORT_BY_START_TIME 				= "12";
	const SORT_BY_END_TIME 					= "13";
	const SORT_BY_RUNNING_JOBS_COUNT 		= "14";
	const SORT_BY_PENDING_JOBS_COUNT 		= "15";
	const SORT_ASC 							= "0";
	const SORT_DESC 						= "1";
	const OK 								= "0";
	const FAILED 							= "1";
	const QUEUE_SUSPENDED                   = "0";
	const QUEUE_ACTIVE						= "1";
	const QUEUE_DELETED                     = "2";
}