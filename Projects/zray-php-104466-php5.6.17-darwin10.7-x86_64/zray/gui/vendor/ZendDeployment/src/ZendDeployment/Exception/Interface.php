<?php

class ZendDeployment_Exception_Interface extends Exception {
	
	/**
	 * Internal deployment error
	 */
	const INTERNAL_SERVER_ERROR 		= 0;
	
	/**
	 * File system error
	 */
	const FILE_SYSTEM_ERROR				= 1;
	
	/**
	 * Database error
	 */
	const DATABASE_ERROR				= 2;
	
	/**
	 * Database max_allowed_packet is misconfigured
	 */
	const DATABASE_MISCONFIGURED		= 3;
	
	/**
	 * VHost not found
	 */
	const VHOST_NOT_FOUND				= 4;
	
	/**
	 * Base url already exists
	 */
	const EXISTING_BASE_URL_ERROR		= 5;
	
	/**
	 * Package is invalid
	 */
	const INVALID_PACKAGE				= 6;
	
	/**
	 * Package descriptor is invalid
	 */
	const INVALID_PACKAGE_DESCRIPTOR	= 7;
	
	/**
	 * Application is not rollbackable
	 */
	const APPLICATION_NOT_ROLLBACKABLE  = 8;
	
	/**
	 * Action is already in progress
	 */
	const ERROR_ALREADY_IN_PROGRESS     = 9;
	
	/**
	 * Plugin name already exists
	 */
	const EXISTING_PLUGIN_NAME_ERROR		= 10;
	
	
}
