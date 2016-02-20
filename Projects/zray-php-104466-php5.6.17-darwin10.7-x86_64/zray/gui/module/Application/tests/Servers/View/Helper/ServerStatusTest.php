<?php

namespace Servers\View\Helper;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class ServerStatusTest extends TestCase
{

	public function testServerStatus() {
		$helper = new ServerStatus();
		self::assertEquals('Warning', $helper(ServerStatus::STATUS_WARNING));
		self::assertEquals('disabled', $helper(ServerStatus::STATUS_DISABLED));
		self::assertEquals('disabling', $helper(ServerStatus::STATUS_DISABLING_SERVER));
		self::assertEquals('disconnecting', $helper(ServerStatus::STATUS_DISCONNECTING_FROM_CLUSTER));
		self::assertEquals('Error', $helper(ServerStatus::STATUS_ERROR));
		self::assertEquals('notExists', $helper(ServerStatus::STATUS_NOT_EXIST));
		self::assertEquals('notResponding', $helper(ServerStatus::STATUS_NOT_RESPONDING));
		self::assertEquals('OK', $helper(ServerStatus::STATUS_OK));
		self::assertEquals('reloadingConfigurations', $helper(ServerStatus::STATUS_RELOADING));
		self::assertEquals('pendingRestart', $helper(ServerStatus::STATUS_RESTART_REQUIRED));
		self::assertEquals('pendingDisable', $helper(ServerStatus::STATUS_SERVER_PENDING_DISABLE));
		self::assertEquals('pendingRemoval', $helper(ServerStatus::STATUS_SERVER_PENDING_REMOVAL));
		self::assertEquals('redeploying', $helper(ServerStatus::STATUS_SERVER_REDEPLOYING));
		self::assertEquals('restarting', $helper(ServerStatus::STATUS_SERVER_RESTARTING));
	}

	public function testServerStatusStatic() {
		self::assertEquals('Warning', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_WARNING));
		self::assertEquals('disabled', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_DISABLED));
		self::assertEquals('disabling', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_DISABLING_SERVER));
		self::assertEquals('disconnecting', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_DISCONNECTING_FROM_CLUSTER));
		self::assertEquals('Error', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_ERROR));
		self::assertEquals('notExists', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_NOT_EXIST));
		self::assertEquals('notResponding', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_NOT_RESPONDING));
		self::assertEquals('OK', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_OK));
		self::assertEquals('reloadingConfigurations', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_RELOADING));
		self::assertEquals('pendingRestart', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_RESTART_REQUIRED));
		self::assertEquals('pendingDisable', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_SERVER_PENDING_DISABLE));
		self::assertEquals('pendingRemoval', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_SERVER_PENDING_REMOVAL));
		self::assertEquals('redeploying', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_SERVER_REDEPLOYING));
		self::assertEquals('restarting', ServerStatus::getServerStatusAsString(ServerStatus::STATUS_SERVER_RESTARTING));
	}
	
}

