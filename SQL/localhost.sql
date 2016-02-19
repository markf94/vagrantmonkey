-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 19, 2016 at 11:46 PM
-- Server version: 5.5.46-0ubuntu0.14.04.2
-- PHP Version: 5.6.15-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sites_monkeytestmoritz`
--

-- --------------------------------------------------------

--
-- Table structure for table `j_assets`
--

CREATE TABLE IF NOT EXISTS `j_assets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set parent.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `level` int(10) unsigned NOT NULL COMMENT 'The cached level in the nested tree.',
  `name` varchar(50) NOT NULL COMMENT 'The unique name for the asset.\n',
  `title` varchar(100) NOT NULL COMMENT 'The descriptive title for the asset.',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_asset_name` (`name`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=92 ;

--
-- Dumping data for table `j_assets`
--

INSERT INTO `j_assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES
(1, 0, 0, 177, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'),
(2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'),
(3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'),
(7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(8, 1, 17, 20, 1, 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'),
(9, 1, 21, 22, 1, 'com_cpanel', 'com_cpanel', '{}'),
(10, 1, 23, 24, 1, 'com_installer', 'com_installer', '{"core.admin":[],"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'),
(11, 1, 25, 26, 1, 'com_languages', 'com_languages', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(12, 1, 27, 28, 1, 'com_login', 'com_login', '{}'),
(13, 1, 29, 30, 1, 'com_mailto', 'com_mailto', '{}'),
(14, 1, 31, 32, 1, 'com_massmail', 'com_massmail', '{}'),
(15, 1, 33, 34, 1, 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'),
(16, 1, 35, 36, 1, 'com_menus', 'com_menus', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(17, 1, 37, 38, 1, 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(18, 1, 39, 142, 1, 'com_modules', 'com_modules', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(19, 1, 143, 146, 1, 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(20, 1, 147, 148, 1, 'com_plugins', 'com_plugins', '{"core.admin":{"7":1},"core.manage":[],"core.edit":[],"core.edit.state":[]}'),
(21, 1, 149, 150, 1, 'com_redirect', 'com_redirect', '{"core.admin":{"7":1},"core.manage":[]}'),
(22, 1, 151, 152, 1, 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(23, 1, 153, 154, 1, 'com_templates', 'com_templates', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(24, 1, 155, 158, 1, 'com_users', 'com_users', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(26, 1, 159, 160, 1, 'com_wrapper', 'com_wrapper', '{}'),
(27, 8, 18, 19, 2, 'com_content.category.2', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(30, 19, 144, 145, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(32, 24, 156, 157, 1, 'com_users.category.7', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(33, 1, 161, 162, 1, 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(34, 1, 163, 164, 1, 'com_joomlaupdate', 'com_joomlaupdate', '{"core.admin":[],"core.manage":[],"core.delete":[],"core.edit.state":[]}'),
(35, 1, 165, 166, 1, 'com_tags', 'com_tags', '{"core.admin":[],"core.manage":[],"core.manage":[],"core.delete":[],"core.edit.state":[]}'),
(36, 1, 167, 168, 1, 'com_contenthistory', 'com_contenthistory', '{}'),
(37, 1, 169, 170, 1, 'com_ajax', 'com_ajax', '{}'),
(38, 1, 171, 172, 1, 'com_postinstall', 'com_postinstall', '{}'),
(39, 18, 40, 41, 2, 'com_modules.module.1', 'Main Menu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(40, 18, 42, 43, 2, 'com_modules.module.2', 'Login', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(41, 18, 44, 45, 2, 'com_modules.module.3', 'Popular Articles', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(42, 18, 46, 47, 2, 'com_modules.module.4', 'Recently Added Articles', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(43, 18, 48, 49, 2, 'com_modules.module.8', 'Toolbar', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(44, 18, 50, 51, 2, 'com_modules.module.9', 'Quick Icons', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(45, 18, 52, 53, 2, 'com_modules.module.10', 'Logged-in Users', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(46, 18, 54, 55, 2, 'com_modules.module.12', 'Admin Menu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(47, 18, 56, 57, 2, 'com_modules.module.13', 'Admin Submenu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(48, 18, 58, 59, 2, 'com_modules.module.14', 'User Status', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(49, 18, 60, 61, 2, 'com_modules.module.15', 'Title', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(50, 18, 62, 63, 2, 'com_modules.module.16', 'Login Form', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(51, 18, 64, 65, 2, 'com_modules.module.17', 'Breadcrumbs', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(52, 18, 66, 67, 2, 'com_modules.module.79', 'Multilanguage status', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(53, 18, 68, 69, 2, 'com_modules.module.86', 'Joomla Version', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(54, 1, 173, 174, 1, 'com_gantry', 'Gantry', '{}'),
(55, 18, 70, 71, 2, 'com_modules.module.87', 'RokNavMenu', '{}'),
(56, 1, 175, 176, 1, 'com_easysocial', 'com_easysocial', '{}'),
(57, 18, 72, 73, 2, 'com_modules.module.88', 'EasySocial Albums', '{}'),
(58, 18, 74, 75, 2, 'com_modules.module.89', 'EasySocial Calendar', '{}'),
(59, 18, 76, 77, 2, 'com_modules.module.90', 'EasySocial Dating Search', '{}'),
(60, 18, 78, 79, 2, 'com_modules.module.91', 'EasySocial Dropdown Menu', '{}'),
(61, 18, 80, 81, 2, 'com_modules.module.92', 'Recent Blog Posts (EasyBlog)', '{}'),
(62, 18, 82, 83, 2, 'com_modules.module.93', 'EasySocial Event Menu', '{}'),
(63, 18, 84, 85, 2, 'com_modules.module.94', 'EasySocial Events', '{}'),
(64, 18, 86, 87, 2, 'com_modules.module.95', 'EasySocial Event Categories', '{}'),
(65, 18, 88, 89, 2, 'com_modules.module.96', 'EasySocial Followers', '{}'),
(66, 18, 90, 91, 2, 'com_modules.module.97', 'EasySocial Friends', '{}'),
(67, 18, 92, 93, 2, 'com_modules.module.98', 'EasySocial Group Menu', '{}'),
(68, 18, 94, 95, 2, 'com_modules.module.99', 'EasySocial Groups', '{}'),
(69, 18, 96, 97, 2, 'com_modules.module.100', 'EasySocial Group Categories', '{}'),
(70, 18, 98, 99, 2, 'com_modules.module.101', 'EasySocial Leader Board', '{}'),
(71, 18, 100, 101, 2, 'com_modules.module.102', 'EasySocial Log Box', '{}'),
(72, 18, 102, 103, 2, 'com_modules.module.103', 'EasySocial Login', '{}'),
(73, 18, 104, 105, 2, 'com_modules.module.104', 'EasySocial Menu', '{}'),
(74, 18, 106, 107, 2, 'com_modules.module.105', 'EasySocial Notifications', '{}'),
(75, 18, 108, 109, 2, 'com_modules.module.106', 'EasySocial OAuth Login', '{}'),
(76, 18, 110, 111, 2, 'com_modules.module.107', 'EasySocial Recent Photos', '{}'),
(77, 18, 112, 113, 2, 'com_modules.module.108', 'EasySocial Profile Completeness', '{}'),
(78, 18, 114, 115, 2, 'com_modules.module.109', 'EasySocial Quick Post', '{}'),
(79, 18, 116, 117, 2, 'com_modules.module.110', 'EasySocial Recent Polls', '{}'),
(80, 18, 118, 119, 2, 'com_modules.module.111', 'EasySocial Quick Registration', '{}'),
(81, 18, 120, 121, 2, 'com_modules.module.112', 'EasySocial Registration Requester', '{}'),
(82, 18, 122, 123, 2, 'com_modules.module.113', 'EasySocial Search', '{}'),
(83, 18, 124, 125, 2, 'com_modules.module.114', 'EasySocial Stream', '{}'),
(84, 18, 126, 127, 2, 'com_modules.module.115', 'EasySocial Toolbar', '{}'),
(85, 18, 128, 129, 2, 'com_modules.module.116', 'EasySocial Users', '{}'),
(86, 18, 130, 131, 2, 'com_modules.module.117', 'EasySocial Videos Module', '{}'),
(87, 18, 132, 133, 2, 'com_modules.module.118', 'Online Users', '{}'),
(88, 18, 134, 135, 2, 'com_modules.module.119', 'Recent Users', '{}'),
(89, 18, 136, 137, 2, 'com_modules.module.120', 'Recent Albums', '{}'),
(90, 18, 138, 139, 2, 'com_modules.module.121', 'Leaderboard', '{}'),
(91, 18, 140, 141, 2, 'com_modules.module.122', 'Dating Search', '{}');

-- --------------------------------------------------------

--
-- Table structure for table `j_associations`
--

CREATE TABLE IF NOT EXISTS `j_associations` (
  `id` int(11) NOT NULL COMMENT 'A reference to the associated item.',
  `context` varchar(50) NOT NULL COMMENT 'The context of the associated item.',
  `key` char(32) NOT NULL COMMENT 'The key for the association computed from an md5 on associated ids.',
  PRIMARY KEY (`context`,`id`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_banner_clients`
--

CREATE TABLE IF NOT EXISTS `j_banner_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `contact` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `extrainfo` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `metakey` text NOT NULL,
  `own_prefix` tinyint(4) NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  KEY `idx_own_prefix` (`own_prefix`),
  KEY `idx_metakey_prefix` (`metakey_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_banner_tracks`
--

CREATE TABLE IF NOT EXISTS `j_banner_tracks` (
  `track_date` datetime NOT NULL,
  `track_type` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`track_date`,`track_type`,`banner_id`),
  KEY `idx_track_date` (`track_date`),
  KEY `idx_track_type` (`track_type`),
  KEY `idx_banner_id` (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_banners`
--

CREATE TABLE IF NOT EXISTS `j_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `imptotal` int(11) NOT NULL DEFAULT '0',
  `impmade` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `clickurl` varchar(200) NOT NULL DEFAULT '',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `custombannercode` varchar(2048) NOT NULL,
  `sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `params` text NOT NULL,
  `own_prefix` tinyint(1) NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reset` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_own_prefix` (`own_prefix`),
  KEY `idx_metakey_prefix` (`metakey_prefix`),
  KEY `idx_banner_catid` (`catid`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_categories`
--

CREATE TABLE IF NOT EXISTS `j_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the j_assets table.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `extension` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `cat_idx` (`extension`,`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `j_categories`
--

INSERT INTO `j_categories` (`id`, `asset_id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `extension`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `hits`, `language`, `version`) VALUES
(1, 0, 0, 0, 11, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '{}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(7, 32, 1, 9, 10, 1, 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_contact_details`
--

CREATE TABLE IF NOT EXISTS `j_contact_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `con_position` varchar(255) DEFAULT NULL,
  `address` text,
  `suburb` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postcode` varchar(100) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `misc` mediumtext,
  `image` varchar(255) DEFAULT NULL,
  `email_to` varchar(255) DEFAULT NULL,
  `default_con` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `catid` int(11) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `webpage` varchar(255) NOT NULL DEFAULT '',
  `sortname1` varchar(255) NOT NULL,
  `sortname2` varchar(255) NOT NULL,
  `sortname3` varchar(255) NOT NULL,
  `language` char(7) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_content`
--

CREATE TABLE IF NOT EXISTS `j_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the j_assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_content_frontpage`
--

CREATE TABLE IF NOT EXISTS `j_content_frontpage` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_content_rating`
--

CREATE TABLE IF NOT EXISTS `j_content_rating` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_content_types`
--

CREATE TABLE IF NOT EXISTS `j_content_types` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) NOT NULL DEFAULT '',
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `table` varchar(255) NOT NULL DEFAULT '',
  `rules` text NOT NULL,
  `field_mappings` text NOT NULL,
  `router` varchar(255) NOT NULL DEFAULT '',
  `content_history_options` varchar(5120) DEFAULT NULL COMMENT 'JSON string for com_contenthistory options',
  PRIMARY KEY (`type_id`),
  KEY `idx_alias` (`type_alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `j_content_types`
--

INSERT INTO `j_content_types` (`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
(1, 'Article', 'com_content.article', '{"special":{"dbtable":"j_content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}, "special":{"fulltext":"fulltext"}}', 'ContentHelperRoute::getArticleRoute', '{"formFile":"administrator\\/components\\/com_content\\/models\\/forms\\/article.xml", "hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ]}'),
(2, 'Contact', 'com_contact.contact', '{"special":{"dbtable":"j_contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}}', 'ContactHelperRoute::getContactRoute', '{"formFile":"administrator\\/components\\/com_contact\\/models\\/forms\\/contact.xml","hideFields":["default_con","checked_out","checked_out_time","version","xreference"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[ {"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ] }'),
(3, 'Newsfeed', 'com_newsfeeds.newsfeed', '{"special":{"dbtable":"j_newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}}', 'NewsfeedsHelperRoute::getNewsfeedRoute', '{"formFile":"administrator\\/components\\/com_newsfeeds\\/models\\/forms\\/newsfeed.xml","hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ]}'),
(4, 'User', 'com_users.user', '{"special":{"dbtable":"j_users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{}}', 'UsersHelperRoute::getUserRoute', ''),
(5, 'Article Category', 'com_content.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContentHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(6, 'Contact Category', 'com_contact.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContactHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(7, 'Newsfeeds Category', 'com_newsfeeds.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'NewsfeedsHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(8, 'Tag', 'com_tags.tag', '{"special":{"dbtable":"j_tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}}', 'TagsHelperRoute::getTagRoute', '{"formFile":"administrator\\/components\\/com_tags\\/models\\/forms\\/tag.xml", "hideFields":["checked_out","checked_out_time","version", "lft", "rgt", "level", "path", "urls", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}]}'),
(9, 'Banner', 'com_banners.banner', '{"special":{"dbtable":"j_banners","key":"id","type":"Banner","prefix":"BannersTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"null","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"null"}, "special":{"imptotal":"imptotal", "impmade":"impmade", "clicks":"clicks", "clickurl":"clickurl", "custombannercode":"custombannercode", "cid":"cid", "purchase_type":"purchase_type", "track_impressions":"track_impressions", "track_clicks":"track_clicks"}}', '', '{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/banner.xml", "hideFields":["checked_out","checked_out_time","version", "reset"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "imptotal", "impmade", "reset"], "convertToInt":["publish_up", "publish_down", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"cid","targetTable":"j_banner_clients","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ]}'),
(10, 'Banners Category', 'com_banners.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(11, 'Banner Client', 'com_banners.client', '{"special":{"dbtable":"j_banner_clients","key":"id","type":"Client","prefix":"BannersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/client.xml", "hideFields":["checked_out","checked_out_time"], "ignoreChanges":["checked_out", "checked_out_time"], "convertToInt":[], "displayLookup":[]}'),
(12, 'User Notes', 'com_users.note', '{"special":{"dbtable":"j_user_notes","key":"id","type":"Note","prefix":"UsersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_users\\/models\\/forms\\/note.xml", "hideFields":["checked_out","checked_out_time", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}]}'),
(13, 'User Notes Category', 'com_users.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}');

-- --------------------------------------------------------

--
-- Table structure for table `j_contentitem_tag_map`
--

CREATE TABLE IF NOT EXISTS `j_contentitem_tag_map` (
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `core_content_id` int(10) unsigned NOT NULL COMMENT 'PK from the core content table',
  `content_item_id` int(11) NOT NULL COMMENT 'PK from the content type table',
  `tag_id` int(10) unsigned NOT NULL COMMENT 'PK from the tag table',
  `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date of most recent save for this tag-item',
  `type_id` mediumint(8) NOT NULL COMMENT 'PK from the content_type table',
  UNIQUE KEY `uc_ItemnameTagid` (`type_id`,`content_item_id`,`tag_id`),
  KEY `idx_tag_type` (`tag_id`,`type_id`),
  KEY `idx_date_id` (`tag_date`,`tag_id`),
  KEY `idx_tag` (`tag_id`),
  KEY `idx_type` (`type_id`),
  KEY `idx_core_content_id` (`core_content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps items from content tables to tags';

-- --------------------------------------------------------

--
-- Table structure for table `j_core_log_searches`
--

CREATE TABLE IF NOT EXISTS `j_core_log_searches` (
  `search_term` varchar(128) NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_extensions`
--

CREATE TABLE IF NOT EXISTS `j_extensions` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `element` varchar(100) NOT NULL,
  `folder` varchar(100) NOT NULL,
  `client_id` tinyint(3) NOT NULL,
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `protected` tinyint(3) NOT NULL DEFAULT '0',
  `manifest_cache` text NOT NULL,
  `params` text NOT NULL,
  `custom_data` text NOT NULL,
  `system_data` text NOT NULL,
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) DEFAULT '0',
  `state` int(11) DEFAULT '0',
  PRIMARY KEY (`extension_id`),
  KEY `element_clientid` (`element`,`client_id`),
  KEY `element_folder_clientid` (`element`,`folder`,`client_id`),
  KEY `extension` (`type`,`element`,`folder`,`client_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10048 ;

--
-- Dumping data for table `j_extensions`
--

INSERT INTO `j_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(1, 'com_mailto', 'component', 'com_mailto', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(2, 'com_wrapper', 'component', 'com_wrapper', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(3, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(4, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, '', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":"","save_history":"1","history_limit":10}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(5, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(6, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(7, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(8, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, '', '{"show_contact_category":"hide","save_history":"1","history_limit":10,"show_contact_list":"0","presentation_style":"sliders","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","image":"","allow_vcard":"0","show_articles":"0","show_profile":"0","show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","show_headings":"1","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","allow_vcard_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_category_crumb":"0","metakey":"","metadesc":"","robots":"","author":"","rights":"","xreference":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(9, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(11, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, '', '{"administrator":"en-GB","site":"en-GB"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(12, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(13, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, '', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,application\\/x-shockwave-flash,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/x-zip","upload_mime_illegal":"text\\/html"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(14, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(15, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(16, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(17, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, '', '{"newsfeed_layout":"_:default","save_history":"1","history_limit":5,"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_character_count":"0","feed_display_order":"des","float_first":"right","float_second":"right","show_tags":"1","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"-1","show_empty_categories":"0","show_subcat_desc":"1","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_items_cat":"1","filter_field":"1","show_pagination_limit":"1","show_headings":"1","show_articles":"0","show_link":"1","show_pagination":"1","show_pagination_results":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(18, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(19, 'com_search', 'component', 'com_search', '', 1, 1, 1, 0, '', '{"enabled":"0","show_date":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(20, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, '', '{"template_positions_display":"0","upload_limit":"2","image_formats":"gif,bmp,jpg,jpeg,png","source_formats":"txt,less,ini,xml,js,php,css","font_formats":"woff,ttf,otf","compressed_formats":"zip"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(22, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, '', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","show_publishing_options":"1","show_article_options":"1","save_history":"1","history_limit":10,"show_urls_images_frontend":"0","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_subcategory_content":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(23, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, '', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"NONE","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(24, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(25, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, '', '{"allowUserRegistration":"0","new_usertype":"2","guest_usergroup":"9","sendpassword":"1","useractivation":"1","mail_to_admin":"0","captcha":"","frontend_userparams":"1","site_language":"0","change_login_name":"0","reset_count":"10","reset_time":"1","minimum_length":"4","minimum_integers":"0","minimum_symbols":"0","minimum_uppercase":"0","save_history":"1","history_limit":5,"mailSubjectPrefix":"","mailBodySuffix":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(27, 'com_finder', 'component', 'com_finder', '', 1, 1, 0, 0, '', '{"show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_advanced":"1","expand_advanced":"0","show_date_filters":"0","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","memory_table_limit":30000,"title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stemmer":"snowball"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(28, 'com_joomlaupdate', 'component', 'com_joomlaupdate', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(29, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 1, '', '{"tag_layout":"_:default","save_history":"1","history_limit":5,"show_tag_title":"0","tag_list_show_tag_image":"0","tag_list_show_tag_description":"0","tag_list_image":"","show_tag_num_items":"0","tag_list_orderby":"title","tag_list_orderby_direction":"ASC","show_headings":"0","tag_list_show_date":"0","tag_list_show_item_image":"0","tag_list_show_item_description":"0","tag_list_item_maximum_characters":0,"return_any_or_all":"1","include_children":"0","maximum":200,"tag_list_language_filter":"all","tags_layout":"_:default","all_tags_orderby":"title","all_tags_orderby_direction":"ASC","all_tags_show_tag_image":"0","all_tags_show_tag_descripion":"0","all_tags_tag_maximum_characters":20,"all_tags_show_tag_hits":"0","filter_field":"1","show_pagination_limit":"1","show_pagination":"2","show_pagination_results":"1","tag_field_ajax_mode":"1","show_feed_link":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(30, 'com_contenthistory', 'component', 'com_contenthistory', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(32, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(101, 'SimplePie', 'library', 'simplepie', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(103, 'Joomla! Platform', 'library', 'joomla', '', 0, 1, 1, 1, '', '{"mediaversion":"ae3ed2b8e55688a1054e6fdefb3911cf"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(104, 'IDNA Convert', 'library', 'idna_convert', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(106, 'PHPass', 'library', 'phpass', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(200, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(201, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(202, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(203, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(204, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(205, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(206, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(207, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(208, 'mod_login', 'module', 'mod_login', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(209, 'mod_menu', 'module', 'mod_menu', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(210, 'mod_articles_news', 'module', 'mod_articles_news', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(211, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(212, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(213, 'mod_search', 'module', 'mod_search', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(214, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(215, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(216, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(218, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(219, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(220, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(221, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(222, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(223, 'mod_finder', 'module', 'mod_finder', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(300, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(301, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(302, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(303, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(304, 'mod_login', 'module', 'mod_login', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(305, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(307, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(308, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(309, 'mod_status', 'module', 'mod_status', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(310, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(311, 'mod_title', 'module', 'mod_title', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(312, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(313, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, '', '{"cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(314, 'mod_version', 'module', 'mod_version', '', 1, 1, 1, 0, '', '{"format":"short","product":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(315, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, '', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(316, 'mod_tags_popular', 'module', 'mod_tags_popular', '', 0, 1, 1, 0, '', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(317, 'mod_tags_similar', 'module', 'mod_tags_similar', '', 0, 1, 1, 0, '', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(400, 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(401, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(402, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(403, 'plg_content_contact', 'plugin', 'contact', 'content', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(404, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '', '{"mode":"1"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(406, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '', '{"style":"xhtml"}', '', '', 0, '2011-09-18 15:22:50', 0, 0),
(407, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 0, '', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(408, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 0, '', '{"position":"1"}', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(409, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(410, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '', '{"lineNumbers":"1","lineWrapping":"1","matchTags":"1","matchBrackets":"1","marker-gutter":"1","autoCloseTags":"1","autoCloseBrackets":"1","autoFocus":"1","theme":"default","tabmode":"indent"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(411, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(412, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 0, '', '{"mode":"1","skin":"0","mobile":"0","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","invalid_elements":"script,applet,iframe","extended_elements":"","html_height":"550","html_width":"750","resizing":"1","element_path":"1","fonts":"1","paste":"1","searchreplace":"1","insertdate":"1","colors":"1","table":"1","smilies":"1","hr":"1","link":"1","media":"1","print":"1","directionality":"1","fullscreen":"1","alignment":"1","visualchars":"1","visualblocks":"1","nonbreaking":"1","template":"1","blockquote":"1","wordcount":"1","advlist":"1","autosave":"1","contextmenu":"1","inlinepopups":"1","custom_plugin":"","custom_button":""}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(413, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(414, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(415, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(416, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(417, 'plg_search_categories', 'plugin', 'categories', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(418, 'plg_search_contacts', 'plugin', 'contacts', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(419, 'plg_search_content', 'plugin', 'content', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(420, 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(422, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(423, 'plg_system_p3p', 'plugin', 'p3p', 'system', 0, 0, 1, 0, '', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(424, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 1, '', '{"browsercache":"0","cachetime":"15"}', '', '', 0, '0000-00-00 00:00:00', 9, 0),
(425, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(426, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(427, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 0, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(428, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(429, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 8, 0),
(430, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(431, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 0, '', '{"autowebpage":"","category":"34","autopublish":"0"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(432, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '', '{"autoregister":"1","mail_to_user":"1","forceLogout":"1"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(433, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 0, '', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(434, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(436, 'plg_system_languagecode', 'plugin', 'languagecode', 'system', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 10, 0),
(437, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(438, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(439, 'plg_captcha_recaptcha', 'plugin', 'recaptcha', 'captcha', 0, 0, 1, 0, '', '{"public_key":"","private_key":"","theme":"clean"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(440, 'plg_system_highlight', 'plugin', 'highlight', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(441, 'plg_content_finder', 'plugin', 'finder', 'content', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(442, 'plg_finder_categories', 'plugin', 'categories', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(443, 'plg_finder_contacts', 'plugin', 'contacts', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(444, 'plg_finder_content', 'plugin', 'content', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(445, 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(447, 'plg_finder_tags', 'plugin', 'tags', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(449, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(450, 'plg_twofactorauth_yubikey', 'plugin', 'yubikey', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(451, 'plg_search_tags', 'plugin', 'tags', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","show_tagged_items":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(503, 'beez3', 'template', 'beez3', '', 0, 1, 1, 0, '', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, '', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(506, 'protostar', 'template', 'protostar', '', 0, 1, 1, 0, '', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(507, 'isis', 'template', 'isis', '', 1, 1, 1, 0, '', '{"templateColor":"","logoFile":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(600, 'English (United Kingdom)', 'language', 'en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(601, 'English (United Kingdom)', 'language', 'en-GB', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(700, 'files_joomla', 'file', 'joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10001, 'Gantry', 'library', 'lib_gantry', '', 0, 1, 1, 0, '{"name":"Gantry","type":"library","creationDate":"March 9, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"4.1.29","description":"Gantry Starting Template for Joomla! v4.1.29","group":"","filename":"lib_gantry"}', '{}', '{"last_update":1455802360}', '', 0, '0000-00-00 00:00:00', 0, 0),
(10002, 'Gantry', 'component', 'com_gantry', '', 0, 1, 0, 0, '{"name":"Gantry","type":"component","creationDate":"March 9, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"4.1.29","description":"Gantry Starting Template for Joomla! v4.1.29","group":"","filename":"gantry"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10003, 'System - Gantry', 'plugin', 'gantry', 'system', 0, 1, 1, 0, '{"name":"System - Gantry","type":"plugin","creationDate":"March 9, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"4.1.29","description":"Gantry System Plugin for Joomla","group":"","filename":"gantry"}', '{"debugloglevel":"63"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(10004, 'jf_connecto', 'template', 'jf_connecto', '', 0, 1, 1, 0, '{"name":"jf_connecto","type":"template","creationDate":"14.05.15","author":"JoomForest.com","copyright":"Copyright (C) 2011-2015 JoomForest. All rights reserved.","authorEmail":"support@joomforest.com","authorUrl":"http:\\/\\/www.joomforest.com\\/","version":"1.2","description":"JF Connecto v1.2","group":"","filename":"templateDetails"}', '{"master":"true"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10005, 'System - RokExtender', 'plugin', 'rokextender', 'system', 0, 1, 1, 0, '{"name":"System - RokExtender","type":"plugin","creationDate":"October 31, 2012","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2012 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"2.0.0","description":"System - Gantry","group":"","filename":"rokextender"}', '{"registered":"\\/modules\\/mod_roknavmenu\\/lib\\/RokNavMenuEvents.php"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(10006, 'RokNavMenu', 'module', 'mod_roknavmenu', '', 0, 1, 1, 0, '{"name":"RokNavMenu","type":"module","creationDate":"February 24, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"2.0.8","description":"RocketTheme Customizable Navigation Menu","group":"","filename":"mod_roknavmenu"}', '{"limit_levels":"0","startLevel":"0","endLevel":"0","showAllChildren":"0","filteringspacer2":"","theme":"default","custom_layout":"default.php","custom_formatter":"default.php","cache":"0","module_cache":"1","cache_time":"900","cachemode":"itemid"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10007, 'com_easysocial', 'component', 'com_easysocial', '', 1, 1, 0, 0, '{"name":"com_easysocial","type":"component","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright 2009 - 2013 Stack Ideas Sdn Bhd. All Rights Reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10008, 'Authentication - EasySocial', 'plugin', 'easysocial', 'authentication', 0, 1, 1, 0, '{"name":"Authentication - EasySocial","type":"plugin","creationDate":"30\\/03\\/2013","author":"Mark Lee","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"stackideas.com","version":"1.0","description":"\\n\\t\\t\\n\\t\\tAn authentication plugin that allows oauth users to login to the site.\\n\\t\\t\\n\\t","group":"","filename":"easysocial"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10009, 'PLG_CONTENT_EASYSOCIAL', 'plugin', 'easysocial', 'content', 0, 1, 1, 0, '{"name":"PLG_CONTENT_EASYSOCIAL","type":"plugin","creationDate":"27th March 2014","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.0.5","description":"PLG_CONTENT_EASYSOCIAL_XML_DESCRIPTION","group":"","filename":"easysocial"}', '{"modify_contact_link":"1","display_info":"1","load_comments":"0","guest_viewcomments":"1","placement":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10010, 'plg_finder_easysocialalbums', 'plugin', 'easysocialalbums', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialalbums","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Albums.","group":"","filename":"easysocialalbums"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10011, 'plg_finder_easysocialevents', 'plugin', 'easysocialevents', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialevents","type":"plugin","creationDate":"August 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Events.","group":"","filename":"easysocialevents"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10012, 'plg_finder_easysocialgroups', 'plugin', 'easysocialgroups', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialgroups","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Groups.","group":"","filename":"easysocialgroups"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10013, 'plg_finder_easysocialphotos', 'plugin', 'easysocialphotos', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialphotos","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Photos.","group":"","filename":"easysocialphotos"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10014, 'plg_finder_easysocialusers', 'plugin', 'easysocialusers', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialusers","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial users'' profile.","group":"","filename":"easysocialusers"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10015, 'plg_finder_easysocialvideos', 'plugin', 'easysocialvideos', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialvideos","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Videos.","group":"","filename":"easysocialvideos"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10016, 'System - EasySocial', 'plugin', 'easysocial', 'system', 0, 1, 1, 0, '{"name":"System - EasySocial","type":"plugin","creationDate":"05\\/11\\/2013","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.0.0","description":"PLG_SYSTEM_EASYSOCIAL_XML_DESCRIPTION","group":"","filename":"easysocial"}', '{"redirection":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10017, 'User - EasySocial', 'plugin', 'easysocial', 'user', 0, 1, 1, 0, '{"name":"User - EasySocial","type":"plugin","creationDate":"30\\/03\\/2013","author":"Mark Lee","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"stackideas.com","version":"1.0","description":"\\n\\t\\t\\n\\t\\t\\tUser plugin for EasySocial.\\n\\t\\t\\n\\t","group":"","filename":"easysocial"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(10018, 'EasySocial Albums', 'module', 'mod_easysocial_albums', '', 0, 1, 1, 0, '{"name":"EasySocial Albums","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_ALBUMS_DESC","group":"","filename":"mod_easysocial_albums"}', '{"total":"6","withCover":"0","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10019, 'EasySocial Calendar', 'module', 'mod_easysocial_calendar', '', 0, 1, 1, 0, '{"name":"EasySocial Calendar","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_CALENDAR_DESC","group":"","filename":"mod_easysocial_calendar"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10020, 'EasySocial Dating Search', 'module', 'mod_easysocial_dating_search', '', 0, 1, 1, 0, '{"name":"EasySocial Dating Search","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_DATING_SEARCH_DESC","group":"","filename":"mod_easysocial_dating_search"}', '{"searchname":"1","searchgender":"1","searchage":"1","searchdistance":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10021, 'EasySocial Dropdown Menu', 'module', 'mod_easysocial_dropdown_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Dropdown Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_DROPDOWN_MENU_DESC","group":"","filename":"mod_easysocial_dropdown_menu"}', '{"show_my_profile":"1","show_account_settings":"1","show_sign_in":"1","show_sign_out":"1","render_menus":"1","menu_type":"","popbox_position":"bottom","popbox_collision":"flip","popbox_offset":"10","register_button":"1","remember_me_style":"visible_checked","use_secure_url":"0","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10022, 'Recent Blog Posts (EasyBlog)', 'module', 'mod_easysocial_easyblog_posts', '', 0, 1, 1, 0, '{"name":"Recent Blog Posts (EasyBlog)","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_RECENT_BLOG_POSTS_DESC","group":"","filename":"mod_easysocial_easyblog_posts"}', '{"show_image":"1","show_author":"1","show_category":"1","popover":"1","total":"5","sorting":"latest","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10023, 'EasySocial Event Menu', 'module', 'mod_easysocial_event_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Event Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_EVENT_MENU_DESC","group":"","filename":"mod_easysocial_event_menu"}', '{"show_avatar":"1","show_name":"1","show_members":"1","show_edit":"1","show_pending":"1","show_apps":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10024, 'EasySocial Events', 'module', 'mod_easysocial_events', '', 0, 1, 1, 0, '{"name":"EasySocial Events","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_EVENTS_DESC","group":"","filename":"mod_easysocial_events"}', '{"filter":"0","category":"","ordering":"latest","display_member_counter":"1","display_category":"1","display_limit":"5","event_inclusion":"","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10025, 'EasySocial Event Categories', 'module', 'mod_easysocial_events_categories', '', 0, 1, 1, 0, '{"name":"EasySocial Event Categories","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_EVENTS_CATEGORIES_DESC","group":"","filename":"mod_easysocial_events_categories"}', '{"ordering":"ordering","display_desc":"1","desc_max":"250","display_counter":"1","display_avatar":"1","display_limit":"5","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10026, 'EasySocial Followers', 'module', 'mod_easysocial_followers', '', 0, 1, 1, 0, '{"name":"EasySocial Followers","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2015 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_FOLLOWERS_DESC","group":"","filename":"mod_easysocial_followers"}', '{"filter":"recent","total":"10","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10027, 'EasySocial Friends', 'module', 'mod_easysocial_friends', '', 0, 1, 1, 0, '{"name":"EasySocial Friends","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_FRIENDS_DESC","group":"","filename":"mod_easysocial_friends"}', '{"limit":"6","popover":"1","popover_position":"top-left","showall_link":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10028, 'EasySocial Group Menu', 'module', 'mod_easysocial_group_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Group Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_GROUP_MENU_DESC","group":"","filename":"mod_easysocial_group_menu"}', '{"show_avatar":"1","show_name":"1","show_members":"1","show_edit":"1","show_pending":"1","show_apps":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10029, 'EasySocial Groups', 'module', 'mod_easysocial_groups', '', 0, 1, 1, 0, '{"name":"EasySocial Groups","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2015 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_GROUPS_DESC","group":"","filename":"mod_easysocial_groups"}', '{"filter":"0","category":"","ordering":"latest","display_member_counter":"1","display_category":"1","display_actions":"1","display_limit":"5","group_inclusion":"","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10030, 'EasySocial Group Categories', 'module', 'mod_easysocial_groups_categories', '', 0, 1, 1, 0, '{"name":"EasySocial Group Categories","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_GROUPS_CATEGORIES_DESC","group":"","filename":"mod_easysocial_groups_categories"}', '{"ordering":"latest","display_desc":"1","desc_max":"250","display_counter":"1","display_avatar":"1","display_limit":"5","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10031, 'EasySocial Leader Board', 'module', 'mod_easysocial_leaderboard', '', 0, 1, 1, 0, '{"name":"EasySocial Leader Board","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_LEADERBOARD_DESC","group":"","filename":"mod_easysocial_leaderboard"}', '{"total":"5","popover":"1","popover_position":"top-left","showall_link":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10032, 'EasySocial Log Box', 'module', 'mod_easysocial_logbox', '', 0, 1, 1, 0, '{"name":"EasySocial Log Box","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_LOGBOX_DESC","group":"","filename":"mod_easysocial_logbox"}', '{"show_forget_username":"1","show_forget_password":"1","show_remember_me":"1","show_facebook_login":"1","show_quick_registration":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10033, 'EasySocial Login', 'module', 'mod_easysocial_login', '', 0, 1, 1, 0, '{"name":"EasySocial Login","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_LOGIN_DESC","group":"","filename":"mod_easysocial_login"}', '{"modulestyle":"vertical","show_register_link":"1","show_forget_username":"1","show_forget_password":"1","show_remember_me":"1","remember_me_style":"visible_checked","use_secure_url":"0","show_logout_button":"1","show_facebook_login":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10034, 'EasySocial Menu', 'module', 'mod_easysocial_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_MENU_DESC","group":"","filename":"mod_easysocial_menu"}', '{"show_avatar":"1","show_name":"1","show_points":"1","show_edit":"1","show_notifications":"1","show_system_notifications":"1","interval_notifications_system":"60","show_friends_notifications":"1","interval_notifications_friends":"60","show_conversation_notifications":"1","interval_notifications_conversations":"60","show_achievements":"1","show_navigation":"1","show_conversation":"1","show_friends":"1","show_followers":"1","show_photos":"1","show_videos":"1","show_apps":"1","show_activity":"1","integrate_easyblog":"1","show_signout":"1","popbox_position":"bottom","popbox_collision":"flip","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10035, 'EasySocial Notifications', 'module', 'mod_easysocial_notifications', '', 0, 1, 1, 0, '{"name":"EasySocial Notifications","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_NOTIFICATIONS_DESC","group":"","filename":"mod_easysocial_notifications"}', '{"show_system_notifications":"1","interval_notifications_system":"60","show_friends_notifications":"1","interval_notifications_friends":"60","show_conversation_notifications":"1","interval_notifications_conversations":"60","popbox_position":"bottom","popbox_collision":"flip","popbox_offset":"10","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10036, 'EasySocial OAuth Login', 'module', 'mod_easysocial_oauth', '', 0, 1, 1, 0, '{"name":"EasySocial OAuth Login","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_OAUTH_DESC","group":"","filename":"mod_easysocial_oauth"}', '{"cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10037, 'EasySocial Recent Photos', 'module', 'mod_easysocial_photos', '', 0, 1, 1, 0, '{"name":"EasySocial Recent Photos","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_PHOTOS_DESC","group":"","filename":"mod_easysocial_photos"}', '{"display_popup":"1","avatar":"1","cover":"1","ordering":"created","limit":"20","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10038, 'EasySocial Profile Completeness', 'module', 'mod_easysocial_profile_completeness', '', 0, 1, 1, 0, '{"name":"EasySocial Profile Completeness","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_PROFILE_COMPLETENESS_DESC","group":"","filename":"mod_easysocial_profile_completeness"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10039, 'EasySocial Quick Post', 'module', 'mod_easysocial_quickpost', '', 0, 1, 1, 0, '{"name":"EasySocial Quick Post","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_QUICKPOST_DESC","group":"","filename":"mod_easysocial_quickpost"}', '{"show_public":"0","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10040, 'EasySocial Recent Polls', 'module', 'mod_easysocial_recentpolls', '', 0, 1, 1, 0, '{"name":"EasySocial Recent Polls","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_RECENTPOLLS_DESC","group":"","filename":"mod_easysocial_recentpolls"}', '{"display_limit":"5","display_pollitems":"1","display_pollitems_scorebar":"1","display_author":"1","display_createdate":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);
INSERT INTO `j_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(10041, 'EasySocial Quick Registration', 'module', 'mod_easysocial_register', '', 0, 1, 1, 0, '{"name":"EasySocial Quick Registration","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_REGISTER_DESC","group":"","filename":"mod_easysocial_register"}', '{"register_type":"quick","show_heading_title":"1","heading_title":"Don''t have an account?","show_heading_desc":"1","heading_desc":"Register now to join the community!","social":"1","splash_image":"1","splash_image_url":"","splash_image_title":"MOD_EASYSOCIAL_REGISTER_SPLASH_TITLE_JOIN_US_TODAY","splash_footer_content":"MOD_EASYSOCIAL_REGISTER_SPLASH_FOOTER_CONTENT","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10042, 'EasySocial Registration Requester', 'module', 'mod_easysocial_registration_requester', '', 0, 1, 1, 0, '{"name":"EasySocial Registration Requester","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_REGISTRATION_REQUESTER_DESC","group":"","filename":"mod_easysocial_registration_requester"}', '{"show_heading_title":"1","heading_title":"MOD_EASYSOCIAL_REGISTRATION_REQUESTER_HEADING_TITLE_DEFAULT","show_heading_desc":"1","heading_desc":"MOD_EASYSOCIAL_REGISTRATION_REQUESTER_HEADING_DESCRIPTION_DEFAULT","social":"1","splash_image":"1","splash_image_url":"\\/modules\\/mod_easysocial_registration_requester\\/images\\/splash.jpg","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10043, 'EasySocial Search', 'module', 'mod_easysocial_search', '', 0, 1, 1, 0, '{"name":"EasySocial Search","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_SEARCH_DESC","group":"","filename":"mod_easysocial_search"}', '{"showadvancedlink":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10044, 'EasySocial Stream', 'module', 'mod_easysocial_stream', '', 0, 1, 1, 0, '{"name":"EasySocial Stream","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_STREAM_DESC","group":"","filename":"mod_easysocial_stream"}', '{"total":"10","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10045, 'EasySocial Toolbar', 'module', 'mod_easysocial_toolbar', '', 0, 1, 1, 0, '{"name":"EasySocial Toolbar","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_TOOLBAR_DESC","group":"","filename":"mod_easysocial_toolbar"}', '{"show_on_easysocial":"0","show_dashboard":"1","show_friends":"1","show_conversations":"1","show_notifications":"1","show_search":"1","show_login":"1","show_profile":"1","responsive":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10046, 'EasySocial Users', 'module', 'mod_easysocial_users', '', 0, 1, 1, 0, '{"name":"EasySocial Users","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_USERS_DESC","group":"","filename":"mod_easysocial_users"}', '{"filter":"recent","total":"10","ordering":"registerDate","hasavatar":"0","direction":"desc","popover":"1","popover_position":"top-left","showall_link":"1","user_inclusion":"","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10047, 'EasySocial Videos Module', 'module', 'mod_easysocial_videos', '', 0, 1, 1, 0, '{"name":"EasySocial Videos Module","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_VIDEOS_DESC","group":"","filename":"mod_easysocial_videos"}', '{"filter":"created","category":"","source":"created","sorting":"created","limit":"20","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_filters`
--

CREATE TABLE IF NOT EXISTS `j_finder_filters` (
  `filter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_alias` varchar(255) NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `map_count` int(10) unsigned NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `params` mediumtext,
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links`
--

CREATE TABLE IF NOT EXISTS `j_finder_links` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `indexdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `md5sum` varchar(32) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `state` int(5) DEFAULT '1',
  `access` int(5) DEFAULT '0',
  `language` varchar(8) NOT NULL,
  `publish_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `list_price` double unsigned NOT NULL DEFAULT '0',
  `sale_price` double unsigned NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL,
  `object` mediumblob NOT NULL,
  PRIMARY KEY (`link_id`),
  KEY `idx_type` (`type_id`),
  KEY `idx_title` (`title`),
  KEY `idx_md5` (`md5sum`),
  KEY `idx_url` (`url`(75)),
  KEY `idx_published_list` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`list_price`),
  KEY `idx_published_sale` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`sale_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms0`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms0` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms1`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms1` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms2`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms2` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms3`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms3` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms4`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms4` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms5`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms5` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms6`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms6` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms7`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms7` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms8`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms8` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms9`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms9` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsa`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsa` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsb`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsb` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsc`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsc` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsd`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsd` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termse`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termse` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsf`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsf` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_taxonomy`
--

CREATE TABLE IF NOT EXISTS `j_finder_taxonomy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `access` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `state` (`state`),
  KEY `ordering` (`ordering`),
  KEY `access` (`access`),
  KEY `idx_parent_published` (`parent_id`,`state`,`access`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_finder_taxonomy`
--

INSERT INTO `j_finder_taxonomy` (`id`, `parent_id`, `title`, `state`, `access`, `ordering`) VALUES
(1, 0, 'ROOT', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_taxonomy_map`
--

CREATE TABLE IF NOT EXISTS `j_finder_taxonomy_map` (
  `link_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`node_id`),
  KEY `link_id` (`link_id`),
  KEY `node_id` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_terms`
--

CREATE TABLE IF NOT EXISTS `j_finder_terms` (
  `term_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '0',
  `soundex` varchar(75) NOT NULL,
  `links` int(10) NOT NULL DEFAULT '0',
  `language` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`term_id`),
  UNIQUE KEY `idx_term` (`term`),
  KEY `idx_term_phrase` (`term`,`phrase`),
  KEY `idx_stem_phrase` (`stem`,`phrase`),
  KEY `idx_soundex_phrase` (`soundex`,`phrase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_terms_common`
--

CREATE TABLE IF NOT EXISTS `j_finder_terms_common` (
  `term` varchar(75) NOT NULL,
  `language` varchar(3) NOT NULL,
  KEY `idx_word_lang` (`term`,`language`),
  KEY `idx_lang` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_finder_terms_common`
--

INSERT INTO `j_finder_terms_common` (`term`, `language`) VALUES
('a', 'en'),
('about', 'en'),
('after', 'en'),
('ago', 'en'),
('all', 'en'),
('am', 'en'),
('an', 'en'),
('and', 'en'),
('ani', 'en'),
('any', 'en'),
('are', 'en'),
('aren''t', 'en'),
('as', 'en'),
('at', 'en'),
('be', 'en'),
('but', 'en'),
('by', 'en'),
('for', 'en'),
('from', 'en'),
('get', 'en'),
('go', 'en'),
('how', 'en'),
('if', 'en'),
('in', 'en'),
('into', 'en'),
('is', 'en'),
('isn''t', 'en'),
('it', 'en'),
('its', 'en'),
('me', 'en'),
('more', 'en'),
('most', 'en'),
('must', 'en'),
('my', 'en'),
('new', 'en'),
('no', 'en'),
('none', 'en'),
('not', 'en'),
('noth', 'en'),
('nothing', 'en'),
('of', 'en'),
('off', 'en'),
('often', 'en'),
('old', 'en'),
('on', 'en'),
('onc', 'en'),
('once', 'en'),
('onli', 'en'),
('only', 'en'),
('or', 'en'),
('other', 'en'),
('our', 'en'),
('ours', 'en'),
('out', 'en'),
('over', 'en'),
('page', 'en'),
('she', 'en'),
('should', 'en'),
('small', 'en'),
('so', 'en'),
('some', 'en'),
('than', 'en'),
('thank', 'en'),
('that', 'en'),
('the', 'en'),
('their', 'en'),
('theirs', 'en'),
('them', 'en'),
('then', 'en'),
('there', 'en'),
('these', 'en'),
('they', 'en'),
('this', 'en'),
('those', 'en'),
('thus', 'en'),
('time', 'en'),
('times', 'en'),
('to', 'en'),
('too', 'en'),
('true', 'en'),
('under', 'en'),
('until', 'en'),
('up', 'en'),
('upon', 'en'),
('use', 'en'),
('user', 'en'),
('users', 'en'),
('veri', 'en'),
('version', 'en'),
('very', 'en'),
('via', 'en'),
('want', 'en'),
('was', 'en'),
('way', 'en'),
('were', 'en'),
('what', 'en'),
('when', 'en'),
('where', 'en'),
('whi', 'en'),
('which', 'en'),
('who', 'en'),
('whom', 'en'),
('whose', 'en'),
('why', 'en'),
('wide', 'en'),
('will', 'en'),
('with', 'en'),
('within', 'en'),
('without', 'en'),
('would', 'en'),
('yes', 'en'),
('yet', 'en'),
('you', 'en'),
('your', 'en'),
('yours', 'en');

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_tokens`
--

CREATE TABLE IF NOT EXISTS `j_finder_tokens` (
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '1',
  `context` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `language` char(3) NOT NULL DEFAULT '',
  KEY `idx_word` (`term`),
  KEY `idx_context` (`context`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_tokens_aggregate`
--

CREATE TABLE IF NOT EXISTS `j_finder_tokens_aggregate` (
  `term_id` int(10) unsigned NOT NULL,
  `map_suffix` char(1) NOT NULL,
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `term_weight` float unsigned NOT NULL,
  `context` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `context_weight` float unsigned NOT NULL,
  `total_weight` float unsigned NOT NULL,
  `language` char(3) NOT NULL DEFAULT '',
  KEY `token` (`term`),
  KEY `keyword_id` (`term_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_types`
--

CREATE TABLE IF NOT EXISTS `j_finder_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `mime` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_languages`
--

CREATE TABLE IF NOT EXISTS `j_languages` (
  `lang_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang_code` char(7) NOT NULL,
  `title` varchar(50) NOT NULL,
  `title_native` varchar(50) NOT NULL,
  `sef` varchar(50) NOT NULL,
  `image` varchar(50) NOT NULL,
  `description` varchar(512) NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `sitename` varchar(1024) NOT NULL DEFAULT '',
  `published` int(11) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`),
  UNIQUE KEY `idx_sef` (`sef`),
  UNIQUE KEY `idx_image` (`image`),
  UNIQUE KEY `idx_langcode` (`lang_code`),
  KEY `idx_access` (`access`),
  KEY `idx_ordering` (`ordering`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_languages`
--

INSERT INTO `j_languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `description`, `metakey`, `metadesc`, `sitename`, `published`, `access`, `ordering`) VALUES
(1, 'en-GB', 'English (UK)', 'English (UK)', 'en', 'en', '', '', '', '', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_menu`
--

CREATE TABLE IF NOT EXISTS `j_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to j_menu_types.menutype',
  `title` varchar(255) NOT NULL COMMENT 'The display title of the menu item.',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'The SEF alias of the menu item.',
  `note` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.',
  `link` varchar(1024) NOT NULL COMMENT 'The actually link the menu item refers to.',
  `type` varchar(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator',
  `published` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The published state of the menu link.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The parent menu item in the menu tree.',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The relative level in the tree.',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to j_extensions.id',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to j_users.id',
  `checked_out_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.',
  `browserNav` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The click behaviour of the link.',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The access level required to view the menu item.',
  `img` varchar(255) NOT NULL COMMENT 'The image of the menu item.',
  `template_style_id` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL COMMENT 'JSON encoded data for the menu item.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `home` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Indicates if this menu item is the home or default page.',
  `language` char(7) NOT NULL DEFAULT '',
  `client_id` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_client_id_parent_id_alias_language` (`client_id`,`parent_id`,`alias`,`language`),
  KEY `idx_componentid` (`component_id`,`menutype`,`published`,`access`),
  KEY `idx_menutype` (`menutype`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_path` (`path`(255)),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=122 ;

--
-- Dumping data for table `j_menu`
--

INSERT INTO `j_menu` (`id`, `menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES
(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, 83, 0, '*', 0),
(2, 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 0, 1, 1, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners', 0, '', 1, 10, 0, '*', 1),
(3, 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 0, 2, 2, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners', 0, '', 2, 3, 0, '*', 1),
(4, 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 0, 2, 2, 6, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-cat', 0, '', 4, 5, 0, '*', 1),
(5, 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 0, 2, 2, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-clients', 0, '', 6, 7, 0, '*', 1),
(6, 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 0, 2, 2, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-tracks', 0, '', 8, 9, 0, '*', 1),
(7, 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 0, 1, 1, 8, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact', 0, '', 11, 16, 0, '*', 1),
(8, 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 0, 7, 2, 8, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact', 0, '', 12, 13, 0, '*', 1),
(9, 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 0, 7, 2, 6, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact-cat', 0, '', 14, 15, 0, '*', 1),
(10, 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 0, 1, 1, 15, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages', 0, '', 17, 22, 0, '*', 1),
(11, 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 0, 10, 2, 15, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1),
(12, 'menu', 'com_messages_read', 'Read Private Message', '', 'Messaging/Read Private Message', 'index.php?option=com_messages', 'component', 0, 10, 2, 15, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages-read', 0, '', 20, 21, 0, '*', 1),
(13, 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 1, 1, 17, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 23, 28, 0, '*', 1),
(14, 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 13, 2, 17, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 24, 25, 0, '*', 1),
(15, 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 0, 13, 2, 6, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds-cat', 0, '', 26, 27, 0, '*', 1),
(16, 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 0, 1, 1, 24, 0, '0000-00-00 00:00:00', 0, 0, 'class:redirect', 0, '', 29, 30, 0, '*', 1),
(17, 'menu', 'com_search', 'Basic Search', '', 'Basic Search', 'index.php?option=com_search', 'component', 0, 1, 1, 19, 0, '0000-00-00 00:00:00', 0, 0, 'class:search', 0, '', 31, 32, 0, '*', 1),
(18, 'menu', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 0, 1, 1, 27, 0, '0000-00-00 00:00:00', 0, 0, 'class:finder', 0, '', 33, 34, 0, '*', 1),
(19, 'menu', 'com_joomlaupdate', 'Joomla! Update', '', 'Joomla! Update', 'index.php?option=com_joomlaupdate', 'component', 1, 1, 1, 28, 0, '0000-00-00 00:00:00', 0, 0, 'class:joomlaupdate', 0, '', 35, 36, 0, '*', 1),
(20, 'main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 0, 1, 1, 29, 0, '0000-00-00 00:00:00', 0, 1, 'class:tags', 0, '', 37, 38, 0, '', 1),
(21, 'main', 'com_postinstall', 'Post-installation messages', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', 0, 1, 1, 32, 0, '0000-00-00 00:00:00', 0, 1, 'class:postinstall', 0, '', 39, 40, 0, '*', 1),
(101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, 22, 0, '0000-00-00 00:00:00', 0, 1, '', 0, '{"featured_categories":[""],"layout_type":"blog","num_leading_articles":"1","num_intro_articles":"3","num_columns":"3","num_links":"0","multi_column_order":"1","orderby_pri":"","orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":1,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 41, 42, 1, '*', 0),
(102, 'main', 'COM_EASYSOCIAL', 'com_easysocial', '', 'com_easysocial', 'index.php?option=com_easysocial', 'component', 0, 1, 1, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial.png', 0, '{}', 43, 80, 0, '', 1),
(103, 'main', 'COM_EASYSOCIAL_MENU_SETTINGS', 'com_easysocial_menu_settings', '', 'com_easysocial/com_easysocial_menu_settings', 'index.php?option=com_easysocial&view=settings', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-settings.png', 0, '{}', 44, 45, 0, '', 1),
(104, 'main', 'COM_EASYSOCIAL_MENU_USERS', 'com_easysocial_menu_users', '', 'com_easysocial/com_easysocial_menu_users', 'index.php?option=com_easysocial&view=users', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-users.png', 0, '{}', 46, 47, 0, '', 1),
(105, 'main', 'COM_EASYSOCIAL_MENU_THEMES', 'com_easysocial_menu_themes', '', 'com_easysocial/com_easysocial_menu_themes', 'index.php?option=com_easysocial&view=themes', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-themes.png', 0, '{}', 48, 49, 0, '', 1),
(106, 'main', 'COM_EASYSOCIAL_MENU_LANGUAGES', 'com_easysocial_menu_languages', '', 'com_easysocial/com_easysocial_menu_languages', 'index.php?option=com_easysocial&view=languages', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-languages.png', 0, '{}', 50, 51, 0, '', 1),
(107, 'main', 'COM_EASYSOCIAL_MENU_PROFILES', 'com_easysocial_menu_profiles', '', 'com_easysocial/com_easysocial_menu_profiles', 'index.php?option=com_easysocial&view=profiles', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-profiles.png', 0, '{}', 52, 53, 0, '', 1),
(108, 'main', 'COM_EASYSOCIAL_MENU_GROUPS', 'com_easysocial_menu_groups', '', 'com_easysocial/com_easysocial_menu_groups', 'index.php?option=com_easysocial&view=groups', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-groups.png', 0, '{}', 54, 55, 0, '', 1),
(109, 'main', 'COM_EASYSOCIAL_MENU_EVENTS', 'com_easysocial_menu_events', '', 'com_easysocial/com_easysocial_menu_events', 'index.php?option=com_easysocial&view=events', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-events.png', 0, '{}', 56, 57, 0, '', 1),
(110, 'main', 'COM_EASYSOCIAL_MENU_ALBUMS', 'com_easysocial_menu_albums', '', 'com_easysocial/com_easysocial_menu_albums', 'index.php?option=com_easysocial&view=albums', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-albums.png', 0, '{}', 58, 59, 0, '', 1),
(111, 'main', 'COM_EASYSOCIAL_MENU_VIDEOS', 'com_easysocial_menu_videos', '', 'com_easysocial/com_easysocial_menu_videos', 'index.php?option=com_easysocial&view=videos', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-videos.png', 0, '{}', 60, 61, 0, '', 1),
(112, 'main', 'COM_EASYSOCIAL_MENU_PRIVACY', 'com_easysocial_menu_privacy', '', 'com_easysocial/com_easysocial_menu_privacy', 'index.php?option=com_easysocial&view=privacy', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-privacy.png', 0, '{}', 62, 63, 0, '', 1),
(113, 'main', 'COM_EASYSOCIAL_MENU_POINTS', 'com_easysocial_menu_points', '', 'com_easysocial/com_easysocial_menu_points', 'index.php?option=com_easysocial&view=points', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-points.png', 0, '{}', 64, 65, 0, '', 1),
(114, 'main', 'COM_EASYSOCIAL_MENU_ALERTS', 'com_easysocial_menu_alerts', '', 'com_easysocial/com_easysocial_menu_alerts', 'index.php?option=com_easysocial&view=alerts', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-alerts.png', 0, '{}', 66, 67, 0, '', 1),
(115, 'main', 'COM_EASYSOCIAL_MENU_BADGES', 'com_easysocial_menu_badges', '', 'com_easysocial/com_easysocial_menu_badges', 'index.php?option=com_easysocial&view=badges', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-badges.png', 0, '{}', 68, 69, 0, '', 1),
(116, 'main', 'COM_EASYSOCIAL_MENU_APPS', 'com_easysocial_menu_apps', '', 'com_easysocial/com_easysocial_menu_apps', 'index.php?option=com_easysocial&view=apps', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-apps.png', 0, '{}', 70, 71, 0, '', 1),
(117, 'main', 'COM_EASYSOCIAL_MENU_REPORTS', 'com_easysocial_menu_reports', '', 'com_easysocial/com_easysocial_menu_reports', 'index.php?option=com_easysocial&view=reports', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-reports.png', 0, '{}', 72, 73, 0, '', 1),
(118, 'main', 'COM_EASYSOCIAL_MENU_EMAIL_ACTIVITIES', 'com_easysocial_menu_email_activities', '', 'com_easysocial/com_easysocial_menu_email_activities', 'index.php?option=com_easysocial&view=mailer', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-emails.png', 0, '{}', 74, 75, 0, '', 1),
(119, 'main', 'COM_EASYSOCIAL_MENU_MIGRATORS', 'com_easysocial_menu_migrators', '', 'com_easysocial/com_easysocial_menu_migrators', 'index.php?option=com_easysocial&view=migrators', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-migrators.png', 0, '{}', 76, 77, 0, '', 1),
(120, 'main', 'COM_EASYSOCIAL_MENU_ACCESS', 'com_easysocial_menu_access', '', 'com_easysocial/com_easysocial_menu_access', 'index.php?option=com_easysocial&view=access', 'component', 0, 102, 2, 10007, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-access.png', 0, '{}', 78, 79, 0, '', 1),
(121, 'mainmenu', 'Community', 'community', '', 'community', 'index.php?option=com_easysocial&view=dashboard', 'component', 1, 1, 1, 10007, 0, '0000-00-00 00:00:00', 0, 1, '', 0, '', 81, 82, 0, '*', 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_menu_types`
--

CREATE TABLE IF NOT EXISTS `j_menu_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) NOT NULL,
  `title` varchar(48) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_menutype` (`menutype`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_menu_types`
--

INSERT INTO `j_menu_types` (`id`, `menutype`, `title`, `description`) VALUES
(1, 'mainmenu', 'Main Menu', 'The main menu for the site');

-- --------------------------------------------------------

--
-- Table structure for table `j_messages`
--

CREATE TABLE IF NOT EXISTS `j_messages` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id_from` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id_to` int(10) unsigned NOT NULL DEFAULT '0',
  `folder_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `date_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `useridto_state` (`user_id_to`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_messages_cfg`
--

CREATE TABLE IF NOT EXISTS `j_messages_cfg` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `cfg_name` varchar(100) NOT NULL DEFAULT '',
  `cfg_value` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `idx_user_var_name` (`user_id`,`cfg_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_modules`
--

CREATE TABLE IF NOT EXISTS `j_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the j_assets table.',
  `title` varchar(100) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `position` varchar(50) NOT NULL DEFAULT '',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `module` varchar(50) DEFAULT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `showtitle` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `client_id` tinyint(4) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`published`,`access`),
  KEY `newsfeeds` (`module`,`published`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123 ;

--
-- Dumping data for table `j_modules`
--

INSERT INTO `j_modules` (`id`, `asset_id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
(1, 39, 'Main Menu', '', '', 1, 'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_menu', 1, 1, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'),
(2, 40, 'Login', '', '', 1, 'login', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_login', 1, 1, '', 1, '*'),
(3, 41, 'Popular Articles', '', '', 3, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(4, 42, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(8, 43, 'Toolbar', '', '', 1, 'toolbar', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_toolbar', 3, 1, '', 1, '*'),
(9, 44, 'Quick Icons', '', '', 1, 'icon', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_quickicon', 3, 1, '', 1, '*'),
(10, 45, 'Logged-in Users', '', '', 2, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(12, 46, 'Admin Menu', '', '', 1, 'menu', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*'),
(13, 47, 'Admin Submenu', '', '', 1, 'submenu', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_submenu', 3, 1, '', 1, '*'),
(14, 48, 'User Status', '', '', 2, 'status', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_status', 3, 1, '', 1, '*'),
(15, 49, 'Title', '', '', 1, 'title', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_title', 3, 1, '', 1, '*'),
(16, 50, 'Login Form', '', '', 7, 'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*'),
(17, 51, 'Breadcrumbs', '', '', 1, 'position-2', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'),
(79, 52, 'Multilanguage status', '', '', 1, 'status', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(86, 53, 'Joomla Version', '', '', 1, 'footer', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_version', 3, 1, '{"format":"short","product":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(88, 57, 'EasySocial Albums', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_albums', 1, 1, '', 0, '*'),
(89, 58, 'EasySocial Calendar', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_calendar', 1, 1, '', 0, '*'),
(90, 59, 'EasySocial Dating Search', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_dating_search', 1, 1, '', 0, '*'),
(91, 60, 'EasySocial Dropdown Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_dropdown_menu', 1, 1, '', 0, '*'),
(92, 61, 'Recent Blog Posts (EasyBlog)', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_easyblog_posts', 1, 1, '', 0, '*'),
(93, 62, 'EasySocial Event Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_event_menu', 1, 1, '', 0, '*'),
(94, 63, 'EasySocial Events', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_events', 1, 1, '', 0, '*'),
(95, 64, 'EasySocial Event Categories', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_events_categories', 1, 1, '', 0, '*'),
(96, 65, 'EasySocial Followers', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_followers', 1, 1, '', 0, '*'),
(97, 66, 'EasySocial Friends', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_friends', 1, 1, '', 0, '*'),
(98, 67, 'EasySocial Group Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_group_menu', 1, 1, '', 0, '*'),
(99, 68, 'EasySocial Groups', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_groups', 1, 1, '', 0, '*'),
(100, 69, 'EasySocial Group Categories', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_groups_categories', 1, 1, '', 0, '*'),
(101, 70, 'EasySocial Leader Board', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_leaderboard', 1, 1, '', 0, '*'),
(102, 71, 'EasySocial Log Box', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_logbox', 1, 1, '', 0, '*'),
(103, 72, 'EasySocial Login', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_login', 1, 1, '', 0, '*'),
(104, 73, 'EasySocial Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_menu', 1, 1, '', 0, '*'),
(105, 74, 'EasySocial Notifications', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_notifications', 1, 1, '', 0, '*'),
(106, 75, 'EasySocial OAuth Login', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_oauth', 1, 1, '', 0, '*'),
(107, 76, 'EasySocial Recent Photos', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_photos', 1, 1, '', 0, '*'),
(108, 77, 'EasySocial Profile Completeness', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_profile_completeness', 1, 1, '', 0, '*'),
(109, 78, 'EasySocial Quick Post', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_quickpost', 1, 1, '', 0, '*'),
(110, 79, 'EasySocial Recent Polls', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_recentpolls', 1, 1, '', 0, '*'),
(111, 80, 'EasySocial Quick Registration', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_register', 1, 1, '', 0, '*'),
(112, 81, 'EasySocial Registration Requester', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_registration_requester', 1, 1, '', 0, '*'),
(113, 82, 'EasySocial Search', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_search', 1, 1, '', 0, '*'),
(114, 83, 'EasySocial Stream', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_stream', 1, 1, '', 0, '*'),
(115, 84, 'EasySocial Toolbar', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_toolbar', 1, 1, '', 0, '*'),
(116, 85, 'EasySocial Users', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_users', 1, 1, '', 0, '*'),
(117, 86, 'EasySocial Videos Module', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_videos', 1, 1, '', 0, '*'),
(118, 87, 'Online Users', '', '', 1, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_users', 1, 1, '{"filter":"online","total":"5","ordering":"name","direction":"asc"}', 0, '*'),
(119, 88, 'Recent Users', '', '', 2, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_users', 1, 1, '{"filter":"recent","total":"5","ordering":"registerDate","direction":"desc"}', 0, '*'),
(120, 89, 'Recent Albums', '', '', 3, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_albums', 1, 1, '', 0, '*'),
(121, 90, 'Leaderboard', '', '', 4, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_leaderboard', 1, 1, '{"total":"5"}', 0, '*'),
(122, 91, 'Dating Search', '', '', 1, 'es-users-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_dating_search', 1, 1, '{"searchname":"1","searchgender":"1","searchage":"1","searchdistance":"1"}', 0, '*');

-- --------------------------------------------------------

--
-- Table structure for table `j_modules_menu`
--

CREATE TABLE IF NOT EXISTS `j_modules_menu` (
  `moduleid` int(11) NOT NULL DEFAULT '0',
  `menuid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`moduleid`,`menuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_modules_menu`
--

INSERT INTO `j_modules_menu` (`moduleid`, `menuid`) VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 0),
(6, 0),
(7, 0),
(8, 0),
(9, 0),
(10, 0),
(12, 0),
(13, 0),
(14, 0),
(15, 0),
(16, 0),
(17, 0),
(79, 0),
(86, 0),
(88, 0),
(89, 0),
(90, 0),
(91, 0),
(92, 0),
(93, 0),
(94, 0),
(95, 0),
(96, 0),
(97, 0),
(98, 0),
(99, 0),
(100, 0),
(101, 0),
(102, 0),
(103, 0),
(104, 0),
(105, 0),
(106, 0),
(107, 0),
(108, 0),
(109, 0),
(110, 0),
(111, 0),
(112, 0),
(113, 0),
(114, 0),
(115, 0),
(116, 0),
(117, 0),
(118, 121),
(119, 121),
(120, 121),
(121, 121),
(122, 121);

-- --------------------------------------------------------

--
-- Table structure for table `j_newsfeeds`
--

CREATE TABLE IF NOT EXISTS `j_newsfeeds` (
  `catid` int(11) NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `link` varchar(200) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `numarticles` int(10) unsigned NOT NULL DEFAULT '1',
  `cache_time` int(10) unsigned NOT NULL DEFAULT '3600',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rtl` tinyint(4) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `images` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_overrider`
--

CREATE TABLE IF NOT EXISTS `j_overrider` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_postinstall_messages`
--

CREATE TABLE IF NOT EXISTS `j_postinstall_messages` (
  `postinstall_message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension_id` bigint(20) NOT NULL DEFAULT '700' COMMENT 'FK to j_extensions',
  `title_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for the title',
  `description_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for description',
  `action_key` varchar(255) NOT NULL DEFAULT '',
  `language_extension` varchar(255) NOT NULL DEFAULT 'com_postinstall' COMMENT 'Extension holding lang keys',
  `language_client_id` tinyint(3) NOT NULL DEFAULT '1',
  `type` varchar(10) NOT NULL DEFAULT 'link' COMMENT 'Message type - message, link, action',
  `action_file` varchar(255) DEFAULT '' COMMENT 'RAD URI to the PHP file containing action method',
  `action` varchar(255) DEFAULT '' COMMENT 'Action method name or URL',
  `condition_file` varchar(255) DEFAULT NULL COMMENT 'RAD URI to file holding display condition method',
  `condition_method` varchar(255) DEFAULT NULL COMMENT 'Display condition method, must return boolean',
  `version_introduced` varchar(50) NOT NULL DEFAULT '3.2.0' COMMENT 'Version when this message was introduced',
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`postinstall_message_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `j_postinstall_messages`
--

INSERT INTO `j_postinstall_messages` (`postinstall_message_id`, `extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`) VALUES
(1, 700, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION', 'plg_twofactorauth_totp', 1, 'action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_condition', '3.2.0', 1),
(2, 700, 'COM_CPANEL_WELCOME_BEGINNERS_TITLE', 'COM_CPANEL_WELCOME_BEGINNERS_MESSAGE', '', 'com_cpanel', 1, 'message', '', '', '', '', '3.2.0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_redirect_links`
--

CREATE TABLE IF NOT EXISTS `j_redirect_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_url` varchar(255) NOT NULL,
  `new_url` varchar(255) DEFAULT NULL,
  `referer` varchar(150) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(4) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `header` smallint(3) NOT NULL DEFAULT '301',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_link_old` (`old_url`),
  KEY `idx_link_modifed` (`modified_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_schemas`
--

CREATE TABLE IF NOT EXISTS `j_schemas` (
  `extension_id` int(11) NOT NULL,
  `version_id` varchar(20) NOT NULL,
  PRIMARY KEY (`extension_id`,`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_session`
--

CREATE TABLE IF NOT EXISTS `j_session` (
  `session_id` varchar(200) NOT NULL DEFAULT '',
  `client_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `guest` tinyint(4) unsigned DEFAULT '1',
  `time` varchar(14) DEFAULT '',
  `data` mediumtext,
  `userid` int(11) DEFAULT '0',
  `username` varchar(150) DEFAULT '',
  PRIMARY KEY (`session_id`),
  KEY `userid` (`userid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_session`
--

INSERT INTO `j_session` (`session_id`, `client_id`, `guest`, `time`, `data`, `userid`, `username`) VALUES
('8f6oam37k73u47fe03qgn20486', 0, 1, '1455890170', 'joomla|s:4352:"TzoyNDoiSm9vbWxhXFJlZ2lzdHJ5XFJlZ2lzdHJ5IjoyOntzOjc6IgAqAGRhdGEiO086ODoic3RkQ2xhc3MiOjI6e3M6OToiX19kZWZhdWx0IjtPOjg6InN0ZENsYXNzIjozMDp7czo3OiJzZXNzaW9uIjtPOjg6InN0ZENsYXNzIjo0OntzOjc6ImNvdW50ZXIiO2k6MjtzOjU6InRpbWVyIjtPOjg6InN0ZENsYXNzIjozOntzOjU6InN0YXJ0IjtpOjE0NTU4OTAxNjk7czo0OiJsYXN0IjtpOjE0NTU4OTAxNjk7czozOiJub3ciO2k6MTQ1NTg5MDE3MDt9czo2OiJjbGllbnQiO086ODoic3RkQ2xhc3MiOjE6e3M6OToiZm9yd2FyZGVkIjtzOjEwOiIzMy4zMy4zMy4xIjt9czo1OiJ0b2tlbiI7czozMjoiNGY4ZDVlMDZkZjEzODdmM2NhODkyNWVmZTExMjcxOGEiO31zOjg6InJlZ2lzdHJ5IjtPOjI0OiJKb29tbGFcUmVnaXN0cnlcUmVnaXN0cnkiOjI6e3M6NzoiACoAZGF0YSI7Tzo4OiJzdGRDbGFzcyI6MDp7fXM6OToic2VwYXJhdG9yIjtzOjE6Ii4iO31zOjQ6InVzZXIiO086NToiSlVzZXIiOjI2OntzOjk6IgAqAGlzUm9vdCI7TjtzOjI6ImlkIjtpOjA7czo0OiJuYW1lIjtOO3M6ODoidXNlcm5hbWUiO047czo1OiJlbWFpbCI7TjtzOjg6InBhc3N3b3JkIjtOO3M6MTQ6InBhc3N3b3JkX2NsZWFyIjtzOjA6IiI7czo1OiJibG9jayI7TjtzOjk6InNlbmRFbWFpbCI7aTowO3M6MTI6InJlZ2lzdGVyRGF0ZSI7TjtzOjEzOiJsYXN0dmlzaXREYXRlIjtOO3M6MTA6ImFjdGl2YXRpb24iO047czo2OiJwYXJhbXMiO047czo2OiJncm91cHMiO2E6MTp7aTowO3M6MToiOSI7fXM6NToiZ3Vlc3QiO2k6MTtzOjEzOiJsYXN0UmVzZXRUaW1lIjtOO3M6MTA6InJlc2V0Q291bnQiO047czoxMjoicmVxdWlyZVJlc2V0IjtOO3M6MTA6IgAqAF9wYXJhbXMiO086MjQ6Ikpvb21sYVxSZWdpc3RyeVxSZWdpc3RyeSI6Mjp7czo3OiIAKgBkYXRhIjtPOjg6InN0ZENsYXNzIjowOnt9czo5OiJzZXBhcmF0b3IiO3M6MToiLiI7fXM6MTQ6IgAqAF9hdXRoR3JvdXBzIjtOO3M6MTQ6IgAqAF9hdXRoTGV2ZWxzIjthOjM6e2k6MDtpOjE7aToxO2k6MTtpOjI7aTo1O31zOjE1OiIAKgBfYXV0aEFjdGlvbnMiO047czoxMjoiACoAX2Vycm9yTXNnIjtOO3M6MTM6IgAqAHVzZXJIZWxwZXIiO086MTg6IkpVc2VyV3JhcHBlckhlbHBlciI6MDp7fXM6MTA6IgAqAF9lcnJvcnMiO2E6MDp7fXM6MzoiYWlkIjtpOjA7fXM6MTE6ImFwcGxpY2F0aW9uIjtPOjg6InN0ZENsYXNzIjoxOntzOjU6InF1ZXVlIjtOO31zOjQ5OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1uYW1lIjtOO3M6NTQ6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLW1lbnUtdHlwZSI7TjtzOjU2OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1sYXlvdXQtbW9kZSI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9wcmVsb2FkZXItaW1hZ2UiO047czo1NDoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtbG9nby10eXBlIjtOO3M6NjI6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWxvZ28tY3VzdG9tLWltYWdlIjtOO3M6NTc6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19iZyI7TjtzOjYxOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfaGVhZGVyIjtOO3M6NjQ6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19zbGlkZXNob3ciO047czo2NToiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX2JyZWFkY3J1bWIiO047czo3MjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX2JyZWFkY3J1bWJfYm9yZGVyIjtOO3M6NjQ6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19mb290ZXJfYmciO047czo2NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX2Zvb3Rlcl90ZXh0IjtOO3M6NjY6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19mb290ZXJfbGluayI7TjtzOjU5OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfbWFpbiI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lYl9VQ19NYWluQ29sb3IiO047czo2NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZWJfVUNfVG9vbGJhclRvcEdSIjtOO3M6Njk6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2ViX1VDX1Rvb2xiYXJCb3R0b21HUiI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lc19VQ19NYWluQ29sb3IiO047czo2NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZXNfVUNfVG9vbGJhclRvcEdSIjtOO3M6Njk6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2VzX1VDX1Rvb2xiYXJCb3R0b21HUiI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lc19VQ19CdXR0b25SZWQiO047czo2NToiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZXNfVUNfQnV0dG9uR3JlZW4iO047czo2MzoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY2JfVUNfTWFpbkNvbG9yIjtOO3M6NjY6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NiX1VDX01lbnViYXJDb2xvciI7TjtzOjY2OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jYl9VQ19UZW1wbGF0ZUJvZHkiO047fXM6MTY6Il9fY29tX2Vhc3lzb2NpYWwiO086ODoic3RkQ2xhc3MiOjI6e3M6MTA6ImVhc3lzb2NpYWwiO086ODoic3RkQ2xhc3MiOjE6e3M6ODoiY2FsbGJhY2siO047fXM6ODoibWVzc2FnZXMiO047fX1zOjk6InNlcGFyYXRvciI7czoxOiIuIjt9";', 0, ''),
('j4v8s4tk3b2j4hpronv9q834e4', 1, 0, '1455919273', 'joomla|s:2532:"TzoyNDoiSm9vbWxhXFJlZ2lzdHJ5XFJlZ2lzdHJ5IjoyOntzOjc6IgAqAGRhdGEiO086ODoic3RkQ2xhc3MiOjE6e3M6OToiX19kZWZhdWx0IjtPOjg6InN0ZENsYXNzIjozOntzOjc6InNlc3Npb24iO086ODoic3RkQ2xhc3MiOjQ6e3M6NzoiY291bnRlciI7aTo2O3M6NToidGltZXIiO086ODoic3RkQ2xhc3MiOjM6e3M6NToic3RhcnQiO2k6MTQ1NTkxODk0MjtzOjQ6Imxhc3QiO2k6MTQ1NTkxOTI2OTtzOjM6Im5vdyI7aToxNDU1OTE5MjcxO31zOjY6ImNsaWVudCI7Tzo4OiJzdGRDbGFzcyI6MTp7czo5OiJmb3J3YXJkZWQiO3M6MTA6IjMzLjMzLjMzLjEiO31zOjU6InRva2VuIjtzOjMyOiIzZmQ1OTEzMWY2MGMyMDljNWExZmViYzc4NmI0Nzk3NSI7fXM6ODoicmVnaXN0cnkiO086MjQ6Ikpvb21sYVxSZWdpc3RyeVxSZWdpc3RyeSI6Mjp7czo3OiIAKgBkYXRhIjtPOjg6InN0ZENsYXNzIjoyOntzOjExOiJhcHBsaWNhdGlvbiI7Tzo4OiJzdGRDbGFzcyI6MTp7czo0OiJsYW5nIjtzOjU6ImVuLUdCIjt9czoxMzoiY29tX2luc3RhbGxlciI7Tzo4OiJzdGRDbGFzcyI6Mjp7czo3OiJtZXNzYWdlIjtzOjA6IiI7czoxNzoiZXh0ZW5zaW9uX21lc3NhZ2UiO3M6MDoiIjt9fXM6OToic2VwYXJhdG9yIjtzOjE6Ii4iO31zOjQ6InVzZXIiO086NToiSlVzZXIiOjI4OntzOjk6IgAqAGlzUm9vdCI7YjoxO3M6MjoiaWQiO3M6MzoiOTUxIjtzOjQ6Im5hbWUiO3M6MTA6IlN1cGVyIFVzZXIiO3M6ODoidXNlcm5hbWUiO3M6NToiYWRtaW4iO3M6NToiZW1haWwiO3M6MTc6ImFkbWluQGV4YW1wbGUuY29tIjtzOjg6InBhc3N3b3JkIjtzOjYwOiIkMnkkMTAkUmU2SHpHTWVRMS44NlFJV2xRRmZHZWJoa1h1ZlZldms4Qi5rYVVUay5xRVczODcxelcuN1ciO3M6MTQ6InBhc3N3b3JkX2NsZWFyIjtzOjA6IiI7czo1OiJibG9jayI7czoxOiIwIjtzOjk6InNlbmRFbWFpbCI7czoxOiIxIjtzOjEyOiJyZWdpc3RlckRhdGUiO3M6MTk6IjIwMTMtMDctMjQgMDk6MDc6NDMiO3M6MTM6Imxhc3R2aXNpdERhdGUiO3M6MTk6IjIwMTYtMDItMTggMTM6MTM6MjYiO3M6MTA6ImFjdGl2YXRpb24iO3M6MToiMCI7czo2OiJwYXJhbXMiO3M6OTI6InsiYWRtaW5fc3R5bGUiOiIiLCJhZG1pbl9sYW5ndWFnZSI6IiIsImxhbmd1YWdlIjoiIiwiZWRpdG9yIjoiIiwiaGVscHNpdGUiOiIiLCJ0aW1lem9uZSI6IiJ9IjtzOjY6Imdyb3VwcyI7YToxOntpOjg7czoxOiI4Ijt9czo1OiJndWVzdCI7aTowO3M6MTM6Imxhc3RSZXNldFRpbWUiO3M6MTk6IjAwMDAtMDAtMDAgMDA6MDA6MDAiO3M6MTA6InJlc2V0Q291bnQiO3M6MToiMCI7czoxMjoicmVxdWlyZVJlc2V0IjtzOjE6IjAiO3M6MTA6IgAqAF9wYXJhbXMiO086MjQ6Ikpvb21sYVxSZWdpc3RyeVxSZWdpc3RyeSI6Mjp7czo3OiIAKgBkYXRhIjtPOjg6InN0ZENsYXNzIjo2OntzOjExOiJhZG1pbl9zdHlsZSI7czowOiIiO3M6MTQ6ImFkbWluX2xhbmd1YWdlIjtzOjA6IiI7czo4OiJsYW5ndWFnZSI7czowOiIiO3M6NjoiZWRpdG9yIjtzOjA6IiI7czo4OiJoZWxwc2l0ZSI7czowOiIiO3M6ODoidGltZXpvbmUiO3M6MDoiIjt9czo5OiJzZXBhcmF0b3IiO3M6MToiLiI7fXM6MTQ6IgAqAF9hdXRoR3JvdXBzIjthOjI6e2k6MDtpOjE7aToxO2k6ODt9czoxNDoiACoAX2F1dGhMZXZlbHMiO2E6NTp7aTowO2k6MTtpOjE7aToxO2k6MjtpOjI7aTozO2k6MztpOjQ7aTo2O31zOjE1OiIAKgBfYXV0aEFjdGlvbnMiO047czoxMjoiACoAX2Vycm9yTXNnIjtOO3M6MTM6IgAqAHVzZXJIZWxwZXIiO086MTg6IkpVc2VyV3JhcHBlckhlbHBlciI6MDp7fXM6MTA6IgAqAF9lcnJvcnMiO2E6MDp7fXM6MzoiYWlkIjtpOjA7czo2OiJvdHBLZXkiO3M6MDoiIjtzOjQ6Im90ZXAiO3M6MDoiIjt9fX1zOjk6InNlcGFyYXRvciI7czoxOiIuIjt9";', 951, 'admin'),
('ucvb78ka4fhhs7ovhh393fhfc6', 0, 1, '1455925566', 'joomla|s:4504:"TzoyNDoiSm9vbWxhXFJlZ2lzdHJ5XFJlZ2lzdHJ5IjoyOntzOjc6IgAqAGRhdGEiO086ODoic3RkQ2xhc3MiOjI6e3M6OToiX19kZWZhdWx0IjtPOjg6InN0ZENsYXNzIjozMDp7czo3OiJzZXNzaW9uIjtPOjg6InN0ZENsYXNzIjo0OntzOjc6ImNvdW50ZXIiO2k6MTgyO3M6NToidGltZXIiO086ODoic3RkQ2xhc3MiOjM6e3M6NToic3RhcnQiO2k6MTQ1NTkxODk0OTtzOjQ6Imxhc3QiO2k6MTQ1NTkyNTUzNjtzOjM6Im5vdyI7aToxNDU1OTI1NTY2O31zOjY6ImNsaWVudCI7Tzo4OiJzdGRDbGFzcyI6MTp7czo5OiJmb3J3YXJkZWQiO3M6MTA6IjMzLjMzLjMzLjEiO31zOjU6InRva2VuIjtzOjMyOiJkMjdiNDM1YTBlMTQxMDAzNmUyZmQ1ZjhkZWUwYTY0ZiI7fXM6ODoicmVnaXN0cnkiO086MjQ6Ikpvb21sYVxSZWdpc3RyeVxSZWdpc3RyeSI6Mjp7czo3OiIAKgBkYXRhIjtPOjg6InN0ZENsYXNzIjoxOntzOjE0OiJjb21fZWFzeXNvY2lhbCI7Tzo4OiJzdGRDbGFzcyI6MTp7czo1OiJ1c2VycyI7Tzo4OiJzdGRDbGFzcyI6MTp7czoxMDoibGltaXRzdGFydCI7Tjt9fX1zOjk6InNlcGFyYXRvciI7czoxOiIuIjt9czo0OiJ1c2VyIjtPOjU6IkpVc2VyIjoyNjp7czo5OiIAKgBpc1Jvb3QiO2I6MDtzOjI6ImlkIjtpOjA7czo0OiJuYW1lIjtOO3M6ODoidXNlcm5hbWUiO047czo1OiJlbWFpbCI7TjtzOjg6InBhc3N3b3JkIjtOO3M6MTQ6InBhc3N3b3JkX2NsZWFyIjtzOjA6IiI7czo1OiJibG9jayI7TjtzOjk6InNlbmRFbWFpbCI7aTowO3M6MTI6InJlZ2lzdGVyRGF0ZSI7TjtzOjEzOiJsYXN0dmlzaXREYXRlIjtOO3M6MTA6ImFjdGl2YXRpb24iO047czo2OiJwYXJhbXMiO047czo2OiJncm91cHMiO2E6MTp7aTowO3M6MToiOSI7fXM6NToiZ3Vlc3QiO2k6MTtzOjEzOiJsYXN0UmVzZXRUaW1lIjtOO3M6MTA6InJlc2V0Q291bnQiO047czoxMjoicmVxdWlyZVJlc2V0IjtOO3M6MTA6IgAqAF9wYXJhbXMiO086MjQ6Ikpvb21sYVxSZWdpc3RyeVxSZWdpc3RyeSI6Mjp7czo3OiIAKgBkYXRhIjtPOjg6InN0ZENsYXNzIjowOnt9czo5OiJzZXBhcmF0b3IiO3M6MToiLiI7fXM6MTQ6IgAqAF9hdXRoR3JvdXBzIjthOjI6e2k6MDtpOjE7aToxO2k6OTt9czoxNDoiACoAX2F1dGhMZXZlbHMiO2E6Mzp7aTowO2k6MTtpOjE7aToxO2k6MjtpOjU7fXM6MTU6IgAqAF9hdXRoQWN0aW9ucyI7TjtzOjEyOiIAKgBfZXJyb3JNc2ciO047czoxMzoiACoAdXNlckhlbHBlciI7TzoxODoiSlVzZXJXcmFwcGVySGVscGVyIjowOnt9czoxMDoiACoAX2Vycm9ycyI7YTowOnt9czozOiJhaWQiO2k6MDt9czoyMzoiZ2FudHJ5LWN1cnJlbnQtdGVtcGxhdGUiO3M6MTE6ImpmX2Nvbm5lY3RvIjtzOjQ5OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1uYW1lIjtOO3M6NTQ6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLW1lbnUtdHlwZSI7TjtzOjU2OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1sYXlvdXQtbW9kZSI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9wcmVsb2FkZXItaW1hZ2UiO047czo1NDoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtbG9nby10eXBlIjtOO3M6NjI6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWxvZ28tY3VzdG9tLWltYWdlIjtOO3M6NTc6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19iZyI7TjtzOjYxOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfaGVhZGVyIjtOO3M6NjQ6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19zbGlkZXNob3ciO047czo2NToiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX2JyZWFkY3J1bWIiO047czo3MjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX2JyZWFkY3J1bWJfYm9yZGVyIjtOO3M6NjQ6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19mb290ZXJfYmciO047czo2NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX2Zvb3Rlcl90ZXh0IjtOO3M6NjY6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19mb290ZXJfbGluayI7TjtzOjU5OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfbWFpbiI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lYl9VQ19NYWluQ29sb3IiO047czo2NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZWJfVUNfVG9vbGJhclRvcEdSIjtOO3M6Njk6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2ViX1VDX1Rvb2xiYXJCb3R0b21HUiI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lc19VQ19NYWluQ29sb3IiO047czo2NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZXNfVUNfVG9vbGJhclRvcEdSIjtOO3M6Njk6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2VzX1VDX1Rvb2xiYXJCb3R0b21HUiI7TjtzOjYzOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lc19VQ19CdXR0b25SZWQiO047czo2NToiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZXNfVUNfQnV0dG9uR3JlZW4iO047czo2MzoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY2JfVUNfTWFpbkNvbG9yIjtOO3M6NjY6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NiX1VDX01lbnViYXJDb2xvciI7TjtzOjY2OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jYl9VQ19UZW1wbGF0ZUJvZHkiO047fXM6MTY6Il9fY29tX2Vhc3lzb2NpYWwiO086ODoic3RkQ2xhc3MiOjI6e3M6MTA6ImVhc3lzb2NpYWwiO086ODoic3RkQ2xhc3MiOjE6e3M6ODoiY2FsbGJhY2siO047fXM6ODoibWVzc2FnZXMiO047fX1zOjk6InNlcGFyYXRvciI7czoxOiIuIjt9";', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_access`
--

CREATE TABLE IF NOT EXISTS `j_social_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_access_logs`
--

CREATE TABLE IF NOT EXISTS `j_social_access_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rule` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `utype` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rule` (`rule`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_utypes` (`uid`,`utype`),
  KEY `idx_created` (`created`),
  KEY `idx_useritems` (`rule`,`user_id`,`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_access_rules`
--

CREATE TABLE IF NOT EXISTS `j_social_access_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `element` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `extension` (`extension`),
  KEY `element` (`element`),
  KEY `group` (`group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=60 ;

--
-- Dumping data for table `j_social_access_rules`
--

INSERT INTO `j_social_access_rules` (`id`, `name`, `title`, `description`, `extension`, `element`, `group`, `state`, `created`, `params`) VALUES
(1, 'comments.add', 'Add Comments', 'Allow this usergroup to add comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 13:12:48', '{}'),
(2, 'comments.read', 'Read Comments', 'Allow this usergroup to read comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 13:12:48', '{}'),
(3, 'comments.report', 'Report Comments', 'Allow this usergroup to report comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 13:12:48', '{}'),
(4, 'comments.edit', 'Edit Comments', 'Allow this usergroup to edit all comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 13:12:48', '{"default":false}'),
(5, 'comments.editown', 'Edit Own Comments', 'Allow this usergroup to edit own authored comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 13:12:48', '{}'),
(6, 'comments.delete', 'Delete Comments', 'Allow this usergroup to delete all comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 13:12:48', '{"default":false}'),
(7, 'comments.deleteown', 'Delete Own Comments', 'Allow this usergroup to delete own authored comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 13:12:48', '{}'),
(8, 'conversations.create', 'Start New Conversation', 'If enabled, user''s in this group will be allowed to start a new conversation.', 'com_easysocial', 'conversations', 'user', 1, '2016-02-18 13:12:49', '{}'),
(9, 'conversations.invite', 'Invite Users to Group Conversation', 'If enabled, user''s in this group will be allowed to invite other users into an existing group conversation.', 'com_easysocial', 'conversations', 'user', 1, '2016-02-18 13:12:49', '{}'),
(10, 'conversations.send.daily', 'Daily Send Limit', 'Configure the maximum number of messages user can send a day.', 'com_easysocial', 'conversations', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"messages","default":0}'),
(11, 'events.create', 'Create Event', 'Specify if user is allowed to create event.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 13:12:49', '{}'),
(12, 'events.limit', 'Event Limit', 'Specify the maximum number of events that can be created.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 13:12:49', '{"type":"limitinterval","default":{"value":0,"interval":0}}'),
(13, 'events.join', 'Total Events Allowed to Attend', 'Specify the total events that a user is allowed to attend.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(14, 'events.moderate', 'Moderate Event Creation', 'Specify if event created by user should be moderated by admin.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 13:12:49', '{}'),
(15, 'photos.enabled', 'Allow Photo Albums', 'Specify if photo albums is allowed for events in this category.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 13:12:49', '{}'),
(16, 'photos.max', 'Total Photos Allowed', 'Specify the total photos allowed for events in this category.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_EVENTS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(17, 'photos.maxdaily', 'Total Daily Uploads Allowed', 'Specify the total photos allowed to be uploaded per day for events in this category.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_EVENTS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(18, 'photos.maxsize', 'Maximum File Size Allowed', 'Specify the maximum file sized allowed for photos uploaded.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 13:12:49', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(19, 'files.enabled', 'Allow File Sharing', 'Specify if the file sharing feature is allowed for events in this category.', 'com_easysocial', 'files', 'event', 1, '2016-02-18 13:12:49', '{"type":"boolean","default":true}'),
(20, 'files.max', 'Total Files Allowed', 'Specify the total files allowed for events in this category.', 'com_easysocial', 'files', 'event', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_EVENTS_FILES_TOTAL_FILES_SUFFIX","default":0}'),
(21, 'files.maxsize', 'Maximum File Size Allowed', 'Specify the maximum file sized allowed for files uploaded.', 'com_easysocial', 'files', 'event', 1, '2016-02-18 13:12:49', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(22, 'videos.create', 'Allow Videos', 'COM_EASYSOCIAL_ACCESS_EVENTS_VIDEOS_ENABLED_TIPS', 'com_easysocial', 'videos', 'event', 1, '2016-02-18 13:12:49', '{"type":"boolean","default":1}'),
(23, 'videos.total', 'Total Videos Allowed', 'COM_EASYSOCIAL_ACCESS_EVENTS_VIDEOS_TOTAL_VIDEOS_ALLOWED_TIPS', 'com_easysocial', 'videos', 'event', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(24, 'videos.maxsize', 'Maximum File Size For Video Uploads', 'Determines the maximum file size allowed for video uploads.', 'com_easysocial', 'videos', 'event', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"MB","default":0}'),
(25, 'events.groupevent', 'Allow Group Event', 'Specify if groups in this category should allow event creation.', 'com_easysocial', 'events', 'group', 1, '2016-02-18 13:12:49', '{"type":"boolean","default":true}'),
(26, 'files.upload', 'File Uploads', 'This access determines if the user is allowed to upload files in the story form.', 'com_easysocial', 'files', 'user', 1, '2016-02-18 13:12:49', '{}'),
(27, 'friends.list.enabled', 'Create Friends List', 'Determines if the user in this group is allowed to create friend lists.', 'com_easysocial', 'friends', 'user', 1, '2016-02-18 13:12:49', '{}'),
(28, 'friends.list.limit', 'Friends List', 'Set the number of friend lists users from this group is allowed to create.', 'com_easysocial', 'friends', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(29, 'friends.limit', 'Friends Limit', 'Define the amount of friends a user from this group is allowed to have. Includes sent friend’s request.', 'com_easysocial', 'friends', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(30, 'groups.create', 'Create Groups', 'Determines if the user has access to create new groups.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 13:12:49', '{}'),
(31, 'groups.limit', 'Group Limit', 'Determines the total number of groups the user is allowed to create on the site.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 13:12:49', '{"type":"limitinterval","default":{"value":0,"interval":0}}'),
(32, 'groups.join', 'Total Groups Allowed To Join', 'Defines the total number of groups the user is allowed to join.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(33, 'groups.moderate', 'Moderate Group Creation', 'Determines if the group should be moderated first before being published.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 13:12:49', '{}'),
(34, 'videos.create', 'Allow Videos', 'COM_EASYSOCIAL_ACCESS_GROUPS_VIDEOS_ENABLED_DESC', 'com_easysocial', 'videos', 'group', 1, '2016-02-18 13:12:49', '{"type":"boolean","default":1}'),
(35, 'videos.total', 'Total Videos Allowed', 'This access rule determines how many videos a group is allowed to have.', 'com_easysocial', 'videos', 'group', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(36, 'videos.maxsize', 'Maximum File Size For Video Uploads', 'Determines the maximum file size allowed for video uploads.', 'com_easysocial', 'videos', 'group', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"MB","default":0}'),
(37, 'photos.enabled', 'Allow Photo Albums', 'Determines if photo albums are allowed for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 13:12:49', '{}'),
(38, 'photos.max', 'Total Photos Allowed', 'Determines the total number of photos allowed for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_GROUPS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(39, 'photos.maxdaily', 'Total Daily Uploads Allowed', 'Determines the total number of photos allowed to be uploaded daily for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_GROUPS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(40, 'photos.maxsize', 'Maximum File Size Allowed', 'Determines the maximum file size allowed for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 13:12:49', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(41, 'files.enabled', 'Allow Filesharing', 'Determines if the file sharing should be enabled for groups in this category.', 'com_easysocial', 'files', 'group', 1, '2016-02-18 13:12:49', '{"type":"boolean","default":true}'),
(42, 'files.max', 'Total Files Allowed', 'Set the total number of files allowed to be uploaded via the file manager.', 'com_easysocial', 'files', 'group', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_GROUPS_FILES_TOTAL_FILES_SUFFIX","default":0}'),
(43, 'files.maxsize', 'Maximum File Size Allowed', 'Set the upload limit for files that are uploaded via the file manager.', 'com_easysocial', 'files', 'group', 1, '2016-02-18 13:12:49', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(44, 'albums.create', 'Create albums', 'This option determines if the user is allowed to create a new album.', 'com_easysocial', 'albums', 'user', 1, '2016-02-18 13:12:49', '{}'),
(45, 'albums.total', 'Maximum albums', 'Set the number of photo albums a user is allowed to create on the site.', 'com_easysocial', 'albums', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(46, 'photos.uploader.maxsize', 'Maximum file size', 'Set the maximum file size of photo upload.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 13:12:49', '{"type":"text","class":"form-control input-sm input-short text-center","default":5,"suffix":"MB"}'),
(47, 'photos.uploader.max', 'Maximum photos upload', 'Set the number of photos upload that are allowed by user.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"photos","default":500}'),
(48, 'photos.uploader.maxdaily', 'Maximum photo upload per day', 'Set the number of daily photos upload that are allowed by user.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"photos","default":20}'),
(49, 'photos.totalfiles.limit', 'Maximum Storage Size', 'Here, you may define a maximum storage size allowed for users in this profile type.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","suffix":"MB","default":0}'),
(50, 'polls.create', 'Create Poll', 'This option determines if the user is allowed to create poll.', 'com_easysocial', 'polls', 'user', 1, '2016-02-18 13:12:49', '{}'),
(51, 'polls.vote', 'Vote on Polls', 'This option determines if the user is allowed to vote on polls.', 'com_easysocial', 'polls', 'user', 1, '2016-02-18 13:12:49', '{}'),
(52, 'reports.submit', 'Submit Reports', 'By enabling this option, users in this group will be allowed to submit a report.', 'com_easysocial', 'reports', 'user', 1, '2016-02-18 13:12:49', '{}'),
(53, 'reports.limit', 'Total Reports', 'Specify the total number of reports this user may submit.', 'com_easysocial', 'reports', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(54, 'stream.hide', 'Allow hiding single stream item', 'Allow user to hide stream item from frontend.', 'com_easysocial', 'stream', 'user', 1, '2016-02-18 13:12:49', '{}'),
(55, 'stream.delete', 'Allow delete single stream item', 'Allow user to delete stream item from frontend.', 'com_easysocial', 'stream', 'user', 1, '2016-02-18 13:12:49', '{"default":false}'),
(56, 'videos.create', 'Video Creation', 'Determines if user is allowed to add new videos in the videos section', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 13:12:49', '{"type":"boolean"}'),
(57, 'videos.total', 'Total Videos', 'Determines the total number of videos a user can create on the site.', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":0}'),
(58, 'videos.maxsize', 'Maximum File Size For Video Uploads', 'Determines the maximum file size allowed for video uploads.', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 13:12:49', '{"type":"text","class":"form-control input-sm input-short text-center","default":8,"suffix":"MB"}'),
(59, 'videos.daily', 'Total Daily Video Creation', 'Determines the total number of videos a user can create in a day.', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 13:12:49', '{"type":"limit","default":50,"suffix":"videos"}');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_albums`
--

CREATE TABLE IF NOT EXISTS `j_social_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cover_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `caption` text NOT NULL,
  `created` datetime NOT NULL,
  `assigned_date` datetime NOT NULL,
  `ordering` tinyint(3) NOT NULL,
  `params` text NOT NULL,
  `core` tinyint(3) NOT NULL,
  `hits` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `user_id` (`user_id`),
  KEY `idx_albums_user_assigned` (`uid`,`type`,`assigned_date`),
  KEY `idx_core` (`core`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_albums_favourite`
--

CREATE TABLE IF NOT EXISTS `j_social_albums_favourite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_alert`
--

CREATE TABLE IF NOT EXISTS `j_social_alert` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(255) NOT NULL,
  `element` varchar(255) NOT NULL,
  `rule` varchar(255) NOT NULL,
  `email` int(1) NOT NULL DEFAULT '1',
  `system` int(1) NOT NULL DEFAULT '1',
  `core` int(1) NOT NULL DEFAULT '0',
  `app` int(1) NOT NULL DEFAULT '0',
  `field` tinyint(3) NOT NULL DEFAULT '0',
  `group` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_alert_field` (`field`),
  KEY `idx_alert_published` (`published`),
  KEY `idx_alert_element` (`element`),
  KEY `idx_alert_rule` (`rule`),
  KEY `idx_alert_published_field` (`published`,`field`),
  KEY `idx_alert_isfield` (`published`,`field`,`element`(64),`rule`(64))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

--
-- Dumping data for table `j_social_alert`
--

INSERT INTO `j_social_alert` (`id`, `extension`, `element`, `rule`, `email`, `system`, `core`, `app`, `field`, `group`, `created`, `published`) VALUES
(1, '', 'relationship', 'request', 1, 1, 0, 0, 1, 'user', '2016-02-18 13:12:38', 1),
(2, '', 'relationship', 'approve', 1, 1, 0, 0, 1, 'user', '2016-02-18 13:12:38', 1),
(3, '', 'relationship', 'reject', 1, 1, 0, 0, 1, 'user', '2016-02-18 13:12:38', 1),
(4, '', 'albums', 'comment.add', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(5, '', 'albums', 'favourite', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(6, '', 'badges', 'unlocked', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(7, '', 'broadcast', 'notify', 0, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(8, '', 'comments', 'item', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(9, '', 'comments', 'involved', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(10, '', 'comments', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(11, '', 'comments', 'like', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(12, '', 'conversations', 'reply', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(13, '', 'conversations', 'invite', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(14, '', 'conversations', 'new', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(15, '', 'conversations', 'invited', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(16, '', 'conversations', 'leave', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(17, '', 'events', 'discussion.create', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(18, '', 'events', 'discussion.reply', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(19, '', 'events', 'discussion.answered', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(20, '', 'events', 'discussion.locked', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(21, '', 'events', 'guest.makeadmin', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(22, '', 'events', 'guest.revokeadmin', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(23, '', 'events', 'guest.reject', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(24, '', 'events', 'guest.approve', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(25, '', 'events', 'guest.remove', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(26, '', 'events', 'guest.going', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(27, '', 'events', 'guest.maybe', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(28, '', 'events', 'guest.notgoing', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(29, '', 'events', 'guest.request', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(30, '', 'events', 'guest.withdraw', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(31, '', 'events', 'guest.invited', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(32, '', 'events', 'news', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(33, '', 'events', 'task.created', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(34, '', 'events', 'task.completed', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(35, '', 'events', 'milestone.created', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(36, '', 'events', 'updates', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(37, '', 'events', 'video.create', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(38, '', 'friends', 'approve', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(39, '', 'friends', 'request', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(40, '', 'groups', 'invited', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(41, '', 'groups', 'approved', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(42, '', 'groups', 'joined', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(43, '', 'groups', 'user.rejected', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(44, '', 'groups', 'updates', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(45, '', 'groups', 'promoted', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(46, '', 'groups', 'requested', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(47, '', 'groups', 'leave', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(48, '', 'groups', 'news', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(49, '', 'groups', 'discussion.create', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(50, '', 'groups', 'discussion.reply', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(51, '', 'groups', 'milestone.create', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(52, '', 'groups', 'task.create', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(53, '', 'groups', 'task.completed', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(54, '', 'groups', 'user.removed', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(55, '', 'groups', 'video.create', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(56, '', 'likes', 'item', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(57, '', 'likes', 'involved', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(58, '', 'photos', 'comment.add', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(59, '', 'photos', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(60, '', 'photos', 'likes', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(61, '', 'profile', 'followed', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(62, '', 'profile', 'story', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(63, '', 'repost', 'item', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(64, '', 'stream', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(65, '', 'videos', 'comment.add', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(66, '', 'videos', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1),
(67, '', 'videos', 'likes', 1, 1, 1, 0, 0, '', '2016-02-18 13:12:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_alert_map`
--

CREATE TABLE IF NOT EXISTS `j_social_alert_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT '0',
  `alert_id` bigint(20) NOT NULL,
  `email` int(1) DEFAULT '1',
  `system` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_alertmap_alertid` (`alert_id`),
  KEY `idx_alertmap_userid` (`user_id`),
  KEY `idx_alertmap_alertuser` (`alert_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps`
--

CREATE TABLE IF NOT EXISTS `j_social_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `core` tinyint(4) NOT NULL DEFAULT '0',
  `system` tinyint(3) NOT NULL DEFAULT '0',
  `unique` tinyint(4) NOT NULL DEFAULT '0',
  `default` tinyint(3) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'It could be widgets,fields or applications',
  `element` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `ordering` int(11) NOT NULL,
  `params` text NOT NULL,
  `version` varchar(255) NOT NULL,
  `widget` tinyint(3) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `installable` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `type` (`type`),
  KEY `core` (`core`),
  KEY `idx_default_widget` (`state`,`group`,`widget`,`default`),
  KEY `idx_group` (`group`),
  KEY `idx_apps_element` (`element`),
  KEY `idx_apps_type_group` (`type`(64),`group`(64))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=169 ;

--
-- Dumping data for table `j_social_apps`
--

INSERT INTO `j_social_apps` (`id`, `core`, `system`, `unique`, `default`, `type`, `element`, `group`, `title`, `alias`, `state`, `created`, `ordering`, `params`, `version`, `widget`, `visible`, `installable`) VALUES
(1, 0, 0, 0, 0, 'apps', 'adsense', 'user', 'Adsense', 'adsense', 1, '2016-02-18 13:12:27', 0, '{}', '1.0.205', 1, 1, 1),
(2, 1, 0, 0, 0, 'apps', 'albums', 'user', 'Albums', 'albums', 1, '2016-02-18 13:12:27', 0, '{}', '1.0.206', 1, 1, 1),
(3, 1, 1, 0, 0, 'apps', 'apps', 'user', 'Apps', 'apps', 1, '2016-02-18 13:12:27', 0, '{}', '1.0.205', 0, 1, 0),
(4, 0, 0, 0, 0, 'apps', 'article', 'user', 'Article', 'article', 1, '2016-02-18 13:12:27', 0, '{}', '1.2.204', 0, 1, 1),
(5, 1, 1, 0, 0, 'apps', 'badges', 'user', 'Badges', 'badges', 1, '2016-02-18 13:12:27', 0, '{}', '1.0.205', 0, 1, 0),
(6, 0, 0, 0, 0, 'apps', 'birthday', 'user', 'Upcoming Birthday', 'upcoming-birthday', 1, '2016-02-18 13:12:27', 0, '{}', '1.0.205', 1, 1, 1),
(7, 1, 1, 0, 0, 'apps', 'broadcast', 'user', 'Broadcast', 'broadcast', 1, '2016-02-18 13:12:27', 0, '{}', '1.0.341', 0, 1, 0),
(8, 0, 0, 0, 0, 'apps', 'calendar', 'user', 'Calendar', 'calendar', 1, '2016-02-18 13:12:27', 0, '{}', '1.0.206', 1, 1, 1),
(9, 1, 1, 0, 0, 'apps', 'events', 'user', 'Events', 'events', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.241', 1, 1, 0),
(10, 1, 1, 0, 0, 'apps', 'facebook', 'user', 'Facebook', 'facebook', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.205', 0, 1, 0),
(11, 0, 0, 0, 0, 'apps', 'feeds', 'user', 'Feeds', 'feeds', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.206', 0, 1, 1),
(12, 0, 0, 0, 0, 'apps', 'files', 'user', 'Files', 'files', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.205', 0, 1, 0),
(13, 1, 0, 0, 0, 'apps', 'followers', 'user', 'Followers', 'followers', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.205', 1, 1, 0),
(14, 1, 0, 0, 0, 'apps', 'friends', 'user', 'Friends', 'friends', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.205', 1, 1, 1),
(15, 1, 1, 0, 0, 'apps', 'groups', 'user', 'Groups', 'groups', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.205', 1, 1, 0),
(16, 0, 0, 0, 0, 'apps', 'k2', 'user', 'K2', 'k2', 1, '2016-02-18 13:12:28', 0, '{}', '1.2.207', 0, 1, 1),
(17, 0, 0, 0, 0, 'apps', 'kunena', 'user', 'Kunena Forum', 'kunena-forum', 1, '2016-02-18 13:12:28', 0, '{}', '1.4.33', 1, 1, 1),
(18, 1, 0, 0, 0, 'apps', 'links', 'user', 'Links', 'links', 1, '2016-02-18 13:12:28', 0, '{}', '1.0.205', 0, 1, 0),
(19, 0, 0, 0, 0, 'apps', 'mtree', 'user', 'Mosets Tree', 'mosets-tree', 1, '2016-02-18 13:12:28', 0, '{}', '1.3.65', 1, 1, 1),
(20, 0, 0, 0, 0, 'apps', 'notes', 'user', 'Notes', 'notes', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.205', 0, 1, 1),
(21, 1, 0, 0, 0, 'apps', 'photos', 'user', 'Photos', 'photos', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.205', 1, 1, 1),
(22, 1, 0, 0, 0, 'apps', 'polls', 'user', 'Polls', 'polls', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.46', 0, 1, 0),
(23, 1, 1, 0, 0, 'apps', 'profiles', 'user', 'Profiles', 'profiles', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.205', 0, 1, 0),
(24, 0, 0, 0, 0, 'apps', 'relationship', 'user', 'Relationship', 'relationship', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.204', 0, 1, 1),
(25, 1, 1, 0, 0, 'apps', 'shares', 'user', 'Shares', 'shares', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.205', 0, 1, 0),
(26, 1, 1, 0, 0, 'apps', 'story', 'user', 'Story', 'story', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.205', 0, 1, 0),
(27, 0, 0, 0, 0, 'apps', 'tasks', 'user', 'Tasks', 'tasks', 1, '2016-02-18 13:12:29', 0, '{}', '1.2.383', 0, 1, 1),
(28, 1, 0, 0, 0, 'apps', 'users', 'user', 'Users', 'users', 1, '2016-02-18 13:12:29', 0, '{}', '1.0.384', 1, 1, 0),
(29, 1, 0, 0, 0, 'apps', 'videos', 'user', 'Videos', 'videos', 1, '2016-02-18 13:12:29', 0, '{}', '1.4.45', 1, 1, 1),
(30, 0, 0, 0, 0, 'apps', 'discussions', 'group', 'Discussions', 'discussions', 1, '2016-02-18 13:12:30', 0, '{}', '1.0.204', 1, 1, 1),
(31, 1, 0, 0, 0, 'apps', 'events', 'group', 'Events', 'events', 1, '2016-02-18 13:12:30', 0, '{}', '1.0.241', 1, 1, 0),
(32, 0, 0, 0, 0, 'apps', 'feeds', 'group', 'Feeds', 'feeds', 1, '2016-02-18 13:12:30', 0, '{}', '1.3.241', 0, 1, 1),
(33, 1, 0, 0, 0, 'apps', 'files', 'group', 'Files', 'files', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 1, 1, 1),
(34, 1, 1, 0, 0, 'apps', 'groups', 'group', 'Groups', 'groups', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 0, 1, 1),
(35, 1, 0, 0, 0, 'apps', 'links', 'group', 'Links', 'links', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 0, 1, 0),
(36, 0, 0, 0, 0, 'apps', 'members', 'group', 'Members', 'members', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 1, 1, 1),
(37, 1, 0, 0, 0, 'apps', 'news', 'group', 'News', 'news', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 1, 1, 0),
(38, 1, 0, 0, 0, 'apps', 'photos', 'group', 'Photos', 'photos', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 1, 1, 1),
(39, 1, 0, 0, 0, 'apps', 'polls', 'group', 'Polls', 'polls', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.46', 0, 1, 0),
(40, 1, 1, 0, 0, 'apps', 'shares', 'group', 'Shares', 'shares', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 0, 1, 0),
(41, 1, 1, 0, 0, 'apps', 'story', 'group', 'Story', 'story', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 0, 1, 0),
(42, 1, 0, 0, 0, 'apps', 'tasks', 'group', 'Tasks', 'tasks', 1, '2016-02-18 13:12:31', 0, '{}', '1.0.384', 1, 1, 1),
(43, 1, 0, 0, 0, 'apps', 'videos', 'group', 'Videos', 'videos', 1, '2016-02-18 13:12:31', 0, '{}', '1.4.45', 1, 1, 0),
(44, 0, 0, 0, 0, 'apps', 'discussions', 'event', 'Discussions', 'discussions', 1, '2016-02-18 13:12:32', 0, '{}', '1.0.241', 1, 1, 1),
(45, 1, 1, 0, 0, 'apps', 'events', 'event', 'Events', 'events', 1, '2016-02-18 13:12:32', 0, '{}', '1.0.241', 0, 1, 1),
(46, 1, 0, 0, 0, 'apps', 'files', 'event', 'Files', 'files', 1, '2016-02-18 13:12:32', 0, '{}', '1.0.241', 1, 1, 1),
(47, 1, 0, 0, 0, 'apps', 'guests', 'event', 'Guests', 'guests', 1, '2016-02-18 13:12:32', 0, '{}', '1.0.241', 1, 1, 1),
(48, 1, 0, 0, 0, 'apps', 'links', 'event', 'Links', 'links', 1, '2016-02-18 13:12:32', 0, '{}', '1.0.241', 0, 1, 0),
(49, 1, 0, 0, 0, 'apps', 'news', 'event', 'News', 'news', 1, '2016-02-18 13:12:33', 0, '{}', '1.0.241', 1, 1, 0),
(50, 1, 0, 0, 0, 'apps', 'photos', 'event', 'Photos', 'photos', 1, '2016-02-18 13:12:33', 0, '{}', '1.0.241', 1, 1, 1),
(51, 1, 0, 0, 0, 'apps', 'polls', 'event', 'Polls', 'polls', 1, '2016-02-18 13:12:33', 0, '{}', '1.0.46', 0, 1, 0),
(52, 1, 1, 0, 0, 'apps', 'shares', 'event', 'Shares', 'shares', 1, '2016-02-18 13:12:33', 0, '{}', '1.0.241', 0, 1, 0),
(53, 1, 1, 0, 0, 'apps', 'story', 'event', 'Story', 'story', 1, '2016-02-18 13:12:33', 0, '{}', '1.0.241', 0, 1, 0),
(54, 1, 0, 0, 0, 'apps', 'tasks', 'event', 'Tasks', 'tasks', 1, '2016-02-18 13:12:33', 0, '{}', '1.0.241', 1, 1, 1),
(55, 1, 0, 0, 0, 'apps', 'videos', 'event', 'Videos', 'videos', 1, '2016-02-18 13:12:33', 0, '{}', '1.4.45', 1, 1, 0),
(56, 0, 0, 1, 0, 'fields', 'acymailing', 'user', 'Acymailing', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0', 0, 1, 0),
(57, 0, 0, 0, 0, 'fields', 'address', 'user', 'Address', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(58, 0, 0, 0, 0, 'fields', 'autocomplete', 'user', 'Autocomplete', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.4.0', 0, 1, 0),
(59, 1, 0, 1, 0, 'fields', 'avatar', 'user', 'Avatar', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(60, 0, 0, 1, 0, 'fields', 'birthday', 'user', 'Birthday', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(61, 0, 0, 0, 0, 'fields', 'boolean', 'user', 'Boolean', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(62, 0, 0, 0, 0, 'fields', 'checkbox', 'user', 'Checkboxes', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(63, 0, 0, 1, 0, 'fields', 'country', 'user', 'Country', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(64, 1, 0, 1, 0, 'fields', 'cover', 'user', 'Cover', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(65, 0, 0, 0, 0, 'fields', 'currency', 'user', 'Currency', '', 1, '2016-02-18 13:12:36', 0, '{}', '1.0.0', 0, 1, 0),
(66, 0, 0, 0, 0, 'fields', 'datetime', 'user', 'Datetime', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(67, 0, 0, 0, 0, 'fields', 'dropdown', 'user', 'Dropdown List', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(68, 0, 0, 0, 0, 'fields', 'email', 'user', 'Email', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(69, 0, 0, 0, 0, 'fields', 'file', 'user', 'File', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(70, 0, 0, 1, 0, 'fields', 'gender', 'user', 'Gender', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0', 0, 1, 0),
(71, 0, 0, 0, 0, 'fields', 'header', 'user', 'Header', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(72, 0, 0, 1, 0, 'fields', 'headline', 'user', 'Headline', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(73, 0, 0, 0, 0, 'fields', 'html', 'user', 'Html', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(74, 1, 0, 1, 0, 'fields', 'joomla_email', 'user', 'Joomla User Email', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(75, 1, 0, 1, 0, 'fields', 'joomla_fullname', 'user', 'Joomla User Fullname', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(76, 0, 0, 1, 0, 'fields', 'joomla_joindate', 'user', 'Joomla Joined Date', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(77, 0, 0, 1, 0, 'fields', 'joomla_language', 'user', 'Joomla Language', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(78, 0, 0, 1, 0, 'fields', 'joomla_lastlogin', 'user', 'Joomla Last Login Date', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(79, 1, 0, 1, 0, 'fields', 'joomla_password', 'user', 'Joomla User Password', '', 1, '2016-02-18 13:12:37', 0, '{}', '1.0.0', 0, 1, 0),
(80, 0, 0, 1, 0, 'fields', 'joomla_timezone', 'user', 'Joomla User Timezone', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(81, 0, 0, 1, 0, 'fields', 'joomla_twofactor', 'user', 'Joomla Two Factor', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(82, 0, 0, 1, 0, 'fields', 'joomla_user_editor', 'user', 'Joomla User Editor', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(83, 1, 0, 1, 0, 'fields', 'joomla_username', 'user', 'Joomla Username', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(84, 0, 0, 1, 0, 'fields', 'kunena_signature', 'user', 'Kunena Signature', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(85, 0, 0, 1, 0, 'fields', 'mailchimp', 'user', 'Mailchimp', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0', 0, 1, 0),
(86, 0, 0, 0, 0, 'fields', 'mollom', 'user', 'Mollom', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(87, 0, 0, 0, 0, 'fields', 'multidropdown', 'user', 'Multi Dropdown List', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(88, 0, 0, 0, 0, 'fields', 'multilist', 'user', 'Multilist', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(89, 0, 0, 0, 0, 'fields', 'multitextbox', 'user', 'Multi Textbox', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(90, 0, 0, 1, 0, 'fields', 'permalink', 'user', 'Permalink', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0', 0, 1, 0),
(91, 0, 0, 1, 0, 'fields', 'recaptcha', 'user', 'Recaptcha', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(92, 0, 0, 1, 0, 'fields', 'relationship', 'user', 'Relationship Status', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(93, 0, 0, 0, 0, 'fields', 'separator', 'user', 'Separator', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(94, 0, 0, 1, 0, 'fields', 'skype', 'user', 'Skype', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0', 0, 1, 0),
(95, 0, 0, 0, 0, 'fields', 'terms', 'user', 'Terms', '', 1, '2016-02-18 13:12:38', 0, '{}', '1.0.0', 0, 1, 0),
(96, 0, 0, 0, 0, 'fields', 'text', 'user', 'Text', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(97, 0, 0, 0, 0, 'fields', 'textarea', 'user', 'Textarea', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(98, 0, 0, 0, 0, 'fields', 'textbox', 'user', 'Textbox', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(99, 0, 0, 0, 0, 'fields', 'url', 'user', 'URL', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(100, 0, 0, 0, 0, 'fields', 'vmvendor', 'user', 'Virtuemart Vendor Field', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.4.0', 0, 1, 0),
(101, 0, 0, 0, 0, 'fields', 'address', 'group', 'Address', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(102, 1, 0, 1, 0, 'fields', 'avatar', 'group', 'Avatar', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(103, 0, 0, 0, 0, 'fields', 'boolean', 'group', 'Boolean', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(104, 0, 0, 0, 0, 'fields', 'checkbox', 'group', 'Checkboxes', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(105, 1, 0, 1, 0, 'fields', 'cover', 'group', 'Cover', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(106, 0, 0, 0, 0, 'fields', 'datetime', 'group', 'Datetime', '', 1, '2016-02-18 13:12:39', 0, '{}', '1.0.0', 0, 1, 0),
(107, 1, 0, 1, 0, 'fields', 'description', 'group', 'Description', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(108, 1, 0, 1, 0, 'fields', 'discussions', 'group', 'Discussions', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(109, 0, 0, 0, 0, 'fields', 'dropdown', 'group', 'Dropdown List', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(110, 0, 0, 0, 0, 'fields', 'email', 'group', 'Email', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(111, 0, 0, 1, 0, 'fields', 'eventcreate', 'group', 'Event Create', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(112, 0, 0, 0, 0, 'fields', 'file', 'group', 'File', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(113, 0, 0, 0, 0, 'fields', 'header', 'group', 'Header', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(114, 0, 0, 0, 0, 'fields', 'headline', 'group', 'Headline', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(115, 0, 0, 0, 0, 'fields', 'html', 'group', 'Html', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(116, 0, 0, 1, 0, 'fields', 'moderation', 'group', 'Post Moderation', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.3.0', 0, 1, 0),
(117, 0, 0, 0, 0, 'fields', 'multidropdown', 'group', 'Multi Dropdown List', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(118, 0, 0, 0, 0, 'fields', 'multilist', 'group', 'Multilist', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(119, 0, 0, 0, 0, 'fields', 'multitextbox', 'group', 'Multi Textbox', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(120, 1, 0, 1, 0, 'fields', 'news', 'group', 'News', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(121, 0, 0, 1, 0, 'fields', 'permalink', 'group', 'Permalink', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0', 0, 1, 0),
(122, 0, 0, 1, 0, 'fields', 'permissions', 'group', 'Stream Permissions', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.3.0', 0, 1, 0),
(123, 1, 0, 1, 0, 'fields', 'photos', 'group', 'Photos', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(124, 0, 0, 0, 0, 'fields', 'separator', 'group', 'Separator', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(125, 0, 0, 0, 0, 'fields', 'text', 'group', 'Text', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(126, 0, 0, 0, 0, 'fields', 'textarea', 'group', 'Textarea', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(127, 0, 0, 0, 0, 'fields', 'textbox', 'group', 'Textbox', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(128, 1, 0, 1, 0, 'fields', 'title', 'group', 'Title', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(129, 1, 0, 1, 0, 'fields', 'type', 'group', 'Group Type', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(130, 0, 0, 0, 0, 'fields', 'url', 'group', 'URL', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.0.0', 0, 1, 0),
(131, 1, 0, 1, 0, 'fields', 'videos', 'group', 'Videos', '', 1, '2016-02-18 13:12:40', 0, '{}', '1.4.0', 0, 1, 0),
(132, 0, 0, 0, 0, 'fields', 'address', 'event', 'Address', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(133, 0, 0, 1, 0, 'fields', 'allday', 'event', 'All Day', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(134, 1, 0, 1, 0, 'fields', 'avatar', 'event', 'Avatar', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(135, 0, 0, 0, 0, 'fields', 'boolean', 'event', 'Boolean', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(136, 0, 0, 0, 0, 'fields', 'checkbox', 'event', 'Checkboxes', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(137, 1, 0, 1, 0, 'fields', 'configAllowMaybe', 'event', 'Config - Allow Maybe', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(138, 1, 0, 1, 0, 'fields', 'configNotGoingGuest', 'event', 'Config - Not Going Guest', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(139, 1, 0, 1, 0, 'fields', 'cover', 'event', 'Cover', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(140, 0, 0, 0, 0, 'fields', 'datetime', 'event', 'Datetime', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(141, 1, 0, 1, 0, 'fields', 'description', 'event', 'Description', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(142, 1, 0, 1, 0, 'fields', 'discussions', 'event', 'Discussions', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(143, 0, 0, 0, 0, 'fields', 'dropdown', 'event', 'Dropdown List', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(144, 0, 0, 0, 0, 'fields', 'email', 'event', 'Email', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(145, 0, 0, 0, 0, 'fields', 'file', 'event', 'File', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(146, 0, 0, 1, 0, 'fields', 'guestLimit', 'event', 'Guest Limit', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(147, 0, 0, 0, 0, 'fields', 'header', 'event', 'Header', '', 1, '2016-02-18 13:12:41', 0, '{}', '1.0.0', 0, 1, 0),
(148, 0, 0, 0, 0, 'fields', 'headline', 'event', 'Headline', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(149, 0, 0, 0, 0, 'fields', 'html', 'event', 'Html', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(150, 0, 0, 1, 0, 'fields', 'membertransfer', 'event', 'Group Member Transfer', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(151, 0, 0, 0, 0, 'fields', 'multidropdown', 'event', 'Multi Dropdown List', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(152, 0, 0, 0, 0, 'fields', 'multilist', 'event', 'Multilist', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(153, 0, 0, 0, 0, 'fields', 'multitextbox', 'event', 'Multi Textbox', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(154, 1, 0, 1, 0, 'fields', 'news', 'event', 'News', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(155, 0, 0, 1, 0, 'fields', 'ownerstate', 'event', 'Owner State', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(156, 0, 0, 1, 0, 'fields', 'permalink', 'event', 'Permalink', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0', 0, 1, 0),
(157, 1, 0, 1, 0, 'fields', 'photos', 'event', 'Photos', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(158, 0, 0, 1, 0, 'fields', 'recurring', 'event', 'Recurring', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(159, 0, 0, 0, 0, 'fields', 'separator', 'event', 'Separator', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(160, 1, 0, 1, 0, 'fields', 'startend', 'event', 'Event Start End', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(161, 0, 0, 0, 0, 'fields', 'text', 'event', 'Text', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(162, 0, 0, 0, 0, 'fields', 'textarea', 'event', 'Textarea', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(163, 0, 0, 0, 0, 'fields', 'textbox', 'event', 'Textbox', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(164, 1, 0, 1, 0, 'fields', 'title', 'event', 'Title', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(165, 1, 0, 1, 0, 'fields', 'type', 'event', 'Group Type', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(166, 0, 0, 1, 0, 'fields', 'upcomingreminder', 'event', 'Upcoming Event Reminder', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(167, 0, 0, 0, 0, 'fields', 'url', 'event', 'URL', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.0.0', 0, 1, 0),
(168, 1, 0, 1, 0, 'fields', 'videos', 'event', 'Videos', '', 1, '2016-02-18 13:12:42', 0, '{}', '1.4.0', 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps_calendar`
--

CREATE TABLE IF NOT EXISTS `j_social_apps_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `reminder` tinyint(3) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `all_day` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps_map`
--

CREATE TABLE IF NOT EXISTS `j_social_apps_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `app_id` int(11) NOT NULL,
  `position` varchar(50) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_uid_type` (`app_id`,`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps_views`
--

CREATE TABLE IF NOT EXISTS `j_social_apps_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `view` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_view` (`app_id`,`view`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `j_social_apps_views`
--

INSERT INTO `j_social_apps_views` (`id`, `app_id`, `view`, `type`, `title`, `description`) VALUES
(1, 4, 'profile', 'embed', '', ''),
(2, 8, 'dashboard', 'canvas', '', ''),
(3, 8, 'profile', 'canvas', '', ''),
(4, 11, 'dashboard', 'embed', '', ''),
(5, 11, 'profile', 'embed', '', ''),
(6, 16, 'profile', 'embed', '', ''),
(7, 17, 'profile', 'embed', '', ''),
(8, 19, 'profile', 'embed', '', ''),
(9, 20, 'dashboard', 'embed', '', ''),
(10, 20, 'profile', 'embed', '', ''),
(11, 27, 'dashboard', 'embed', '', ''),
(12, 30, 'groups', 'embed', '', ''),
(13, 32, 'groups', 'embed', '', ''),
(14, 33, 'groups', 'embed', '', ''),
(15, 36, 'groups', 'embed', '', ''),
(16, 37, 'groups', 'embed', '', ''),
(17, 42, 'groups', 'embed', '', ''),
(18, 44, 'events', 'embed', '', ''),
(19, 46, 'events', 'embed', '', ''),
(20, 47, 'events', 'embed', '', ''),
(21, 49, 'events', 'embed', '', ''),
(22, 54, 'events', 'embed', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_avatars`
--

CREATE TABLE IF NOT EXISTS `j_social_avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary ID',
  `uid` int(11) NOT NULL COMMENT 'Node''s ID',
  `type` varchar(255) NOT NULL,
  `avatar_id` bigint(20) NOT NULL COMMENT 'If the node is using a default avatar, this field will be populated with an id.',
  `photo_id` int(11) NOT NULL COMMENT 'If the avatar is created from a photo, this field will be populated with the photo id.',
  `small` text NOT NULL,
  `medium` text NOT NULL,
  `square` text NOT NULL,
  `large` text NOT NULL,
  `modified` datetime NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `avatar_id` (`avatar_id`),
  KEY `photo_id` (`photo_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_uid_type` (`uid`,`type`),
  KEY `idx_storage_cron` (`storage`,`avatar_id`,`small`(64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_badges`
--

CREATE TABLE IF NOT EXISTS `j_social_badges` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `command` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `howto` text NOT NULL,
  `avatar` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `frequency` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `discuss_badges_alias` (`alias`),
  KEY `discuss_badges_published` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `j_social_badges`
--

INSERT INTO `j_social_badges` (`id`, `command`, `extension`, `title`, `alias`, `description`, `howto`, `avatar`, `created`, `state`, `frequency`) VALUES
(1, 'create.article', 'com_content', 'Publisher', 'publisher', 'Loves contributing articles.', 'To unlock this badge, you need to create up to 50 new articles.', 'media/com_easysocial/apps/user/article/assets/badges/publisher.png', '2016-02-18 13:12:27', 1, 50),
(2, 'update.article', 'com_content', 'Proof Reading', 'proof-reading', 'Great proof reading skills', 'To unlock this badge, you need to update 50 existing articles.', 'media/com_easysocial/apps/user/article/assets/badges/proof-reading.png', '2016-02-18 13:12:27', 1, 50),
(3, 'read.article', 'com_content', 'Great Reader', 'great-reader', 'Loves reading through articles.', 'To unlock this badge, you need to read up to 100 articles.', 'media/com_easysocial/apps/user/article/assets/badges/great-reader.png', '2016-02-18 13:12:27', 1, 100),
(4, 'apps.install', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_APPLICANT_TITLE', 'applicant', 'COM_EASYSOCIAL_BADGES_APPLICANT_DESC', 'COM_EASYSOCIAL_BADGES_APPLICANT_HOWTO', 'media/com_easysocial/badges/applicant.png', '2016-02-18 13:12:47', 1, 10),
(5, 'conversation.reply', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_CHATTER_TITLE', 'chatter', 'COM_EASYSOCIAL_BADGES_CHATTER_DESC', 'COM_EASYSOCIAL_BADGES_CHATTER_HOWTO', 'media/com_easysocial/badges/chatter.png', '2016-02-18 13:12:47', 1, 150),
(6, 'conversation.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_SOCIALIZER_TITLE', 'socializer', 'COM_EASYSOCIAL_BADGES_SOCIALIZER_DESC', 'COM_EASYSOCIAL_BADGES_SOCIALIZER_HOWTO', 'media/com_easysocial/badges/socializer.png', '2016-02-18 13:12:47', 1, 15),
(7, 'conversation.invite', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_GOSSIPER_TITLE', 'gossiper', 'COM_EASYSOCIAL_BADGES_GOSSIPER_DESC', 'COM_EASYSOCIAL_BADGES_GOSSIPER_HOWTO', 'media/com_easysocial/badges/gossiper.png', '2016-02-18 13:12:47', 1, 10),
(8, 'conversation.leave', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_LEAVER_TITLE', 'leaver', 'COM_EASYSOCIAL_BADGES_LEAVER_DESC', 'COM_EASYSOCIAL_BADGES_LEAVER_HOWTO', 'media/com_easysocial/badges/leaver.png', '2016-02-18 13:12:47', 1, 20),
(9, 'followers.follow', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FOLLOWER_TITLE', 'follower', 'COM_EASYSOCIAL_BADGES_FOLLOWER_DESC', 'COM_EASYSOCIAL_BADGES_FOLLOWER_HOWTO', 'media/com_easysocial/badges/follower.png', '2016-02-18 13:12:47', 1, 60),
(10, 'followers.followed', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_THOUGHT_LEADER_TITLE', 'thought-leader', 'COM_EASYSOCIAL_BADGES_THOUGHT_LEADER_DESC', 'COM_EASYSOCIAL_BADGES_THOUGHT_LEADER_HOWTO', 'media/com_easysocial/badges/thought-leader.png', '2016-02-18 13:12:47', 1, 60),
(11, 'friends.remove', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FRIEND_HATER_TITLE', 'friend-hater', 'COM_EASYSOCIAL_BADGES_FRIEND_HATER_DESC', 'COM_EASYSOCIAL_BADGES_FRIEND_HATER_HOWTO', 'media/com_easysocial/badges/friend-hater.png', '2016-02-18 13:12:47', 1, 10),
(12, 'friends.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FRIEND_SEEKER_TITLE', 'friend-seeker', 'COM_EASYSOCIAL_BADGES_FRIEND_SEEKER_DESC', 'COM_EASYSOCIAL_BADGES_FRIEND_SEEKER_HOWTO', 'media/com_easysocial/badges/friend-seeker.png', '2016-02-18 13:12:47', 1, 30),
(13, 'friends.list.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FRIEND_ORGANIZER_TITLE', 'friend-organizer', 'COM_EASYSOCIAL_BADGES_FRIEND_ORGANIZER_DESC', 'COM_EASYSOCIAL_BADGES_FRIEND_ORGANIZER_HOWTO', 'media/com_easysocial/badges/friend-organizer.png', '2016-02-18 13:12:47', 1, 10),
(14, 'photos.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_PHOTOGENIC_TITLE', 'photogenic', 'COM_EASYSOCIAL_BADGES_PHOTOGENIC_DESC', 'COM_EASYSOCIAL_BADGES_PHOTOGENIC_HOWTO', 'media/com_easysocial/badges/photogenic.png', '2016-02-18 13:12:47', 1, 30),
(15, 'photos.browse', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_JOURNALIST_TITLE', 'journalist', 'COM_EASYSOCIAL_BADGES_JOURNALIST_DESC', 'COM_EASYSOCIAL_BADGES_JOURNALIST_HOWTO', 'media/com_easysocial/badges/journalist.png', '2016-02-18 13:12:47', 1, 150),
(16, 'photos.tag', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_PHOTO_TAGGER_TITLE', 'photo-tagger', 'COM_EASYSOCIAL_BADGES_PHOTO_TAGGER_DESC', 'COM_EASYSOCIAL_BADGES_PHOTO_TAGGER_HOWTO', 'media/com_easysocial/badges/photo-tagger.png', '2016-02-18 13:12:47', 1, 50),
(17, 'photos.superstar', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_SUPER_STAR_TITLE', 'super-star', 'COM_EASYSOCIAL_BADGES_SUPER_STAR_DESC', 'COM_EASYSOCIAL_BADGES_SUPER_STAR_HOWTO', 'media/com_easysocial/badges/super-star.png', '2016-02-18 13:12:47', 1, 50),
(18, 'points.achieve', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_POINTS_ACHIEVER', 'points-achiever', 'COM_EASYSOCIAL_BADGES_POINTS_ACHIEVER_DESC', 'COM_EASYSOCIAL_BADGES_POINTS_ACHIEVER_HOWTO', 'media/com_easysocial/badges/points-achiever.png', '2016-02-18 13:12:47', 1, 100),
(19, 'profile.view', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_EXPLORER_TITLE', 'explorer', 'COM_EASYSOCIAL_BADGES_EXPLORER_DESC', 'COM_EASYSOCIAL_BADGES_EXPLORER_HOWTO', 'media/com_easysocial/badges/explorer.png', '2016-02-18 13:12:47', 1, 50),
(20, 'registration.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_NEWBIE_TITLE', 'newbie', 'COM_EASYSOCIAL_BADGES_NEWBIE_DESC', 'COM_EASYSOCIAL_BADGES_NEWBIE_HOWTO', 'media/com_easysocial/badges/newbie.png', '2016-02-18 13:12:47', 1, 1),
(21, 'reports.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_REPORTER_TITLE', 'reporter', 'COM_EASYSOCIAL_BADGES_REPORTER_DESC', 'COM_EASYSOCIAL_BADGES_REPORTER_HOWTO', 'media/com_easysocial/badges/reporter.png', '2016-02-18 13:12:47', 1, 20),
(22, 'search.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_SEARCH_ENGINE_TITLE', 'search-engine', 'COM_EASYSOCIAL_BADGES_SEARCH_ENGINE_DESC', 'COM_EASYSOCIAL_BADGES_SEARCH_ENGINE_HOWTO', 'media/com_easysocial/badges/search-engine.png', '2016-02-18 13:12:47', 1, 50),
(23, 'story.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_STORY_TELLER_TITLE', 'story-teller', 'COM_EASYSOCIAL_BADGES_STORY_TELLER_DESC', 'COM_EASYSOCIAL_BADGES_STORY_TELLER_HOWTO', 'media/com_easysocial/badges/story-teller.png', '2016-02-18 13:12:47', 1, 30);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_badges_history`
--

CREATE TABLE IF NOT EXISTS `j_social_badges_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achieved` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_badges_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_badges_maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `custom_message` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `badge_id` (`badge_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_block_users`
--

CREATE TABLE IF NOT EXISTS `j_social_block_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`target_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_targetid` (`target_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_bookmarks`
--

CREATE TABLE IF NOT EXISTS `j_social_bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `uid` int(11) NOT NULL COMMENT 'The bookmarked item id',
  `type` varchar(255) NOT NULL COMMENT 'The bookmarked type',
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'The owner of the bookmarked item',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `user_id` (`user_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_user_utype` (`uid`,`type`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_broadcasts`
--

CREATE TABLE IF NOT EXISTS `j_social_broadcasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `link` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_broadcast` (`target_id`,`target_type`,`state`,`created`),
  KEY `idx_created` (`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `cluster_type` varchar(255) NOT NULL,
  `creator_type` varchar(255) NOT NULL,
  `creator_uid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `featured` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  `hits` int(11) NOT NULL,
  `type` tinyint(3) NOT NULL,
  `key` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `parent_type` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL COMMENT 'The longitude value of the event for proximity search purposes',
  `latitude` varchar(255) NOT NULL COMMENT 'The latitude value of the event for proximity search purposes',
  `address` text NOT NULL COMMENT 'The full address value of the event for displaying purposes',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `idx_state` (`state`),
  KEY `idx_clustertype` (`cluster_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_categories`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'The creator of the category',
  `ordering` tinyint(3) NOT NULL,
  `site_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `j_social_clusters_categories`
--

INSERT INTO `j_social_clusters_categories` (`id`, `type`, `title`, `alias`, `description`, `created`, `state`, `uid`, `ordering`, `site_id`) VALUES
(1, 'group', 'General', 'general', 'General groups', '2016-02-18 13:12:50', 1, 951, 1, NULL),
(2, 'group', 'Automobile', 'automobile', 'Cars, motors, vehicle and all things related to automobile.', '2016-02-18 13:12:50', 1, 951, 2, NULL),
(3, 'group', 'Technology', 'technology', 'Multimedia, IT, and all the tech', '2016-02-18 13:12:50', 1, 951, 3, NULL),
(4, 'group', 'Business', 'business', 'Let''s talk business', '2016-02-18 13:12:51', 1, 951, 4, NULL),
(5, 'group', 'Music', 'music', 'Pop, rock, electronic and all', '2016-02-18 13:12:51', 1, 951, 5, NULL),
(6, 'event', 'General', 'general-2', 'General events', '2016-02-18 13:12:51', 1, 951, 1, NULL),
(7, 'event', 'Meeting', 'meeting', 'Weekly meeting events.', '2016-02-18 13:12:51', 1, 951, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_categories_access`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_categories_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'create',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`profile_id`),
  KEY `category_id_2` (`category_id`,`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_news`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `hits` int(11) NOT NULL,
  `comments` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`,`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_nodes`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(4) NOT NULL,
  `owner` tinyint(3) NOT NULL,
  `admin` tinyint(3) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `reminder_sent` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`,`state`),
  KEY `invited_by` (`invited_by`),
  KEY `idx_clusters_nodes_uid` (`uid`),
  KEY `idx_clusters_nodes_user` (`uid`,`state`,`created`),
  KEY `idx_members` (`cluster_id`,`type`,`state`),
  KEY `idx_reminder_sent` (`reminder_sent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_comments`
--

CREATE TABLE IF NOT EXISTS `j_social_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `comment` text NOT NULL,
  `stream_id` bigint(20) DEFAULT '0',
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `depth` bigint(10) DEFAULT '0',
  `parent` bigint(20) DEFAULT '0',
  `child` bigint(20) DEFAULT '0',
  `lft` bigint(20) DEFAULT '0',
  `rgt` bigint(20) DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `social_comments_uid` (`uid`),
  KEY `social_comments_type` (`element`),
  KEY `social_comments_createdby` (`created_by`),
  KEY `social_comments_content_type` (`element`,`uid`),
  KEY `social_comments_content_type_by` (`element`,`uid`,`created_by`),
  KEY `social_comments_content_parent` (`element`,`uid`,`parent`),
  KEY `idx_comment_batch` (`stream_id`,`element`,`uid`),
  KEY `idx_comment_stream_id` (`stream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_config`
--

CREATE TABLE IF NOT EXISTS `j_social_config` (
  `type` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `value_binary` blob,
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_social_config`
--

INSERT INTO `j_social_config` (`type`, `value`, `value_binary`) VALUES
('dbversion', '1.4.7', NULL),
('site', '{"alerts":{"paths":["admin","site","apps","fields","plugins","modules"]},"general":{"key":"889d190f117f5a134eca72a369f57d48","ajaxindex":true,"environment":"static","mode":"compressed","inline":false,"super":false,"profiler":false,"logger":false,"site":{"loginemail":true,"login":"","logout":"","lockdown":{"enabled":false,"registration":false},"twofactor":false},"url":{"purge":true,"interval":"1"},"cron":{"secure":false,"key":"","limit":20},"location":{"language":"en","proximity":{"unit":"mile"}},"cdn":{"enabled":false,"url":"","passive":false}},"groups":{"enabled":true,"stream":{"create":true},"invite":{"nonfriends":false},"item":{"display":"timeline"}},"antispam":{"recaptcha":{"public":"","private":""},"akismet":{"key":"dff980f9f600"},"mollom":{"public":"","private":"","servers":""}},"conversations":{"enabled":1,"limit":20,"akismet":1,"archiving":1,"editor":"","locations":1,"multiple":1,"nonfriend":false,"pagination":{"enabled":true,"limit":20,"toolbarlimit":5},"attachments":{"enabled":1,"types":["txt","jpg","png","gif","zip","pdf"],"maxsize":3,"storage":"media\\/com_easysocial\\/uploads\\/conversations"},"layout":{"intro":200}},"email":{"html":1,"replyto":"","heading":{"company":"Stack Ideas Sdn Bhd"},"sender":{"email":"","name":""}},"links":{"cache":{"images":false,"location":"\\/media\\/com_easysocial\\/cache\\/links"}},"notifications":{"general":{"pagination":10},"broadcast":{"popup":true,"interval":15,"sticky":true,"period":8},"system":{"autoread":false,"enabled":true,"polling":30},"friends":{"enabled":true,"polling":30},"conversation":{"enabled":true,"polling":30}},"reports":{"enabled":true,"automation":true,"threshold":30,"maxip":5,"guests":false,"features":{"stream":true,"user":true,"comments":true},"notifications":{"moderators":true,"custom":false,"emails":""}},"storage":{"avatar":"joomla","photos":"joomla","files":"joomla","links":"joomla","videos":"joomla","joomla":{"limit":10},"amazon":{"access":"","secret":"","bucket":"","ssl":true,"limit":10,"delete":true,"region":"","class":""}},"photos":{"enabled":true,"quality":80,"downloads":true,"original":true,"import":{"exif":true},"popup":{"default":true},"storage":{"container":"\\/media\\/com_easysocial\\/photos"},"uploader":{"maxsize":"32"},"pagination":{"photo":10,"album":10},"exif":["aperture","iso","exposure","copyright","camera"],"layout":{"size":"large","mode":"cover","pattern":"tile","threshold":128,"ratio":"4x3"}},"avatars":{"storage":{"container":"\\/media\\/com_easysocial\\/avatars","default":"defaults","defaults":{"profiles":"profiles"},"user":"users","group":"group","event":"event","clusters":"clusters","profiles":"profiles"},"default":{"user":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/square.png"},"profiles":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/square.png"},"group":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/square.png"},"event":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/square.png"},"clusterscategory":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/square.png"}}},"connector":{"client":"curl"},"covers":{"storage":{"container":"\\/media\\/com_easysocial\\/covers","default":"defaults","defaults":{"profiles":"profiles"},"user":"users","group":"group","event":"event","profiles":"profiles","clusters":"clusters"},"default":{"user":{"default":"\\/media\\/com_easysocial\\/defaults\\/covers\\/user\\/default.jpg"},"group":{"default":"\\/media\\/com_easysocial\\/defaults\\/covers\\/group\\/default.jpg"},"event":{"default":"\\/media\\/com_easysocial\\/defaults\\/covers\\/event\\/default.jpg"}}},"files":{"storage":{"container":"\\/media\\/com_easysocial\\/files","fields":{"container":"fields","user":"user"},"conversations":{"container":"conversations"},"group":{"container":"groups"},"user":{"container":"users"},"comments":{"container":"comments"}}},"friends":{"invites":{"enabled":true},"list":{"enabled":true,"showEmpty":true},"stream":{"create":1}},"lists":{"display":{"limit":5},"stream":{"create":1}},"apps":{"browser":true,"tnc":{"required":true,"message":"COM_EASYSOCIAL_APPS_TNC"}},"layout":{"dashboard":{"apps":{"limit":5},"lists":{"limit":5}},"leaderboard":{"limit":20},"profile":{"apps":{"limit":15}},"spotlight":[],"groups":[]},"oauth":{"facebook":{"app":"","secret":"","pull":true,"push":true,"opengraph":{"enabled":true},"jfbconnect":{"enabled":false},"registration":{"enabled":false,"type":"simplified","profile":1,"avatar":true,"cover":true,"timeline":true,"totalTimeline":1},"username":"email"},"twitter":{"app":"","secret":"","registration":{"enabled":true,"avatar":true,"tweets":true,"totalTweets":true}},"myspace":{"app":"","secret":""}},"leaderboard":{"listings":{"admin":false}},"badges":{"enabled":true,"paths":["admin","site","apps","fields","plugins","modules"]},"followers":{"enabled":true},"points":{"enabled":true,"history":{"limit":60},"paths":["admin","site","apps","fields","plugins","modules"]},"profiles":{"stream":{"create":1,"update":1}},"registrations":{"enabled":1,"emailasusername":0,"change.selection":1,"email":{"password":true},"profiles":{"avatar":true,"showUsers":true,"usersCount":20,"showType":true},"steps":{"progress":1,"heading":1},"mini":{"mode":"quick","profile":"default"}},"stream":{"translations":{"bing":false,"explicit":false,"bingid":"","bingsecret":""},"aggregation":{"enabled":1,"duration":15},"rss":{"enabled":true},"timestamp":{"enabled":true},"bookmarks":{"enabled":true},"content":{"nofollow":false,"truncate":false,"truncatelength":250},"archive":{"enabled":false,"duration":6},"pin":{"enabled":true},"comments":{"enabled":true,"guestview":false},"follow":{"enabled":true},"likes":{"enabled":true},"repost":{"enabled":true},"sharing":{"enabled":true},"pagination":{"style":"loadmore","autoload":true,"pagelimit":10,"sort":"modified"},"story":{"mentions":true,"moods":true,"location":true,"entertosubmit":false},"updates":{"enabled":true,"interval":30},"actions":["likes","comments","repost"]},"activity":{"pagination":{"max":5,"limit":10}},"story":{"friends_enabled":1},"location":{"coords":"40.702147,-74.015794","provider":"maps","foursquare":{"clientid":"","clientsecret":""},"places":{"api":""},"maps":{"api":""}},"toolbar":{"display":true},"theme":{"site":"wireframe","site_base":"wireframe","admin":"default","admin_base":"default","apps":"default","fields":"default","compiler":{"mode":"off","use_absolute_uri":0,"allow_template_override":1}},"uploader":{"storage":{"container":"\\/media\\/com_easysocial\\/tmp"}},"users":{"change_username":1,"display":{"profiletype":true},"displayName":"username","aliasName":"username","deleteLogic":"unpublish","simpleUrl":false,"avatarWebcam":true,"blocking":{"enabled":true},"reminder":{"enabled":false,"duration":30},"dashboard":{"start":"me"},"stream":{"login":1,"logout":1,"friend":1,"following":1,"profile":1},"listings":{"admin":false,"sorting":"latest","esadadmin":true},"indexer":{"name":"realname","email":false,"privacy":true},"profile":{"display":"timeline"}},"sharing":{"enabled":1,"vendors":{"email":1,"facebook":1,"twitter":1,"google":1,"live":1,"linkedin":1,"myspace":1,"vk":1,"stumbleupon":1,"digg":1,"tumblr":1,"evernote":1,"reddit":1,"delicious":1},"email":{"limit":10}},"access":{"paths":["admin","site","apps","fields","plugins","modules"]},"comments":{"reply":0,"maxlevel":3,"limit":5,"attachments":true,"resize":{"enabled":false,"width":"1024","height":"768"},"storage":"\\/media\\/com_easysocial\\/comments","enter":"submit","submit":1,"smileys":true},"user":{"completeprofile":{"required":false,"strict":true,"action":"info"}},"events":{"stream":{"create":true},"startofweek":1,"enabled":true,"recurringlimit":50,"ical":true,"invite":{"nonfriends":false},"timeformat":"12h","listing":{"includefeatured":false,"includegroup":false},"item":{"display":"timeline"}},"video":{"enabled":true,"uploads":false,"embeds":true,"ffmpeg":"\\/opt\\/local\\/bin\\/ffmpeg","autoencode":true,"audiobitrate":"64k","size":"720","storage":{"container":"\\/media\\/com_easysocial\\/videos"},"layout":{"item":{"recent":true,"total":5,"hits":true,"duration":true,"details":true,"tags":true}}}}', NULL),
('scriptversion', '1.4.7', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `lastreplied` datetime NOT NULL,
  `type` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations_message`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations_message` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) NOT NULL,
  `type` varchar(200) NOT NULL,
  `message` text,
  `created` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations_message_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations_message_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `conversation_id` bigint(20) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `isread` tinyint(1) NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - publish, 2 - archive, 3 - trash',
  PRIMARY KEY (`id`),
  KEY `node_id` (`user_id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `message_id` (`message_id`),
  KEY `idx_user_conversation` (`user_id`,`state`,`conversation_id`,`message_id`),
  KEY `idx_user_conversation_isread` (`user_id`,`state`,`isread`,`conversation_id`,`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations_participants`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations_participants` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `state` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `social_conversation_maps_conversation_id` (`conversation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_covers`
--

CREATE TABLE IF NOT EXISTS `j_social_covers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary ID',
  `uid` int(11) NOT NULL COMMENT 'Node''s ID',
  `type` varchar(255) NOT NULL,
  `photo_id` int(13) NOT NULL COMMENT 'If the node is using a default avatar, this field will be populated with an id.',
  `cover_id` int(11) NOT NULL,
  `x` varchar(255) NOT NULL,
  `y` varchar(255) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `avatar_id` (`photo_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_uid_type` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_default_avatars`
--

CREATE TABLE IF NOT EXISTS `j_social_default_avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `large` text NOT NULL,
  `medium` text NOT NULL,
  `small` text NOT NULL,
  `square` text NOT NULL,
  `default` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `system` (`default`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_default_covers`
--

CREATE TABLE IF NOT EXISTS `j_social_default_covers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `large` text NOT NULL,
  `small` text NOT NULL,
  `default` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `system` (`default`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_discussions`
--

CREATE TABLE IF NOT EXISTS `j_social_discussions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'This determines if this is a reply to a discussion. If it is a reply, it should contain the parent''s id here.',
  `uid` int(11) NOT NULL COMMENT 'The unique id this discussion is associated to. For example, if it is associated with a group, it should store the group''s id.',
  `type` varchar(255) NOT NULL COMMENT 'The unique type this discussion is associated to. For example, if it is associated with a group, it should store the type as group',
  `answer_id` int(11) NOT NULL COMMENT 'This is only applicable to main question. This should contain the reference to the discussion that is an answer.',
  `last_reply_id` int(11) NOT NULL COMMENT 'Determines the last reply for the discussion',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0' COMMENT 'Stores the total views for this discussion.',
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `last_replied` datetime NOT NULL COMMENT 'Stores the last replied date time.',
  `votes` int(11) NOT NULL COMMENT 'Determines the vote count for this discussion.',
  `total_replies` int(11) NOT NULL DEFAULT '0' COMMENT 'This is to denormalize the reply count of a discussion.',
  `lock` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Determines if this discussion is locked',
  `params` text NOT NULL COMMENT 'Stores additional raw parameters for the discussion that doesn''t need to be indexed',
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `uid_2` (`uid`,`type`),
  KEY `id` (`id`,`parent_id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_discussions_files`
--

CREATE TABLE IF NOT EXISTS `j_social_discussions_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`,`discussion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_events_meta`
--

CREATE TABLE IF NOT EXISTS `j_social_events_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL COMMENT 'The event cluster id',
  `start` datetime NOT NULL COMMENT 'The start datetime of the event',
  `end` datetime NOT NULL COMMENT 'The end datetime of the event',
  `timezone` varchar(255) NOT NULL COMMENT 'The optional timezone of the event for datetime calculation',
  `all_day` tinyint(3) NOT NULL COMMENT 'Flag if this event is an all day event',
  `group_id` int(11) NOT NULL COMMENT 'The group id if this is a group event',
  `reminder` int(11) DEFAULT '0' COMMENT 'the number of days before the actual event date',
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`),
  KEY `idx_reminder` (`reminder`),
  KEY `idx_upcoming_reminder` (`reminder`,`start`),
  KEY `idx_start` (`start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_feeds`
--

CREATE TABLE IF NOT EXISTS `j_social_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `url` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields`
--

CREATE TABLE IF NOT EXISTS `j_social_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_key` text NOT NULL,
  `app_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `display_title` tinyint(3) NOT NULL,
  `description` text NOT NULL,
  `display_description` tinyint(3) NOT NULL,
  `default` text NOT NULL,
  `validation` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `searchable` tinyint(4) NOT NULL DEFAULT '1',
  `required` tinyint(4) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `core` tinyint(4) NOT NULL DEFAULT '0',
  `visible_registration` tinyint(3) NOT NULL,
  `visible_edit` tinyint(3) NOT NULL,
  `visible_display` tinyint(3) NOT NULL,
  `visible_mini_registration` tinyint(3) NOT NULL DEFAULT '0',
  `friend_suggest` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_id` (`app_id`),
  KEY `required` (`required`),
  KEY `searchable` (`searchable`),
  KEY `state` (`state`),
  KEY `step_id` (`step_id`),
  KEY `friend_suggest` (`friend_suggest`),
  KEY `idx_unique_key` (`unique_key`(64)),
  KEY `idx_advanced_search1` (`searchable`,`state`,`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=132 ;

--
-- Dumping data for table `j_social_fields`
--

INSERT INTO `j_social_fields` (`id`, `unique_key`, `app_id`, `step_id`, `title`, `display_title`, `description`, `display_description`, `default`, `validation`, `state`, `searchable`, `required`, `params`, `ordering`, `core`, `visible_registration`, `visible_edit`, `visible_display`, `visible_mini_registration`, `friend_suggest`) VALUES
(1, 'HEADER', 71, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ACCOUNT_INFORMATION', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ACCOUNT_INFORMATION_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(2, 'JOOMLA_FULLNAME', 75, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_YOUR_NAME', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_YOUR_NAME_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(3, 'JOOMLA_USERNAME', 83, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DESIRED_USERNAME', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DESIRED_USERNAME_DESC', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DESIRED_USERNAME_DEFAULT', '', 1, 0, 1, '', 2, 0, 1, 1, 0, 0, 0),
(4, 'JOOMLA_PASSWORD', 79, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PASSWORD', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PASSWORD_DESC', 1, '', '', 1, 0, 1, '{"reconfirm_password":true,"password_strength":true}', 3, 0, 1, 1, 0, 0, 0),
(5, 'JOOMLA_EMAIL', 74, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EMAIL_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EMAIL_ADDRESS_DESC', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EMAIL_DEFAULT', '', 1, 1, 1, '', 4, 0, 1, 1, 0, 0, 0),
(6, 'JOOMLA_USER_EDITOR', 82, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDITOR', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDITOR_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 0, 1, 0, 0, 0),
(7, 'JOOMLA_TIMEZONE', 80, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_TIMEZONE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_TIMEZONE_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(8, 'PERMALINK', 90, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PERMALINK_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(9, 'HEADER-1', 71, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DETAILS', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DETAILS_DESC', 1, '', '', 1, 1, 1, '', 8, 0, 1, 1, 0, 0, 0),
(10, 'GENDER', 70, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_GENDER', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_GENDER_DESC', 1, '', '', 1, 1, 1, '', 9, 0, 1, 1, 1, 0, 0),
(11, 'BIRTHDAY', 60, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_BIRTHDAY', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_BIRTHDAY_DESC', 1, '', '', 1, 1, 1, '{"calendar":true}', 10, 0, 1, 1, 1, 0, 0),
(12, 'ADDRESS', 57, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ADDRESS_DESC', 1, '', '', 1, 1, 1, '', 11, 0, 1, 1, 1, 0, 0),
(13, 'TEXTBOX', 98, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_MOBILE_PHONE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_MOBILE_PHONE_DESC', 1, '', '', 1, 1, 0, '{"placeholder":"COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_MOBILE_PHONE_DEFAULT"}', 12, 0, 1, 1, 1, 0, 0),
(14, 'URL', 99, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_WEBSITE_DESC', 1, '', '', 1, 1, 0, '{"placeholder":"COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_WEBSITE_DEFAULT"}', 13, 0, 1, 1, 1, 0, 0),
(15, 'HEADER-2', 71, 2, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(16, 'TEXTBOX-1', 98, 2, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_COLLEGE_OR_UNIVERSITY', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_COLLEGE_OR_UNIVERSITY_DESC', 1, '', '', 1, 1, 0, '', 1, 0, 1, 1, 1, 0, 0),
(17, 'TEXTBOX-2', 98, 2, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_GRADUATION_YEAR', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_GRADUATION_YEAR_DESC', 1, '', '', 1, 1, 0, '', 2, 0, 1, 1, 1, 0, 0),
(18, 'HEADER-3', 71, 3, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_APPEARANCE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_APPEARANCE_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(19, 'AVATAR', 59, 3, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_PICTURE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_PICTURE_DESC', 1, '', '', 1, 1, 0, '', 1, 0, 1, 1, 0, 0, 0),
(20, 'COVER', 64, 3, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_COVER', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_COVER_DESC', 1, '', '', 1, 1, 0, '', 2, 0, 1, 1, 0, 0, 0),
(21, 'HEADER', 113, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(22, 'TITLE', 128, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(23, 'PERMALINK', 121, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(24, 'DESCRIPTION', 107, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(25, 'TYPE', 129, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(26, 'URL', 130, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(27, 'PHOTOS', 123, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(28, 'VIDEOS', 131, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(29, 'NEWS', 120, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(30, 'DISCUSSIONS', 108, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(31, 'HEADER-1', 113, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(32, 'AVATAR', 102, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(33, 'COVER', 105, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(34, 'HEADER', 113, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(35, 'TITLE', 128, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(36, 'PERMALINK', 121, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(37, 'DESCRIPTION', 107, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(38, 'TYPE', 129, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(39, 'URL', 130, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(40, 'PHOTOS', 123, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(41, 'VIDEOS', 131, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(42, 'NEWS', 120, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(43, 'DISCUSSIONS', 108, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(44, 'HEADER-1', 113, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(45, 'AVATAR', 102, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(46, 'COVER', 105, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(47, 'HEADER', 113, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(48, 'TITLE', 128, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(49, 'PERMALINK', 121, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(50, 'DESCRIPTION', 107, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(51, 'TYPE', 129, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(52, 'URL', 130, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(53, 'PHOTOS', 123, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(54, 'VIDEOS', 131, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(55, 'NEWS', 120, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(56, 'DISCUSSIONS', 108, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(57, 'HEADER-1', 113, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(58, 'AVATAR', 102, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(59, 'COVER', 105, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(60, 'HEADER', 113, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(61, 'TITLE', 128, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(62, 'PERMALINK', 121, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(63, 'DESCRIPTION', 107, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(64, 'TYPE', 129, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(65, 'URL', 130, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(66, 'PHOTOS', 123, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(67, 'VIDEOS', 131, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(68, 'NEWS', 120, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(69, 'DISCUSSIONS', 108, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(70, 'HEADER-1', 113, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(71, 'AVATAR', 102, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(72, 'COVER', 105, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(73, 'HEADER', 113, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(74, 'TITLE', 128, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(75, 'PERMALINK', 121, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(76, 'DESCRIPTION', 107, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(77, 'TYPE', 129, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(78, 'URL', 130, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(79, 'PHOTOS', 123, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(80, 'VIDEOS', 131, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(81, 'NEWS', 120, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(82, 'DISCUSSIONS', 108, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(83, 'HEADER-1', 113, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(84, 'AVATAR', 102, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(85, 'COVER', 105, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(86, 'HEADER', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(87, 'TITLE', 164, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(88, 'PERMALINK', 156, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(89, 'STARTEND', 160, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(90, 'ALLDAY', 133, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY_DESC', 1, '', '', 1, 1, 1, '', 4, 0, 1, 1, 1, 0, 0),
(91, 'RECURRING', 158, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(92, 'DESCRIPTION', 141, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 6, 0, 1, 1, 1, 0, 0),
(93, 'TYPE', 165, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE_DESC', 1, '', '', 1, 0, 1, '', 7, 0, 1, 1, 0, 0, 0),
(94, 'URL', 167, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(95, 'HEADER-1', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION_DESC', 1, '', '', 1, 1, 1, '', 9, 0, 1, 1, 0, 0, 0),
(96, 'CONFIGALLOWMAYBE', 137, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(97, 'CONFIGNOTGOINGGUEST', 138, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST_DESC', 1, '', '', 1, 1, 1, '', 11, 0, 1, 1, 0, 0, 0),
(98, 'GUESTLIMIT', 146, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT_DESC', 1, '', '', 1, 1, 1, '', 12, 0, 1, 1, 0, 0, 0),
(99, 'HEADER-2', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES_DESC', 1, '', '', 1, 1, 1, '', 13, 0, 1, 1, 0, 0, 0),
(100, 'PHOTOS', 157, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO_DESC', 1, '1', '', 1, 1, 0, '', 14, 0, 1, 1, 1, 0, 0),
(101, 'VIDEOS', 168, 9, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS', 1, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS_DESC', 1, '1', '', 1, 1, 0, '', 15, 0, 1, 1, 1, 0, 0),
(102, 'NEWS', 154, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS_DESC', 1, '1', '', 1, 1, 0, '', 16, 0, 1, 1, 1, 0, 0),
(103, 'DISCUSSIONS', 142, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS_DESC', 1, '1', '', 1, 1, 0, '', 17, 0, 1, 1, 1, 0, 0),
(104, 'HEADER-3', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS_DESC', 1, '', '', 1, 1, 1, '', 18, 0, 1, 1, 0, 0, 0),
(105, 'ADDRESS', 132, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS_DESC', 1, '', '', 1, 1, 1, '', 19, 0, 1, 1, 1, 0, 0),
(106, 'HEADER-4', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 20, 0, 1, 1, 0, 0, 0),
(107, 'AVATAR', 134, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 21, 0, 1, 1, 1, 0, 0),
(108, 'COVER', 139, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER_DESC', 1, '', '', 1, 1, 0, '', 22, 0, 1, 1, 1, 0, 0),
(109, 'HEADER', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(110, 'TITLE', 164, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(111, 'PERMALINK', 156, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(112, 'STARTEND', 160, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(113, 'ALLDAY', 133, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY_DESC', 1, '', '', 1, 1, 1, '', 4, 0, 1, 1, 1, 0, 0),
(114, 'RECURRING', 158, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(115, 'DESCRIPTION', 141, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 6, 0, 1, 1, 1, 0, 0),
(116, 'TYPE', 165, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE_DESC', 1, '', '', 1, 0, 1, '', 7, 0, 1, 1, 0, 0, 0),
(117, 'URL', 167, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(118, 'HEADER-1', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION_DESC', 1, '', '', 1, 1, 1, '', 9, 0, 1, 1, 0, 0, 0),
(119, 'CONFIGALLOWMAYBE', 137, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(120, 'CONFIGNOTGOINGGUEST', 138, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST_DESC', 1, '', '', 1, 1, 1, '', 11, 0, 1, 1, 0, 0, 0),
(121, 'GUESTLIMIT', 146, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT_DESC', 1, '', '', 1, 1, 1, '', 12, 0, 1, 1, 0, 0, 0),
(122, 'HEADER-2', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES_DESC', 1, '', '', 1, 1, 1, '', 13, 0, 1, 1, 0, 0, 0),
(123, 'PHOTOS', 157, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO_DESC', 1, '1', '', 1, 1, 0, '', 14, 0, 1, 1, 1, 0, 0),
(124, 'VIDEOS', 168, 10, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS', 1, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS_DESC', 1, '1', '', 1, 1, 0, '', 15, 0, 1, 1, 1, 0, 0),
(125, 'NEWS', 154, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS_DESC', 1, '1', '', 1, 1, 0, '', 16, 0, 1, 1, 1, 0, 0),
(126, 'DISCUSSIONS', 142, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS_DESC', 1, '1', '', 1, 1, 0, '', 17, 0, 1, 1, 1, 0, 0),
(127, 'HEADER-3', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS_DESC', 1, '', '', 1, 1, 1, '', 18, 0, 1, 1, 0, 0, 0),
(128, 'ADDRESS', 132, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS_DESC', 1, '', '', 1, 1, 1, '', 19, 0, 1, 1, 1, 0, 0),
(129, 'HEADER-4', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 20, 0, 1, 1, 0, 0, 0),
(130, 'AVATAR', 134, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 21, 0, 1, 1, 1, 0, 0),
(131, 'COVER', 139, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER_DESC', 1, '', '', 1, 1, 0, '', 22, 0, 1, 1, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_data`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `datakey` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `params` text NOT NULL,
  `raw` text,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`,`uid`),
  KEY `node_id` (`uid`),
  KEY `idx_uid_type` (`uid`,`type`),
  KEY `idx_type_raw` (`type`(25),`raw`(255)),
  KEY `idx_type_key_raw` (`type`(25),`datakey`(50),`raw`(255)),
  FULLTEXT KEY `fields_data_raw` (`raw`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `j_social_fields_data`
--

INSERT INTO `j_social_fields_data` (`id`, `field_id`, `uid`, `type`, `datakey`, `data`, `params`, `raw`) VALUES
(1, 2, 953, 'user', 'first', 'Manager', '', 'Manager'),
(2, 2, 953, 'user', 'middle', '', '', ''),
(3, 2, 953, 'user', 'last', '', '', ''),
(4, 2, 953, 'user', 'name', 'Manager', '', 'Manager'),
(5, 2, 951, 'user', 'first', 'Super', '', 'Super'),
(6, 2, 951, 'user', 'middle', '', '', ''),
(7, 2, 951, 'user', 'last', 'User', '', 'User'),
(8, 2, 951, 'user', 'name', 'Super User', '', 'Super User'),
(9, 2, 952, 'user', 'first', 'User', '', 'User'),
(10, 2, 952, 'user', 'middle', '', '', ''),
(11, 2, 952, 'user', 'last', '', '', ''),
(12, 2, 952, 'user', 'name', 'User', '', 'User');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_options`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `ordering` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_parents` (`parent_id`,`key`),
  KEY `idx_parentid` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_position`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `position` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_rules`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `match_text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_steps`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `sequence` int(11) NOT NULL,
  `visible_registration` tinyint(3) NOT NULL,
  `visible_edit` tinyint(3) NOT NULL,
  `visible_display` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`uid`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `j_social_fields_steps`
--

INSERT INTO `j_social_fields_steps` (`id`, `uid`, `type`, `title`, `description`, `state`, `created`, `sequence`, `visible_registration`, `visible_edit`, `visible_display`) VALUES
(1, 1, 'profiles', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_BASIC_INFORMATION', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_BASIC_INFORMATION_DESC', 1, '2016-02-18 13:12:49', 1, 1, 1, 1),
(2, 1, 'profiles', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_EDUCATION', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_EDUCATION_DESC', 1, '2016-02-18 13:12:50', 2, 1, 1, 1),
(3, 1, 'profiles', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_APPEARANCE', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_APPEARANCE_DESC', 1, '2016-02-18 13:12:50', 3, 1, 1, 0),
(4, 1, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 13:12:50', 1, 1, 1, 1),
(5, 2, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 13:12:50', 1, 1, 1, 1),
(6, 3, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 13:12:50', 1, 1, 1, 1),
(7, 4, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 13:12:51', 1, 1, 1, 1),
(8, 5, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 13:12:51', 1, 1, 1, 1),
(9, 6, 'clusters', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO_DESC', 1, '2016-02-18 13:12:51', 1, 1, 1, 1),
(10, 7, 'clusters', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO_DESC', 1, '2016-02-18 13:12:51', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_files`
--

CREATE TABLE IF NOT EXISTS `j_social_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `hits` int(11) NOT NULL,
  `hash` text NOT NULL,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `size` text NOT NULL,
  `mime` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `collection_id` (`collection_id`),
  KEY `idx_storage_cron` (`storage`,`created`),
  KEY `idx_created` (`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_files_collections`
--

CREATE TABLE IF NOT EXISTS `j_social_files_collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'This is the person that creates the item.',
  `title` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_friends`
--

CREATE TABLE IF NOT EXISTS `j_social_friends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `actor_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `message` text NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_friends_actor` (`actor_id`),
  KEY `idx_friends_target` (`target_id`),
  KEY `idx_friends_actor_state` (`actor_id`,`state`),
  KEY `idx_friends_target_state` (`target_id`,`state`),
  KEY `idx_actor_target` (`actor_id`,`target_id`,`state`),
  KEY `idx_target_actor` (`target_id`,`actor_id`,`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_friends_invitations`
--

CREATE TABLE IF NOT EXISTS `j_social_friends_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` text NOT NULL,
  `created` datetime NOT NULL,
  `message` text NOT NULL,
  `registered_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_indexer`
--

CREATE TABLE IF NOT EXISTS `j_social_indexer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `utype` varchar(64) DEFAULT NULL,
  `component` varchar(64) DEFAULT NULL,
  `title` text NOT NULL,
  `content` longtext NOT NULL,
  `link` text,
  `last_update` datetime NOT NULL,
  `ucreator` bigint(20) unsigned DEFAULT '0',
  `image` text,
  PRIMARY KEY (`id`),
  KEY `social_source` (`uid`,`utype`,`component`),
  FULLTEXT KEY `social_indexer_snapshot` (`title`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_languages`
--

CREATE TABLE IF NOT EXISTS `j_social_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `updated` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `translator` varchar(255) NOT NULL,
  `progress` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_likes`
--

CREATE TABLE IF NOT EXISTS `j_social_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `stream_id` bigint(20) DEFAULT '0',
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `social_likes_uid` (`uid`),
  KEY `social_likes_contenttype` (`type`),
  KEY `social_likes_createdby` (`created_by`),
  KEY `social_likes_content_type` (`type`,`uid`),
  KEY `social_likes_content_type_by` (`type`,`uid`,`created_by`),
  KEY `idx_stream_id` (`stream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_links`
--

CREATE TABLE IF NOT EXISTS `j_social_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_links_images`
--

CREATE TABLE IF NOT EXISTS `j_social_links_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_url` text NOT NULL,
  `internal_url` text NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `idx_storage_cron` (`storage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_lists`
--

CREATE TABLE IF NOT EXISTS `j_social_lists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `alias` text NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `default` tinyint(3) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_lists_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_lists_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `list_id` bigint(20) NOT NULL,
  `target_id` bigint(20) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target_id` (`target_id`),
  KEY `idx_target_list_type` (`target_id`,`list_id`,`target_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_locations`
--

CREATE TABLE IF NOT EXISTS `j_social_locations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `type` text NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created` datetime NOT NULL,
  `short_address` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_logger`
--

CREATE TABLE IF NOT EXISTS `j_social_logger` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `line` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_mailer`
--

CREATE TABLE IF NOT EXISTS `j_social_mailer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_name` text NOT NULL,
  `sender_email` text NOT NULL,
  `replyto_email` text NOT NULL,
  `recipient_name` text NOT NULL,
  `recipient_email` text NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `template` text NOT NULL,
  `html` tinyint(4) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `response` text NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  `priority` tinyint(4) NOT NULL COMMENT '1 - Low , 2 - Medium , 3 - High , 4 - Highest',
  `language` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_migrators`
--

CREATE TABLE IF NOT EXISTS `j_social_migrators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `oid` bigint(20) unsigned NOT NULL,
  `element` varchar(100) NOT NULL,
  `component` varchar(100) NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `component_content` (`component`,`oid`,`element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Store migrated content id and map with easysocial item id.' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_moods`
--

CREATE TABLE IF NOT EXISTS `j_social_moods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key of the row',
  `namespace` varchar(255) NOT NULL COMMENT 'Determines if this item is tied to a specific item',
  `namespace_uid` int(11) NOT NULL,
  `icon` varchar(255) NOT NULL COMMENT 'Contains the css class for the emoticon',
  `verb` varchar(255) NOT NULL COMMENT 'Feeling, Watching, Eating etc',
  `subject` text NOT NULL COMMENT 'Happy, Sad, Angry etc',
  `custom` tinyint(3) NOT NULL COMMENT 'Determines if the user supplied a custom text',
  `text` text NOT NULL COMMENT 'If there is a custom text, based on the custom column, this text will be used.',
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_notes`
--

CREATE TABLE IF NOT EXISTS `j_social_notes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `title` text NOT NULL,
  `alias` text NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_notifications`
--

CREATE TABLE IF NOT EXISTS `j_social_notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `context_ids` text NOT NULL,
  `context_type` varchar(255) NOT NULL,
  `cmd` text NOT NULL,
  `app_id` bigint(20) DEFAULT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `image` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '0',
  `actor_id` int(11) NOT NULL,
  `actor_type` varchar(255) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `params` text NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `node_id` (`uid`,`created`),
  KEY `idx_target_state` (`target_id`,`target_type`,`state`),
  KEY `idx_target_created` (`target_id`,`target_type`,`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_oauth`
--

CREATE TABLE IF NOT EXISTS `j_social_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_id` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `client` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `secret` text NOT NULL,
  `created` datetime NOT NULL,
  `expires` varchar(255) NOT NULL,
  `pull` tinyint(3) NOT NULL,
  `push` tinyint(3) NOT NULL,
  `params` text NOT NULL,
  `last_pulled` datetime NOT NULL,
  `last_pushed` datetime NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pull` (`pull`,`push`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_oauth_history`
--

CREATE TABLE IF NOT EXISTS `j_social_oauth_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_id` int(11) NOT NULL,
  `remote_id` int(11) NOT NULL,
  `remote_type` varchar(255) NOT NULL,
  `local_id` int(11) NOT NULL,
  `local_type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_photos`
--

CREATE TABLE IF NOT EXISTS `j_social_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `caption` text NOT NULL,
  `created` datetime NOT NULL,
  `assigned_date` datetime NOT NULL,
  `ordering` tinyint(3) NOT NULL,
  `featured` tinyint(3) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  `total_size` bigint(20) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_photos_user_photos` (`state`,`uid`,`type`,`ordering`),
  KEY `idx_albums` (`state`,`album_id`,`ordering`),
  KEY `idx_storage_cron` (`state`,`storage`,`created`),
  KEY `idx_created` (`created`),
  KEY `idx_state_created` (`state`,`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_photos_meta`
--

CREATE TABLE IF NOT EXISTS `j_social_photos_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `group` varchar(255) NOT NULL,
  `property` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `photo_id` (`photo_id`),
  KEY `group` (`group`),
  KEY `idx_sql1` (`photo_id`,`group`(64),`property`),
  KEY `idx_sql2` (`photo_id`,`group`(64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_photos_tag`
--

CREATE TABLE IF NOT EXISTS `j_social_photos_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `label` text NOT NULL,
  `top` varchar(255) NOT NULL,
  `left` varchar(255) NOT NULL,
  `width` varchar(255) NOT NULL,
  `height` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_points`
--

CREATE TABLE IF NOT EXISTS `j_social_points` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `command` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'The title of the points',
  `description` text NOT NULL,
  `alias` varchar(255) NOT NULL COMMENT 'The permalink that links to the points.',
  `created` datetime NOT NULL COMMENT 'Creation datetime of the points.',
  `threshold` int(11) DEFAULT NULL COMMENT 'Optional value if app needs to give points based on certain actions multiple times.',
  `interval` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0 - every time , 1 - once , 2 - twice - n times',
  `points` int(11) NOT NULL DEFAULT '0' COMMENT 'The amount of points to be given.',
  `state` tinyint(3) NOT NULL COMMENT 'The state of the points. 0 - unpublished, 1 - published ',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `command_id` (`command`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=93 ;

--
-- Dumping data for table `j_social_points`
--

INSERT INTO `j_social_points` (`id`, `command`, `extension`, `title`, `description`, `alias`, `created`, `threshold`, `interval`, `points`, `state`, `params`) VALUES
(1, 'create.article', 'com_content', 'PLG_APP_USER_ARTICLE_CREATE_ARTICLE_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_CREATE_ARTICLE_POINTS_DESC', 'create-article', '2016-02-18 13:12:27', NULL, 0, 5, 1, ''),
(2, 'delete.article', 'com_content', 'PLG_APP_USER_ARTICLE_DELETED_ARTICLE_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_DELETED_ARTICLE_POINTS_DESC', 'deleted-article', '2016-02-18 13:12:27', NULL, 0, -5, 1, ''),
(3, 'read.article', 'com_content', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_POINTS_DESC', 'read-article', '2016-02-18 13:12:27', NULL, 0, 1, 1, ''),
(4, 'author.read.article', 'com_content', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_BY_USER_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_BY_USER_POINTS_DESC', 'article-read-user', '2016-02-18 13:12:27', NULL, 0, 1, 1, ''),
(5, 'apps.install', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_INSTALL_APPLICATIONS', 'COM_EASYSOCIAL_POINTS_INSTALL_APPLICATIONS_DESC', 'install-apps', '2016-02-18 13:12:47', NULL, 0, 5, 1, ''),
(6, 'apps.uninstall', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNINSTALL_APPLICATIONS', 'COM_EASYSOCIAL_POINTS_UNINSTALL_APPLICATIONS_DESC', 'uninstall-apps', '2016-02-18 13:12:47', NULL, 0, -5, 1, ''),
(7, 'badges.achieve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_BADGES_ACHIEVE', 'COM_EASYSOCIAL_POINTS_BADGES_ACHIEVE_DESC', 'badges-achieve', '2016-02-18 13:12:47', NULL, 0, 5, 1, ''),
(8, 'conversation.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION_DESC', 'conversation-starter', '2016-02-18 13:12:47', NULL, 0, 5, 1, ''),
(9, 'conversation.create.group', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION_GROUP', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION_GROUP_DESC', 'conversation-group-starter', '2016-02-18 13:12:47', NULL, 0, 10, 1, ''),
(10, 'conversation.reply', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REPLY_CONVERSATION', 'COM_EASYSOCIAL_POINTS_REPLY_CONVERSATION_DESC', 'conversation-reply', '2016-02-18 13:12:47', NULL, 0, 2, 1, ''),
(11, 'conversation.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_INVITE_TO_CONVERSATION', 'COM_EASYSOCIAL_POINTS_INVITE_TO_CONVERSATION_DESC', 'invite-user-conversation', '2016-02-18 13:12:47', NULL, 0, 5, 1, ''),
(12, 'conversation.read', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_READ_CONVERSATION', 'COM_EASYSOCIAL_POINTS_READ_CONVERSATION_DESC', 'conversation.read', '2016-02-18 13:12:47', NULL, 0, 1, 1, ''),
(13, 'events.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_CREATE_DESC', 'events-create', '2016-02-18 13:12:47', NULL, 0, 5, 1, ''),
(14, 'events.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_UPDATE', 'COM_EASYSOCIAL_POINTS_EVENTS_UPDATE_DESC', 'events-update', '2016-02-18 13:12:47', NULL, 0, 2, 1, ''),
(15, 'events.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_REMOVED', 'COM_EASYSOCIAL_POINTS_EVENTS_REMOVED_DESC', 'events-remove', '2016-02-18 13:12:47', NULL, 0, -5, 1, ''),
(16, 'events.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_INVITE', 'COM_EASYSOCIAL_POINTS_EVENTS_INVITE_DESC', 'events-invite', '2016-02-18 13:12:47', NULL, 0, 1, 1, ''),
(17, 'events.going', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_GOING', 'COM_EASYSOCIAL_POINTS_EVENTS_GOING_DESC', 'events-going', '2016-02-18 13:12:47', NULL, 0, 2, 1, ''),
(18, 'events.notgoing', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_NOTGOING', 'COM_EASYSOCIAL_POINTS_EVENTS_NOTGOING_DESC', 'events-notgoing', '2016-02-18 13:12:47', NULL, 0, -2, 1, ''),
(19, 'events.discussion.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_CREATE_DESC', 'events-discussion-create', '2016-02-18 13:12:47', NULL, 0, 1, 1, ''),
(20, 'events.discussion.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_DELETE_DESC', 'events-discussion-delete', '2016-02-18 13:12:47', NULL, 0, -1, 1, ''),
(21, 'events.discussion.answer', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_ANSWER', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_ANSWER_DESC', 'events-discussion-answer', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(22, 'events.discussion.reply', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_REPLY', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_REPLY_DESC', 'events-discussion-reply', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(23, 'events.news.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_CREATE_DESC', 'events-news-create', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(24, 'events.news.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_DELETE_DESC', 'events-news-delete', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(25, 'events.milestone.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_CREATE_DESC', 'events-milestone-create', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(26, 'events.milestone.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_DELETE_DESC', 'events-milestone-delete', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(27, 'events.task.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_CREATE_DESC', 'events-task-create', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(28, 'events.task.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_DELETE_DESC', 'events-task-delete', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(29, 'events.task.resolve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_RESOLVE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_RESOLVE_DESC', 'events-task-resolve', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(30, 'events.task.unresolve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_UNRESOLVE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_UNRESOLVE_DESC', 'events-task-unresolve', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(31, 'files.upload', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FILES_UPLOAD', 'COM_EASYSOCIAL_POINTS_FILES_UPLOAD_DESC', 'file-upload', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(32, 'files.download', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FILES_DOWNLOAD', 'COM_EASYSOCIAL_POINTS_FILES_DOWNLOAD_DESC', 'file-download', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(33, 'friends.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_ADD', 'COM_EASYSOCIAL_POINTS_FRIENDS_ADD_DESC', 'friends-add', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(34, 'friends.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_REMOVE', 'COM_EASYSOCIAL_POINTS_FRIENDS_REMOVE_DESC', 'friends-remove', '2016-02-18 13:12:48', NULL, 0, -5, 1, ''),
(35, 'friends.approve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_APPROVE', 'COM_EASYSOCIAL_POINTS_FRIENDS_APPROVE_DESC', 'friends-approve', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(36, 'friends.list.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_CREATE_FRIEND_LIST', 'COM_EASYSOCIAL_POINTS_CREATE_FRIEND_LIST_DESC', 'friends-list-create', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(37, 'friends.list.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_FRIEND_LIST', 'COM_EASYSOCIAL_POINTS_REMOVE_FRIEND_LIST_DESC', 'friends-list-delete', '2016-02-18 13:12:48', NULL, 0, -2, 1, ''),
(38, 'friends.list.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_ASSIGN_FRIEND_TO_LIST', 'COM_EASYSOCIAL_POINTS_ASSIGN_FRIEND_TO_LIST_DESC', 'friends-list-add', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(39, 'friends.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE_DESC', 'friends-invite', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(40, 'friends.registered', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE_REGISTERED', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE_REGISTERED_DESC', 'friends-invite-registered', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(41, 'groups.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_CREATE_GROUP', 'COM_EASYSOCIAL_POINTS_CREATE_GROUP_DESC', 'create-group', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(42, 'groups.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVED_GROUP', 'COM_EASYSOCIAL_POINTS_REMOVED_GROUP_DESC', 'removed-group', '2016-02-18 13:12:48', NULL, 0, -5, 1, ''),
(43, 'groups.join', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_JOIN_GROUP', 'COM_EASYSOCIAL_POINTS_JOIN_GROUP_DESC', 'join-group', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(44, 'groups.leave', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_LEAVE_GROUP', 'COM_EASYSOCIAL_POINTS_LEAVE_GROUP_DESC', 'leave-group', '2016-02-18 13:12:48', NULL, 0, -2, 1, ''),
(45, 'groups.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_INVITE_FRIENDS', 'COM_EASYSOCIAL_POINTS_GROUP_INVITE_FRIENDS_DESC', 'invite-friends-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(46, 'groups.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPDATE_GROUP', 'COM_EASYSOCIAL_POINTS_UPDATE_GROUP_DESC', 'updated-group', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(47, 'groups.discussion.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_DISCUSSION_DESC', 'create-discussion-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(48, 'groups.discussion.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_DISCUSSION_DESC', 'delete-discussion-group', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(49, 'groups.discussion.reply', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_REPLY_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_REPLY_DISCUSSION_DESC', 'reply-discussion-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(50, 'groups.discussion.answer', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_ANSWER_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_ANSWER_DISCUSSION_DESC', 'answer-discussion-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(51, 'groups.news.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_NEWS', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_NEWS_DESC', 'create-news-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(52, 'groups.news.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_NEWS', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_NEWS_DESC', 'delete-news-group', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(53, 'groups.milestone.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_MILESTONE', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_MILESTONE_DESC', 'create-milestone-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(54, 'groups.milestone.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_MILESTONE', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_MILESTONE_DESC', 'delete-milestone-group', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(55, 'groups.task.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_TASK', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_TASK_DESC', 'create-task-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(56, 'groups.task.resolve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_RESOLVE_TASK', 'COM_EASYSOCIAL_POINTS_GROUP_RESOLVE_TASK_DESC', 'resolve-task-group', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(57, 'groups.task.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_TASK', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_TASK_DESC', 'delete-task-group', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(58, 'photos.upload', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPLOAD_PHOTO', 'COM_EASYSOCIAL_POINTS_UPLOAD_PHOTO_DESC', 'upload-photo', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(59, 'photos.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO_DESC', 'remove-photo', '2016-02-18 13:12:48', NULL, 0, -5, 1, ''),
(60, 'photos.albums.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_CREATE_PHOTO_ALBUM', 'COM_EASYSOCIAL_POINTS_CREATE_PHOTO_ALBUM_DESC', 'create-photo-album', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(61, 'photos.albums.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO_ALBUM', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO_ALBUM_DESC', 'remove-photo-album', '2016-02-18 13:12:48', NULL, 0, -5, 1, ''),
(62, 'photos.tag', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_TAG_ON_PHOTO', 'COM_EASYSOCIAL_POINTS_TAG_ON_PHOTO_DESC', 'tag-photo', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(63, 'photos.untag', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_TAG_ON_PHOTO', 'COM_EASYSOCIAL_POINTS_REMOVE_TAG_ON_PHOTO_DESC', 'remove-tag-photo', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(64, 'photos.like', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_LIKE_PHOTO', 'COM_EASYSOCIAL_POINTS_LIKE_PHOTO_DESC', 'like-photo', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(65, 'photos.unlike', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNLIKE_PHOTO', 'COM_EASYSOCIAL_POINTS_UNLIKE_PHOTO_DESC', 'unlike-photo', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(66, 'photos.comment.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_PHOTO', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_PHOTO_DESC', 'comment-photo', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(67, 'photos.comment.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_PHOTO', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_PHOTO_DESC', 'comment-photo-removed', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(68, 'polls.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_ADD', 'COM_EASYSOCIAL_POINTS_POLLS_ADD_DESC', 'add-polls', '2016-02-18 13:12:48', NULL, 0, 3, 1, ''),
(69, 'polls.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_REMOVE', 'COM_EASYSOCIAL_POINTS_POLLS_REMOVE_DESC', 'remove-polls', '2016-02-18 13:12:48', NULL, 0, -3, 1, ''),
(70, 'polls.vote', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_VOTE', 'COM_EASYSOCIAL_POINTS_POLLS_VOTE_DESC', 'vote-polls', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(71, 'polls.unvote', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_UNVOTE', 'COM_EASYSOCIAL_POINTS_POLLS_UNVOTE_DESC', 'unvote-polls', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(72, 'post.like', 'com_easysocial', 'Like Posts', 'Earn points when someone likes your posts.', 'like-posts', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(73, 'post.unlike', 'com_easysocial', 'Unlike Posts', 'Demote points when someone unlike your posts.', 'unlike-posts', '2016-02-18 13:12:48', NULL, 0, -5, 1, ''),
(74, 'privacy.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_PRIVACY_UPDATE', 'COM_EASYSOCIAL_POINTS_PRIVACY_UPDATE_DESC', 'privacy-update', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(75, 'profile.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE_DESC', 'profile-update', '2016-02-18 13:12:48', NULL, 0, 15, 1, ''),
(76, 'profile.avatar.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE_AVATAR', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE_AVATAR_DESC', 'profile-avatar-update', '2016-02-18 13:12:48', NULL, 1, 5, 1, ''),
(77, 'profile.follow', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FOLLOW_USER', 'COM_EASYSOCIAL_POINTS_FOLLOW_USER_DESC', 'profile-follow', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(78, 'profile.unfollow', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNFOLLOW_USER', 'COM_EASYSOCIAL_POINTS_UNFOLLOW_USER_DESC', 'profile-unfollow', '2016-02-18 13:12:48', NULL, 0, -2, 1, ''),
(79, 'profile.followed', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_BEING_FOLLOWED', 'COM_EASYSOCIAL_POINTS_BEING_FOLLOWED_DESC', 'profile-followed', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(80, 'profile.unfollowed', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNFOLLOWED_BY_USER', 'COM_EASYSOCIAL_POINTS_UNFOLLOWED_BY_USER_DESC', 'profile-unfollowed', '2016-02-18 13:12:48', NULL, 0, -2, 1, ''),
(81, 'reports.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REPORTS_ADD', 'COM_EASYSOCIAL_POINTS_REPORTS_ADD_DESC', 'reports-create', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(82, 'reports.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REPORTS_REMOVED', 'COM_EASYSOCIAL_POINTS_REPORTS_REMOVED_DESC', 'reports-removed', '2016-02-18 13:12:48', NULL, 0, -5, 1, ''),
(83, 'story.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POST_NEW_UPDATE', 'COM_EASYSOCIAL_POINTS_POST_NEW_UPDATE_DESC', 'story-create', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(84, 'user.registration', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_USER_REGISTER', 'COM_EASYSOCIAL_POINTS_USER_REGISTER_DESC', 'registration', '2016-02-18 13:12:48', NULL, 0, 20, 1, ''),
(85, 'video.upload', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPLOAD_VIDEO', 'COM_EASYSOCIAL_POINTS_UPLOAD_VIDEO_DESC', 'upload-video', '2016-02-18 13:12:48', NULL, 0, 5, 1, ''),
(86, 'video.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_VIDEO', 'COM_EASYSOCIAL_POINTS_REMOVE_VIDEO_DESC', 'remove-video', '2016-02-18 13:12:48', NULL, 0, -5, 1, ''),
(87, 'video.like', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_LIKE_VIDEO', 'COM_EASYSOCIAL_POINTS_LIKE_VIDEO_DESC', 'like-video', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(88, 'video.unlike', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNLIKE_VIDEO', 'COM_EASYSOCIAL_POINTS_UNLIKE_VIDEO_DESC', 'unlike-video', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(89, 'video.comment.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_VIDEO', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_VIDEO_DESC', 'comment-video', '2016-02-18 13:12:48', NULL, 0, 1, 1, ''),
(90, 'video.comment.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_VIDEO', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_VIDEO_DESC', 'comment-video-removed', '2016-02-18 13:12:48', NULL, 0, -1, 1, ''),
(91, 'video.featured', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_VIDEO_FEATURED', 'COM_EASYSOCIAL_POINTS_VIDEO_FEATURED_DESC', 'video-featured', '2016-02-18 13:12:48', NULL, 0, 2, 1, ''),
(92, 'video.unfeatured', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_VIDEO_UNFEATURED', 'COM_EASYSOCIAL_POINTS_VIDEO_UNFEATURED_DESC', 'video-unfeatured', '2016-02-18 13:12:48', NULL, 0, -2, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_points_history`
--

CREATE TABLE IF NOT EXISTS `j_social_points_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Primary key for this table',
  `points_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL COMMENT 'The target user id',
  `points` int(11) NOT NULL COMMENT 'The number of points',
  `created` datetime NOT NULL COMMENT 'The date time value when the user earned the point.',
  `state` tinyint(3) NOT NULL COMMENT 'The publish state',
  `message` text NOT NULL COMMENT 'Any custom message set for this points assignment',
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `points_id` (`points_id`),
  KEY `idx_userid` (`user_id`),
  KEY `user_points` (`user_id`,`points`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_polls`
--

CREATE TABLE IF NOT EXISTS `j_social_polls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `title` text NOT NULL,
  `multiple` tinyint(1) DEFAULT '0',
  `locked` tinyint(1) DEFAULT '0',
  `cluster_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiry_date` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_element_id` (`element`,`uid`),
  KEY `idx_clusterid` (`cluster_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_polls_items`
--

CREATE TABLE IF NOT EXISTS `j_social_polls_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint(20) unsigned NOT NULL,
  `value` text NOT NULL,
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pollid` (`poll_id`),
  KEY `idx_polls` (`poll_id`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_polls_users`
--

CREATE TABLE IF NOT EXISTS `j_social_polls_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint(20) unsigned NOT NULL,
  `poll_itemid` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pollid` (`poll_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_pollitem` (`poll_itemid`),
  KEY `idx_poll_user` (`poll_id`,`user_id`),
  KEY `idx_poll_item_user` (`poll_id`,`poll_itemid`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL COMMENT 'object type e.g. photos, friends, albums, profile and etc',
  `rule` varchar(64) NOT NULL COMMENT 'rule type e.g. view_friends, view, search, comment, tag and etc',
  `value` int(11) DEFAULT '0',
  `options` text,
  `description` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `core` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type_rule` (`type`,`rule`),
  KEY `type_rule_privacy` (`type`,`rule`,`value`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `j_social_privacy`
--

INSERT INTO `j_social_privacy` (`id`, `type`, `rule`, `value`, `options`, `description`, `state`, `core`) VALUES
(1, 'apps', 'calendar', 0, '{"options":["public","member","friend","only_me","custom"]}', 'APPS_USER_CALENDAR_PRIVACY_FIELD_DESC', 1, 0),
(2, 'field', 'address', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_ADDRESS_PRIVACY_FIELD_DESC', 1, 0),
(3, 'field', 'birthday', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_BIRTHDAY_PRIVACY_FIELD_DESC', 1, 0),
(4, 'field', 'boolean', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_BOOLEAN_PRIVACY_FIELD_DESC', 1, 0),
(5, 'field', 'checkbox', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_CHECKBOX_PRIVACY_FIELD_DESC', 1, 0),
(6, 'field', 'country', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_COUNTRY_PRIVACY_FIELD_DESC', 1, 0),
(7, 'field', 'currency', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_CURRENCY_PRIVACY_FIELD_DESC', 1, 0),
(8, 'field', 'datetime', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_DATETIME_PRIVACY_FIELD_DESC', 1, 0),
(9, 'field', 'dropdown', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_DROPDOWN_PRIVACY_FIELD_DESC', 1, 0),
(10, 'field', 'email', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_EMAIL_PRIVACY_FIELD_DESC', 1, 0),
(11, 'field', 'file', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_FILE_PRIVACY_FIELD_DESC', 1, 0),
(12, 'field', 'gender', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_GENDER_PRIVACY_FIELD_DESC', 1, 0),
(13, 'field', 'headline', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_HEADLINE_PRIVACY_FIELD_DESC', 1, 0),
(14, 'field', 'joomla_email', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_JOOMLA_EMAIL_PRIVACY_FIELD_DESC', 1, 0),
(15, 'field', 'joomla_timezone', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_JOOMLA_TIMEZONE_PRIVACY_FIELD_DESC', 1, 0),
(16, 'field', 'multidropdown', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_MULTIDROPDOWN_PRIVACY_FIELD_DESC', 1, 0),
(17, 'field', 'multilist', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_MULTILIST_PRIVACY_FIELD_DESC', 1, 0),
(18, 'field', 'multitextbox', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_MULTITEXTBOX_PRIVACY_FIELD_DESC', 1, 0),
(19, 'field', 'relationship', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_RELATIONSHIP_PRIVACY_FIELD_DESC', 1, 0),
(20, 'field', 'textarea', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_TEXTAREA_PRIVACY_FIELD_DESC', 1, 0),
(21, 'field', 'textbox', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_TEXTBOX_PRIVACY_FIELD_DESC', 1, 0),
(22, 'field', 'url', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_URL_PRIVACY_FIELD_DESC', 1, 0),
(23, 'achievements', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_ACHIEVEMENTS_VIEW', 1, 1),
(24, 'albums', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_ALBUMS_VIEW', 1, 1),
(25, 'core', 'view', 0, '{"options":["public","member","friend","only_me"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_CORE_VIEW', 1, 1),
(26, 'followers', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_FOLLOWERS_VIEW', 1, 1),
(27, 'friends', 'view', 0, '{"options":["public","member","friends_of_friend","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_FRIENDS_VIEW', 1, 1),
(28, 'friends', 'request', 10, '{"options":["public","member","friends_of_friend"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_FRIENDS_REQUEST', 1, 1),
(29, 'photos', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_VIEW', 1, 1),
(30, 'photos', 'tagme', 30, '{"options":["friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_TAGME', 1, 1),
(31, 'photos', 'tag', 30, '{"options":["friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_TAG', 1, 1),
(32, 'polls', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_POLLS_VIEW', 1, 1),
(33, 'polls', 'vote', 10, '{"options":["member","friend","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_POLLS_VOTE', 1, 1),
(34, 'profiles', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_VIEW', 1, 1),
(35, 'profiles', 'search', 0, '{"options":["public","member","friends_of_friend","friend","only_me"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_SEARCH', 1, 1),
(36, 'profiles', 'post.status', 10, '{"options":["public","member","friend","only_me"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_POST_STATUS', 1, 1),
(37, 'profiles', 'post.message', 10, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_POST_MESSAGE', 1, 1),
(38, 'story', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_STORY_VIEW', 1, 1),
(39, 'story', 'post.comment', 10, '{"options":["member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_STORY_POST_COMMENT', 1, 1),
(40, 'videos', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_VIEW', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy_customize`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy_customize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'id from user or profile or item',
  `utype` varchar(64) NOT NULL COMMENT 'user or profile or item',
  `user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_type` (`uid`,`utype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy_items`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `privacy_id` int(11) NOT NULL COMMENT 'key to social_privacy.id',
  `user_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'object id e.g streamid, activityid and etc',
  `type` varchar(64) NOT NULL COMMENT 'object type e.g. stream, activity and etc',
  `value` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `privacy_id` (`privacy_id`),
  KEY `user_privacy_item` (`user_id`,`uid`,`type`),
  KEY `idx_uid_type` (`uid`,`type`),
  KEY `idx_user_type` (`user_id`,`type`),
  KEY `idx_value_user` (`value`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy_map`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `privacy_id` int(11) NOT NULL COMMENT 'key to social_privacy.id',
  `uid` int(11) NOT NULL COMMENT 'userid or profileid',
  `utype` varchar(64) NOT NULL COMMENT 'user or profile',
  `value` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `privacy_id` (`privacy_id`),
  KEY `uid_type` (`uid`,`utype`),
  KEY `uid_type_value` (`uid`,`utype`,`value`),
  KEY `idx_privacy_uid_type` (`privacy_id`,`uid`,`utype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_profiles`
--

CREATE TABLE IF NOT EXISTS `j_social_profiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `gid` text NOT NULL,
  `default` tinyint(4) NOT NULL,
  `default_avatar` int(11) DEFAULT NULL COMMENT 'If this field contains an id, it''s from the default avatar, otherwise use system''s default avatar.',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `state` tinyint(4) NOT NULL,
  `params` text NOT NULL,
  `registration` tinyint(4) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `community_access` tinyint(3) NOT NULL DEFAULT '1',
  `apps` text NOT NULL,
  `site_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `profile_esad` (`community_access`),
  KEY `idx_profile_access` (`id`,`community_access`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_social_profiles`
--

INSERT INTO `j_social_profiles` (`id`, `title`, `alias`, `description`, `gid`, `default`, `default_avatar`, `created`, `modified`, `state`, `params`, `registration`, `ordering`, `community_access`, `apps`, `site_id`) VALUES
(1, 'Registered Users', 'registered-users', 'This is the default profile that was created in the site.', '["2"]', 1, 0, '2016-02-18 13:12:49', '2016-02-18 13:12:49', 1, '{"delete_account":0,"theme":"","registration":"approvals"}', 1, 1, 1, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_profiles_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_profiles_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_profile_users` (`profile_id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `j_social_profiles_maps`
--

INSERT INTO `j_social_profiles_maps` (`id`, `profile_id`, `user_id`, `state`, `created`) VALUES
(1, 1, 953, 1, '2016-02-18 13:13:14'),
(2, 1, 951, 1, '2016-02-18 13:13:14'),
(3, 1, 952, 1, '2016-02-18 13:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_regions`
--

CREATE TABLE IF NOT EXISTS `j_social_regions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(64) NOT NULL,
  `parent_uid` bigint(20) NOT NULL,
  `parent_type` varchar(255) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `site_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_registrations`
--

CREATE TABLE IF NOT EXISTS `j_social_registrations` (
  `session_id` varchar(200) NOT NULL,
  `profile_id` bigint(20) NOT NULL,
  `created` datetime NOT NULL,
  `values` text NOT NULL,
  `step` bigint(20) NOT NULL,
  `step_access` text NOT NULL,
  `errors` text NOT NULL,
  UNIQUE KEY `session_id` (`session_id`),
  KEY `profile_id` (`profile_id`),
  KEY `step` (`step`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_relationship_status`
--

CREATE TABLE IF NOT EXISTS `j_social_relationship_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor` bigint(20) NOT NULL,
  `target` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `relation_type` (`type`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_reports`
--

CREATE TABLE IF NOT EXISTS `j_social_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `extension` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_rss`
--

CREATE TABLE IF NOT EXISTS `j_social_rss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `url` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`),
  KEY `uid` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_search_filter`
--

CREATE TABLE IF NOT EXISTS `j_social_search_filter` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `filter` text NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sitewide` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_searchfilter_element_id` (`element`,`uid`),
  KEY `idx_searchfilter_owner` (`element`,`uid`,`created_by`),
  KEY `idx_searchfilter_alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_shares`
--

CREATE TABLE IF NOT EXISTS `j_social_shares` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `element` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shares_element` (`uid`,`element`),
  KEY `shares_element_user` (`uid`,`element`,`user_id`),
  KEY `shares_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_step_sessions`
--

CREATE TABLE IF NOT EXISTS `j_social_step_sessions` (
  `session_id` varchar(200) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `values` text NOT NULL,
  `step` bigint(20) NOT NULL,
  `step_access` text NOT NULL,
  `errors` text NOT NULL,
  UNIQUE KEY `session_id` (`session_id`),
  KEY `profile_id` (`uid`),
  KEY `step` (`step`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_storage_log`
--

CREATE TABLE IF NOT EXISTS `j_social_storage_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` int(11) NOT NULL,
  `object_type` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream`
--

CREATE TABLE IF NOT EXISTS `j_social_stream` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint(20) unsigned NOT NULL,
  `alias` varchar(255) DEFAULT '',
  `actor_type` varchar(64) DEFAULT 'user',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edited` datetime DEFAULT '0000-00-00 00:00:00',
  `title` text,
  `content` text,
  `context_type` varchar(64) DEFAULT '',
  `verb` varchar(64) DEFAULT '',
  `stream_type` varchar(15) DEFAULT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `target_id` bigint(20) NOT NULL,
  `location_id` int(11) NOT NULL,
  `mood_id` int(11) NOT NULL,
  `with` text NOT NULL,
  `ispublic` tinyint(3) NOT NULL DEFAULT '0',
  `cluster_id` int(11) DEFAULT '0',
  `cluster_type` varchar(64) DEFAULT NULL,
  `cluster_access` tinyint(3) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `privacy_id` int(11) DEFAULT NULL,
  `access` int(11) NOT NULL DEFAULT '0',
  `custom_access` text,
  `last_action` varchar(255) DEFAULT NULL,
  `last_userid` bigint(20) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `stream_actor` (`actor_id`),
  KEY `stream_created` (`created`),
  KEY `stream_modified` (`modified`),
  KEY `stream_alias` (`alias`),
  KEY `stream_source` (`actor_type`),
  KEY `idx_stream_context_type` (`context_type`),
  KEY `idx_stream_target` (`target_id`),
  KEY `idx_actor_modified` (`actor_id`,`modified`),
  KEY `idx_target_context_modified` (`target_id`,`context_type`,`modified`),
  KEY `idx_sitewide_modified` (`sitewide`,`modified`),
  KEY `idx_ispublic` (`ispublic`,`modified`),
  KEY `idx_clusterid` (`cluster_id`),
  KEY `idx_cluster_items` (`cluster_id`,`cluster_type`,`modified`),
  KEY `idx_cluster_access` (`cluster_id`,`cluster_access`),
  KEY `idx_access` (`access`),
  KEY `idx_custom_access` (`access`,`custom_access`(255)),
  KEY `idx_stream_total_cluster` (`cluster_id`,`cluster_access`,`context_type`,`id`,`actor_id`),
  KEY `idx_stream_total_user` (`cluster_id`,`access`,`actor_id`,`context_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_social_stream`
--

INSERT INTO `j_social_stream` (`id`, `actor_id`, `alias`, `actor_type`, `created`, `modified`, `edited`, `title`, `content`, `context_type`, `verb`, `stream_type`, `sitewide`, `target_id`, `location_id`, `mood_id`, `with`, `ispublic`, `cluster_id`, `cluster_type`, `cluster_access`, `params`, `state`, `privacy_id`, `access`, `custom_access`, `last_action`, `last_userid`) VALUES
(1, 951, '', 'user', '2016-02-18 13:13:26', '2016-02-18 13:13:26', '0000-00-00 00:00:00', NULL, NULL, 'users', 'login', NULL, 0, 0, 0, 0, '', 0, 0, NULL, 0, NULL, 1, 25, 0, '', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_assets`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_filter`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_filter` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `utype` varchar(255) DEFAULT 'user',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `streamfilter_uidtype` (`uid`,`utype`),
  KEY `streamfilter_alias` (`alias`),
  KEY `streamfilter_cluster_user` (`uid`,`utype`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_filter_item`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_filter_item` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filter_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `filteritem_fid` (`filter_id`),
  KEY `filteritem_type` (`type`),
  KEY `filteritem_fidtype` (`filter_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_hide`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_hide` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `context` varchar(255) DEFAULT NULL,
  `actor_id` bigint(20) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `stream_hide_user` (`user_id`),
  KEY `stream_hide_uid` (`uid`),
  KEY `stream_hide_actorid` (`actor_id`),
  KEY `stream_hide_user_uid` (`user_id`,`uid`),
  KEY `idx_stream_hide_context` (`context`,`user_id`,`uid`,`actor_id`),
  KEY `idx_stream_hide_actor` (`actor_id`,`user_id`,`uid`,`context`),
  KEY `idx_stream_hide_uid` (`uid`,`user_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_history`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_history` (
  `id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned NOT NULL,
  `alias` varchar(255) DEFAULT '',
  `actor_type` varchar(64) DEFAULT 'user',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edited` datetime DEFAULT '0000-00-00 00:00:00',
  `title` text,
  `content` text,
  `context_type` varchar(64) DEFAULT '',
  `verb` varchar(64) DEFAULT '',
  `stream_type` varchar(15) DEFAULT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `target_id` bigint(20) NOT NULL,
  `location_id` int(11) NOT NULL,
  `mood_id` int(11) NOT NULL,
  `with` text NOT NULL,
  `ispublic` tinyint(3) NOT NULL DEFAULT '0',
  `cluster_id` int(11) DEFAULT '0',
  `cluster_type` varchar(64) DEFAULT NULL,
  `cluster_access` tinyint(3) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `privacy_id` int(11) DEFAULT NULL,
  `access` int(11) NOT NULL DEFAULT '0',
  `custom_access` text,
  `last_action` varchar(255) DEFAULT NULL,
  `last_userid` bigint(20) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `stream_history_created` (`created`),
  KEY `stream_history_modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_item`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_item` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint(20) unsigned NOT NULL,
  `actor_type` varchar(255) DEFAULT 'people',
  `context_type` varchar(64) DEFAULT '',
  `context_id` bigint(20) unsigned DEFAULT '0',
  `verb` varchar(64) DEFAULT '',
  `target_id` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` bigint(20) unsigned NOT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `activity_actor` (`actor_id`),
  KEY `activity_created` (`created`),
  KEY `activity_context` (`context_type`),
  KEY `activity_context_id` (`context_id`),
  KEY `idx_context_verb` (`context_type`,`verb`),
  KEY `idx_uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_social_stream_item`
--

INSERT INTO `j_social_stream_item` (`id`, `actor_id`, `actor_type`, `context_type`, `context_id`, `verb`, `target_id`, `created`, `uid`, `sitewide`, `params`, `state`) VALUES
(1, 951, 'user', 'users', 951, 'login', 0, '2016-02-18 13:13:26', 1, 0, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_item_history`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_item_history` (
  `id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned NOT NULL,
  `actor_type` varchar(255) DEFAULT 'people',
  `context_type` varchar(64) DEFAULT '',
  `context_id` bigint(20) unsigned DEFAULT '0',
  `verb` varchar(64) DEFAULT '',
  `target_id` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` bigint(20) unsigned NOT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_history_uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_sticky`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_sticky` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` bigint(20) unsigned NOT NULL,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_streamid` (`stream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_tags`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `utype` varchar(255) DEFAULT 'user',
  `with` tinyint(3) unsigned DEFAULT '0',
  `offset` int(11) DEFAULT '0',
  `length` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `streamtags_streamid` (`stream_id`),
  KEY `streamtags_uidtype` (`uid`,`utype`),
  KEY `streamtags_uidoffset` (`stream_id`,`offset`),
  KEY `streamtags_title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_subscriptions`
--

CREATE TABLE IF NOT EXISTS `j_social_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'object id e.g userid, groupid, streamid and etc',
  `type` varchar(64) NOT NULL COMMENT 'subscription type e.g. user, group, stream and etc',
  `user_id` int(11) DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_type` (`uid`,`type`),
  KEY `uid_type_user` (`uid`,`type`,`user_id`),
  KEY `uid_type_email` (`uid`,`type`),
  KEY `idx_uid` (`uid`),
  KEY `idx_type_userid` (`type`,`user_id`),
  KEY `idx_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tags`
--

CREATE TABLE IF NOT EXISTS `j_social_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_type` varchar(255) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `creator_type` varchar(255) NOT NULL,
  `offset` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_targets` (`target_id`,`target_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tasks`
--

CREATE TABLE IF NOT EXISTS `j_social_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `milestone_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `due` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`),
  KEY `milestone_id` (`milestone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tasks_milestones`
--

CREATE TABLE IF NOT EXISTS `j_social_tasks_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `due` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_themes`
--

CREATE TABLE IF NOT EXISTS `j_social_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `element` (`element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tmp`
--

CREATE TABLE IF NOT EXISTS `j_social_tmp` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `key` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `expired` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `node_id` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_uploader`
--

CREATE TABLE IF NOT EXISTS `j_social_uploader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `name` text NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_users`
--

CREATE TABLE IF NOT EXISTS `j_social_users` (
  `user_id` bigint(20) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `params` text NOT NULL,
  `connections` int(11) NOT NULL,
  `permalink` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'joomla',
  `auth` varchar(255) NOT NULL,
  `completed_fields` int(11) NOT NULL DEFAULT '0',
  `reminder_sent` tinyint(1) DEFAULT '0',
  `require_reset` tinyint(1) DEFAULT '0',
  `block_date` datetime DEFAULT '0000-00-00 00:00:00',
  `block_period` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `state` (`state`),
  KEY `alias` (`alias`),
  KEY `connections` (`connections`),
  KEY `permalink` (`permalink`),
  KEY `idx_types` (`user_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_social_users`
--

INSERT INTO `j_social_users` (`user_id`, `alias`, `state`, `params`, `connections`, `permalink`, `type`, `auth`, `completed_fields`, `reminder_sent`, `require_reset`, `block_date`, `block_period`) VALUES
(951, '', 1, '', 0, '', 'joomla', '', 0, 0, 0, '0000-00-00 00:00:00', 0),
(952, '', 1, '', 0, '', 'joomla', '', 0, 0, 0, '0000-00-00 00:00:00', 0),
(953, '', 1, '', 0, '', 'joomla', '', 0, 0, 0, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_videos`
--

CREATE TABLE IF NOT EXISTS `j_social_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key for this table',
  `title` varchar(255) NOT NULL COMMENT 'Title of the video',
  `description` text NOT NULL COMMENT 'The description of the video',
  `user_id` int(11) NOT NULL COMMENT 'The user id that created this video',
  `uid` int(11) NOT NULL COMMENT 'This video may belong to another node other than the user.',
  `type` varchar(255) NOT NULL COMMENT 'This video may belong to another node other than the user.',
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `featured` tinyint(3) NOT NULL,
  `category_id` int(11) NOT NULL,
  `hits` int(11) NOT NULL COMMENT 'Total hits received for this video',
  `duration` varchar(255) NOT NULL COMMENT 'Duration of the video',
  `size` int(11) NOT NULL COMMENT 'The file size of the video',
  `params` text NOT NULL COMMENT 'Store video params',
  `storage` varchar(255) NOT NULL COMMENT 'Storage for videos',
  `path` text NOT NULL,
  `original` text NOT NULL,
  `file_title` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `thumbnail` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`,`user_id`,`state`,`featured`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_videos_categories`
--

CREATE TABLE IF NOT EXISTS `j_social_videos_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `default` tinyint(3) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL COMMENT 'The user id that created this category',
  `created` datetime NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `j_social_videos_categories`
--

INSERT INTO `j_social_videos_categories` (`id`, `title`, `alias`, `description`, `state`, `default`, `user_id`, `created`, `ordering`) VALUES
(1, 'General', 'general', 'General videos', 1, 1, 951, '2016-02-18 13:12:52', 0),
(2, 'Music', 'music', 'Music videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(3, 'Sports', 'sports', 'Sports videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(4, 'News', 'news', 'News videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(5, 'Gaming', 'gaming', 'Gaming videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(6, 'Movies', 'movies', 'Movies videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(7, 'Documentary', 'documentary', 'Documentary videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(8, 'Fashion', 'fashion', 'Fashion videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(9, 'Travel', 'travel', 'Travel videos', 1, 0, 951, '2016-02-18 13:12:52', 0),
(10, 'Technology', 'technology', 'Technology videos', 1, 0, 951, '2016-02-18 13:12:52', 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_videos_categories_access`
--

CREATE TABLE IF NOT EXISTS `j_social_videos_categories_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`profile_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_tags`
--

CREATE TABLE IF NOT EXISTS `j_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `tag_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_tags`
--

INSERT INTO `j_tags` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `created_by_alias`, `modified_user_id`, `modified_time`, `images`, `urls`, `hits`, `language`, `version`, `publish_up`, `publish_down`) VALUES
(1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 42, '2011-01-01 00:00:01', '', 0, '0000-00-00 00:00:00', '', '', 0, '*', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `j_template_styles`
--

CREATE TABLE IF NOT EXISTS `j_template_styles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(50) NOT NULL DEFAULT '',
  `client_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `home` char(7) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_template` (`template`),
  KEY `idx_home` (`home`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `j_template_styles`
--

INSERT INTO `j_template_styles` (`id`, `template`, `client_id`, `home`, `title`, `params`) VALUES
(4, 'beez3', 0, '0', 'Beez3 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\\/joomla_black.png","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","templatecolor":"personal","html5":"0"}'),
(5, 'hathor', 1, '0', 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}'),
(7, 'protostar', 0, '0', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'),
(8, 'isis', 1, '1', 'isis - Default', '{"templateColor":"","logoFile":""}'),
(10, 'jf_connecto', 0, '1', 'JF Connecto - Default', '{"presets":"Blue","master":"true","current_id":"true","template_full_name":"jf_connecto","grid_system":"12","template_prefix":"jf_connecto-","cookie_time":"31536000","override_set":"2.5","name":"Preset1","copy_lang_files_if_diff":"1","viewswitcher-priority":"1","logo-priority":"2","copyright-priority":"3","styledeclaration-priority":"4","fontsizer-priority":"5","date-priority":"7","totop-priority":"8","systemmessages-priority":"9","morearticles-priority":"12","smartload-priority":"13","pagesuffix-priority":"14","resetsettings-priority":"15","analytics-priority":"16","dropdownmenu-priority":"18","jstools-priority":"21","moduleoverlays-priority":"22","rtl-priority":"23","splitmenu-priority":"24","webfonts-priority":"27","styledeclaration-enabled":"1","date":{"enabled":"0","position":"top-d","clientside":"0","formats":"%A, %B %d, %Y"},"fontsizer":{"enabled":"0","position":"feature-b"},"branding":{"enabled":"1","position":"copyright-a"},"copyright":{"enabled":"1","position":"copyright-a","text":"The Joomla!\\u2122 name is used under a limited license from Open Source Matters in the United States and other countries.<br>JoomForest.com is not affiliated with or endorsed by Open Source Matters or the Joomla! Project.<br>Copyright \\u00a9 2011-2015 JoomForest.com. All Rights Reserved.","layout":"3,3,3,3","showall":"0","showmax":"6"},"systemmessages":{"enabled":"1","position":"showcase-a"},"resetsettings":{"enabled":"0","position":"copyright-d","text":"Reset Settings"},"analytics":{"enabled":"0","code":"","position":"analytics"},"menu":{"enabled":"1","type":"dropdownmenu","dropdownmenu":{"theme":"gantry-dropdown","limit_levels":"0","startLevel":"0","showAllChildren":"1","class_sfx":"top","cache":"0","menutype":"mainmenu","position":"header-b","responsive-menu":"selectbox","enable-current-id":"0","module_cache":"1"},"splitmenu":{"mainmenu-limit_levels":"1","mainmenu-startLevel":"0","mainmenu-endLevel":"0","mainmenu-class_sfx":"top","submenu-limit_levels":"1","submenu-startLevel":"1","submenu-endLevel":"9","cache":"0","menutype":"mainmenu","theme":"gantry-splitmenu","mainmenu-position":"header-b","submenu-position":"sidebar-a","submenu-title":"1","submenu-class_sfx":"","submenu-module_sfx":"","responsive-menu":"panel","roknavmenu_dropdown_enable-current-id":"0","module_cache":"1"}},"top":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"header":{"layout":"4,8","showall":"0","showmax":"6"},"showcase":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"feature":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"utility":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"maintop":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"mainbodyPosition":"6,3,3","mainbottom":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"extension":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"bottom":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"footer":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"layout-mode":"responsive","component-enabled":"0","mainbody-enabled":"1","rtl-enabled":"1","pagesuffix-enabled":"0","selectivizr-enabled":"0","less":{"compression":"1","compilewait":"2","debugheader":"0"},"jf_jquery_easing":"1","jf_preloader":{"enabled":"1","position":"jf-preloader","jf_preloader_bg":"#ffffff","jf_preloader_type":"css3","image":"","jf_preloader_color":"#37AFCD"},"jf_scrolltop":{"enabled":"1","position":"jf-scrolltop"},"jf_stickyheader":{"enabled":"1","jf_stickyheader_Style":"light","jf_stickyheader_Color":"#ffffff"},"jf_prettyphoto":{"enabled":"1","jf_pp_theme":"pp_default","jf_pp_bgopacity":"0.9","jf_pp_slidespeed":"5000","jf_pp_share":"on"},"jf_animate":{"enabled":"1","jf_animate_all":"0","jf_animate_all_sheet":"\\/\\/cdnjs.cloudflare.com\\/ajax\\/libs\\/animate.css\\/3.2.0\\/animate.min.css","jf_animate_custom":"1","jf_animate_1":"0","jf_animate_1_type":"bounce","jf_animate_1_delay":"","jf_animate_1_tags":"","jf_animate_2":"0","jf_animate_2_type":"flash","jf_animate_2_delay":"","jf_animate_2_tags":"","jf_animate_3":"0","jf_animate_3_type":"pulse","jf_animate_3_delay":"","jf_animate_3_tags":"","jf_animate_4":"0","jf_animate_4_type":"rubberBand","jf_animate_4_delay":"","jf_animate_4_tags":"","jf_animate_5":"0","jf_animate_5_type":"shake","jf_animate_5_delay":"","jf_animate_5_tags":"","jf_animate_6":"0","jf_animate_6_type":"swing","jf_animate_6_delay":"","jf_animate_6_tags":"","jf_animate_7":"0","jf_animate_7_type":"tada","jf_animate_7_delay":"","jf_animate_7_tags":""},"jf_canvastext":{"enabled":"0","jf_canvastext_words":"\\"Animated\\",\\"Canvas\\",\\"Text\\"","jf_canvastext_height":"170","jf_canvastext_padding":"margin:100px 0","jf_canvastext_fontsize":"100","jf_canvastext_fontfamily":"\\\\\\"Open Sans\\\\\\", sans-serif","jf_canvastext_color":"#37AFCD"},"logo":{"enabled":"1","position":"header-a","type":"gantry","custom":{"image":""}},"jf_font":{"enabled":"1","jf_font_tags":"html,body","jf_font_family":"Helvetica,Arial,Sans-Serif"},"jf_webfont":{"enabled":"1","jf_webfont_stylesheet":"\\/\\/fonts.googleapis.com\\/css?family=Open+Sans:400,600,700","jf_webfont_tags":"h1,h2,h3,h4,h5,h6,.jf_typo_title,.jf_typo_code_toggle .trigger,.jf_typo_dropcap,.jf_typo_button,#jf_pricing_table,.default-tipsy-inner,.item-page .tags a,.component-content .pagenav li a,.readon,.readmore,button.validate,#member-profile a,#member-registration a,.formelm-buttons button,.btn-primary,.component-content .pagination,.category-list,select,.component-content #searchForm .inputbox,.component-content .search fieldset legend,label,.component-content .searchintro,.component-content .search-results .result-title,.component-content .search-results .result-category .small,.btn,.component-content .login .control-group input,.component-content .login+div,.component-content #users-profile-core legend,.component-content #users-profile-custom legend,.component-content .profile-edit legend,.component-content .registration legend,.component-content .profile-edit,.component-content .registration,.component-content .remind,.component-content .reset,.component-content .tag-category table.category,.rt-error-content,#rt-offline-body,#rt-offline-body input,#rt-breadcrumbs .breadcrumb a,#rt-breadcrumbs .breadcrumb span,#rt-main ul.menu li a,#login-form,.module-content .search,.gf-menu .item,.gf-menu .item.icon [class^=''icon-''],.gf-menu .item.icon [class*= '' icon-''],.gf-menu .item.icon [class^=''fa-''],.gf-menu .item.icon [class*= '' fa-''],.component-content .contact,#jf_styleswitcher,.jf_typo_accord .trigger,.jf_typo_toggle .trigger,#jf_login,.tooltip,.jf_image_block,#rt-footer ul.menu li a,#rt-footer ul.menu li span,#rt-footer-surround #rt-copyright .rt-block","jf_webfont_family":"''Open Sans'',sans-serif"},"jf_styleswitcher":{"enabled":"1","position":"jf-styleswitcher","jf_styleswitcher_1_color":"#37AFCD","jf_styleswitcher_1":"?presets=Blue","jf_styleswitcher_2_color":"#7A7AC3","jf_styleswitcher_2":"?presets=SlateBlue","jf_styleswitcher_3_color":"#27AE60","jf_styleswitcher_3":"?presets=Green","jf_styleswitcher_4_color":"#F1C40F","jf_styleswitcher_4":"?presets=Yellow","jf_styleswitcher_5_color":"#7F8C8D","jf_styleswitcher_5":"?presets=Grey","jf_styleswitcher_6_color":"#999999","jf_styleswitcher_6":"","jf_styleswitcher_7_color":"#999999","jf_styleswitcher_7":"","jf_styleswitcher_8_color":"#999999","jf_styleswitcher_8":"","jf_styleswitcher_9_color":"#999999","jf_styleswitcher_9":"","jf_styleswitcher_10_color":"#999999","jf_styleswitcher_10":""},"jf_colors_bg":"#ffffff","jf_colors_header":"#37AFCD","jf_colors_slideshow":"#37AFCD","jf_colors_breadcrumb":"#F8F8F8","jf_colors_breadcrumb_border":"#eeeeee","jf_colors_footer_bg":"#ffffff","jf_colors_footer_text":"#555555","jf_colors_footer_link":"#37AFCD","jf_colors_main":"#37AFCD","jf_eb_colors_override":"0","jf_eb_UC_MainColor":"#37AFCD","jf_eb_UC_ToolbarTopGR":"#37AFCD","jf_eb_UC_ToolbarBottomGR":"#37AFCD","jf_es_colors_override":"0","jf_es_UC_MainColor":"#37AFCD","jf_es_UC_ToolbarTopGR":"#37AFCD","jf_es_UC_ToolbarBottomGR":"#37AFCD","jf_es_UC_ButtonRed":"#dd0000","jf_es_UC_ButtonGreen":"#00B856","jf_cb_colors_override":"0","jf_cb_UC_MainColor":"#37AFCD","jf_cb_UC_MenubarColor":"#37AFCD","jf_cb_UC_TemplateBody":"#ffffff","jf_typo_01_core":"1","jf_fontawesome":{"enabled":"1","jf_fontawesome_cdn":"\\/\\/netdna.bootstrapcdn.com\\/font-awesome\\/4.2.0\\/css\\/font-awesome.min.css"},"jf_typo_02_accordions":"1","jf_typo_03_toggles":"1","jf_typo_04_pricing_tables":"1","jf_typo_05_image_video_frames":"1","jf_typo_06_social_icons":"1","jf_typo_bs_tooltips_31":"1","jf_typo_bootstrap":"0"}');

-- --------------------------------------------------------

--
-- Table structure for table `j_ucm_base`
--

CREATE TABLE IF NOT EXISTS `j_ucm_base` (
  `ucm_id` int(10) unsigned NOT NULL,
  `ucm_item_id` int(10) NOT NULL,
  `ucm_type_id` int(11) NOT NULL,
  `ucm_language_id` int(11) NOT NULL,
  PRIMARY KEY (`ucm_id`),
  KEY `idx_ucm_item_id` (`ucm_item_id`),
  KEY `idx_ucm_type_id` (`ucm_type_id`),
  KEY `idx_ucm_language_id` (`ucm_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_ucm_content`
--

CREATE TABLE IF NOT EXISTS `j_ucm_content` (
  `core_content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `core_type_alias` varchar(255) NOT NULL DEFAULT '' COMMENT 'FK to the content types table',
  `core_title` varchar(255) NOT NULL,
  `core_alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `core_body` mediumtext NOT NULL,
  `core_state` tinyint(1) NOT NULL DEFAULT '0',
  `core_checked_out_time` varchar(255) NOT NULL DEFAULT '',
  `core_checked_out_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `core_access` int(10) unsigned NOT NULL DEFAULT '0',
  `core_params` text NOT NULL,
  `core_featured` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `core_metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `core_created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `core_created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `core_created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `core_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Most recent user that modified',
  `core_modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `core_language` char(7) NOT NULL,
  `core_publish_up` datetime NOT NULL,
  `core_publish_down` datetime NOT NULL,
  `core_content_item_id` int(10) unsigned DEFAULT NULL COMMENT 'ID from the individual type table',
  `asset_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the j_assets table.',
  `core_images` text NOT NULL,
  `core_urls` text NOT NULL,
  `core_hits` int(10) unsigned NOT NULL DEFAULT '0',
  `core_version` int(10) unsigned NOT NULL DEFAULT '1',
  `core_ordering` int(11) NOT NULL DEFAULT '0',
  `core_metakey` text NOT NULL,
  `core_metadesc` text NOT NULL,
  `core_catid` int(10) unsigned NOT NULL DEFAULT '0',
  `core_xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `core_type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`core_content_id`),
  KEY `tag_idx` (`core_state`,`core_access`),
  KEY `idx_access` (`core_access`),
  KEY `idx_alias` (`core_alias`),
  KEY `idx_language` (`core_language`),
  KEY `idx_title` (`core_title`),
  KEY `idx_modified_time` (`core_modified_time`),
  KEY `idx_created_time` (`core_created_time`),
  KEY `idx_content_type` (`core_type_alias`),
  KEY `idx_core_modified_user_id` (`core_modified_user_id`),
  KEY `idx_core_checked_out_user_id` (`core_checked_out_user_id`),
  KEY `idx_core_created_user_id` (`core_created_user_id`),
  KEY `idx_core_type_id` (`core_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains core content data in name spaced fields' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_ucm_history`
--

CREATE TABLE IF NOT EXISTS `j_ucm_history` (
  `version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ucm_item_id` int(10) unsigned NOT NULL,
  `ucm_type_id` int(10) unsigned NOT NULL,
  `version_note` varchar(255) NOT NULL DEFAULT '' COMMENT 'Optional version name',
  `save_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `editor_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `character_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of characters in this version.',
  `sha1_hash` varchar(50) NOT NULL DEFAULT '' COMMENT 'SHA1 hash of the version_data column.',
  `version_data` mediumtext NOT NULL COMMENT 'json-encoded string of version data',
  `keep_forever` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=auto delete; 1=keep',
  PRIMARY KEY (`version_id`),
  KEY `idx_ucm_item_id` (`ucm_type_id`,`ucm_item_id`),
  KEY `idx_save_date` (`save_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_update_sites`
--

CREATE TABLE IF NOT EXISTS `j_update_sites` (
  `update_site_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `location` text NOT NULL,
  `enabled` int(11) DEFAULT '0',
  `last_check_timestamp` bigint(20) DEFAULT '0',
  `extra_query` varchar(1000) DEFAULT '',
  PRIMARY KEY (`update_site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Update Sites' AUTO_INCREMENT=7 ;

--
-- Dumping data for table `j_update_sites`
--

INSERT INTO `j_update_sites` (`update_site_id`, `name`, `type`, `location`, `enabled`, `last_check_timestamp`, `extra_query`) VALUES
(1, 'Joomla! Core', 'collection', 'http://update.joomla.org/core/list.xml', 1, 1455919273, ''),
(2, 'Joomla! Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', 1, 1455919273, ''),
(3, 'Accredited Joomla! Translations', 'collection', 'http://update.joomla.org/language/translationlist_3.xml', 1, 0, ''),
(4, 'Joomla! Update Component Update Site', 'extension', 'http://update.joomla.org/core/extensions/com_joomlaupdate.xml', 1, 0, ''),
(5, 'Gantry Framework Update Site', 'extension', 'http://www.gantry-framework.org/updates/joomla16/gantry.xml', 1, 0, ''),
(6, 'RocketTheme Update Directory', 'collection', 'http://updates.rockettheme.com/joomla/updates.xml', 1, 1455919271, '');

-- --------------------------------------------------------

--
-- Table structure for table `j_update_sites_extensions`
--

CREATE TABLE IF NOT EXISTS `j_update_sites_extensions` (
  `update_site_id` int(11) NOT NULL DEFAULT '0',
  `extension_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`update_site_id`,`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Links extensions to update sites';

--
-- Dumping data for table `j_update_sites_extensions`
--

INSERT INTO `j_update_sites_extensions` (`update_site_id`, `extension_id`) VALUES
(1, 700),
(2, 700),
(3, 600),
(4, 28),
(5, 10001),
(6, 10006);

-- --------------------------------------------------------

--
-- Table structure for table `j_updates`
--

CREATE TABLE IF NOT EXISTS `j_updates` (
  `update_id` int(11) NOT NULL AUTO_INCREMENT,
  `update_site_id` int(11) DEFAULT '0',
  `extension_id` int(11) DEFAULT '0',
  `name` varchar(100) DEFAULT '',
  `description` text NOT NULL,
  `element` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `folder` varchar(20) DEFAULT '',
  `client_id` tinyint(3) DEFAULT '0',
  `version` varchar(32) DEFAULT '',
  `data` text NOT NULL,
  `detailsurl` text NOT NULL,
  `infourl` text NOT NULL,
  `extra_query` varchar(1000) DEFAULT '',
  PRIMARY KEY (`update_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Available Updates' AUTO_INCREMENT=16 ;

--
-- Dumping data for table `j_updates`
--

INSERT INTO `j_updates` (`update_id`, `update_site_id`, `extension_id`, `name`, `description`, `element`, `type`, `folder`, `client_id`, `version`, `data`, `detailsurl`, `infourl`, `extra_query`) VALUES
(1, 1, 700, 'Joomla', '', 'joomla', 'file', '', 0, '3.4.8', '', 'http://update.joomla.org/core/sts/extension_sts.xml', '', ''),
(2, 6, 0, 'RokSprocket Module', '', 'mod_roksprocket', 'module', '', 0, '2.1.9', '0802', 'http://updates.rockettheme.com/joomla/138/b61e70db', '', ''),
(3, 6, 0, 'RokGallery Extension', '', 'mod_rokgallery', 'module', '', 0, '2.31', '0802', 'http://updates.rockettheme.com/joomla/286/21ffe006', '', ''),
(4, 6, 0, 'RokBooster Plugin', '', 'rokbooster', 'plugin', 'system', 0, '1.1.15', '0802', 'http://updates.rockettheme.com/joomla/287/cb577720', '', ''),
(5, 6, 0, 'RokAjaxSearch Module', '', 'mod_rokajaxsearch', 'module', '', 0, '2.0.4', '0802', 'http://updates.rockettheme.com/joomla/290/1d5a0af1', '', ''),
(6, 6, 0, 'RokWeather Module', '', 'mod_rokweather', 'module', '', 0, '2.0.4', '0802', 'http://updates.rockettheme.com/joomla/292/a0cbba72', '', ''),
(7, 6, 0, 'RokStock Module', '', 'mod_rokstock', 'module', '', 0, '2.0.2', '0802', 'http://updates.rockettheme.com/joomla/295/87c1121c', '', ''),
(8, 6, 0, 'RokFeatureTable Module', '', 'mod_rokfeaturetable', 'module', '', 0, '1.7', '0802', 'http://updates.rockettheme.com/joomla/296/fb9111b3', '', ''),
(9, 6, 0, 'RokMiniEvents3 Module', '', 'mod_rokminievents3', 'module', '', 0, '3.0.2', '0802', 'http://updates.rockettheme.com/joomla/297/1686051690', '', ''),
(10, 6, 0, 'RokQuickCart Extension', '', 'com_rokquickcart', 'component', '', 1, '2.1.4', '0802', 'http://updates.rockettheme.com/joomla/298/ddfa10eb', '', ''),
(11, 6, 0, 'RokPad Plugin', '', 'rokpad', 'plugin', 'editors', 0, '2.1.9', '0802', 'http://updates.rockettheme.com/joomla/299/e07875c9', '', ''),
(12, 6, 0, 'RokBox Plugin', '', 'rokbox', 'plugin', 'system', 0, '2.0.11', '0802', 'http://updates.rockettheme.com/joomla/301/dfc993d8', '', ''),
(13, 6, 0, 'RokCandy Extension', '', 'rokcandy', 'plugin', 'system', 0, '2.0.2', '0802', 'http://updates.rockettheme.com/joomla/302/2df8a4e2', '', ''),
(14, 6, 0, 'RokComments Plugin', '', 'rokcomments', 'plugin', 'content', 0, '2.0.3', '0802', 'http://updates.rockettheme.com/joomla/303/d1fd7b76', '', ''),
(15, 6, 0, 'RokSocialButtons Plugin', '', 'roksocialbuttons', 'plugin', 'content', 0, '1.5.3', '0802', 'http://updates.rockettheme.com/joomla/381/269989291', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `j_user_keys`
--

CREATE TABLE IF NOT EXISTS `j_user_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `series` varchar(255) NOT NULL,
  `invalid` tinyint(4) NOT NULL,
  `time` varchar(200) NOT NULL,
  `uastring` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `series` (`series`),
  UNIQUE KEY `series_2` (`series`),
  UNIQUE KEY `series_3` (`series`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_user_notes`
--

CREATE TABLE IF NOT EXISTS `j_user_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `review_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_category_id` (`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_user_profiles`
--

CREATE TABLE IF NOT EXISTS `j_user_profiles` (
  `user_id` int(11) NOT NULL,
  `profile_key` varchar(100) NOT NULL,
  `profile_value` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_user_id_profile_key` (`user_id`,`profile_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Simple user profile storage table';

-- --------------------------------------------------------

--
-- Table structure for table `j_user_usergroup_map`
--

CREATE TABLE IF NOT EXISTS `j_user_usergroup_map` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to j_users.id',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to j_usergroups.id',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_user_usergroup_map`
--

INSERT INTO `j_user_usergroup_map` (`user_id`, `group_id`) VALUES
(951, 8),
(952, 2),
(953, 6);

-- --------------------------------------------------------

--
-- Table structure for table `j_usergroups`
--

CREATE TABLE IF NOT EXISTS `j_usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference Id',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_usergroup_parent_title_lookup` (`parent_id`,`title`),
  KEY `idx_usergroup_title_lookup` (`title`),
  KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  KEY `idx_usergroup_nested_set_lookup` (`lft`,`rgt`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `j_usergroups`
--

INSERT INTO `j_usergroups` (`id`, `parent_id`, `lft`, `rgt`, `title`) VALUES
(1, 0, 1, 18, 'Public'),
(2, 1, 8, 15, 'Registered'),
(3, 2, 9, 14, 'Author'),
(4, 3, 10, 13, 'Editor'),
(5, 4, 11, 12, 'Publisher'),
(6, 1, 4, 7, 'Manager'),
(7, 6, 5, 6, 'Administrator'),
(8, 1, 16, 17, 'Super Users'),
(9, 1, 2, 3, 'Guest');

-- --------------------------------------------------------

--
-- Table structure for table `j_users`
--

CREATE TABLE IF NOT EXISTS `j_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `block` tinyint(4) NOT NULL DEFAULT '0',
  `sendEmail` tinyint(4) DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `lastResetTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date of last password reset',
  `resetCount` int(11) NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime',
  `otpKey` varchar(1000) NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys',
  `otep` varchar(1000) NOT NULL DEFAULT '' COMMENT 'One time emergency passwords',
  `requireReset` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Require user to reset password on next login',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_block` (`block`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=954 ;

--
-- Dumping data for table `j_users`
--

INSERT INTO `j_users` (`id`, `name`, `username`, `email`, `password`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`, `lastResetTime`, `resetCount`, `otpKey`, `otep`, `requireReset`) VALUES
(951, 'Super User', 'admin', 'admin@example.com', '$2y$10$Re6HzGMeQ1.86QIWlQFfGebhkXufVevk8B.kaUTk.qEW3871zW.7W', 0, 1, '2013-07-24 09:07:43', '2016-02-19 21:55:45', '0', '{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}', '0000-00-00 00:00:00', 0, '', '', 0),
(952, 'User', 'user', 'user@example.com', '931d334de664be1135bed97fd9bb7b62:ZzvicSTnh9dr1Ln36G3MgkC9WSa9J4PW', 0, 0, '2013-07-24 09:23:03', '0000-00-00 00:00:00', '', '{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}', '0000-00-00 00:00:00', 0, '', '', 0),
(953, 'Manager', 'manager', 'manager@example.com', 'e0f025cc620a663e172c8b25911e5c4e:44wqdHQWhDPcrRg5koGsWJ9Zlhr9WC5x', 0, 0, '2013-07-24 10:53:59', '0000-00-00 00:00:00', '', '{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}', '0000-00-00 00:00:00', 0, '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_viewlevels`
--

CREATE TABLE IF NOT EXISTS `j_viewlevels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `title` varchar(100) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_assetgroup_title_lookup` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `j_viewlevels`
--

INSERT INTO `j_viewlevels` (`id`, `title`, `ordering`, `rules`) VALUES
(1, 'Public', 0, '[1]'),
(2, 'Registered', 2, '[6,2,8]'),
(3, 'Special', 3, '[6,3,8]'),
(5, 'Guest', 1, '[9]'),
(6, 'Super Users', 4, '[8]');
--
-- Database: `sites_monkeytree`
--

-- --------------------------------------------------------

--
-- Table structure for table `j_assets`
--

CREATE TABLE IF NOT EXISTS `j_assets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set parent.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `level` int(10) unsigned NOT NULL COMMENT 'The cached level in the nested tree.',
  `name` varchar(50) NOT NULL COMMENT 'The unique name for the asset.\n',
  `title` varchar(100) NOT NULL COMMENT 'The descriptive title for the asset.',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_asset_name` (`name`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=92 ;

--
-- Dumping data for table `j_assets`
--

INSERT INTO `j_assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES
(1, 0, 0, 177, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'),
(2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'),
(3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'),
(7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(8, 1, 17, 20, 1, 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'),
(9, 1, 21, 22, 1, 'com_cpanel', 'com_cpanel', '{}'),
(10, 1, 23, 24, 1, 'com_installer', 'com_installer', '{"core.admin":[],"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'),
(11, 1, 25, 26, 1, 'com_languages', 'com_languages', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(12, 1, 27, 28, 1, 'com_login', 'com_login', '{}'),
(13, 1, 29, 30, 1, 'com_mailto', 'com_mailto', '{}'),
(14, 1, 31, 32, 1, 'com_massmail', 'com_massmail', '{}'),
(15, 1, 33, 34, 1, 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'),
(16, 1, 35, 36, 1, 'com_menus', 'com_menus', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(17, 1, 37, 38, 1, 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(18, 1, 39, 142, 1, 'com_modules', 'com_modules', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(19, 1, 143, 146, 1, 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(20, 1, 147, 148, 1, 'com_plugins', 'com_plugins', '{"core.admin":{"7":1},"core.manage":[],"core.edit":[],"core.edit.state":[]}'),
(21, 1, 149, 150, 1, 'com_redirect', 'com_redirect', '{"core.admin":{"7":1},"core.manage":[]}'),
(22, 1, 151, 152, 1, 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(23, 1, 153, 154, 1, 'com_templates', 'com_templates', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(24, 1, 155, 158, 1, 'com_users', 'com_users', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(26, 1, 159, 160, 1, 'com_wrapper', 'com_wrapper', '{}'),
(27, 8, 18, 19, 2, 'com_content.category.2', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(30, 19, 144, 145, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(32, 24, 156, 157, 1, 'com_users.category.7', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(33, 1, 161, 162, 1, 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(34, 1, 163, 164, 1, 'com_joomlaupdate', 'com_joomlaupdate', '{"core.admin":[],"core.manage":[],"core.delete":[],"core.edit.state":[]}'),
(35, 1, 165, 166, 1, 'com_tags', 'com_tags', '{"core.admin":[],"core.manage":[],"core.manage":[],"core.delete":[],"core.edit.state":[]}'),
(36, 1, 167, 168, 1, 'com_contenthistory', 'com_contenthistory', '{}'),
(37, 1, 169, 170, 1, 'com_ajax', 'com_ajax', '{}'),
(38, 1, 171, 172, 1, 'com_postinstall', 'com_postinstall', '{}'),
(39, 18, 40, 41, 2, 'com_modules.module.1', 'Main Menu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(40, 18, 42, 43, 2, 'com_modules.module.2', 'Login', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(41, 18, 44, 45, 2, 'com_modules.module.3', 'Popular Articles', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(42, 18, 46, 47, 2, 'com_modules.module.4', 'Recently Added Articles', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(43, 18, 48, 49, 2, 'com_modules.module.8', 'Toolbar', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(44, 18, 50, 51, 2, 'com_modules.module.9', 'Quick Icons', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(45, 18, 52, 53, 2, 'com_modules.module.10', 'Logged-in Users', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(46, 18, 54, 55, 2, 'com_modules.module.12', 'Admin Menu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(47, 18, 56, 57, 2, 'com_modules.module.13', 'Admin Submenu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(48, 18, 58, 59, 2, 'com_modules.module.14', 'User Status', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(49, 18, 60, 61, 2, 'com_modules.module.15', 'Title', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(50, 18, 62, 63, 2, 'com_modules.module.16', 'Login Form', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(51, 18, 64, 65, 2, 'com_modules.module.17', 'Breadcrumbs', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(52, 18, 66, 67, 2, 'com_modules.module.79', 'Multilanguage status', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(53, 18, 68, 69, 2, 'com_modules.module.86', 'Joomla Version', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(54, 1, 173, 174, 1, 'com_easysocial', 'com_easysocial', '{}'),
(55, 18, 70, 71, 2, 'com_modules.module.87', 'EasySocial Albums', '{}'),
(56, 18, 72, 73, 2, 'com_modules.module.88', 'EasySocial Calendar', '{}'),
(57, 18, 74, 75, 2, 'com_modules.module.89', 'EasySocial Dating Search', '{}'),
(58, 18, 76, 77, 2, 'com_modules.module.90', 'EasySocial Dropdown Menu', '{}'),
(59, 18, 78, 79, 2, 'com_modules.module.91', 'Recent Blog Posts (EasyBlog)', '{}'),
(60, 18, 80, 81, 2, 'com_modules.module.92', 'EasySocial Event Menu', '{}'),
(61, 18, 82, 83, 2, 'com_modules.module.93', 'EasySocial Events', '{}'),
(62, 18, 84, 85, 2, 'com_modules.module.94', 'EasySocial Event Categories', '{}'),
(63, 18, 86, 87, 2, 'com_modules.module.95', 'EasySocial Followers', '{}'),
(64, 18, 88, 89, 2, 'com_modules.module.96', 'EasySocial Friends', '{}'),
(65, 18, 90, 91, 2, 'com_modules.module.97', 'EasySocial Group Menu', '{}'),
(66, 18, 92, 93, 2, 'com_modules.module.98', 'EasySocial Groups', '{}'),
(67, 18, 94, 95, 2, 'com_modules.module.99', 'EasySocial Group Categories', '{}'),
(68, 18, 96, 97, 2, 'com_modules.module.100', 'EasySocial Leader Board', '{}'),
(69, 18, 98, 99, 2, 'com_modules.module.101', 'EasySocial Log Box', '{}'),
(70, 18, 100, 101, 2, 'com_modules.module.102', 'EasySocial Login', '{}'),
(71, 18, 102, 103, 2, 'com_modules.module.103', 'EasySocial Menu', '{}'),
(72, 18, 104, 105, 2, 'com_modules.module.104', 'EasySocial Notifications', '{}'),
(73, 18, 106, 107, 2, 'com_modules.module.105', 'EasySocial OAuth Login', '{}'),
(74, 18, 108, 109, 2, 'com_modules.module.106', 'EasySocial Recent Photos', '{}'),
(75, 18, 110, 111, 2, 'com_modules.module.107', 'EasySocial Profile Completeness', '{}'),
(76, 18, 112, 113, 2, 'com_modules.module.108', 'EasySocial Quick Post', '{}'),
(77, 18, 114, 115, 2, 'com_modules.module.109', 'EasySocial Recent Polls', '{}'),
(78, 18, 116, 117, 2, 'com_modules.module.110', 'EasySocial Quick Registration', '{}'),
(79, 18, 118, 119, 2, 'com_modules.module.111', 'EasySocial Registration Requester', '{}'),
(80, 18, 120, 121, 2, 'com_modules.module.112', 'EasySocial Search', '{}'),
(81, 18, 122, 123, 2, 'com_modules.module.113', 'EasySocial Stream', '{}'),
(82, 18, 124, 125, 2, 'com_modules.module.114', 'EasySocial Toolbar', '{}'),
(83, 18, 126, 127, 2, 'com_modules.module.115', 'EasySocial Users', '{}'),
(84, 18, 128, 129, 2, 'com_modules.module.116', 'EasySocial Videos Module', '{}'),
(85, 18, 130, 131, 2, 'com_modules.module.117', 'Online Users', '{}'),
(86, 18, 132, 133, 2, 'com_modules.module.118', 'Recent Users', '{}'),
(87, 18, 134, 135, 2, 'com_modules.module.119', 'Recent Albums', '{}'),
(88, 18, 136, 137, 2, 'com_modules.module.120', 'Leaderboard', '{}'),
(89, 18, 138, 139, 2, 'com_modules.module.121', 'Dating Search', '{}'),
(90, 1, 175, 176, 1, 'com_gantry', 'Gantry', '{}'),
(91, 18, 140, 141, 2, 'com_modules.module.122', 'RokNavMenu', '{}');

-- --------------------------------------------------------

--
-- Table structure for table `j_associations`
--

CREATE TABLE IF NOT EXISTS `j_associations` (
  `id` int(11) NOT NULL COMMENT 'A reference to the associated item.',
  `context` varchar(50) NOT NULL COMMENT 'The context of the associated item.',
  `key` char(32) NOT NULL COMMENT 'The key for the association computed from an md5 on associated ids.',
  PRIMARY KEY (`context`,`id`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_banner_clients`
--

CREATE TABLE IF NOT EXISTS `j_banner_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `contact` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `extrainfo` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `metakey` text NOT NULL,
  `own_prefix` tinyint(4) NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  KEY `idx_own_prefix` (`own_prefix`),
  KEY `idx_metakey_prefix` (`metakey_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_banner_tracks`
--

CREATE TABLE IF NOT EXISTS `j_banner_tracks` (
  `track_date` datetime NOT NULL,
  `track_type` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`track_date`,`track_type`,`banner_id`),
  KEY `idx_track_date` (`track_date`),
  KEY `idx_track_type` (`track_type`),
  KEY `idx_banner_id` (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_banners`
--

CREATE TABLE IF NOT EXISTS `j_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `imptotal` int(11) NOT NULL DEFAULT '0',
  `impmade` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `clickurl` varchar(200) NOT NULL DEFAULT '',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `custombannercode` varchar(2048) NOT NULL,
  `sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `params` text NOT NULL,
  `own_prefix` tinyint(1) NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reset` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_own_prefix` (`own_prefix`),
  KEY `idx_metakey_prefix` (`metakey_prefix`),
  KEY `idx_banner_catid` (`catid`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_categories`
--

CREATE TABLE IF NOT EXISTS `j_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the j_assets table.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `extension` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `cat_idx` (`extension`,`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `j_categories`
--

INSERT INTO `j_categories` (`id`, `asset_id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `extension`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `hits`, `language`, `version`) VALUES
(1, 0, 0, 0, 11, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '{}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(7, 32, 1, 9, 10, 1, 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_contact_details`
--

CREATE TABLE IF NOT EXISTS `j_contact_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `con_position` varchar(255) DEFAULT NULL,
  `address` text,
  `suburb` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postcode` varchar(100) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `misc` mediumtext,
  `image` varchar(255) DEFAULT NULL,
  `email_to` varchar(255) DEFAULT NULL,
  `default_con` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `catid` int(11) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `webpage` varchar(255) NOT NULL DEFAULT '',
  `sortname1` varchar(255) NOT NULL,
  `sortname2` varchar(255) NOT NULL,
  `sortname3` varchar(255) NOT NULL,
  `language` char(7) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_content`
--

CREATE TABLE IF NOT EXISTS `j_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the j_assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_content_frontpage`
--

CREATE TABLE IF NOT EXISTS `j_content_frontpage` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_content_rating`
--

CREATE TABLE IF NOT EXISTS `j_content_rating` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_content_types`
--

CREATE TABLE IF NOT EXISTS `j_content_types` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_title` varchar(255) NOT NULL DEFAULT '',
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `table` varchar(255) NOT NULL DEFAULT '',
  `rules` text NOT NULL,
  `field_mappings` text NOT NULL,
  `router` varchar(255) NOT NULL DEFAULT '',
  `content_history_options` varchar(5120) DEFAULT NULL COMMENT 'JSON string for com_contenthistory options',
  PRIMARY KEY (`type_id`),
  KEY `idx_alias` (`type_alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `j_content_types`
--

INSERT INTO `j_content_types` (`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
(1, 'Article', 'com_content.article', '{"special":{"dbtable":"j_content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}, "special":{"fulltext":"fulltext"}}', 'ContentHelperRoute::getArticleRoute', '{"formFile":"administrator\\/components\\/com_content\\/models\\/forms\\/article.xml", "hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ]}'),
(2, 'Contact', 'com_contact.contact', '{"special":{"dbtable":"j_contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}}', 'ContactHelperRoute::getContactRoute', '{"formFile":"administrator\\/components\\/com_contact\\/models\\/forms\\/contact.xml","hideFields":["default_con","checked_out","checked_out_time","version","xreference"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[ {"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ] }'),
(3, 'Newsfeed', 'com_newsfeeds.newsfeed', '{"special":{"dbtable":"j_newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}}', 'NewsfeedsHelperRoute::getNewsfeedRoute', '{"formFile":"administrator\\/components\\/com_newsfeeds\\/models\\/forms\\/newsfeed.xml","hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ]}'),
(4, 'User', 'com_users.user', '{"special":{"dbtable":"j_users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{}}', 'UsersHelperRoute::getUserRoute', ''),
(5, 'Article Category', 'com_content.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContentHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(6, 'Contact Category', 'com_contact.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContactHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(7, 'Newsfeeds Category', 'com_newsfeeds.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'NewsfeedsHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(8, 'Tag', 'com_tags.tag', '{"special":{"dbtable":"j_tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}}', 'TagsHelperRoute::getTagRoute', '{"formFile":"administrator\\/components\\/com_tags\\/models\\/forms\\/tag.xml", "hideFields":["checked_out","checked_out_time","version", "lft", "rgt", "level", "path", "urls", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}]}'),
(9, 'Banner', 'com_banners.banner', '{"special":{"dbtable":"j_banners","key":"id","type":"Banner","prefix":"BannersTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"null","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"null"}, "special":{"imptotal":"imptotal", "impmade":"impmade", "clicks":"clicks", "clickurl":"clickurl", "custombannercode":"custombannercode", "cid":"cid", "purchase_type":"purchase_type", "track_impressions":"track_impressions", "track_clicks":"track_clicks"}}', '', '{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/banner.xml", "hideFields":["checked_out","checked_out_time","version", "reset"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "imptotal", "impmade", "reset"], "convertToInt":["publish_up", "publish_down", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"cid","targetTable":"j_banner_clients","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"created_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"modified_by","targetTable":"j_users","targetColumn":"id","displayColumn":"name"} ]}'),
(10, 'Banners Category', 'com_banners.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}'),
(11, 'Banner Client', 'com_banners.client', '{"special":{"dbtable":"j_banner_clients","key":"id","type":"Client","prefix":"BannersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/client.xml", "hideFields":["checked_out","checked_out_time"], "ignoreChanges":["checked_out", "checked_out_time"], "convertToInt":[], "displayLookup":[]}'),
(12, 'User Notes', 'com_users.note', '{"special":{"dbtable":"j_user_notes","key":"id","type":"Note","prefix":"UsersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_users\\/models\\/forms\\/note.xml", "hideFields":["checked_out","checked_out_time", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"catid","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}]}'),
(13, 'User Notes Category', 'com_users.category', '{"special":{"dbtable":"j_categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"j_ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"j_viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"j_users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"j_categories","targetColumn":"id","displayColumn":"title"}]}');

-- --------------------------------------------------------

--
-- Table structure for table `j_contentitem_tag_map`
--

CREATE TABLE IF NOT EXISTS `j_contentitem_tag_map` (
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `core_content_id` int(10) unsigned NOT NULL COMMENT 'PK from the core content table',
  `content_item_id` int(11) NOT NULL COMMENT 'PK from the content type table',
  `tag_id` int(10) unsigned NOT NULL COMMENT 'PK from the tag table',
  `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date of most recent save for this tag-item',
  `type_id` mediumint(8) NOT NULL COMMENT 'PK from the content_type table',
  UNIQUE KEY `uc_ItemnameTagid` (`type_id`,`content_item_id`,`tag_id`),
  KEY `idx_tag_type` (`tag_id`,`type_id`),
  KEY `idx_date_id` (`tag_date`,`tag_id`),
  KEY `idx_tag` (`tag_id`),
  KEY `idx_type` (`type_id`),
  KEY `idx_core_content_id` (`core_content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps items from content tables to tags';

-- --------------------------------------------------------

--
-- Table structure for table `j_core_log_searches`
--

CREATE TABLE IF NOT EXISTS `j_core_log_searches` (
  `search_term` varchar(128) NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_extensions`
--

CREATE TABLE IF NOT EXISTS `j_extensions` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `element` varchar(100) NOT NULL,
  `folder` varchar(100) NOT NULL,
  `client_id` tinyint(3) NOT NULL,
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `protected` tinyint(3) NOT NULL DEFAULT '0',
  `manifest_cache` text NOT NULL,
  `params` text NOT NULL,
  `custom_data` text NOT NULL,
  `system_data` text NOT NULL,
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) DEFAULT '0',
  `state` int(11) DEFAULT '0',
  PRIMARY KEY (`extension_id`),
  KEY `element_clientid` (`element`,`client_id`),
  KEY `element_folder_clientid` (`element`,`folder`,`client_id`),
  KEY `extension` (`type`,`element`,`folder`,`client_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=750 ;

--
-- Dumping data for table `j_extensions`
--

INSERT INTO `j_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(1, 'com_mailto', 'component', 'com_mailto', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(2, 'com_wrapper', 'component', 'com_wrapper', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(3, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(4, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, '', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":"","save_history":"1","history_limit":10}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(5, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(6, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(7, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(8, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, '', '{"show_contact_category":"hide","save_history":"1","history_limit":10,"show_contact_list":"0","presentation_style":"sliders","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","image":"","allow_vcard":"0","show_articles":"0","show_profile":"0","show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","show_headings":"1","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","allow_vcard_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_category_crumb":"0","metakey":"","metadesc":"","robots":"","author":"","rights":"","xreference":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(9, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(11, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, '', '{"administrator":"en-GB","site":"en-GB"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(12, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(13, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, '', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,application\\/x-shockwave-flash,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/x-zip","upload_mime_illegal":"text\\/html"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(14, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(15, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(16, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(17, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, '', '{"newsfeed_layout":"_:default","save_history":"1","history_limit":5,"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_character_count":"0","feed_display_order":"des","float_first":"right","float_second":"right","show_tags":"1","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"-1","show_empty_categories":"0","show_subcat_desc":"1","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_items_cat":"1","filter_field":"1","show_pagination_limit":"1","show_headings":"1","show_articles":"0","show_link":"1","show_pagination":"1","show_pagination_results":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(18, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(19, 'com_search', 'component', 'com_search', '', 1, 1, 1, 0, '', '{"enabled":"0","show_date":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(20, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, '', '{"template_positions_display":"0","upload_limit":"2","image_formats":"gif,bmp,jpg,jpeg,png","source_formats":"txt,less,ini,xml,js,php,css","font_formats":"woff,ttf,otf","compressed_formats":"zip"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(22, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, '', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","show_publishing_options":"1","show_article_options":"1","save_history":"1","history_limit":10,"show_urls_images_frontend":"0","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_subcategory_content":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(23, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, '', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"NONE","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(24, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(25, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, '', '{"allowUserRegistration":"0","new_usertype":"2","guest_usergroup":"9","sendpassword":"1","useractivation":"1","mail_to_admin":"0","captcha":"","frontend_userparams":"1","site_language":"0","change_login_name":"0","reset_count":"10","reset_time":"1","minimum_length":"4","minimum_integers":"0","minimum_symbols":"0","minimum_uppercase":"0","save_history":"1","history_limit":5,"mailSubjectPrefix":"","mailBodySuffix":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(27, 'com_finder', 'component', 'com_finder', '', 1, 1, 0, 0, '', '{"show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_advanced":"1","expand_advanced":"0","show_date_filters":"0","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","memory_table_limit":30000,"title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stemmer":"snowball"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(28, 'com_joomlaupdate', 'component', 'com_joomlaupdate', '', 1, 1, 0, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(29, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 1, '', '{"tag_layout":"_:default","save_history":"1","history_limit":5,"show_tag_title":"0","tag_list_show_tag_image":"0","tag_list_show_tag_description":"0","tag_list_image":"","show_tag_num_items":"0","tag_list_orderby":"title","tag_list_orderby_direction":"ASC","show_headings":"0","tag_list_show_date":"0","tag_list_show_item_image":"0","tag_list_show_item_description":"0","tag_list_item_maximum_characters":0,"return_any_or_all":"1","include_children":"0","maximum":200,"tag_list_language_filter":"all","tags_layout":"_:default","all_tags_orderby":"title","all_tags_orderby_direction":"ASC","all_tags_show_tag_image":"0","all_tags_show_tag_descripion":"0","all_tags_tag_maximum_characters":20,"all_tags_show_tag_hits":"0","filter_field":"1","show_pagination_limit":"1","show_pagination":"2","show_pagination_results":"1","tag_field_ajax_mode":"1","show_feed_link":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(30, 'com_contenthistory', 'component', 'com_contenthistory', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(32, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(101, 'SimplePie', 'library', 'simplepie', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(103, 'Joomla! Platform', 'library', 'joomla', '', 0, 1, 1, 1, '', '{"mediaversion":"7a2fff58d38c6d3a6a7e18a3a6213136"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(104, 'IDNA Convert', 'library', 'idna_convert', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(106, 'PHPass', 'library', 'phpass', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(200, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(201, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(202, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(203, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(204, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(205, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(206, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(207, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(208, 'mod_login', 'module', 'mod_login', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(209, 'mod_menu', 'module', 'mod_menu', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(210, 'mod_articles_news', 'module', 'mod_articles_news', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(211, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(212, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(213, 'mod_search', 'module', 'mod_search', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(214, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(215, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(216, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(218, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(219, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(220, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(221, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(222, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(223, 'mod_finder', 'module', 'mod_finder', '', 0, 1, 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(300, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(301, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(302, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(303, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(304, 'mod_login', 'module', 'mod_login', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(305, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(307, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(308, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(309, 'mod_status', 'module', 'mod_status', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(310, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(311, 'mod_title', 'module', 'mod_title', '', 1, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(312, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(313, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, '', '{"cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(314, 'mod_version', 'module', 'mod_version', '', 1, 1, 1, 0, '', '{"format":"short","product":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(315, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, '', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(316, 'mod_tags_popular', 'module', 'mod_tags_popular', '', 0, 1, 1, 0, '', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(317, 'mod_tags_similar', 'module', 'mod_tags_similar', '', 0, 1, 1, 0, '', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(400, 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(401, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(402, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(403, 'plg_content_contact', 'plugin', 'contact', 'content', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(404, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '', '{"mode":"1"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(406, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '', '{"style":"xhtml"}', '', '', 0, '2011-09-18 15:22:50', 0, 0),
(407, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 0, '', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(408, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 0, '', '{"position":"1"}', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(409, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(410, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '', '{"lineNumbers":"1","lineWrapping":"1","matchTags":"1","matchBrackets":"1","marker-gutter":"1","autoCloseTags":"1","autoCloseBrackets":"1","autoFocus":"1","theme":"default","tabmode":"indent"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(411, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(412, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 0, '', '{"mode":"1","skin":"0","mobile":"0","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","invalid_elements":"script,applet,iframe","extended_elements":"","html_height":"550","html_width":"750","resizing":"1","element_path":"1","fonts":"1","paste":"1","searchreplace":"1","insertdate":"1","colors":"1","table":"1","smilies":"1","hr":"1","link":"1","media":"1","print":"1","directionality":"1","fullscreen":"1","alignment":"1","visualchars":"1","visualblocks":"1","nonbreaking":"1","template":"1","blockquote":"1","wordcount":"1","advlist":"1","autosave":"1","contextmenu":"1","inlinepopups":"1","custom_plugin":"","custom_button":""}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(413, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(414, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(415, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(416, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(417, 'plg_search_categories', 'plugin', 'categories', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(418, 'plg_search_contacts', 'plugin', 'contacts', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(419, 'plg_search_content', 'plugin', 'content', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(420, 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(422, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(423, 'plg_system_p3p', 'plugin', 'p3p', 'system', 0, 0, 1, 0, '', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(424, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 1, '', '{"browsercache":"0","cachetime":"15"}', '', '', 0, '0000-00-00 00:00:00', 9, 0),
(425, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(426, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(427, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 0, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(428, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(429, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 8, 0),
(430, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(431, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 0, '', '{"autowebpage":"","category":"34","autopublish":"0"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(432, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '', '{"autoregister":"1","mail_to_user":"1","forceLogout":"1"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(433, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 0, '', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(434, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(436, 'plg_system_languagecode', 'plugin', 'languagecode', 'system', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 10, 0),
(437, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(438, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(439, 'plg_captcha_recaptcha', 'plugin', 'recaptcha', 'captcha', 0, 0, 1, 0, '', '{"public_key":"","private_key":"","theme":"clean"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(440, 'plg_system_highlight', 'plugin', 'highlight', 'system', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(441, 'plg_content_finder', 'plugin', 'finder', 'content', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(442, 'plg_finder_categories', 'plugin', 'categories', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(443, 'plg_finder_contacts', 'plugin', 'contacts', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(444, 'plg_finder_content', 'plugin', 'content', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(445, 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(447, 'plg_finder_tags', 'plugin', 'tags', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(449, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(450, 'plg_twofactorauth_yubikey', 'plugin', 'yubikey', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(451, 'plg_search_tags', 'plugin', 'tags', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","show_tagged_items":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(503, 'beez3', 'template', 'beez3', '', 0, 1, 1, 0, '', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, '', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(506, 'protostar', 'template', 'protostar', '', 0, 1, 1, 0, '', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(507, 'isis', 'template', 'isis', '', 1, 1, 1, 0, '', '{"templateColor":"","logoFile":""}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(600, 'English (United Kingdom)', 'language', 'en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(601, 'English (United Kingdom)', 'language', 'en-GB', '', 1, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(700, 'files_joomla', 'file', 'joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(701, 'com_easysocial', 'component', 'com_easysocial', '', 1, 1, 0, 0, '{"name":"com_easysocial","type":"component","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright 2009 - 2013 Stack Ideas Sdn Bhd. All Rights Reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(702, 'Authentication - EasySocial', 'plugin', 'easysocial', 'authentication', 0, 1, 1, 0, '{"name":"Authentication - EasySocial","type":"plugin","creationDate":"30\\/03\\/2013","author":"Mark Lee","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"stackideas.com","version":"1.0","description":"\\n\\t\\t\\n\\t\\tAn authentication plugin that allows oauth users to login to the site.\\n\\t\\t\\n\\t","group":"","filename":"easysocial"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(703, 'PLG_CONTENT_EASYSOCIAL', 'plugin', 'easysocial', 'content', 0, 1, 1, 0, '{"name":"PLG_CONTENT_EASYSOCIAL","type":"plugin","creationDate":"27th March 2014","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.0.5","description":"PLG_CONTENT_EASYSOCIAL_XML_DESCRIPTION","group":"","filename":"easysocial"}', '{"modify_contact_link":"1","display_info":"1","load_comments":"0","guest_viewcomments":"1","placement":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(704, 'plg_finder_easysocialalbums', 'plugin', 'easysocialalbums', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialalbums","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Albums.","group":"","filename":"easysocialalbums"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(705, 'plg_finder_easysocialevents', 'plugin', 'easysocialevents', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialevents","type":"plugin","creationDate":"August 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Events.","group":"","filename":"easysocialevents"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(706, 'plg_finder_easysocialgroups', 'plugin', 'easysocialgroups', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialgroups","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Groups.","group":"","filename":"easysocialgroups"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(707, 'plg_finder_easysocialphotos', 'plugin', 'easysocialphotos', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialphotos","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Photos.","group":"","filename":"easysocialphotos"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(708, 'plg_finder_easysocialusers', 'plugin', 'easysocialusers', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialusers","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial users'' profile.","group":"","filename":"easysocialusers"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(709, 'plg_finder_easysocialvideos', 'plugin', 'easysocialvideos', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_easysocialvideos","type":"plugin","creationDate":"March 2014","author":"Stackideas","copyright":"Copyright 2009 - 2014 StackIdeas. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/www.stackideas.com","version":"1.0.1","description":"This plugin indexes EasySocial Videos.","group":"","filename":"easysocialvideos"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(710, 'System - EasySocial', 'plugin', 'easysocial', 'system', 0, 1, 1, 0, '{"name":"System - EasySocial","type":"plugin","creationDate":"05\\/11\\/2013","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.0.0","description":"PLG_SYSTEM_EASYSOCIAL_XML_DESCRIPTION","group":"","filename":"easysocial"}', '{"redirection":"1"}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(711, 'User - EasySocial', 'plugin', 'easysocial', 'user', 0, 1, 1, 0, '{"name":"User - EasySocial","type":"plugin","creationDate":"30\\/03\\/2013","author":"Mark Lee","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"stackideas.com","version":"1.0","description":"\\n\\t\\t\\n\\t\\t\\tUser plugin for EasySocial.\\n\\t\\t\\n\\t","group":"","filename":"easysocial"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 1),
(712, 'EasySocial Albums', 'module', 'mod_easysocial_albums', '', 0, 1, 1, 0, '{"name":"EasySocial Albums","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_ALBUMS_DESC","group":"","filename":"mod_easysocial_albums"}', '{"total":"6","withCover":"0","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(713, 'EasySocial Calendar', 'module', 'mod_easysocial_calendar', '', 0, 1, 1, 0, '{"name":"EasySocial Calendar","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_CALENDAR_DESC","group":"","filename":"mod_easysocial_calendar"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(714, 'EasySocial Dating Search', 'module', 'mod_easysocial_dating_search', '', 0, 1, 1, 0, '{"name":"EasySocial Dating Search","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_DATING_SEARCH_DESC","group":"","filename":"mod_easysocial_dating_search"}', '{"searchname":"1","searchgender":"1","searchage":"1","searchdistance":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(715, 'EasySocial Dropdown Menu', 'module', 'mod_easysocial_dropdown_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Dropdown Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_DROPDOWN_MENU_DESC","group":"","filename":"mod_easysocial_dropdown_menu"}', '{"show_my_profile":"1","show_account_settings":"1","show_sign_in":"1","show_sign_out":"1","render_menus":"1","menu_type":"","popbox_position":"bottom","popbox_collision":"flip","popbox_offset":"10","register_button":"1","remember_me_style":"visible_checked","use_secure_url":"0","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(716, 'Recent Blog Posts (EasyBlog)', 'module', 'mod_easysocial_easyblog_posts', '', 0, 1, 1, 0, '{"name":"Recent Blog Posts (EasyBlog)","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_RECENT_BLOG_POSTS_DESC","group":"","filename":"mod_easysocial_easyblog_posts"}', '{"show_image":"1","show_author":"1","show_category":"1","popover":"1","total":"5","sorting":"latest","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(717, 'EasySocial Event Menu', 'module', 'mod_easysocial_event_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Event Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_EVENT_MENU_DESC","group":"","filename":"mod_easysocial_event_menu"}', '{"show_avatar":"1","show_name":"1","show_members":"1","show_edit":"1","show_pending":"1","show_apps":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(718, 'EasySocial Events', 'module', 'mod_easysocial_events', '', 0, 1, 1, 0, '{"name":"EasySocial Events","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_EVENTS_DESC","group":"","filename":"mod_easysocial_events"}', '{"filter":"0","category":"","ordering":"latest","display_member_counter":"1","display_category":"1","display_limit":"5","event_inclusion":"","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(719, 'EasySocial Event Categories', 'module', 'mod_easysocial_events_categories', '', 0, 1, 1, 0, '{"name":"EasySocial Event Categories","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_EVENTS_CATEGORIES_DESC","group":"","filename":"mod_easysocial_events_categories"}', '{"ordering":"ordering","display_desc":"1","desc_max":"250","display_counter":"1","display_avatar":"1","display_limit":"5","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(720, 'EasySocial Followers', 'module', 'mod_easysocial_followers', '', 0, 1, 1, 0, '{"name":"EasySocial Followers","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2015 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_FOLLOWERS_DESC","group":"","filename":"mod_easysocial_followers"}', '{"filter":"recent","total":"10","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(721, 'EasySocial Friends', 'module', 'mod_easysocial_friends', '', 0, 1, 1, 0, '{"name":"EasySocial Friends","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_FRIENDS_DESC","group":"","filename":"mod_easysocial_friends"}', '{"limit":"6","popover":"1","popover_position":"top-left","showall_link":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(722, 'EasySocial Group Menu', 'module', 'mod_easysocial_group_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Group Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_GROUP_MENU_DESC","group":"","filename":"mod_easysocial_group_menu"}', '{"show_avatar":"1","show_name":"1","show_members":"1","show_edit":"1","show_pending":"1","show_apps":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(723, 'EasySocial Groups', 'module', 'mod_easysocial_groups', '', 0, 1, 1, 0, '{"name":"EasySocial Groups","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2015 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_GROUPS_DESC","group":"","filename":"mod_easysocial_groups"}', '{"filter":"0","category":"","ordering":"latest","display_member_counter":"1","display_category":"1","display_actions":"1","display_limit":"5","group_inclusion":"","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(724, 'EasySocial Group Categories', 'module', 'mod_easysocial_groups_categories', '', 0, 1, 1, 0, '{"name":"EasySocial Group Categories","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_GROUPS_CATEGORIES_DESC","group":"","filename":"mod_easysocial_groups_categories"}', '{"ordering":"latest","display_desc":"1","desc_max":"250","display_counter":"1","display_avatar":"1","display_limit":"5","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(725, 'EasySocial Leader Board', 'module', 'mod_easysocial_leaderboard', '', 0, 1, 1, 0, '{"name":"EasySocial Leader Board","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_LEADERBOARD_DESC","group":"","filename":"mod_easysocial_leaderboard"}', '{"total":"5","popover":"1","popover_position":"top-left","showall_link":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(726, 'EasySocial Log Box', 'module', 'mod_easysocial_logbox', '', 0, 1, 1, 0, '{"name":"EasySocial Log Box","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_LOGBOX_DESC","group":"","filename":"mod_easysocial_logbox"}', '{"show_forget_username":"1","show_forget_password":"1","show_remember_me":"1","show_facebook_login":"1","show_quick_registration":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(727, 'EasySocial Login', 'module', 'mod_easysocial_login', '', 0, 1, 1, 0, '{"name":"EasySocial Login","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_LOGIN_DESC","group":"","filename":"mod_easysocial_login"}', '{"modulestyle":"vertical","show_register_link":"1","show_forget_username":"1","show_forget_password":"1","show_remember_me":"1","remember_me_style":"visible_checked","use_secure_url":"0","show_logout_button":"1","show_facebook_login":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(728, 'EasySocial Menu', 'module', 'mod_easysocial_menu', '', 0, 1, 1, 0, '{"name":"EasySocial Menu","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_MENU_DESC","group":"","filename":"mod_easysocial_menu"}', '{"show_avatar":"1","show_name":"1","show_points":"1","show_edit":"1","show_notifications":"1","show_system_notifications":"1","interval_notifications_system":"60","show_friends_notifications":"1","interval_notifications_friends":"60","show_conversation_notifications":"1","interval_notifications_conversations":"60","show_achievements":"1","show_navigation":"1","show_conversation":"1","show_friends":"1","show_followers":"1","show_photos":"1","show_videos":"1","show_apps":"1","show_activity":"1","integrate_easyblog":"1","show_signout":"1","popbox_position":"bottom","popbox_collision":"flip","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(729, 'EasySocial Notifications', 'module', 'mod_easysocial_notifications', '', 0, 1, 1, 0, '{"name":"EasySocial Notifications","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_NOTIFICATIONS_DESC","group":"","filename":"mod_easysocial_notifications"}', '{"show_system_notifications":"1","interval_notifications_system":"60","show_friends_notifications":"1","interval_notifications_friends":"60","show_conversation_notifications":"1","interval_notifications_conversations":"60","popbox_position":"bottom","popbox_collision":"flip","popbox_offset":"10","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(730, 'EasySocial OAuth Login', 'module', 'mod_easysocial_oauth', '', 0, 1, 1, 0, '{"name":"EasySocial OAuth Login","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_OAUTH_DESC","group":"","filename":"mod_easysocial_oauth"}', '{"cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(731, 'EasySocial Recent Photos', 'module', 'mod_easysocial_photos', '', 0, 1, 1, 0, '{"name":"EasySocial Recent Photos","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_PHOTOS_DESC","group":"","filename":"mod_easysocial_photos"}', '{"display_popup":"1","avatar":"1","cover":"1","ordering":"created","limit":"20","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(732, 'EasySocial Profile Completeness', 'module', 'mod_easysocial_profile_completeness', '', 0, 1, 1, 0, '{"name":"EasySocial Profile Completeness","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_PROFILE_COMPLETENESS_DESC","group":"","filename":"mod_easysocial_profile_completeness"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(733, 'EasySocial Quick Post', 'module', 'mod_easysocial_quickpost', '', 0, 1, 1, 0, '{"name":"EasySocial Quick Post","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_QUICKPOST_DESC","group":"","filename":"mod_easysocial_quickpost"}', '{"show_public":"0","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(734, 'EasySocial Recent Polls', 'module', 'mod_easysocial_recentpolls', '', 0, 1, 1, 0, '{"name":"EasySocial Recent Polls","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_RECENTPOLLS_DESC","group":"","filename":"mod_easysocial_recentpolls"}', '{"display_limit":"5","display_pollitems":"1","display_pollitems_scorebar":"1","display_author":"1","display_createdate":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(735, 'EasySocial Quick Registration', 'module', 'mod_easysocial_register', '', 0, 1, 1, 0, '{"name":"EasySocial Quick Registration","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2014 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_REGISTER_DESC","group":"","filename":"mod_easysocial_register"}', '{"register_type":"quick","show_heading_title":"1","heading_title":"Don''t have an account?","show_heading_desc":"1","heading_desc":"Register now to join the community!","social":"1","splash_image":"1","splash_image_url":"","splash_image_title":"MOD_EASYSOCIAL_REGISTER_SPLASH_TITLE_JOIN_US_TODAY","splash_footer_content":"MOD_EASYSOCIAL_REGISTER_SPLASH_FOOTER_CONTENT","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(736, 'EasySocial Registration Requester', 'module', 'mod_easysocial_registration_requester', '', 0, 1, 1, 0, '{"name":"EasySocial Registration Requester","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_REGISTRATION_REQUESTER_DESC","group":"","filename":"mod_easysocial_registration_requester"}', '{"show_heading_title":"1","heading_title":"MOD_EASYSOCIAL_REGISTRATION_REQUESTER_HEADING_TITLE_DEFAULT","show_heading_desc":"1","heading_desc":"MOD_EASYSOCIAL_REGISTRATION_REQUESTER_HEADING_DESCRIPTION_DEFAULT","social":"1","splash_image":"1","splash_image_url":"\\/modules\\/mod_easysocial_registration_requester\\/images\\/splash.jpg","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(737, 'EasySocial Search', 'module', 'mod_easysocial_search', '', 0, 1, 1, 0, '{"name":"EasySocial Search","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_SEARCH_DESC","group":"","filename":"mod_easysocial_search"}', '{"showadvancedlink":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(738, 'EasySocial Stream', 'module', 'mod_easysocial_stream', '', 0, 1, 1, 0, '{"name":"EasySocial Stream","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_STREAM_DESC","group":"","filename":"mod_easysocial_stream"}', '{"total":"10","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);
INSERT INTO `j_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(739, 'EasySocial Toolbar', 'module', 'mod_easysocial_toolbar', '', 0, 1, 1, 0, '{"name":"EasySocial Toolbar","type":"module","creationDate":"31st January 2016","author":"StackIdeas","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_TOOLBAR_DESC","group":"","filename":"mod_easysocial_toolbar"}', '{"show_on_easysocial":"0","show_dashboard":"1","show_friends":"1","show_conversations":"1","show_notifications":"1","show_search":"1","show_login":"1","show_profile":"1","responsive":"1","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(740, 'EasySocial Users', 'module', 'mod_easysocial_users', '', 0, 1, 1, 0, '{"name":"EasySocial Users","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_USERS_DESC","group":"","filename":"mod_easysocial_users"}', '{"filter":"recent","total":"10","ordering":"registerDate","hasavatar":"0","direction":"desc","popover":"1","popover_position":"top-left","showall_link":"1","user_inclusion":"","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(741, 'EasySocial Videos Module', 'module', 'mod_easysocial_videos', '', 0, 1, 1, 0, '{"name":"EasySocial Videos Module","type":"module","creationDate":"31st January 2016","author":"Stack Ideas Sdn Bhd","copyright":"Copyright (C) 2009 - 2013 Stack Ideas Sdn Bhd. All rights reserved.","authorEmail":"support@stackideas.com","authorUrl":"http:\\/\\/stackideas.com","version":"1.4.7","description":"MOD_EASYSOCIAL_VIDEOS_DESC","group":"","filename":"mod_easysocial_videos"}', '{"filter":"created","category":"","source":"created","sorting":"created","limit":"20","cache":"0"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(742, 'jf_connecto_es_13', 'file', 'jf_connecto_es_13', '', 0, 1, 0, 0, '{"name":"jf_connecto_es_13","type":"file","creationDate":"14.05.15","author":"JoomForest.com","copyright":"Copyright (C) 2011-2015 JoomForest. All rights reserved.","authorEmail":"support@joomforest.com","authorUrl":"http:\\/\\/www.joomforest.com\\/","version":"1.2","description":"","group":""}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(744, 'Gantry', 'library', 'lib_gantry', '', 0, 1, 1, 0, '{"name":"Gantry","type":"library","creationDate":"March 9, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"4.1.29","description":"Gantry Starting Template for Joomla! v4.1.29","group":"","filename":"lib_gantry"}', '{}', '{"last_update":1455799738}', '', 0, '0000-00-00 00:00:00', 0, 0),
(745, 'Gantry', 'component', 'com_gantry', '', 0, 1, 0, 0, '{"name":"Gantry","type":"component","creationDate":"March 9, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"4.1.29","description":"Gantry Starting Template for Joomla! v4.1.29","group":"","filename":"gantry"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(746, 'System - Gantry', 'plugin', 'gantry', 'system', 0, 1, 1, 0, '{"name":"System - Gantry","type":"plugin","creationDate":"March 9, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"4.1.29","description":"Gantry System Plugin for Joomla","group":"","filename":"gantry"}', '{"debugloglevel":"63"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(747, 'jf_connecto', 'template', 'jf_connecto', '', 0, 1, 1, 0, '{"name":"jf_connecto","type":"template","creationDate":"14.05.15","author":"JoomForest.com","copyright":"Copyright (C) 2011-2015 JoomForest. All rights reserved.","authorEmail":"support@joomforest.com","authorUrl":"http:\\/\\/www.joomforest.com\\/","version":"1.2","description":"JF Connecto v1.2","group":"","filename":"templateDetails"}', '{"master":"true"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(748, 'System - RokExtender', 'plugin', 'rokextender', 'system', 0, 1, 1, 0, '{"name":"System - RokExtender","type":"plugin","creationDate":"October 31, 2012","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2012 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"2.0.0","description":"System - Gantry","group":"","filename":"rokextender"}', '{"registered":"\\/modules\\/mod_roknavmenu\\/lib\\/RokNavMenuEvents.php"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(749, 'RokNavMenu', 'module', 'mod_roknavmenu', '', 0, 1, 1, 0, '{"name":"RokNavMenu","type":"module","creationDate":"February 24, 2015","author":"RocketTheme, LLC","copyright":"(C) 2005 - 2015 RocketTheme, LLC. All rights reserved.","authorEmail":"support@rockettheme.com","authorUrl":"http:\\/\\/www.rockettheme.com","version":"2.0.8","description":"RocketTheme Customizable Navigation Menu","group":"","filename":"mod_roknavmenu"}', '{"limit_levels":"0","startLevel":"0","endLevel":"0","showAllChildren":"0","filteringspacer2":"","theme":"default","custom_layout":"default.php","custom_formatter":"default.php","cache":"0","module_cache":"1","cache_time":"900","cachemode":"itemid"}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_filters`
--

CREATE TABLE IF NOT EXISTS `j_finder_filters` (
  `filter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_alias` varchar(255) NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `map_count` int(10) unsigned NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `params` mediumtext,
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links`
--

CREATE TABLE IF NOT EXISTS `j_finder_links` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `indexdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `md5sum` varchar(32) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `state` int(5) DEFAULT '1',
  `access` int(5) DEFAULT '0',
  `language` varchar(8) NOT NULL,
  `publish_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `list_price` double unsigned NOT NULL DEFAULT '0',
  `sale_price` double unsigned NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL,
  `object` mediumblob NOT NULL,
  PRIMARY KEY (`link_id`),
  KEY `idx_type` (`type_id`),
  KEY `idx_title` (`title`),
  KEY `idx_md5` (`md5sum`),
  KEY `idx_url` (`url`(75)),
  KEY `idx_published_list` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`list_price`),
  KEY `idx_published_sale` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`sale_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms0`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms0` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms1`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms1` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms2`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms2` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms3`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms3` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms4`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms4` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms5`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms5` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms6`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms6` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms7`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms7` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms8`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms8` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_terms9`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_terms9` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsa`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsa` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsb`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsb` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsc`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsc` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsd`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsd` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termse`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termse` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_links_termsf`
--

CREATE TABLE IF NOT EXISTS `j_finder_links_termsf` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`term_id`),
  KEY `idx_term_weight` (`term_id`,`weight`),
  KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_taxonomy`
--

CREATE TABLE IF NOT EXISTS `j_finder_taxonomy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `access` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `state` (`state`),
  KEY `ordering` (`ordering`),
  KEY `access` (`access`),
  KEY `idx_parent_published` (`parent_id`,`state`,`access`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_finder_taxonomy`
--

INSERT INTO `j_finder_taxonomy` (`id`, `parent_id`, `title`, `state`, `access`, `ordering`) VALUES
(1, 0, 'ROOT', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_taxonomy_map`
--

CREATE TABLE IF NOT EXISTS `j_finder_taxonomy_map` (
  `link_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`link_id`,`node_id`),
  KEY `link_id` (`link_id`),
  KEY `node_id` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_terms`
--

CREATE TABLE IF NOT EXISTS `j_finder_terms` (
  `term_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '0',
  `soundex` varchar(75) NOT NULL,
  `links` int(10) NOT NULL DEFAULT '0',
  `language` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`term_id`),
  UNIQUE KEY `idx_term` (`term`),
  KEY `idx_term_phrase` (`term`,`phrase`),
  KEY `idx_stem_phrase` (`stem`,`phrase`),
  KEY `idx_soundex_phrase` (`soundex`,`phrase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_terms_common`
--

CREATE TABLE IF NOT EXISTS `j_finder_terms_common` (
  `term` varchar(75) NOT NULL,
  `language` varchar(3) NOT NULL,
  KEY `idx_word_lang` (`term`,`language`),
  KEY `idx_lang` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_finder_terms_common`
--

INSERT INTO `j_finder_terms_common` (`term`, `language`) VALUES
('a', 'en'),
('about', 'en'),
('after', 'en'),
('ago', 'en'),
('all', 'en'),
('am', 'en'),
('an', 'en'),
('and', 'en'),
('ani', 'en'),
('any', 'en'),
('are', 'en'),
('aren''t', 'en'),
('as', 'en'),
('at', 'en'),
('be', 'en'),
('but', 'en'),
('by', 'en'),
('for', 'en'),
('from', 'en'),
('get', 'en'),
('go', 'en'),
('how', 'en'),
('if', 'en'),
('in', 'en'),
('into', 'en'),
('is', 'en'),
('isn''t', 'en'),
('it', 'en'),
('its', 'en'),
('me', 'en'),
('more', 'en'),
('most', 'en'),
('must', 'en'),
('my', 'en'),
('new', 'en'),
('no', 'en'),
('none', 'en'),
('not', 'en'),
('noth', 'en'),
('nothing', 'en'),
('of', 'en'),
('off', 'en'),
('often', 'en'),
('old', 'en'),
('on', 'en'),
('onc', 'en'),
('once', 'en'),
('onli', 'en'),
('only', 'en'),
('or', 'en'),
('other', 'en'),
('our', 'en'),
('ours', 'en'),
('out', 'en'),
('over', 'en'),
('page', 'en'),
('she', 'en'),
('should', 'en'),
('small', 'en'),
('so', 'en'),
('some', 'en'),
('than', 'en'),
('thank', 'en'),
('that', 'en'),
('the', 'en'),
('their', 'en'),
('theirs', 'en'),
('them', 'en'),
('then', 'en'),
('there', 'en'),
('these', 'en'),
('they', 'en'),
('this', 'en'),
('those', 'en'),
('thus', 'en'),
('time', 'en'),
('times', 'en'),
('to', 'en'),
('too', 'en'),
('true', 'en'),
('under', 'en'),
('until', 'en'),
('up', 'en'),
('upon', 'en'),
('use', 'en'),
('user', 'en'),
('users', 'en'),
('veri', 'en'),
('version', 'en'),
('very', 'en'),
('via', 'en'),
('want', 'en'),
('was', 'en'),
('way', 'en'),
('were', 'en'),
('what', 'en'),
('when', 'en'),
('where', 'en'),
('whi', 'en'),
('which', 'en'),
('who', 'en'),
('whom', 'en'),
('whose', 'en'),
('why', 'en'),
('wide', 'en'),
('will', 'en'),
('with', 'en'),
('within', 'en'),
('without', 'en'),
('would', 'en'),
('yes', 'en'),
('yet', 'en'),
('you', 'en'),
('your', 'en'),
('yours', 'en');

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_tokens`
--

CREATE TABLE IF NOT EXISTS `j_finder_tokens` (
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '1',
  `context` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `language` char(3) NOT NULL DEFAULT '',
  KEY `idx_word` (`term`),
  KEY `idx_context` (`context`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_tokens_aggregate`
--

CREATE TABLE IF NOT EXISTS `j_finder_tokens_aggregate` (
  `term_id` int(10) unsigned NOT NULL,
  `map_suffix` char(1) NOT NULL,
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `term_weight` float unsigned NOT NULL,
  `context` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `context_weight` float unsigned NOT NULL,
  `total_weight` float unsigned NOT NULL,
  `language` char(3) NOT NULL DEFAULT '',
  KEY `token` (`term`),
  KEY `keyword_id` (`term_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_finder_types`
--

CREATE TABLE IF NOT EXISTS `j_finder_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `mime` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_languages`
--

CREATE TABLE IF NOT EXISTS `j_languages` (
  `lang_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang_code` char(7) NOT NULL,
  `title` varchar(50) NOT NULL,
  `title_native` varchar(50) NOT NULL,
  `sef` varchar(50) NOT NULL,
  `image` varchar(50) NOT NULL,
  `description` varchar(512) NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `sitename` varchar(1024) NOT NULL DEFAULT '',
  `published` int(11) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`),
  UNIQUE KEY `idx_sef` (`sef`),
  UNIQUE KEY `idx_image` (`image`),
  UNIQUE KEY `idx_langcode` (`lang_code`),
  KEY `idx_access` (`access`),
  KEY `idx_ordering` (`ordering`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_languages`
--

INSERT INTO `j_languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `description`, `metakey`, `metadesc`, `sitename`, `published`, `access`, `ordering`) VALUES
(1, 'en-GB', 'English (UK)', 'English (UK)', 'en', 'en', '', '', '', '', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_menu`
--

CREATE TABLE IF NOT EXISTS `j_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to j_menu_types.menutype',
  `title` varchar(255) NOT NULL COMMENT 'The display title of the menu item.',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'The SEF alias of the menu item.',
  `note` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.',
  `link` varchar(1024) NOT NULL COMMENT 'The actually link the menu item refers to.',
  `type` varchar(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator',
  `published` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The published state of the menu link.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The parent menu item in the menu tree.',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The relative level in the tree.',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to j_extensions.id',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to j_users.id',
  `checked_out_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.',
  `browserNav` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The click behaviour of the link.',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The access level required to view the menu item.',
  `img` varchar(255) NOT NULL COMMENT 'The image of the menu item.',
  `template_style_id` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL COMMENT 'JSON encoded data for the menu item.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `home` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Indicates if this menu item is the home or default page.',
  `language` char(7) NOT NULL DEFAULT '',
  `client_id` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_client_id_parent_id_alias_language` (`client_id`,`parent_id`,`alias`,`language`),
  KEY `idx_componentid` (`component_id`,`menutype`,`published`,`access`),
  KEY `idx_menutype` (`menutype`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_path` (`path`(255)),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=160 ;

--
-- Dumping data for table `j_menu`
--

INSERT INTO `j_menu` (`id`, `menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES
(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, 83, 0, '*', 0),
(2, 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 0, 1, 1, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners', 0, '', 1, 10, 0, '*', 1),
(3, 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 0, 2, 2, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners', 0, '', 2, 3, 0, '*', 1),
(4, 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 0, 2, 2, 6, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-cat', 0, '', 4, 5, 0, '*', 1),
(5, 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 0, 2, 2, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-clients', 0, '', 6, 7, 0, '*', 1),
(6, 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 0, 2, 2, 4, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-tracks', 0, '', 8, 9, 0, '*', 1),
(7, 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 0, 1, 1, 8, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact', 0, '', 11, 16, 0, '*', 1),
(8, 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 0, 7, 2, 8, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact', 0, '', 12, 13, 0, '*', 1),
(9, 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 0, 7, 2, 6, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact-cat', 0, '', 14, 15, 0, '*', 1),
(10, 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 0, 1, 1, 15, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages', 0, '', 17, 22, 0, '*', 1),
(11, 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 0, 10, 2, 15, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1),
(12, 'menu', 'com_messages_read', 'Read Private Message', '', 'Messaging/Read Private Message', 'index.php?option=com_messages', 'component', 0, 10, 2, 15, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages-read', 0, '', 20, 21, 0, '*', 1),
(13, 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 1, 1, 17, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 23, 28, 0, '*', 1),
(14, 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 13, 2, 17, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 24, 25, 0, '*', 1),
(15, 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 0, 13, 2, 6, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds-cat', 0, '', 26, 27, 0, '*', 1),
(16, 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 0, 1, 1, 24, 0, '0000-00-00 00:00:00', 0, 0, 'class:redirect', 0, '', 29, 30, 0, '*', 1),
(17, 'menu', 'com_search', 'Basic Search', '', 'Basic Search', 'index.php?option=com_search', 'component', 0, 1, 1, 19, 0, '0000-00-00 00:00:00', 0, 0, 'class:search', 0, '', 31, 32, 0, '*', 1),
(18, 'menu', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 0, 1, 1, 27, 0, '0000-00-00 00:00:00', 0, 0, 'class:finder', 0, '', 33, 34, 0, '*', 1),
(19, 'menu', 'com_joomlaupdate', 'Joomla! Update', '', 'Joomla! Update', 'index.php?option=com_joomlaupdate', 'component', 1, 1, 1, 28, 0, '0000-00-00 00:00:00', 0, 0, 'class:joomlaupdate', 0, '', 35, 36, 0, '*', 1),
(20, 'main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 0, 1, 1, 29, 0, '0000-00-00 00:00:00', 0, 1, 'class:tags', 0, '', 37, 38, 0, '', 1),
(21, 'main', 'com_postinstall', 'Post-installation messages', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', 0, 1, 1, 32, 0, '0000-00-00 00:00:00', 0, 1, 'class:postinstall', 0, '', 39, 40, 0, '*', 1),
(101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, 22, 0, '0000-00-00 00:00:00', 0, 1, '', 0, '{"featured_categories":[""],"layout_type":"blog","num_leading_articles":"1","num_intro_articles":"3","num_columns":"3","num_links":"0","multi_column_order":"1","orderby_pri":"","orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":1,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 41, 42, 1, '*', 0),
(121, 'mainmenu', 'Community', 'community', '', 'community', 'index.php?option=com_easysocial&view=dashboard', 'component', 1, 1, 1, 701, 0, '0000-00-00 00:00:00', 0, 1, '', 0, '', 43, 44, 0, '*', 0),
(141, 'main', 'COM_EASYSOCIAL', 'com_easysocial', '', 'com_easysocial', 'index.php?option=com_easysocial', 'component', 0, 1, 1, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial.png', 0, '{}', 45, 82, 0, '', 1),
(142, 'main', 'COM_EASYSOCIAL_MENU_SETTINGS', 'com_easysocial_menu_settings', '', 'com_easysocial/com_easysocial_menu_settings', 'index.php?option=com_easysocial&view=settings', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-settings.png', 0, '{}', 46, 47, 0, '', 1),
(143, 'main', 'COM_EASYSOCIAL_MENU_USERS', 'com_easysocial_menu_users', '', 'com_easysocial/com_easysocial_menu_users', 'index.php?option=com_easysocial&view=users', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-users.png', 0, '{}', 48, 49, 0, '', 1),
(144, 'main', 'COM_EASYSOCIAL_MENU_THEMES', 'com_easysocial_menu_themes', '', 'com_easysocial/com_easysocial_menu_themes', 'index.php?option=com_easysocial&view=themes', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-themes.png', 0, '{}', 50, 51, 0, '', 1),
(145, 'main', 'COM_EASYSOCIAL_MENU_LANGUAGES', 'com_easysocial_menu_languages', '', 'com_easysocial/com_easysocial_menu_languages', 'index.php?option=com_easysocial&view=languages', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-languages.png', 0, '{}', 52, 53, 0, '', 1),
(146, 'main', 'COM_EASYSOCIAL_MENU_PROFILES', 'com_easysocial_menu_profiles', '', 'com_easysocial/com_easysocial_menu_profiles', 'index.php?option=com_easysocial&view=profiles', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-profiles.png', 0, '{}', 54, 55, 0, '', 1),
(147, 'main', 'COM_EASYSOCIAL_MENU_GROUPS', 'com_easysocial_menu_groups', '', 'com_easysocial/com_easysocial_menu_groups', 'index.php?option=com_easysocial&view=groups', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-groups.png', 0, '{}', 56, 57, 0, '', 1),
(148, 'main', 'COM_EASYSOCIAL_MENU_EVENTS', 'com_easysocial_menu_events', '', 'com_easysocial/com_easysocial_menu_events', 'index.php?option=com_easysocial&view=events', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-events.png', 0, '{}', 58, 59, 0, '', 1),
(149, 'main', 'COM_EASYSOCIAL_MENU_ALBUMS', 'com_easysocial_menu_albums', '', 'com_easysocial/com_easysocial_menu_albums', 'index.php?option=com_easysocial&view=albums', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-albums.png', 0, '{}', 60, 61, 0, '', 1),
(150, 'main', 'COM_EASYSOCIAL_MENU_VIDEOS', 'com_easysocial_menu_videos', '', 'com_easysocial/com_easysocial_menu_videos', 'index.php?option=com_easysocial&view=videos', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-videos.png', 0, '{}', 62, 63, 0, '', 1),
(151, 'main', 'COM_EASYSOCIAL_MENU_PRIVACY', 'com_easysocial_menu_privacy', '', 'com_easysocial/com_easysocial_menu_privacy', 'index.php?option=com_easysocial&view=privacy', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-privacy.png', 0, '{}', 64, 65, 0, '', 1),
(152, 'main', 'COM_EASYSOCIAL_MENU_POINTS', 'com_easysocial_menu_points', '', 'com_easysocial/com_easysocial_menu_points', 'index.php?option=com_easysocial&view=points', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-points.png', 0, '{}', 66, 67, 0, '', 1),
(153, 'main', 'COM_EASYSOCIAL_MENU_ALERTS', 'com_easysocial_menu_alerts', '', 'com_easysocial/com_easysocial_menu_alerts', 'index.php?option=com_easysocial&view=alerts', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-alerts.png', 0, '{}', 68, 69, 0, '', 1),
(154, 'main', 'COM_EASYSOCIAL_MENU_BADGES', 'com_easysocial_menu_badges', '', 'com_easysocial/com_easysocial_menu_badges', 'index.php?option=com_easysocial&view=badges', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-badges.png', 0, '{}', 70, 71, 0, '', 1),
(155, 'main', 'COM_EASYSOCIAL_MENU_APPS', 'com_easysocial_menu_apps', '', 'com_easysocial/com_easysocial_menu_apps', 'index.php?option=com_easysocial&view=apps', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-apps.png', 0, '{}', 72, 73, 0, '', 1),
(156, 'main', 'COM_EASYSOCIAL_MENU_REPORTS', 'com_easysocial_menu_reports', '', 'com_easysocial/com_easysocial_menu_reports', 'index.php?option=com_easysocial&view=reports', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-reports.png', 0, '{}', 74, 75, 0, '', 1),
(157, 'main', 'COM_EASYSOCIAL_MENU_EMAIL_ACTIVITIES', 'com_easysocial_menu_email_activities', '', 'com_easysocial/com_easysocial_menu_email_activities', 'index.php?option=com_easysocial&view=mailer', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-emails.png', 0, '{}', 76, 77, 0, '', 1),
(158, 'main', 'COM_EASYSOCIAL_MENU_MIGRATORS', 'com_easysocial_menu_migrators', '', 'com_easysocial/com_easysocial_menu_migrators', 'index.php?option=com_easysocial&view=migrators', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-migrators.png', 0, '{}', 78, 79, 0, '', 1),
(159, 'main', 'COM_EASYSOCIAL_MENU_ACCESS', 'com_easysocial_menu_access', '', 'com_easysocial/com_easysocial_menu_access', 'index.php?option=com_easysocial&view=access', 'component', 0, 141, 2, 701, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_easysocial/themes/default/images/icons/16/easysocial-access.png', 0, '{}', 80, 81, 0, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_menu_types`
--

CREATE TABLE IF NOT EXISTS `j_menu_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) NOT NULL,
  `title` varchar(48) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_menutype` (`menutype`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_menu_types`
--

INSERT INTO `j_menu_types` (`id`, `menutype`, `title`, `description`) VALUES
(1, 'mainmenu', 'Main Menu', 'The main menu for the site');

-- --------------------------------------------------------

--
-- Table structure for table `j_messages`
--

CREATE TABLE IF NOT EXISTS `j_messages` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id_from` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id_to` int(10) unsigned NOT NULL DEFAULT '0',
  `folder_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `date_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `useridto_state` (`user_id_to`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_messages_cfg`
--

CREATE TABLE IF NOT EXISTS `j_messages_cfg` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `cfg_name` varchar(100) NOT NULL DEFAULT '',
  `cfg_value` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `idx_user_var_name` (`user_id`,`cfg_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_modules`
--

CREATE TABLE IF NOT EXISTS `j_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the j_assets table.',
  `title` varchar(100) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `position` varchar(50) NOT NULL DEFAULT '',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `module` varchar(50) DEFAULT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `showtitle` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `client_id` tinyint(4) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`published`,`access`),
  KEY `newsfeeds` (`module`,`published`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=122 ;

--
-- Dumping data for table `j_modules`
--

INSERT INTO `j_modules` (`id`, `asset_id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
(1, 39, 'Main Menu', '', '', 1, 'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_menu', 1, 1, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'),
(2, 40, 'Login', '', '', 1, 'login', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_login', 1, 1, '', 1, '*'),
(3, 41, 'Popular Articles', '', '', 3, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(4, 42, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(8, 43, 'Toolbar', '', '', 1, 'toolbar', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_toolbar', 3, 1, '', 1, '*'),
(9, 44, 'Quick Icons', '', '', 1, 'icon', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_quickicon', 3, 1, '', 1, '*'),
(10, 45, 'Logged-in Users', '', '', 2, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(12, 46, 'Admin Menu', '', '', 1, 'menu', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*'),
(13, 47, 'Admin Submenu', '', '', 1, 'submenu', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_submenu', 3, 1, '', 1, '*'),
(14, 48, 'User Status', '', '', 2, 'status', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_status', 3, 1, '', 1, '*'),
(15, 49, 'Title', '', '', 1, 'title', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_title', 3, 1, '', 1, '*'),
(16, 50, 'Login Form', '', '', 7, 'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*'),
(17, 51, 'Breadcrumbs', '', '', 1, 'position-2', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'),
(79, 52, 'Multilanguage status', '', '', 1, 'status', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(86, 53, 'Joomla Version', '', '', 1, 'footer', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_version', 3, 1, '{"format":"short","product":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(87, 55, 'EasySocial Albums', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_albums', 1, 1, '', 0, '*'),
(88, 56, 'EasySocial Calendar', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_calendar', 1, 1, '', 0, '*'),
(89, 57, 'EasySocial Dating Search', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_dating_search', 1, 1, '', 0, '*'),
(90, 58, 'EasySocial Dropdown Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_dropdown_menu', 1, 1, '', 0, '*'),
(91, 59, 'Recent Blog Posts (EasyBlog)', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_easyblog_posts', 1, 1, '', 0, '*'),
(92, 60, 'EasySocial Event Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_event_menu', 1, 1, '', 0, '*'),
(93, 61, 'EasySocial Events', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_events', 1, 1, '', 0, '*'),
(94, 62, 'EasySocial Event Categories', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_events_categories', 1, 1, '', 0, '*'),
(95, 63, 'EasySocial Followers', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_followers', 1, 1, '', 0, '*'),
(96, 64, 'EasySocial Friends', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_friends', 1, 1, '', 0, '*'),
(97, 65, 'EasySocial Group Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_group_menu', 1, 1, '', 0, '*'),
(98, 66, 'EasySocial Groups', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_groups', 1, 1, '', 0, '*'),
(99, 67, 'EasySocial Group Categories', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_groups_categories', 1, 1, '', 0, '*'),
(100, 68, 'EasySocial Leader Board', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_leaderboard', 1, 1, '', 0, '*'),
(101, 69, 'EasySocial Log Box', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_logbox', 1, 1, '', 0, '*'),
(102, 70, 'EasySocial Login', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_login', 1, 1, '', 0, '*'),
(103, 71, 'EasySocial Menu', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_menu', 1, 1, '', 0, '*'),
(104, 72, 'EasySocial Notifications', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_notifications', 1, 1, '', 0, '*'),
(105, 73, 'EasySocial OAuth Login', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_oauth', 1, 1, '', 0, '*'),
(106, 74, 'EasySocial Recent Photos', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_photos', 1, 1, '', 0, '*'),
(107, 75, 'EasySocial Profile Completeness', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_profile_completeness', 1, 1, '', 0, '*'),
(108, 76, 'EasySocial Quick Post', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_quickpost', 1, 1, '', 0, '*'),
(109, 77, 'EasySocial Recent Polls', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_recentpolls', 1, 1, '', 0, '*'),
(110, 78, 'EasySocial Quick Registration', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_register', 1, 1, '', 0, '*'),
(111, 79, 'EasySocial Registration Requester', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_registration_requester', 1, 1, '', 0, '*'),
(112, 80, 'EasySocial Search', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_search', 1, 1, '', 0, '*'),
(113, 81, 'EasySocial Stream', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_stream', 1, 1, '', 0, '*'),
(114, 82, 'EasySocial Toolbar', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_toolbar', 1, 1, '', 0, '*'),
(115, 83, 'EasySocial Users', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_users', 1, 1, '', 0, '*'),
(116, 84, 'EasySocial Videos Module', '', '', 0, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'mod_easysocial_videos', 1, 1, '', 0, '*'),
(117, 85, 'Online Users', '', '', 1, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_users', 1, 1, '{"filter":"online","total":"5","ordering":"name","direction":"asc"}', 0, '*'),
(118, 86, 'Recent Users', '', '', 2, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_users', 1, 1, '{"filter":"recent","total":"5","ordering":"registerDate","direction":"desc"}', 0, '*'),
(119, 87, 'Recent Albums', '', '', 3, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_albums', 1, 1, '', 0, '*'),
(120, 88, 'Leaderboard', '', '', 4, 'es-dashboard-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_leaderboard', 1, 1, '{"total":"5"}', 0, '*'),
(121, 89, 'Dating Search', '', '', 1, 'es-users-sidebar-bottom', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_easysocial_dating_search', 1, 1, '{"searchname":"1","searchgender":"1","searchage":"1","searchdistance":"1"}', 0, '*');

-- --------------------------------------------------------

--
-- Table structure for table `j_modules_menu`
--

CREATE TABLE IF NOT EXISTS `j_modules_menu` (
  `moduleid` int(11) NOT NULL DEFAULT '0',
  `menuid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`moduleid`,`menuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_modules_menu`
--

INSERT INTO `j_modules_menu` (`moduleid`, `menuid`) VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 0),
(6, 0),
(7, 0),
(8, 0),
(9, 0),
(10, 0),
(12, 0),
(13, 0),
(14, 0),
(15, 0),
(16, 0),
(17, 0),
(79, 0),
(86, 0),
(87, 0),
(88, 0),
(89, 0),
(90, 0),
(91, 0),
(92, 0),
(93, 0),
(94, 0),
(95, 0),
(96, 0),
(97, 0),
(98, 0),
(99, 0),
(100, 0),
(101, 0),
(102, 0),
(103, 0),
(104, 0),
(105, 0),
(106, 0),
(107, 0),
(108, 0),
(109, 0),
(110, 0),
(111, 0),
(112, 0),
(113, 0),
(114, 0),
(115, 0),
(116, 0),
(117, 121),
(118, 121),
(119, 121),
(120, 121),
(121, 121);

-- --------------------------------------------------------

--
-- Table structure for table `j_newsfeeds`
--

CREATE TABLE IF NOT EXISTS `j_newsfeeds` (
  `catid` int(11) NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `link` varchar(200) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `numarticles` int(10) unsigned NOT NULL DEFAULT '1',
  `cache_time` int(10) unsigned NOT NULL DEFAULT '3600',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rtl` tinyint(4) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `images` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_overrider`
--

CREATE TABLE IF NOT EXISTS `j_overrider` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_postinstall_messages`
--

CREATE TABLE IF NOT EXISTS `j_postinstall_messages` (
  `postinstall_message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension_id` bigint(20) NOT NULL DEFAULT '700' COMMENT 'FK to j_extensions',
  `title_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for the title',
  `description_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'Lang key for description',
  `action_key` varchar(255) NOT NULL DEFAULT '',
  `language_extension` varchar(255) NOT NULL DEFAULT 'com_postinstall' COMMENT 'Extension holding lang keys',
  `language_client_id` tinyint(3) NOT NULL DEFAULT '1',
  `type` varchar(10) NOT NULL DEFAULT 'link' COMMENT 'Message type - message, link, action',
  `action_file` varchar(255) DEFAULT '' COMMENT 'RAD URI to the PHP file containing action method',
  `action` varchar(255) DEFAULT '' COMMENT 'Action method name or URL',
  `condition_file` varchar(255) DEFAULT NULL COMMENT 'RAD URI to file holding display condition method',
  `condition_method` varchar(255) DEFAULT NULL COMMENT 'Display condition method, must return boolean',
  `version_introduced` varchar(50) NOT NULL DEFAULT '3.2.0' COMMENT 'Version when this message was introduced',
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`postinstall_message_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `j_postinstall_messages`
--

INSERT INTO `j_postinstall_messages` (`postinstall_message_id`, `extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`) VALUES
(1, 700, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION', 'plg_twofactorauth_totp', 1, 'action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_condition', '3.2.0', 1),
(2, 700, 'COM_CPANEL_WELCOME_BEGINNERS_TITLE', 'COM_CPANEL_WELCOME_BEGINNERS_MESSAGE', '', 'com_cpanel', 1, 'message', '', '', '', '', '3.2.0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_redirect_links`
--

CREATE TABLE IF NOT EXISTS `j_redirect_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_url` varchar(255) NOT NULL,
  `new_url` varchar(255) DEFAULT NULL,
  `referer` varchar(150) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(4) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `header` smallint(3) NOT NULL DEFAULT '301',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_link_old` (`old_url`),
  KEY `idx_link_modifed` (`modified_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_schemas`
--

CREATE TABLE IF NOT EXISTS `j_schemas` (
  `extension_id` int(11) NOT NULL,
  `version_id` varchar(20) NOT NULL,
  PRIMARY KEY (`extension_id`,`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_session`
--

CREATE TABLE IF NOT EXISTS `j_session` (
  `session_id` varchar(200) NOT NULL DEFAULT '',
  `client_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `guest` tinyint(4) unsigned DEFAULT '1',
  `time` varchar(14) DEFAULT '',
  `data` mediumtext,
  `userid` int(11) DEFAULT '0',
  `username` varchar(150) DEFAULT '',
  PRIMARY KEY (`session_id`),
  KEY `userid` (`userid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_session`
--

INSERT INTO `j_session` (`session_id`, `client_id`, `guest`, `time`, `data`, `userid`, `username`) VALUES
('59bccir99f5csfo70qb4ni8vo1', 0, 1, '1455919401', 'joomla|s:4504:"TzoyNDoiSm9vbWxhXFJlZ2lzdHJ5XFJlZ2lzdHJ5IjoyOntzOjc6IgAqAGRhdGEiO086ODoic3RkQ2xhc3MiOjI6e3M6OToiX19kZWZhdWx0IjtPOjg6InN0ZENsYXNzIjozMDp7czo3OiJzZXNzaW9uIjtPOjg6InN0ZENsYXNzIjo0OntzOjc6ImNvdW50ZXIiO2k6MztzOjU6InRpbWVyIjtPOjg6InN0ZENsYXNzIjozOntzOjU6InN0YXJ0IjtpOjE0NTU5MTkzOTU7czo0OiJsYXN0IjtpOjE0NTU5MTkzOTg7czozOiJub3ciO2k6MTQ1NTkxOTQwMDt9czo2OiJjbGllbnQiO086ODoic3RkQ2xhc3MiOjE6e3M6OToiZm9yd2FyZGVkIjtzOjEwOiIzMy4zMy4zMy4xIjt9czo1OiJ0b2tlbiI7czozMjoiZGMxZTUzZWVmZDcwNjQ3MGI3ZmZiZjNlYjBlM2NlYjAiO31zOjg6InJlZ2lzdHJ5IjtPOjI0OiJKb29tbGFcUmVnaXN0cnlcUmVnaXN0cnkiOjI6e3M6NzoiACoAZGF0YSI7Tzo4OiJzdGRDbGFzcyI6MTp7czoxNDoiY29tX2Vhc3lzb2NpYWwiO086ODoic3RkQ2xhc3MiOjE6e3M6NToidXNlcnMiO086ODoic3RkQ2xhc3MiOjE6e3M6MTA6ImxpbWl0c3RhcnQiO047fX19czo5OiJzZXBhcmF0b3IiO3M6MToiLiI7fXM6NDoidXNlciI7Tzo1OiJKVXNlciI6MjY6e3M6OToiACoAaXNSb290IjtiOjA7czoyOiJpZCI7aTowO3M6NDoibmFtZSI7TjtzOjg6InVzZXJuYW1lIjtOO3M6NToiZW1haWwiO047czo4OiJwYXNzd29yZCI7TjtzOjE0OiJwYXNzd29yZF9jbGVhciI7czowOiIiO3M6NToiYmxvY2siO047czo5OiJzZW5kRW1haWwiO2k6MDtzOjEyOiJyZWdpc3RlckRhdGUiO047czoxMzoibGFzdHZpc2l0RGF0ZSI7TjtzOjEwOiJhY3RpdmF0aW9uIjtOO3M6NjoicGFyYW1zIjtOO3M6NjoiZ3JvdXBzIjthOjE6e2k6MDtzOjE6IjkiO31zOjU6Imd1ZXN0IjtpOjE7czoxMzoibGFzdFJlc2V0VGltZSI7TjtzOjEwOiJyZXNldENvdW50IjtOO3M6MTI6InJlcXVpcmVSZXNldCI7TjtzOjEwOiIAKgBfcGFyYW1zIjtPOjI0OiJKb29tbGFcUmVnaXN0cnlcUmVnaXN0cnkiOjI6e3M6NzoiACoAZGF0YSI7Tzo4OiJzdGRDbGFzcyI6MDp7fXM6OToic2VwYXJhdG9yIjtzOjE6Ii4iO31zOjE0OiIAKgBfYXV0aEdyb3VwcyI7YToyOntpOjA7aToxO2k6MTtpOjk7fXM6MTQ6IgAqAF9hdXRoTGV2ZWxzIjthOjM6e2k6MDtpOjE7aToxO2k6MTtpOjI7aTo1O31zOjE1OiIAKgBfYXV0aEFjdGlvbnMiO047czoxMjoiACoAX2Vycm9yTXNnIjtOO3M6MTM6IgAqAHVzZXJIZWxwZXIiO086MTg6IkpVc2VyV3JhcHBlckhlbHBlciI6MDp7fXM6MTA6IgAqAF9lcnJvcnMiO2E6MDp7fXM6MzoiYWlkIjtpOjA7fXM6MjM6ImdhbnRyeS1jdXJyZW50LXRlbXBsYXRlIjtzOjExOiJqZl9jb25uZWN0byI7czo0OToiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtbmFtZSI7TjtzOjU0OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1tZW51LXR5cGUiO047czo1NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtbGF5b3V0LW1vZGUiO047czo2MzoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfcHJlbG9hZGVyLWltYWdlIjtOO3M6NTQ6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWxvZ28tdHlwZSI7TjtzOjYyOiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1sb2dvLWN1c3RvbS1pbWFnZSI7TjtzOjU3OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfYmciO047czo2MToiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX2hlYWRlciI7TjtzOjY0OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfc2xpZGVzaG93IjtOO3M6NjU6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19icmVhZGNydW1iIjtOO3M6NzI6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19icmVhZGNydW1iX2JvcmRlciI7TjtzOjY0OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfZm9vdGVyX2JnIjtOO3M6NjY6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NvbG9yc19mb290ZXJfdGV4dCI7TjtzOjY2OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jb2xvcnNfZm9vdGVyX2xpbmsiO047czo1OToiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY29sb3JzX21haW4iO047czo2MzoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZWJfVUNfTWFpbkNvbG9yIjtOO3M6NjY6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2ViX1VDX1Rvb2xiYXJUb3BHUiI7TjtzOjY5OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lYl9VQ19Ub29sYmFyQm90dG9tR1IiO047czo2MzoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZXNfVUNfTWFpbkNvbG9yIjtOO3M6NjY6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2VzX1VDX1Rvb2xiYXJUb3BHUiI7TjtzOjY5OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9lc19VQ19Ub29sYmFyQm90dG9tR1IiO047czo2MzoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfZXNfVUNfQnV0dG9uUmVkIjtOO3M6NjU6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2VzX1VDX0J1dHRvbkdyZWVuIjtOO3M6NjM6ImpmX2Nvbm5lY3RvLTc3NjFiNTI3OGVjMjk1MzY0YzFlYmU1ZmI5ZjdkZjBmLWpmX2NiX1VDX01haW5Db2xvciI7TjtzOjY2OiJqZl9jb25uZWN0by03NzYxYjUyNzhlYzI5NTM2NGMxZWJlNWZiOWY3ZGYwZi1qZl9jYl9VQ19NZW51YmFyQ29sb3IiO047czo2NjoiamZfY29ubmVjdG8tNzc2MWI1Mjc4ZWMyOTUzNjRjMWViZTVmYjlmN2RmMGYtamZfY2JfVUNfVGVtcGxhdGVCb2R5IjtOO31zOjE2OiJfX2NvbV9lYXN5c29jaWFsIjtPOjg6InN0ZENsYXNzIjoyOntzOjEwOiJlYXN5c29jaWFsIjtPOjg6InN0ZENsYXNzIjoxOntzOjg6ImNhbGxiYWNrIjtOO31zOjg6Im1lc3NhZ2VzIjtOO319czo5OiJzZXBhcmF0b3IiO3M6MToiLiI7fQ==";', 0, ''),
('s986on3uj7vhg4ddblbj2rlck5', 1, 0, '1455919395', 'joomla|s:2532:"TzoyNDoiSm9vbWxhXFJlZ2lzdHJ5XFJlZ2lzdHJ5IjoyOntzOjc6IgAqAGRhdGEiO086ODoic3RkQ2xhc3MiOjE6e3M6OToiX19kZWZhdWx0IjtPOjg6InN0ZENsYXNzIjozOntzOjc6InNlc3Npb24iO086ODoic3RkQ2xhc3MiOjQ6e3M6NzoiY291bnRlciI7aTo0O3M6NToidGltZXIiO086ODoic3RkQ2xhc3MiOjM6e3M6NToic3RhcnQiO2k6MTQ1NTkxOTM5MDtzOjQ6Imxhc3QiO2k6MTQ1NTkxOTM5MjtzOjM6Im5vdyI7aToxNDU1OTE5Mzk1O31zOjY6ImNsaWVudCI7Tzo4OiJzdGRDbGFzcyI6MTp7czo5OiJmb3J3YXJkZWQiO3M6MTA6IjMzLjMzLjMzLjEiO31zOjU6InRva2VuIjtzOjMyOiI4YTBkNjQ1MjM2YjljZDliOGYyZjg4MDA5MTg2MGNlZiI7fXM6ODoicmVnaXN0cnkiO086MjQ6Ikpvb21sYVxSZWdpc3RyeVxSZWdpc3RyeSI6Mjp7czo3OiIAKgBkYXRhIjtPOjg6InN0ZENsYXNzIjoyOntzOjExOiJhcHBsaWNhdGlvbiI7Tzo4OiJzdGRDbGFzcyI6MTp7czo0OiJsYW5nIjtzOjU6ImVuLUdCIjt9czoxMzoiY29tX2luc3RhbGxlciI7Tzo4OiJzdGRDbGFzcyI6Mjp7czo3OiJtZXNzYWdlIjtzOjA6IiI7czoxNzoiZXh0ZW5zaW9uX21lc3NhZ2UiO3M6MDoiIjt9fXM6OToic2VwYXJhdG9yIjtzOjE6Ii4iO31zOjQ6InVzZXIiO086NToiSlVzZXIiOjI4OntzOjk6IgAqAGlzUm9vdCI7YjoxO3M6MjoiaWQiO3M6MzoiOTUxIjtzOjQ6Im5hbWUiO3M6MTA6IlN1cGVyIFVzZXIiO3M6ODoidXNlcm5hbWUiO3M6NToiYWRtaW4iO3M6NToiZW1haWwiO3M6MTc6ImFkbWluQGV4YW1wbGUuY29tIjtzOjg6InBhc3N3b3JkIjtzOjYwOiIkMnkkMTAkczJmbS5EdE1GRXIzNUtHWDB6Tnl3T0llU2g3NzlFU01PRkxkM2taVzZBdlZGa0xGekxETk8iO3M6MTQ6InBhc3N3b3JkX2NsZWFyIjtzOjA6IiI7czo1OiJibG9jayI7czoxOiIwIjtzOjk6InNlbmRFbWFpbCI7czoxOiIxIjtzOjEyOiJyZWdpc3RlckRhdGUiO3M6MTk6IjIwMTMtMDctMjQgMDk6MDc6NDMiO3M6MTM6Imxhc3R2aXNpdERhdGUiO3M6MTk6IjIwMTYtMDItMTggMDk6NDg6MDMiO3M6MTA6ImFjdGl2YXRpb24iO3M6MToiMCI7czo2OiJwYXJhbXMiO3M6OTI6InsiYWRtaW5fc3R5bGUiOiIiLCJhZG1pbl9sYW5ndWFnZSI6IiIsImxhbmd1YWdlIjoiIiwiZWRpdG9yIjoiIiwiaGVscHNpdGUiOiIiLCJ0aW1lem9uZSI6IiJ9IjtzOjY6Imdyb3VwcyI7YToxOntpOjg7czoxOiI4Ijt9czo1OiJndWVzdCI7aTowO3M6MTM6Imxhc3RSZXNldFRpbWUiO3M6MTk6IjAwMDAtMDAtMDAgMDA6MDA6MDAiO3M6MTA6InJlc2V0Q291bnQiO3M6MToiMCI7czoxMjoicmVxdWlyZVJlc2V0IjtzOjE6IjAiO3M6MTA6IgAqAF9wYXJhbXMiO086MjQ6Ikpvb21sYVxSZWdpc3RyeVxSZWdpc3RyeSI6Mjp7czo3OiIAKgBkYXRhIjtPOjg6InN0ZENsYXNzIjo2OntzOjExOiJhZG1pbl9zdHlsZSI7czowOiIiO3M6MTQ6ImFkbWluX2xhbmd1YWdlIjtzOjA6IiI7czo4OiJsYW5ndWFnZSI7czowOiIiO3M6NjoiZWRpdG9yIjtzOjA6IiI7czo4OiJoZWxwc2l0ZSI7czowOiIiO3M6ODoidGltZXpvbmUiO3M6MDoiIjt9czo5OiJzZXBhcmF0b3IiO3M6MToiLiI7fXM6MTQ6IgAqAF9hdXRoR3JvdXBzIjthOjI6e2k6MDtpOjE7aToxO2k6ODt9czoxNDoiACoAX2F1dGhMZXZlbHMiO2E6NTp7aTowO2k6MTtpOjE7aToxO2k6MjtpOjI7aTozO2k6MztpOjQ7aTo2O31zOjE1OiIAKgBfYXV0aEFjdGlvbnMiO047czoxMjoiACoAX2Vycm9yTXNnIjtOO3M6MTM6IgAqAHVzZXJIZWxwZXIiO086MTg6IkpVc2VyV3JhcHBlckhlbHBlciI6MDp7fXM6MTA6IgAqAF9lcnJvcnMiO2E6MDp7fXM6MzoiYWlkIjtpOjA7czo2OiJvdHBLZXkiO3M6MDoiIjtzOjQ6Im90ZXAiO3M6MDoiIjt9fX1zOjk6InNlcGFyYXRvciI7czoxOiIuIjt9";', 951, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_access`
--

CREATE TABLE IF NOT EXISTS `j_social_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_access_logs`
--

CREATE TABLE IF NOT EXISTS `j_social_access_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rule` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `utype` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rule` (`rule`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_utypes` (`uid`,`utype`),
  KEY `idx_created` (`created`),
  KEY `idx_useritems` (`rule`,`user_id`,`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_access_rules`
--

CREATE TABLE IF NOT EXISTS `j_social_access_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `element` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `extension` (`extension`),
  KEY `element` (`element`),
  KEY `group` (`group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=60 ;

--
-- Dumping data for table `j_social_access_rules`
--

INSERT INTO `j_social_access_rules` (`id`, `name`, `title`, `description`, `extension`, `element`, `group`, `state`, `created`, `params`) VALUES
(1, 'comments.add', 'Add Comments', 'Allow this usergroup to add comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 02:06:13', '{}'),
(2, 'comments.read', 'Read Comments', 'Allow this usergroup to read comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 02:06:13', '{}'),
(3, 'comments.report', 'Report Comments', 'Allow this usergroup to report comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 02:06:13', '{}'),
(4, 'comments.edit', 'Edit Comments', 'Allow this usergroup to edit all comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 02:06:13', '{"default":false}'),
(5, 'comments.editown', 'Edit Own Comments', 'Allow this usergroup to edit own authored comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 02:06:13', '{}'),
(6, 'comments.delete', 'Delete Comments', 'Allow this usergroup to delete all comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 02:06:13', '{"default":false}'),
(7, 'comments.deleteown', 'Delete Own Comments', 'Allow this usergroup to delete own authored comments.', 'com_easysocial', 'comments', 'user', 1, '2016-02-18 02:06:13', '{}'),
(8, 'conversations.create', 'Start New Conversation', 'If enabled, user''s in this group will be allowed to start a new conversation.', 'com_easysocial', 'conversations', 'user', 1, '2016-02-18 02:06:14', '{}'),
(9, 'conversations.invite', 'Invite Users to Group Conversation', 'If enabled, user''s in this group will be allowed to invite other users into an existing group conversation.', 'com_easysocial', 'conversations', 'user', 1, '2016-02-18 02:06:14', '{}'),
(10, 'conversations.send.daily', 'Daily Send Limit', 'Configure the maximum number of messages user can send a day.', 'com_easysocial', 'conversations', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"messages","default":0}'),
(11, 'events.create', 'Create Event', 'Specify if user is allowed to create event.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 02:06:14', '{}'),
(12, 'events.limit', 'Event Limit', 'Specify the maximum number of events that can be created.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 02:06:14', '{"type":"limitinterval","default":{"value":0,"interval":0}}'),
(13, 'events.join', 'Total Events Allowed to Attend', 'Specify the total events that a user is allowed to attend.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(14, 'events.moderate', 'Moderate Event Creation', 'Specify if event created by user should be moderated by admin.', 'com_easysocial', 'events', 'user', 1, '2016-02-18 02:06:14', '{}'),
(15, 'photos.enabled', 'Allow Photo Albums', 'Specify if photo albums is allowed for events in this category.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 02:06:14', '{}'),
(16, 'photos.max', 'Total Photos Allowed', 'Specify the total photos allowed for events in this category.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_EVENTS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(17, 'photos.maxdaily', 'Total Daily Uploads Allowed', 'Specify the total photos allowed to be uploaded per day for events in this category.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_EVENTS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(18, 'photos.maxsize', 'Maximum File Size Allowed', 'Specify the maximum file sized allowed for photos uploaded.', 'com_easysocial', 'photos', 'event', 1, '2016-02-18 02:06:14', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(19, 'files.enabled', 'Allow File Sharing', 'Specify if the file sharing feature is allowed for events in this category.', 'com_easysocial', 'files', 'event', 1, '2016-02-18 02:06:14', '{"type":"boolean","default":true}'),
(20, 'files.max', 'Total Files Allowed', 'Specify the total files allowed for events in this category.', 'com_easysocial', 'files', 'event', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_EVENTS_FILES_TOTAL_FILES_SUFFIX","default":0}'),
(21, 'files.maxsize', 'Maximum File Size Allowed', 'Specify the maximum file sized allowed for files uploaded.', 'com_easysocial', 'files', 'event', 1, '2016-02-18 02:06:14', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(22, 'videos.create', 'Allow Videos', 'COM_EASYSOCIAL_ACCESS_EVENTS_VIDEOS_ENABLED_TIPS', 'com_easysocial', 'videos', 'event', 1, '2016-02-18 02:06:14', '{"type":"boolean","default":1}'),
(23, 'videos.total', 'Total Videos Allowed', 'COM_EASYSOCIAL_ACCESS_EVENTS_VIDEOS_TOTAL_VIDEOS_ALLOWED_TIPS', 'com_easysocial', 'videos', 'event', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(24, 'videos.maxsize', 'Maximum File Size For Video Uploads', 'Determines the maximum file size allowed for video uploads.', 'com_easysocial', 'videos', 'event', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"MB","default":0}'),
(25, 'events.groupevent', 'Allow Group Event', 'Specify if groups in this category should allow event creation.', 'com_easysocial', 'events', 'group', 1, '2016-02-18 02:06:14', '{"type":"boolean","default":true}'),
(26, 'files.upload', 'File Uploads', 'This access determines if the user is allowed to upload files in the story form.', 'com_easysocial', 'files', 'user', 1, '2016-02-18 02:06:14', '{}'),
(27, 'friends.list.enabled', 'Create Friends List', 'Determines if the user in this group is allowed to create friend lists.', 'com_easysocial', 'friends', 'user', 1, '2016-02-18 02:06:14', '{}'),
(28, 'friends.list.limit', 'Friends List', 'Set the number of friend lists users from this group is allowed to create.', 'com_easysocial', 'friends', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(29, 'friends.limit', 'Friends Limit', 'Define the amount of friends a user from this group is allowed to have. Includes sent friend’s request.', 'com_easysocial', 'friends', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(30, 'groups.create', 'Create Groups', 'Determines if the user has access to create new groups.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 02:06:14', '{}'),
(31, 'groups.limit', 'Group Limit', 'Determines the total number of groups the user is allowed to create on the site.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 02:06:14', '{"type":"limitinterval","default":{"value":0,"interval":0}}'),
(32, 'groups.join', 'Total Groups Allowed To Join', 'Defines the total number of groups the user is allowed to join.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(33, 'groups.moderate', 'Moderate Group Creation', 'Determines if the group should be moderated first before being published.', 'com_easysocial', 'groups', 'user', 1, '2016-02-18 02:06:14', '{}'),
(34, 'videos.create', 'Allow Videos', 'COM_EASYSOCIAL_ACCESS_GROUPS_VIDEOS_ENABLED_DESC', 'com_easysocial', 'videos', 'group', 1, '2016-02-18 02:06:14', '{"type":"boolean","default":1}'),
(35, 'videos.total', 'Total Videos Allowed', 'This access rule determines how many videos a group is allowed to have.', 'com_easysocial', 'videos', 'group', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(36, 'videos.maxsize', 'Maximum File Size For Video Uploads', 'Determines the maximum file size allowed for video uploads.', 'com_easysocial', 'videos', 'group', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"MB","default":0}'),
(37, 'photos.enabled', 'Allow Photo Albums', 'Determines if photo albums are allowed for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 02:06:14', '{}'),
(38, 'photos.max', 'Total Photos Allowed', 'Determines the total number of photos allowed for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_GROUPS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(39, 'photos.maxdaily', 'Total Daily Uploads Allowed', 'Determines the total number of photos allowed to be uploaded daily for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_GROUPS_PHOTOS_TOTAL_PHOTOS_SUFFIX","default":0}'),
(40, 'photos.maxsize', 'Maximum File Size Allowed', 'Determines the maximum file size allowed for groups in this category.', 'com_easysocial', 'photos', 'group', 1, '2016-02-18 02:06:14', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(41, 'files.enabled', 'Allow Filesharing', 'Determines if the file sharing should be enabled for groups in this category.', 'com_easysocial', 'files', 'group', 1, '2016-02-18 02:06:14', '{"type":"boolean","default":true}'),
(42, 'files.max', 'Total Files Allowed', 'Set the total number of files allowed to be uploaded via the file manager.', 'com_easysocial', 'files', 'group', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"COM_EASYSOCIAL_ACCESS_GROUPS_FILES_TOTAL_FILES_SUFFIX","default":0}'),
(43, 'files.maxsize', 'Maximum File Size Allowed', 'Set the upload limit for files that are uploaded via the file manager.', 'com_easysocial', 'files', 'group', 1, '2016-02-18 02:06:14', '{"type":"text","class":"form-control input-sm input-short text-center","suffix":"MB","default":"8"}'),
(44, 'albums.create', 'Create albums', 'This option determines if the user is allowed to create a new album.', 'com_easysocial', 'albums', 'user', 1, '2016-02-18 02:06:14', '{}'),
(45, 'albums.total', 'Maximum albums', 'Set the number of photo albums a user is allowed to create on the site.', 'com_easysocial', 'albums', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(46, 'photos.uploader.maxsize', 'Maximum file size', 'Set the maximum file size of photo upload.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 02:06:14', '{"type":"text","class":"form-control input-sm input-short text-center","default":5,"suffix":"MB"}'),
(47, 'photos.uploader.max', 'Maximum photos upload', 'Set the number of photos upload that are allowed by user.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"photos","default":500}'),
(48, 'photos.uploader.maxdaily', 'Maximum photo upload per day', 'Set the number of daily photos upload that are allowed by user.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"photos","default":20}'),
(49, 'photos.totalfiles.limit', 'Maximum Storage Size', 'Here, you may define a maximum storage size allowed for users in this profile type.', 'com_easysocial', 'photos', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","suffix":"MB","default":0}'),
(50, 'polls.create', 'Create Poll', 'This option determines if the user is allowed to create poll.', 'com_easysocial', 'polls', 'user', 1, '2016-02-18 02:06:14', '{}'),
(51, 'polls.vote', 'Vote on Polls', 'This option determines if the user is allowed to vote on polls.', 'com_easysocial', 'polls', 'user', 1, '2016-02-18 02:06:14', '{}'),
(52, 'reports.submit', 'Submit Reports', 'By enabling this option, users in this group will be allowed to submit a report.', 'com_easysocial', 'reports', 'user', 1, '2016-02-18 02:06:14', '{}'),
(53, 'reports.limit', 'Total Reports', 'Specify the total number of reports this user may submit.', 'com_easysocial', 'reports', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(54, 'stream.hide', 'Allow hiding single stream item', 'Allow user to hide stream item from frontend.', 'com_easysocial', 'stream', 'user', 1, '2016-02-18 02:06:14', '{}'),
(55, 'stream.delete', 'Allow delete single stream item', 'Allow user to delete stream item from frontend.', 'com_easysocial', 'stream', 'user', 1, '2016-02-18 02:06:14', '{"default":false}'),
(56, 'videos.create', 'Video Creation', 'Determines if user is allowed to add new videos in the videos section', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 02:06:14', '{"type":"boolean"}'),
(57, 'videos.total', 'Total Videos', 'Determines the total number of videos a user can create on the site.', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":0}'),
(58, 'videos.maxsize', 'Maximum File Size For Video Uploads', 'Determines the maximum file size allowed for video uploads.', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 02:06:14', '{"type":"text","class":"form-control input-sm input-short text-center","default":8,"suffix":"MB"}'),
(59, 'videos.daily', 'Total Daily Video Creation', 'Determines the total number of videos a user can create in a day.', 'com_easysocial', 'videos', 'user', 1, '2016-02-18 02:06:14', '{"type":"limit","default":50,"suffix":"videos"}');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_albums`
--

CREATE TABLE IF NOT EXISTS `j_social_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cover_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `caption` text NOT NULL,
  `created` datetime NOT NULL,
  `assigned_date` datetime NOT NULL,
  `ordering` tinyint(3) NOT NULL,
  `params` text NOT NULL,
  `core` tinyint(3) NOT NULL,
  `hits` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `user_id` (`user_id`),
  KEY `idx_albums_user_assigned` (`uid`,`type`,`assigned_date`),
  KEY `idx_core` (`core`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_albums_favourite`
--

CREATE TABLE IF NOT EXISTS `j_social_albums_favourite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_alert`
--

CREATE TABLE IF NOT EXISTS `j_social_alert` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(255) NOT NULL,
  `element` varchar(255) NOT NULL,
  `rule` varchar(255) NOT NULL,
  `email` int(1) NOT NULL DEFAULT '1',
  `system` int(1) NOT NULL DEFAULT '1',
  `core` int(1) NOT NULL DEFAULT '0',
  `app` int(1) NOT NULL DEFAULT '0',
  `field` tinyint(3) NOT NULL DEFAULT '0',
  `group` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_alert_field` (`field`),
  KEY `idx_alert_published` (`published`),
  KEY `idx_alert_element` (`element`),
  KEY `idx_alert_rule` (`rule`),
  KEY `idx_alert_published_field` (`published`,`field`),
  KEY `idx_alert_isfield` (`published`,`field`,`element`(64),`rule`(64))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

--
-- Dumping data for table `j_social_alert`
--

INSERT INTO `j_social_alert` (`id`, `extension`, `element`, `rule`, `email`, `system`, `core`, `app`, `field`, `group`, `created`, `published`) VALUES
(1, '', 'relationship', 'request', 1, 1, 0, 0, 1, 'user', '2016-02-18 02:06:05', 1),
(2, '', 'relationship', 'approve', 1, 1, 0, 0, 1, 'user', '2016-02-18 02:06:05', 1),
(3, '', 'relationship', 'reject', 1, 1, 0, 0, 1, 'user', '2016-02-18 02:06:05', 1),
(4, '', 'albums', 'comment.add', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(5, '', 'albums', 'favourite', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(6, '', 'badges', 'unlocked', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(7, '', 'broadcast', 'notify', 0, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(8, '', 'comments', 'item', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(9, '', 'comments', 'involved', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(10, '', 'comments', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(11, '', 'comments', 'like', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(12, '', 'conversations', 'reply', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(13, '', 'conversations', 'invite', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(14, '', 'conversations', 'new', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(15, '', 'conversations', 'invited', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(16, '', 'conversations', 'leave', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(17, '', 'events', 'discussion.create', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(18, '', 'events', 'discussion.reply', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(19, '', 'events', 'discussion.answered', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(20, '', 'events', 'discussion.locked', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(21, '', 'events', 'guest.makeadmin', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(22, '', 'events', 'guest.revokeadmin', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(23, '', 'events', 'guest.reject', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(24, '', 'events', 'guest.approve', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(25, '', 'events', 'guest.remove', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(26, '', 'events', 'guest.going', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(27, '', 'events', 'guest.maybe', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(28, '', 'events', 'guest.notgoing', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(29, '', 'events', 'guest.request', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(30, '', 'events', 'guest.withdraw', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(31, '', 'events', 'guest.invited', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(32, '', 'events', 'news', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(33, '', 'events', 'task.created', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(34, '', 'events', 'task.completed', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(35, '', 'events', 'milestone.created', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(36, '', 'events', 'updates', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(37, '', 'events', 'video.create', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(38, '', 'friends', 'approve', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(39, '', 'friends', 'request', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(40, '', 'groups', 'invited', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(41, '', 'groups', 'approved', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(42, '', 'groups', 'joined', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(43, '', 'groups', 'user.rejected', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(44, '', 'groups', 'updates', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(45, '', 'groups', 'promoted', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(46, '', 'groups', 'requested', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(47, '', 'groups', 'leave', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(48, '', 'groups', 'news', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(49, '', 'groups', 'discussion.create', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(50, '', 'groups', 'discussion.reply', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(51, '', 'groups', 'milestone.create', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(52, '', 'groups', 'task.create', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(53, '', 'groups', 'task.completed', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(54, '', 'groups', 'user.removed', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(55, '', 'groups', 'video.create', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(56, '', 'likes', 'item', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(57, '', 'likes', 'involved', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(58, '', 'photos', 'comment.add', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(59, '', 'photos', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(60, '', 'photos', 'likes', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(61, '', 'profile', 'followed', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(62, '', 'profile', 'story', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(63, '', 'repost', 'item', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(64, '', 'stream', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(65, '', 'videos', 'comment.add', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(66, '', 'videos', 'tagged', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1),
(67, '', 'videos', 'likes', 1, 1, 1, 0, 0, '', '2016-02-18 02:06:18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_alert_map`
--

CREATE TABLE IF NOT EXISTS `j_social_alert_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT '0',
  `alert_id` bigint(20) NOT NULL,
  `email` int(1) DEFAULT '1',
  `system` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_alertmap_alertid` (`alert_id`),
  KEY `idx_alertmap_userid` (`user_id`),
  KEY `idx_alertmap_alertuser` (`alert_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps`
--

CREATE TABLE IF NOT EXISTS `j_social_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `core` tinyint(4) NOT NULL DEFAULT '0',
  `system` tinyint(3) NOT NULL DEFAULT '0',
  `unique` tinyint(4) NOT NULL DEFAULT '0',
  `default` tinyint(3) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'It could be widgets,fields or applications',
  `element` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `ordering` int(11) NOT NULL,
  `params` text NOT NULL,
  `version` varchar(255) NOT NULL,
  `widget` tinyint(3) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `installable` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `type` (`type`),
  KEY `core` (`core`),
  KEY `idx_default_widget` (`state`,`group`,`widget`,`default`),
  KEY `idx_group` (`group`),
  KEY `idx_apps_element` (`element`),
  KEY `idx_apps_type_group` (`type`(64),`group`(64))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=169 ;

--
-- Dumping data for table `j_social_apps`
--

INSERT INTO `j_social_apps` (`id`, `core`, `system`, `unique`, `default`, `type`, `element`, `group`, `title`, `alias`, `state`, `created`, `ordering`, `params`, `version`, `widget`, `visible`, `installable`) VALUES
(1, 0, 0, 0, 0, 'apps', 'adsense', 'user', 'Adsense', 'adsense', 1, '2016-02-18 02:05:54', 0, '{}', '1.0.205', 1, 1, 1),
(2, 1, 0, 0, 0, 'apps', 'albums', 'user', 'Albums', 'albums', 1, '2016-02-18 02:05:54', 0, '{}', '1.0.206', 1, 1, 1),
(3, 1, 1, 0, 0, 'apps', 'apps', 'user', 'Apps', 'apps', 1, '2016-02-18 02:05:54', 0, '{}', '1.0.205', 0, 1, 0),
(4, 0, 0, 0, 0, 'apps', 'article', 'user', 'Article', 'article', 1, '2016-02-18 02:05:54', 0, '{}', '1.2.204', 0, 1, 1),
(5, 1, 1, 0, 0, 'apps', 'badges', 'user', 'Badges', 'badges', 1, '2016-02-18 02:05:54', 0, '{}', '1.0.205', 0, 1, 0),
(6, 0, 0, 0, 0, 'apps', 'birthday', 'user', 'Upcoming Birthday', 'upcoming-birthday', 1, '2016-02-18 02:05:54', 0, '{}', '1.0.205', 1, 1, 1),
(7, 1, 1, 0, 0, 'apps', 'broadcast', 'user', 'Broadcast', 'broadcast', 1, '2016-02-18 02:05:54', 0, '{}', '1.0.341', 0, 1, 0),
(8, 0, 0, 0, 0, 'apps', 'calendar', 'user', 'Calendar', 'calendar', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.206', 1, 1, 1),
(9, 1, 1, 0, 0, 'apps', 'events', 'user', 'Events', 'events', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.241', 1, 1, 0),
(10, 1, 1, 0, 0, 'apps', 'facebook', 'user', 'Facebook', 'facebook', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.205', 0, 1, 0),
(11, 0, 0, 0, 0, 'apps', 'feeds', 'user', 'Feeds', 'feeds', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.206', 0, 1, 1),
(12, 0, 0, 0, 0, 'apps', 'files', 'user', 'Files', 'files', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.205', 0, 1, 0),
(13, 1, 0, 0, 0, 'apps', 'followers', 'user', 'Followers', 'followers', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.205', 1, 1, 0),
(14, 1, 0, 0, 0, 'apps', 'friends', 'user', 'Friends', 'friends', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.205', 1, 1, 1),
(15, 1, 1, 0, 0, 'apps', 'groups', 'user', 'Groups', 'groups', 1, '2016-02-18 02:05:55', 0, '{}', '1.0.205', 1, 1, 0),
(16, 0, 0, 0, 0, 'apps', 'k2', 'user', 'K2', 'k2', 1, '2016-02-18 02:05:56', 0, '{}', '1.2.207', 0, 1, 1),
(17, 0, 0, 0, 0, 'apps', 'kunena', 'user', 'Kunena Forum', 'kunena-forum', 1, '2016-02-18 02:05:56', 0, '{}', '1.4.33', 1, 1, 1),
(18, 1, 0, 0, 0, 'apps', 'links', 'user', 'Links', 'links', 1, '2016-02-18 02:05:56', 0, '{}', '1.0.205', 0, 1, 0),
(19, 0, 0, 0, 0, 'apps', 'mtree', 'user', 'Mosets Tree', 'mosets-tree', 1, '2016-02-18 02:05:56', 0, '{}', '1.3.65', 1, 1, 1),
(20, 0, 0, 0, 0, 'apps', 'notes', 'user', 'Notes', 'notes', 1, '2016-02-18 02:05:56', 0, '{}', '1.0.205', 0, 1, 1),
(21, 1, 0, 0, 0, 'apps', 'photos', 'user', 'Photos', 'photos', 1, '2016-02-18 02:05:56', 0, '{}', '1.0.205', 1, 1, 1),
(22, 1, 0, 0, 0, 'apps', 'polls', 'user', 'Polls', 'polls', 1, '2016-02-18 02:05:56', 0, '{}', '1.0.46', 0, 1, 0),
(23, 1, 1, 0, 0, 'apps', 'profiles', 'user', 'Profiles', 'profiles', 1, '2016-02-18 02:05:56', 0, '{}', '1.0.205', 0, 1, 0),
(24, 0, 0, 0, 0, 'apps', 'relationship', 'user', 'Relationship', 'relationship', 1, '2016-02-18 02:05:56', 0, '{}', '1.0.204', 0, 1, 1),
(25, 1, 1, 0, 0, 'apps', 'shares', 'user', 'Shares', 'shares', 1, '2016-02-18 02:05:57', 0, '{}', '1.0.205', 0, 1, 0),
(26, 1, 1, 0, 0, 'apps', 'story', 'user', 'Story', 'story', 1, '2016-02-18 02:05:57', 0, '{}', '1.0.205', 0, 1, 0),
(27, 0, 0, 0, 0, 'apps', 'tasks', 'user', 'Tasks', 'tasks', 1, '2016-02-18 02:05:57', 0, '{}', '1.2.383', 0, 1, 1),
(28, 1, 0, 0, 0, 'apps', 'users', 'user', 'Users', 'users', 1, '2016-02-18 02:05:57', 0, '{}', '1.0.384', 1, 1, 0),
(29, 1, 0, 0, 0, 'apps', 'videos', 'user', 'Videos', 'videos', 1, '2016-02-18 02:05:57', 0, '{}', '1.4.45', 1, 1, 1),
(30, 0, 0, 0, 0, 'apps', 'discussions', 'group', 'Discussions', 'discussions', 1, '2016-02-18 02:05:58', 0, '{}', '1.0.204', 1, 1, 1),
(31, 1, 0, 0, 0, 'apps', 'events', 'group', 'Events', 'events', 1, '2016-02-18 02:05:58', 0, '{}', '1.0.241', 1, 1, 0),
(32, 0, 0, 0, 0, 'apps', 'feeds', 'group', 'Feeds', 'feeds', 1, '2016-02-18 02:05:58', 0, '{}', '1.3.241', 0, 1, 1),
(33, 1, 0, 0, 0, 'apps', 'files', 'group', 'Files', 'files', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 1, 1, 1),
(34, 1, 1, 0, 0, 'apps', 'groups', 'group', 'Groups', 'groups', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 0, 1, 1),
(35, 1, 0, 0, 0, 'apps', 'links', 'group', 'Links', 'links', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 0, 1, 0),
(36, 0, 0, 0, 0, 'apps', 'members', 'group', 'Members', 'members', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 1, 1, 1),
(37, 1, 0, 0, 0, 'apps', 'news', 'group', 'News', 'news', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 1, 1, 0),
(38, 1, 0, 0, 0, 'apps', 'photos', 'group', 'Photos', 'photos', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 1, 1, 1),
(39, 1, 0, 0, 0, 'apps', 'polls', 'group', 'Polls', 'polls', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.46', 0, 1, 0),
(40, 1, 1, 0, 0, 'apps', 'shares', 'group', 'Shares', 'shares', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 0, 1, 0),
(41, 1, 1, 0, 0, 'apps', 'story', 'group', 'Story', 'story', 1, '2016-02-18 02:05:59', 0, '{}', '1.0.384', 0, 1, 0),
(42, 1, 0, 0, 0, 'apps', 'tasks', 'group', 'Tasks', 'tasks', 1, '2016-02-18 02:06:00', 0, '{}', '1.0.384', 1, 1, 1),
(43, 1, 0, 0, 0, 'apps', 'videos', 'group', 'Videos', 'videos', 1, '2016-02-18 02:06:00', 0, '{}', '1.4.45', 1, 1, 0),
(44, 0, 0, 0, 0, 'apps', 'discussions', 'event', 'Discussions', 'discussions', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 1, 1, 1),
(45, 1, 1, 0, 0, 'apps', 'events', 'event', 'Events', 'events', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 0, 1, 1),
(46, 1, 0, 0, 0, 'apps', 'files', 'event', 'Files', 'files', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 1, 1, 1),
(47, 1, 0, 0, 0, 'apps', 'guests', 'event', 'Guests', 'guests', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 1, 1, 1),
(48, 1, 0, 0, 0, 'apps', 'links', 'event', 'Links', 'links', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 0, 1, 0),
(49, 1, 0, 0, 0, 'apps', 'news', 'event', 'News', 'news', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 1, 1, 0),
(50, 1, 0, 0, 0, 'apps', 'photos', 'event', 'Photos', 'photos', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 1, 1, 1),
(51, 1, 0, 0, 0, 'apps', 'polls', 'event', 'Polls', 'polls', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.46', 0, 1, 0),
(52, 1, 1, 0, 0, 'apps', 'shares', 'event', 'Shares', 'shares', 1, '2016-02-18 02:06:01', 0, '{}', '1.0.241', 0, 1, 0),
(53, 1, 1, 0, 0, 'apps', 'story', 'event', 'Story', 'story', 1, '2016-02-18 02:06:02', 0, '{}', '1.0.241', 0, 1, 0),
(54, 1, 0, 0, 0, 'apps', 'tasks', 'event', 'Tasks', 'tasks', 1, '2016-02-18 02:06:02', 0, '{}', '1.0.241', 1, 1, 1),
(55, 1, 0, 0, 0, 'apps', 'videos', 'event', 'Videos', 'videos', 1, '2016-02-18 02:06:02', 0, '{}', '1.4.45', 1, 1, 0),
(56, 0, 0, 1, 0, 'fields', 'acymailing', 'user', 'Acymailing', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.0', 0, 1, 0),
(57, 0, 0, 0, 0, 'fields', 'address', 'user', 'Address', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.0.0', 0, 1, 0),
(58, 0, 0, 0, 0, 'fields', 'autocomplete', 'user', 'Autocomplete', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.4.0', 0, 1, 0),
(59, 1, 0, 1, 0, 'fields', 'avatar', 'user', 'Avatar', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.0.0', 0, 1, 0),
(60, 0, 0, 1, 0, 'fields', 'birthday', 'user', 'Birthday', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.0.0', 0, 1, 0),
(61, 0, 0, 0, 0, 'fields', 'boolean', 'user', 'Boolean', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.0.0', 0, 1, 0),
(62, 0, 0, 0, 0, 'fields', 'checkbox', 'user', 'Checkboxes', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.0.0', 0, 1, 0),
(63, 0, 0, 1, 0, 'fields', 'country', 'user', 'Country', '', 1, '2016-02-18 02:06:03', 0, '{}', '1.0.0', 0, 1, 0),
(64, 1, 0, 1, 0, 'fields', 'cover', 'user', 'Cover', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(65, 0, 0, 0, 0, 'fields', 'currency', 'user', 'Currency', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(66, 0, 0, 0, 0, 'fields', 'datetime', 'user', 'Datetime', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(67, 0, 0, 0, 0, 'fields', 'dropdown', 'user', 'Dropdown List', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(68, 0, 0, 0, 0, 'fields', 'email', 'user', 'Email', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(69, 0, 0, 0, 0, 'fields', 'file', 'user', 'File', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(70, 0, 0, 1, 0, 'fields', 'gender', 'user', 'Gender', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0', 0, 1, 0),
(71, 0, 0, 0, 0, 'fields', 'header', 'user', 'Header', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(72, 0, 0, 1, 0, 'fields', 'headline', 'user', 'Headline', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(73, 0, 0, 0, 0, 'fields', 'html', 'user', 'Html', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(74, 1, 0, 1, 0, 'fields', 'joomla_email', 'user', 'Joomla User Email', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(75, 1, 0, 1, 0, 'fields', 'joomla_fullname', 'user', 'Joomla User Fullname', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(76, 0, 0, 1, 0, 'fields', 'joomla_joindate', 'user', 'Joomla Joined Date', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(77, 0, 0, 1, 0, 'fields', 'joomla_language', 'user', 'Joomla Language', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(78, 0, 0, 1, 0, 'fields', 'joomla_lastlogin', 'user', 'Joomla Last Login Date', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(79, 1, 0, 1, 0, 'fields', 'joomla_password', 'user', 'Joomla User Password', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(80, 0, 0, 1, 0, 'fields', 'joomla_timezone', 'user', 'Joomla User Timezone', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(81, 0, 0, 1, 0, 'fields', 'joomla_twofactor', 'user', 'Joomla Two Factor', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(82, 0, 0, 1, 0, 'fields', 'joomla_user_editor', 'user', 'Joomla User Editor', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(83, 1, 0, 1, 0, 'fields', 'joomla_username', 'user', 'Joomla Username', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(84, 0, 0, 1, 0, 'fields', 'kunena_signature', 'user', 'Kunena Signature', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(85, 0, 0, 1, 0, 'fields', 'mailchimp', 'user', 'Mailchimp', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0', 0, 1, 0),
(86, 0, 0, 0, 0, 'fields', 'mollom', 'user', 'Mollom', '', 1, '2016-02-18 02:06:04', 0, '{}', '1.0.0', 0, 1, 0),
(87, 0, 0, 0, 0, 'fields', 'multidropdown', 'user', 'Multi Dropdown List', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(88, 0, 0, 0, 0, 'fields', 'multilist', 'user', 'Multilist', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(89, 0, 0, 0, 0, 'fields', 'multitextbox', 'user', 'Multi Textbox', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(90, 0, 0, 1, 0, 'fields', 'permalink', 'user', 'Permalink', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0', 0, 1, 0),
(91, 0, 0, 1, 0, 'fields', 'recaptcha', 'user', 'Recaptcha', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(92, 0, 0, 1, 0, 'fields', 'relationship', 'user', 'Relationship Status', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(93, 0, 0, 0, 0, 'fields', 'separator', 'user', 'Separator', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(94, 0, 0, 1, 0, 'fields', 'skype', 'user', 'Skype', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0', 0, 1, 0),
(95, 0, 0, 0, 0, 'fields', 'terms', 'user', 'Terms', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(96, 0, 0, 0, 0, 'fields', 'text', 'user', 'Text', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(97, 0, 0, 0, 0, 'fields', 'textarea', 'user', 'Textarea', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(98, 0, 0, 0, 0, 'fields', 'textbox', 'user', 'Textbox', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(99, 0, 0, 0, 0, 'fields', 'url', 'user', 'URL', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.0.0', 0, 1, 0),
(100, 0, 0, 0, 0, 'fields', 'vmvendor', 'user', 'Virtuemart Vendor Field', '', 1, '2016-02-18 02:06:05', 0, '{}', '1.4.0', 0, 1, 0),
(101, 0, 0, 0, 0, 'fields', 'address', 'group', 'Address', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(102, 1, 0, 1, 0, 'fields', 'avatar', 'group', 'Avatar', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(103, 0, 0, 0, 0, 'fields', 'boolean', 'group', 'Boolean', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(104, 0, 0, 0, 0, 'fields', 'checkbox', 'group', 'Checkboxes', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(105, 1, 0, 1, 0, 'fields', 'cover', 'group', 'Cover', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(106, 0, 0, 0, 0, 'fields', 'datetime', 'group', 'Datetime', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(107, 1, 0, 1, 0, 'fields', 'description', 'group', 'Description', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(108, 1, 0, 1, 0, 'fields', 'discussions', 'group', 'Discussions', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(109, 0, 0, 0, 0, 'fields', 'dropdown', 'group', 'Dropdown List', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(110, 0, 0, 0, 0, 'fields', 'email', 'group', 'Email', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(111, 0, 0, 1, 0, 'fields', 'eventcreate', 'group', 'Event Create', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(112, 0, 0, 0, 0, 'fields', 'file', 'group', 'File', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(113, 0, 0, 0, 0, 'fields', 'header', 'group', 'Header', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(114, 0, 0, 0, 0, 'fields', 'headline', 'group', 'Headline', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(115, 0, 0, 0, 0, 'fields', 'html', 'group', 'Html', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(116, 0, 0, 1, 0, 'fields', 'moderation', 'group', 'Post Moderation', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.3.0', 0, 1, 0),
(117, 0, 0, 0, 0, 'fields', 'multidropdown', 'group', 'Multi Dropdown List', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(118, 0, 0, 0, 0, 'fields', 'multilist', 'group', 'Multilist', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(119, 0, 0, 0, 0, 'fields', 'multitextbox', 'group', 'Multi Textbox', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(120, 1, 0, 1, 0, 'fields', 'news', 'group', 'News', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(121, 0, 0, 1, 0, 'fields', 'permalink', 'group', 'Permalink', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0', 0, 1, 0),
(122, 0, 0, 1, 0, 'fields', 'permissions', 'group', 'Stream Permissions', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.3.0', 0, 1, 0),
(123, 1, 0, 1, 0, 'fields', 'photos', 'group', 'Photos', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(124, 0, 0, 0, 0, 'fields', 'separator', 'group', 'Separator', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(125, 0, 0, 0, 0, 'fields', 'text', 'group', 'Text', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(126, 0, 0, 0, 0, 'fields', 'textarea', 'group', 'Textarea', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(127, 0, 0, 0, 0, 'fields', 'textbox', 'group', 'Textbox', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(128, 1, 0, 1, 0, 'fields', 'title', 'group', 'Title', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(129, 1, 0, 1, 0, 'fields', 'type', 'group', 'Group Type', '', 1, '2016-02-18 02:06:06', 0, '{}', '1.0.0', 0, 1, 0),
(130, 0, 0, 0, 0, 'fields', 'url', 'group', 'URL', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(131, 1, 0, 1, 0, 'fields', 'videos', 'group', 'Videos', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.4.0', 0, 1, 0),
(132, 0, 0, 0, 0, 'fields', 'address', 'event', 'Address', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(133, 0, 0, 1, 0, 'fields', 'allday', 'event', 'All Day', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(134, 1, 0, 1, 0, 'fields', 'avatar', 'event', 'Avatar', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(135, 0, 0, 0, 0, 'fields', 'boolean', 'event', 'Boolean', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(136, 0, 0, 0, 0, 'fields', 'checkbox', 'event', 'Checkboxes', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(137, 1, 0, 1, 0, 'fields', 'configAllowMaybe', 'event', 'Config - Allow Maybe', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(138, 1, 0, 1, 0, 'fields', 'configNotGoingGuest', 'event', 'Config - Not Going Guest', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(139, 1, 0, 1, 0, 'fields', 'cover', 'event', 'Cover', '', 1, '2016-02-18 02:06:07', 0, '{}', '1.0.0', 0, 1, 0),
(140, 0, 0, 0, 0, 'fields', 'datetime', 'event', 'Datetime', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(141, 1, 0, 1, 0, 'fields', 'description', 'event', 'Description', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(142, 1, 0, 1, 0, 'fields', 'discussions', 'event', 'Discussions', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(143, 0, 0, 0, 0, 'fields', 'dropdown', 'event', 'Dropdown List', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(144, 0, 0, 0, 0, 'fields', 'email', 'event', 'Email', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(145, 0, 0, 0, 0, 'fields', 'file', 'event', 'File', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(146, 0, 0, 1, 0, 'fields', 'guestLimit', 'event', 'Guest Limit', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(147, 0, 0, 0, 0, 'fields', 'header', 'event', 'Header', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(148, 0, 0, 0, 0, 'fields', 'headline', 'event', 'Headline', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(149, 0, 0, 0, 0, 'fields', 'html', 'event', 'Html', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(150, 0, 0, 1, 0, 'fields', 'membertransfer', 'event', 'Group Member Transfer', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(151, 0, 0, 0, 0, 'fields', 'multidropdown', 'event', 'Multi Dropdown List', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(152, 0, 0, 0, 0, 'fields', 'multilist', 'event', 'Multilist', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(153, 0, 0, 0, 0, 'fields', 'multitextbox', 'event', 'Multi Textbox', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(154, 1, 0, 1, 0, 'fields', 'news', 'event', 'News', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(155, 0, 0, 1, 0, 'fields', 'ownerstate', 'event', 'Owner State', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(156, 0, 0, 1, 0, 'fields', 'permalink', 'event', 'Permalink', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0', 0, 1, 0),
(157, 1, 0, 1, 0, 'fields', 'photos', 'event', 'Photos', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(158, 0, 0, 1, 0, 'fields', 'recurring', 'event', 'Recurring', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(159, 0, 0, 0, 0, 'fields', 'separator', 'event', 'Separator', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(160, 1, 0, 1, 0, 'fields', 'startend', 'event', 'Event Start End', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(161, 0, 0, 0, 0, 'fields', 'text', 'event', 'Text', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(162, 0, 0, 0, 0, 'fields', 'textarea', 'event', 'Textarea', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(163, 0, 0, 0, 0, 'fields', 'textbox', 'event', 'Textbox', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(164, 1, 0, 1, 0, 'fields', 'title', 'event', 'Title', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(165, 1, 0, 1, 0, 'fields', 'type', 'event', 'Group Type', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(166, 0, 0, 1, 0, 'fields', 'upcomingreminder', 'event', 'Upcoming Event Reminder', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(167, 0, 0, 0, 0, 'fields', 'url', 'event', 'URL', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.0.0', 0, 1, 0),
(168, 1, 0, 1, 0, 'fields', 'videos', 'event', 'Videos', '', 1, '2016-02-18 02:06:08', 0, '{}', '1.4.0', 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps_calendar`
--

CREATE TABLE IF NOT EXISTS `j_social_apps_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `reminder` tinyint(3) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `all_day` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps_map`
--

CREATE TABLE IF NOT EXISTS `j_social_apps_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `app_id` int(11) NOT NULL,
  `position` varchar(50) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_uid_type` (`app_id`,`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_apps_views`
--

CREATE TABLE IF NOT EXISTS `j_social_apps_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `view` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_view` (`app_id`,`view`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;

--
-- Dumping data for table `j_social_apps_views`
--

INSERT INTO `j_social_apps_views` (`id`, `app_id`, `view`, `type`, `title`, `description`) VALUES
(23, 4, 'profile', 'embed', '', ''),
(24, 8, 'dashboard', 'canvas', '', ''),
(27, 11, 'profile', 'embed', '', ''),
(26, 11, 'dashboard', 'embed', '', ''),
(28, 16, 'profile', 'embed', '', ''),
(29, 17, 'profile', 'embed', '', ''),
(30, 19, 'profile', 'embed', '', ''),
(32, 20, 'profile', 'embed', '', ''),
(31, 20, 'dashboard', 'embed', '', ''),
(33, 27, 'dashboard', 'embed', '', ''),
(34, 30, 'groups', 'embed', '', ''),
(35, 32, 'groups', 'embed', '', ''),
(36, 33, 'groups', 'embed', '', ''),
(37, 36, 'groups', 'embed', '', ''),
(38, 37, 'groups', 'embed', '', ''),
(39, 42, 'groups', 'embed', '', ''),
(40, 44, 'events', 'embed', '', ''),
(41, 46, 'events', 'embed', '', ''),
(42, 47, 'events', 'embed', '', ''),
(43, 49, 'events', 'embed', '', ''),
(44, 54, 'events', 'embed', '', ''),
(25, 8, 'profile', 'canvas', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_avatars`
--

CREATE TABLE IF NOT EXISTS `j_social_avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary ID',
  `uid` int(11) NOT NULL COMMENT 'Node''s ID',
  `type` varchar(255) NOT NULL,
  `avatar_id` bigint(20) NOT NULL COMMENT 'If the node is using a default avatar, this field will be populated with an id.',
  `photo_id` int(11) NOT NULL COMMENT 'If the avatar is created from a photo, this field will be populated with the photo id.',
  `small` text NOT NULL,
  `medium` text NOT NULL,
  `square` text NOT NULL,
  `large` text NOT NULL,
  `modified` datetime NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `avatar_id` (`avatar_id`),
  KEY `photo_id` (`photo_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_uid_type` (`uid`,`type`),
  KEY `idx_storage_cron` (`storage`,`avatar_id`,`small`(64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_badges`
--

CREATE TABLE IF NOT EXISTS `j_social_badges` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `command` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `howto` text NOT NULL,
  `avatar` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `frequency` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `discuss_badges_alias` (`alias`),
  KEY `discuss_badges_published` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `j_social_badges`
--

INSERT INTO `j_social_badges` (`id`, `command`, `extension`, `title`, `alias`, `description`, `howto`, `avatar`, `created`, `state`, `frequency`) VALUES
(1, 'create.article', 'com_content', 'Publisher', 'publisher', 'Loves contributing articles.', 'To unlock this badge, you need to create up to 50 new articles.', 'media/com_easysocial/apps/user/article/assets/badges/publisher.png', '2016-02-18 02:05:54', 1, 50),
(2, 'update.article', 'com_content', 'Proof Reading', 'proof-reading', 'Great proof reading skills', 'To unlock this badge, you need to update 50 existing articles.', 'media/com_easysocial/apps/user/article/assets/badges/proof-reading.png', '2016-02-18 02:05:54', 1, 50),
(3, 'read.article', 'com_content', 'Great Reader', 'great-reader', 'Loves reading through articles.', 'To unlock this badge, you need to read up to 100 articles.', 'media/com_easysocial/apps/user/article/assets/badges/great-reader.png', '2016-02-18 02:05:54', 1, 100),
(4, 'apps.install', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_APPLICANT_TITLE', 'applicant', 'COM_EASYSOCIAL_BADGES_APPLICANT_DESC', 'COM_EASYSOCIAL_BADGES_APPLICANT_HOWTO', 'media/com_easysocial/badges/applicant.png', '2016-02-18 02:06:12', 1, 10),
(5, 'conversation.reply', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_CHATTER_TITLE', 'chatter', 'COM_EASYSOCIAL_BADGES_CHATTER_DESC', 'COM_EASYSOCIAL_BADGES_CHATTER_HOWTO', 'media/com_easysocial/badges/chatter.png', '2016-02-18 02:06:12', 1, 150),
(6, 'conversation.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_SOCIALIZER_TITLE', 'socializer', 'COM_EASYSOCIAL_BADGES_SOCIALIZER_DESC', 'COM_EASYSOCIAL_BADGES_SOCIALIZER_HOWTO', 'media/com_easysocial/badges/socializer.png', '2016-02-18 02:06:12', 1, 15),
(7, 'conversation.invite', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_GOSSIPER_TITLE', 'gossiper', 'COM_EASYSOCIAL_BADGES_GOSSIPER_DESC', 'COM_EASYSOCIAL_BADGES_GOSSIPER_HOWTO', 'media/com_easysocial/badges/gossiper.png', '2016-02-18 02:06:12', 1, 10),
(8, 'conversation.leave', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_LEAVER_TITLE', 'leaver', 'COM_EASYSOCIAL_BADGES_LEAVER_DESC', 'COM_EASYSOCIAL_BADGES_LEAVER_HOWTO', 'media/com_easysocial/badges/leaver.png', '2016-02-18 02:06:12', 1, 20),
(9, 'followers.follow', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FOLLOWER_TITLE', 'follower', 'COM_EASYSOCIAL_BADGES_FOLLOWER_DESC', 'COM_EASYSOCIAL_BADGES_FOLLOWER_HOWTO', 'media/com_easysocial/badges/follower.png', '2016-02-18 02:06:12', 1, 60),
(10, 'followers.followed', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_THOUGHT_LEADER_TITLE', 'thought-leader', 'COM_EASYSOCIAL_BADGES_THOUGHT_LEADER_DESC', 'COM_EASYSOCIAL_BADGES_THOUGHT_LEADER_HOWTO', 'media/com_easysocial/badges/thought-leader.png', '2016-02-18 02:06:12', 1, 60),
(11, 'friends.remove', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FRIEND_HATER_TITLE', 'friend-hater', 'COM_EASYSOCIAL_BADGES_FRIEND_HATER_DESC', 'COM_EASYSOCIAL_BADGES_FRIEND_HATER_HOWTO', 'media/com_easysocial/badges/friend-hater.png', '2016-02-18 02:06:12', 1, 10),
(12, 'friends.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FRIEND_SEEKER_TITLE', 'friend-seeker', 'COM_EASYSOCIAL_BADGES_FRIEND_SEEKER_DESC', 'COM_EASYSOCIAL_BADGES_FRIEND_SEEKER_HOWTO', 'media/com_easysocial/badges/friend-seeker.png', '2016-02-18 02:06:12', 1, 30),
(13, 'friends.list.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_FRIEND_ORGANIZER_TITLE', 'friend-organizer', 'COM_EASYSOCIAL_BADGES_FRIEND_ORGANIZER_DESC', 'COM_EASYSOCIAL_BADGES_FRIEND_ORGANIZER_HOWTO', 'media/com_easysocial/badges/friend-organizer.png', '2016-02-18 02:06:12', 1, 10),
(14, 'photos.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_PHOTOGENIC_TITLE', 'photogenic', 'COM_EASYSOCIAL_BADGES_PHOTOGENIC_DESC', 'COM_EASYSOCIAL_BADGES_PHOTOGENIC_HOWTO', 'media/com_easysocial/badges/photogenic.png', '2016-02-18 02:06:12', 1, 30),
(15, 'photos.browse', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_JOURNALIST_TITLE', 'journalist', 'COM_EASYSOCIAL_BADGES_JOURNALIST_DESC', 'COM_EASYSOCIAL_BADGES_JOURNALIST_HOWTO', 'media/com_easysocial/badges/journalist.png', '2016-02-18 02:06:12', 1, 150),
(16, 'photos.tag', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_PHOTO_TAGGER_TITLE', 'photo-tagger', 'COM_EASYSOCIAL_BADGES_PHOTO_TAGGER_DESC', 'COM_EASYSOCIAL_BADGES_PHOTO_TAGGER_HOWTO', 'media/com_easysocial/badges/photo-tagger.png', '2016-02-18 02:06:12', 1, 50),
(17, 'photos.superstar', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_SUPER_STAR_TITLE', 'super-star', 'COM_EASYSOCIAL_BADGES_SUPER_STAR_DESC', 'COM_EASYSOCIAL_BADGES_SUPER_STAR_HOWTO', 'media/com_easysocial/badges/super-star.png', '2016-02-18 02:06:12', 1, 50),
(18, 'points.achieve', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_POINTS_ACHIEVER', 'points-achiever', 'COM_EASYSOCIAL_BADGES_POINTS_ACHIEVER_DESC', 'COM_EASYSOCIAL_BADGES_POINTS_ACHIEVER_HOWTO', 'media/com_easysocial/badges/points-achiever.png', '2016-02-18 02:06:12', 1, 100),
(19, 'profile.view', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_EXPLORER_TITLE', 'explorer', 'COM_EASYSOCIAL_BADGES_EXPLORER_DESC', 'COM_EASYSOCIAL_BADGES_EXPLORER_HOWTO', 'media/com_easysocial/badges/explorer.png', '2016-02-18 02:06:12', 1, 50),
(20, 'registration.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_NEWBIE_TITLE', 'newbie', 'COM_EASYSOCIAL_BADGES_NEWBIE_DESC', 'COM_EASYSOCIAL_BADGES_NEWBIE_HOWTO', 'media/com_easysocial/badges/newbie.png', '2016-02-18 02:06:12', 1, 1),
(21, 'reports.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_REPORTER_TITLE', 'reporter', 'COM_EASYSOCIAL_BADGES_REPORTER_DESC', 'COM_EASYSOCIAL_BADGES_REPORTER_HOWTO', 'media/com_easysocial/badges/reporter.png', '2016-02-18 02:06:12', 1, 20),
(22, 'search.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_SEARCH_ENGINE_TITLE', 'search-engine', 'COM_EASYSOCIAL_BADGES_SEARCH_ENGINE_DESC', 'COM_EASYSOCIAL_BADGES_SEARCH_ENGINE_HOWTO', 'media/com_easysocial/badges/search-engine.png', '2016-02-18 02:06:12', 1, 50),
(23, 'story.create', 'com_easysocial', 'COM_EASYSOCIAL_BADGES_STORY_TELLER_TITLE', 'story-teller', 'COM_EASYSOCIAL_BADGES_STORY_TELLER_DESC', 'COM_EASYSOCIAL_BADGES_STORY_TELLER_HOWTO', 'media/com_easysocial/badges/story-teller.png', '2016-02-18 02:06:12', 1, 30);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_badges_history`
--

CREATE TABLE IF NOT EXISTS `j_social_badges_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achieved` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_badges_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_badges_maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `custom_message` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `badge_id` (`badge_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_block_users`
--

CREATE TABLE IF NOT EXISTS `j_social_block_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`target_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_targetid` (`target_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_bookmarks`
--

CREATE TABLE IF NOT EXISTS `j_social_bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `uid` int(11) NOT NULL COMMENT 'The bookmarked item id',
  `type` varchar(255) NOT NULL COMMENT 'The bookmarked type',
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'The owner of the bookmarked item',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `user_id` (`user_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_user_utype` (`uid`,`type`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_broadcasts`
--

CREATE TABLE IF NOT EXISTS `j_social_broadcasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `link` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_broadcast` (`target_id`,`target_type`,`state`,`created`),
  KEY `idx_created` (`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `cluster_type` varchar(255) NOT NULL,
  `creator_type` varchar(255) NOT NULL,
  `creator_uid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `featured` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  `hits` int(11) NOT NULL,
  `type` tinyint(3) NOT NULL,
  `key` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `parent_type` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL COMMENT 'The longitude value of the event for proximity search purposes',
  `latitude` varchar(255) NOT NULL COMMENT 'The latitude value of the event for proximity search purposes',
  `address` text NOT NULL COMMENT 'The full address value of the event for displaying purposes',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `idx_state` (`state`),
  KEY `idx_clustertype` (`cluster_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_categories`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'The creator of the category',
  `ordering` tinyint(3) NOT NULL,
  `site_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `j_social_clusters_categories`
--

INSERT INTO `j_social_clusters_categories` (`id`, `type`, `title`, `alias`, `description`, `created`, `state`, `uid`, `ordering`, `site_id`) VALUES
(1, 'group', 'General', 'general', 'General groups', '2016-02-18 02:06:15', 1, 951, 1, NULL),
(2, 'group', 'Automobile', 'automobile', 'Cars, motors, vehicle and all things related to automobile.', '2016-02-18 02:06:16', 1, 951, 2, NULL),
(3, 'group', 'Technology', 'technology', 'Multimedia, IT, and all the tech', '2016-02-18 02:06:16', 1, 951, 3, NULL),
(4, 'group', 'Business', 'business', 'Let''s talk business', '2016-02-18 02:06:16', 1, 951, 4, NULL),
(5, 'group', 'Music', 'music', 'Pop, rock, electronic and all', '2016-02-18 02:06:16', 1, 951, 5, NULL),
(6, 'event', 'General', 'general-2', 'General events', '2016-02-18 02:06:17', 1, 951, 1, NULL),
(7, 'event', 'Meeting', 'meeting', 'Weekly meeting events.', '2016-02-18 02:06:17', 1, 951, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_categories_access`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_categories_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'create',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`profile_id`),
  KEY `category_id_2` (`category_id`,`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_news`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `hits` int(11) NOT NULL,
  `comments` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`,`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_clusters_nodes`
--

CREATE TABLE IF NOT EXISTS `j_social_clusters_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(4) NOT NULL,
  `owner` tinyint(3) NOT NULL,
  `admin` tinyint(3) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `reminder_sent` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`,`state`),
  KEY `invited_by` (`invited_by`),
  KEY `idx_clusters_nodes_uid` (`uid`),
  KEY `idx_clusters_nodes_user` (`uid`,`state`,`created`),
  KEY `idx_members` (`cluster_id`,`type`,`state`),
  KEY `idx_reminder_sent` (`reminder_sent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_comments`
--

CREATE TABLE IF NOT EXISTS `j_social_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `comment` text NOT NULL,
  `stream_id` bigint(20) DEFAULT '0',
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `depth` bigint(10) DEFAULT '0',
  `parent` bigint(20) DEFAULT '0',
  `child` bigint(20) DEFAULT '0',
  `lft` bigint(20) DEFAULT '0',
  `rgt` bigint(20) DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `social_comments_uid` (`uid`),
  KEY `social_comments_type` (`element`),
  KEY `social_comments_createdby` (`created_by`),
  KEY `social_comments_content_type` (`element`,`uid`),
  KEY `social_comments_content_type_by` (`element`,`uid`,`created_by`),
  KEY `social_comments_content_parent` (`element`,`uid`,`parent`),
  KEY `idx_comment_batch` (`stream_id`,`element`,`uid`),
  KEY `idx_comment_stream_id` (`stream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_config`
--

CREATE TABLE IF NOT EXISTS `j_social_config` (
  `type` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `value_binary` blob,
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_social_config`
--

INSERT INTO `j_social_config` (`type`, `value`, `value_binary`) VALUES
('dbversion', '1.4.7', NULL),
('site', '{"alerts":{"paths":["admin","site","apps","fields","plugins","modules"]},"general":{"key":"889d190f117f5a134eca72a369f57d48","ajaxindex":true,"environment":"static","mode":"compressed","inline":false,"super":false,"profiler":false,"logger":false,"site":{"loginemail":true,"login":"","logout":"","lockdown":{"enabled":false,"registration":false},"twofactor":false},"url":{"purge":true,"interval":"1"},"cron":{"secure":false,"key":"","limit":20},"location":{"language":"en","proximity":{"unit":"mile"}},"cdn":{"enabled":false,"url":"","passive":false}},"groups":{"enabled":true,"stream":{"create":true},"invite":{"nonfriends":false},"item":{"display":"timeline"}},"antispam":{"recaptcha":{"public":"","private":""},"akismet":{"key":"dff980f9f600"},"mollom":{"public":"","private":"","servers":""}},"conversations":{"enabled":1,"limit":20,"akismet":1,"archiving":1,"editor":"","locations":1,"multiple":1,"nonfriend":false,"pagination":{"enabled":true,"limit":20,"toolbarlimit":5},"attachments":{"enabled":1,"types":["txt","jpg","png","gif","zip","pdf"],"maxsize":3,"storage":"media\\/com_easysocial\\/uploads\\/conversations"},"layout":{"intro":200}},"email":{"html":1,"replyto":"","heading":{"company":"Stack Ideas Sdn Bhd"},"sender":{"email":"","name":""}},"links":{"cache":{"images":false,"location":"\\/media\\/com_easysocial\\/cache\\/links"}},"notifications":{"general":{"pagination":10},"broadcast":{"popup":true,"interval":15,"sticky":true,"period":8},"system":{"autoread":false,"enabled":true,"polling":30},"friends":{"enabled":true,"polling":30},"conversation":{"enabled":true,"polling":30}},"reports":{"enabled":true,"automation":true,"threshold":30,"maxip":5,"guests":false,"features":{"stream":true,"user":true,"comments":true},"notifications":{"moderators":true,"custom":false,"emails":""}},"storage":{"avatar":"joomla","photos":"joomla","files":"joomla","links":"joomla","videos":"joomla","joomla":{"limit":10},"amazon":{"access":"","secret":"","bucket":"","ssl":true,"limit":10,"delete":true,"region":"","class":""}},"photos":{"enabled":true,"quality":80,"downloads":true,"original":true,"import":{"exif":true},"popup":{"default":true},"storage":{"container":"\\/media\\/com_easysocial\\/photos"},"uploader":{"maxsize":"32"},"pagination":{"photo":10,"album":10},"exif":["aperture","iso","exposure","copyright","camera"],"layout":{"size":"large","mode":"cover","pattern":"tile","threshold":128,"ratio":"4x3"}},"avatars":{"storage":{"container":"\\/media\\/com_easysocial\\/avatars","default":"defaults","defaults":{"profiles":"profiles"},"user":"users","group":"group","event":"event","clusters":"clusters","profiles":"profiles"},"default":{"user":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/user\\/square.png"},"profiles":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/profiles\\/square.png"},"group":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/group\\/square.png"},"event":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/event\\/square.png"},"clusterscategory":{"small":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/small.png","medium":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/medium.png","large":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/large.png","square":"\\/media\\/com_easysocial\\/defaults\\/avatars\\/clusterscategory\\/square.png"}}},"connector":{"client":"curl"},"covers":{"storage":{"container":"\\/media\\/com_easysocial\\/covers","default":"defaults","defaults":{"profiles":"profiles"},"user":"users","group":"group","event":"event","profiles":"profiles","clusters":"clusters"},"default":{"user":{"default":"\\/media\\/com_easysocial\\/defaults\\/covers\\/user\\/default.jpg"},"group":{"default":"\\/media\\/com_easysocial\\/defaults\\/covers\\/group\\/default.jpg"},"event":{"default":"\\/media\\/com_easysocial\\/defaults\\/covers\\/event\\/default.jpg"}}},"files":{"storage":{"container":"\\/media\\/com_easysocial\\/files","fields":{"container":"fields","user":"user"},"conversations":{"container":"conversations"},"group":{"container":"groups"},"user":{"container":"users"},"comments":{"container":"comments"}}},"friends":{"invites":{"enabled":true},"list":{"enabled":true,"showEmpty":true},"stream":{"create":1}},"lists":{"display":{"limit":5},"stream":{"create":1}},"apps":{"browser":true,"tnc":{"required":true,"message":"COM_EASYSOCIAL_APPS_TNC"}},"layout":{"dashboard":{"apps":{"limit":5},"lists":{"limit":5}},"leaderboard":{"limit":20},"profile":{"apps":{"limit":15}},"spotlight":[],"groups":[]},"oauth":{"facebook":{"app":"","secret":"","pull":true,"push":true,"opengraph":{"enabled":true},"jfbconnect":{"enabled":false},"registration":{"enabled":false,"type":"simplified","profile":"1","avatar":true,"cover":true,"timeline":true,"totalTimeline":1},"username":"email"},"twitter":{"app":"","secret":"","registration":{"enabled":true,"avatar":true,"tweets":true,"totalTweets":true}},"myspace":{"app":"","secret":""}},"leaderboard":{"listings":{"admin":false}},"badges":{"enabled":true,"paths":["admin","site","apps","fields","plugins","modules"]},"followers":{"enabled":true},"points":{"enabled":true,"history":{"limit":60},"paths":["admin","site","apps","fields","plugins","modules"]},"profiles":{"stream":{"create":1,"update":1}},"registrations":{"enabled":1,"emailasusername":0,"change.selection":1,"email":{"password":true},"profiles":{"avatar":true,"showUsers":true,"usersCount":20,"showType":true},"steps":{"progress":1,"heading":1},"mini":{"mode":"quick","profile":"default"}},"stream":{"translations":{"bing":false,"explicit":false,"bingid":"","bingsecret":""},"aggregation":{"enabled":1,"duration":15},"rss":{"enabled":true},"timestamp":{"enabled":true},"bookmarks":{"enabled":true},"content":{"nofollow":false,"truncate":false,"truncatelength":250},"archive":{"enabled":false,"duration":6},"pin":{"enabled":true},"comments":{"enabled":true,"guestview":false},"follow":{"enabled":true},"likes":{"enabled":true},"repost":{"enabled":true},"sharing":{"enabled":true},"pagination":{"style":"loadmore","autoload":true,"pagelimit":10,"sort":"modified"},"story":{"mentions":true,"moods":true,"location":true,"entertosubmit":false},"updates":{"enabled":true,"interval":30},"actions":["likes","comments","repost"]},"activity":{"pagination":{"max":5,"limit":10}},"story":{"friends_enabled":1},"location":{"coords":"40.702147,-74.015794","provider":"maps","foursquare":{"clientid":"","clientsecret":""},"places":{"api":""},"maps":{"api":""}},"toolbar":{"display":true},"theme":{"site":"","site_base":"wireframe","admin":"default","admin_base":"default","apps":"default","fields":"default","compiler":{"mode":"off","use_absolute_uri":0,"allow_template_override":1}},"uploader":{"storage":{"container":"\\/media\\/com_easysocial\\/tmp"}},"users":{"change_username":1,"display":{"profiletype":true},"displayName":"username","aliasName":"username","deleteLogic":"unpublish","simpleUrl":false,"avatarWebcam":true,"blocking":{"enabled":true},"reminder":{"enabled":false,"duration":30},"dashboard":{"start":"me"},"stream":{"login":1,"logout":1,"friend":1,"following":1,"profile":1},"listings":{"admin":false,"sorting":"latest","esadadmin":true},"indexer":{"name":"realname","email":false,"privacy":true},"profile":{"display":"timeline"}},"sharing":{"enabled":1,"vendors":{"email":1,"facebook":1,"twitter":1,"google":1,"live":1,"linkedin":1,"myspace":1,"vk":1,"stumbleupon":1,"digg":1,"tumblr":1,"evernote":1,"reddit":1,"delicious":1},"email":{"limit":10}},"access":{"paths":["admin","site","apps","fields","plugins","modules"]},"comments":{"reply":0,"maxlevel":3,"limit":5,"attachments":true,"resize":{"enabled":false,"width":"1024","height":"768"},"storage":"\\/media\\/com_easysocial\\/comments","enter":"submit","submit":1,"smileys":true},"user":{"completeprofile":{"required":false,"strict":true,"action":"info"}},"events":{"stream":{"create":true},"startofweek":1,"enabled":true,"recurringlimit":50,"ical":true,"invite":{"nonfriends":false},"timeformat":"12h","listing":{"includefeatured":false,"includegroup":false},"item":{"display":"timeline"}},"video":{"enabled":true,"uploads":false,"embeds":true,"ffmpeg":"\\/opt\\/local\\/bin\\/ffmpeg","autoencode":true,"audiobitrate":"64k","size":"720","storage":{"container":"\\/media\\/com_easysocial\\/videos"},"layout":{"item":{"recent":true,"total":5,"hits":true,"duration":true,"details":true,"tags":true}}}}', NULL),
('scriptversion', '1.4.7', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `lastreplied` datetime NOT NULL,
  `type` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations_message`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations_message` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) NOT NULL,
  `type` varchar(200) NOT NULL,
  `message` text,
  `created` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations_message_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations_message_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `conversation_id` bigint(20) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `isread` tinyint(1) NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - publish, 2 - archive, 3 - trash',
  PRIMARY KEY (`id`),
  KEY `node_id` (`user_id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `message_id` (`message_id`),
  KEY `idx_user_conversation` (`user_id`,`state`,`conversation_id`,`message_id`),
  KEY `idx_user_conversation_isread` (`user_id`,`state`,`isread`,`conversation_id`,`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_conversations_participants`
--

CREATE TABLE IF NOT EXISTS `j_social_conversations_participants` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `state` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `social_conversation_maps_conversation_id` (`conversation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_covers`
--

CREATE TABLE IF NOT EXISTS `j_social_covers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary ID',
  `uid` int(11) NOT NULL COMMENT 'Node''s ID',
  `type` varchar(255) NOT NULL,
  `photo_id` int(13) NOT NULL COMMENT 'If the node is using a default avatar, this field will be populated with an id.',
  `cover_id` int(11) NOT NULL,
  `x` varchar(255) NOT NULL,
  `y` varchar(255) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `avatar_id` (`photo_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_uid_type` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_default_avatars`
--

CREATE TABLE IF NOT EXISTS `j_social_default_avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `large` text NOT NULL,
  `medium` text NOT NULL,
  `small` text NOT NULL,
  `square` text NOT NULL,
  `default` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `system` (`default`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_default_covers`
--

CREATE TABLE IF NOT EXISTS `j_social_default_covers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `large` text NOT NULL,
  `small` text NOT NULL,
  `default` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `system` (`default`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_discussions`
--

CREATE TABLE IF NOT EXISTS `j_social_discussions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'This determines if this is a reply to a discussion. If it is a reply, it should contain the parent''s id here.',
  `uid` int(11) NOT NULL COMMENT 'The unique id this discussion is associated to. For example, if it is associated with a group, it should store the group''s id.',
  `type` varchar(255) NOT NULL COMMENT 'The unique type this discussion is associated to. For example, if it is associated with a group, it should store the type as group',
  `answer_id` int(11) NOT NULL COMMENT 'This is only applicable to main question. This should contain the reference to the discussion that is an answer.',
  `last_reply_id` int(11) NOT NULL COMMENT 'Determines the last reply for the discussion',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0' COMMENT 'Stores the total views for this discussion.',
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `last_replied` datetime NOT NULL COMMENT 'Stores the last replied date time.',
  `votes` int(11) NOT NULL COMMENT 'Determines the vote count for this discussion.',
  `total_replies` int(11) NOT NULL DEFAULT '0' COMMENT 'This is to denormalize the reply count of a discussion.',
  `lock` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Determines if this discussion is locked',
  `params` text NOT NULL COMMENT 'Stores additional raw parameters for the discussion that doesn''t need to be indexed',
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `uid_2` (`uid`,`type`),
  KEY `id` (`id`,`parent_id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_discussions_files`
--

CREATE TABLE IF NOT EXISTS `j_social_discussions_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`,`discussion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_events_meta`
--

CREATE TABLE IF NOT EXISTS `j_social_events_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL COMMENT 'The event cluster id',
  `start` datetime NOT NULL COMMENT 'The start datetime of the event',
  `end` datetime NOT NULL COMMENT 'The end datetime of the event',
  `timezone` varchar(255) NOT NULL COMMENT 'The optional timezone of the event for datetime calculation',
  `all_day` tinyint(3) NOT NULL COMMENT 'Flag if this event is an all day event',
  `group_id` int(11) NOT NULL COMMENT 'The group id if this is a group event',
  `reminder` int(11) DEFAULT '0' COMMENT 'the number of days before the actual event date',
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`),
  KEY `idx_reminder` (`reminder`),
  KEY `idx_upcoming_reminder` (`reminder`,`start`),
  KEY `idx_start` (`start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_feeds`
--

CREATE TABLE IF NOT EXISTS `j_social_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `url` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields`
--

CREATE TABLE IF NOT EXISTS `j_social_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_key` text NOT NULL,
  `app_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `display_title` tinyint(3) NOT NULL,
  `description` text NOT NULL,
  `display_description` tinyint(3) NOT NULL,
  `default` text NOT NULL,
  `validation` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `searchable` tinyint(4) NOT NULL DEFAULT '1',
  `required` tinyint(4) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `core` tinyint(4) NOT NULL DEFAULT '0',
  `visible_registration` tinyint(3) NOT NULL,
  `visible_edit` tinyint(3) NOT NULL,
  `visible_display` tinyint(3) NOT NULL,
  `visible_mini_registration` tinyint(3) NOT NULL DEFAULT '0',
  `friend_suggest` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_id` (`app_id`),
  KEY `required` (`required`),
  KEY `searchable` (`searchable`),
  KEY `state` (`state`),
  KEY `step_id` (`step_id`),
  KEY `friend_suggest` (`friend_suggest`),
  KEY `idx_unique_key` (`unique_key`(64)),
  KEY `idx_advanced_search1` (`searchable`,`state`,`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=132 ;

--
-- Dumping data for table `j_social_fields`
--

INSERT INTO `j_social_fields` (`id`, `unique_key`, `app_id`, `step_id`, `title`, `display_title`, `description`, `display_description`, `default`, `validation`, `state`, `searchable`, `required`, `params`, `ordering`, `core`, `visible_registration`, `visible_edit`, `visible_display`, `visible_mini_registration`, `friend_suggest`) VALUES
(1, 'HEADER', 71, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ACCOUNT_INFORMATION', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ACCOUNT_INFORMATION_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(2, 'JOOMLA_FULLNAME', 75, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_YOUR_NAME', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_YOUR_NAME_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(3, 'JOOMLA_USERNAME', 83, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DESIRED_USERNAME', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DESIRED_USERNAME_DESC', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DESIRED_USERNAME_DEFAULT', '', 1, 0, 1, '', 2, 0, 1, 1, 0, 0, 0),
(4, 'JOOMLA_PASSWORD', 79, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PASSWORD', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PASSWORD_DESC', 1, '', '', 1, 0, 1, '{"reconfirm_password":true,"password_strength":true}', 3, 0, 1, 1, 0, 0, 0),
(5, 'JOOMLA_EMAIL', 74, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EMAIL_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EMAIL_ADDRESS_DESC', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EMAIL_DEFAULT', '', 1, 1, 1, '', 4, 0, 1, 1, 0, 0, 0),
(6, 'JOOMLA_USER_EDITOR', 82, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDITOR', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDITOR_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 0, 1, 0, 0, 0),
(7, 'JOOMLA_TIMEZONE', 80, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_TIMEZONE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_TIMEZONE_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(8, 'PERMALINK', 90, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PERMALINK_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(9, 'HEADER-1', 71, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DETAILS', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_DETAILS_DESC', 1, '', '', 1, 1, 1, '', 8, 0, 1, 1, 0, 0, 0),
(10, 'GENDER', 70, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_GENDER', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_GENDER_DESC', 1, '', '', 1, 1, 1, '', 9, 0, 1, 1, 1, 0, 0),
(11, 'BIRTHDAY', 60, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_BIRTHDAY', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_BIRTHDAY_DESC', 1, '', '', 1, 1, 1, '{"calendar":true}', 10, 0, 1, 1, 1, 0, 0),
(12, 'ADDRESS', 57, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ADDRESS_DESC', 1, '', '', 1, 1, 1, '', 11, 0, 1, 1, 1, 0, 0),
(13, 'TEXTBOX', 98, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_MOBILE_PHONE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_MOBILE_PHONE_DESC', 1, '', '', 1, 1, 0, '{"placeholder":"COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_MOBILE_PHONE_DEFAULT"}', 12, 0, 1, 1, 1, 0, 0),
(14, 'URL', 99, 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_WEBSITE_DESC', 1, '', '', 1, 1, 0, '{"placeholder":"COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_WEBSITE_DEFAULT"}', 13, 0, 1, 1, 1, 0, 0),
(15, 'HEADER-2', 71, 2, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(16, 'TEXTBOX-1', 98, 2, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_COLLEGE_OR_UNIVERSITY', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_COLLEGE_OR_UNIVERSITY_DESC', 1, '', '', 1, 1, 0, '', 1, 0, 1, 1, 1, 0, 0),
(17, 'TEXTBOX-2', 98, 2, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_GRADUATION_YEAR', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_GRADUATION_YEAR_DESC', 1, '', '', 1, 1, 0, '', 2, 0, 1, 1, 1, 0, 0),
(18, 'HEADER-3', 71, 3, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_APPEARANCE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_APPEARANCE_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(19, 'AVATAR', 59, 3, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_PICTURE', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_PICTURE_DESC', 1, '', '', 1, 1, 0, '', 1, 0, 1, 1, 0, 0, 0),
(20, 'COVER', 64, 3, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_COVER', 1, 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_PROFILE_COVER_DESC', 1, '', '', 1, 1, 0, '', 2, 0, 1, 1, 0, 0, 0),
(21, 'HEADER', 113, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(22, 'TITLE', 128, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(23, 'PERMALINK', 121, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(24, 'DESCRIPTION', 107, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(25, 'TYPE', 129, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(26, 'URL', 130, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(27, 'PHOTOS', 123, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(28, 'VIDEOS', 131, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(29, 'NEWS', 120, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(30, 'DISCUSSIONS', 108, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(31, 'HEADER-1', 113, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(32, 'AVATAR', 102, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(33, 'COVER', 105, 4, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(34, 'HEADER', 113, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(35, 'TITLE', 128, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(36, 'PERMALINK', 121, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(37, 'DESCRIPTION', 107, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(38, 'TYPE', 129, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(39, 'URL', 130, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(40, 'PHOTOS', 123, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(41, 'VIDEOS', 131, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(42, 'NEWS', 120, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(43, 'DISCUSSIONS', 108, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(44, 'HEADER-1', 113, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(45, 'AVATAR', 102, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(46, 'COVER', 105, 5, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(47, 'HEADER', 113, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(48, 'TITLE', 128, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(49, 'PERMALINK', 121, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(50, 'DESCRIPTION', 107, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(51, 'TYPE', 129, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(52, 'URL', 130, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(53, 'PHOTOS', 123, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(54, 'VIDEOS', 131, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(55, 'NEWS', 120, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(56, 'DISCUSSIONS', 108, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(57, 'HEADER-1', 113, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(58, 'AVATAR', 102, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(59, 'COVER', 105, 6, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(60, 'HEADER', 113, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(61, 'TITLE', 128, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(62, 'PERMALINK', 121, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(63, 'DESCRIPTION', 107, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(64, 'TYPE', 129, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(65, 'URL', 130, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(66, 'PHOTOS', 123, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(67, 'VIDEOS', 131, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(68, 'NEWS', 120, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(69, 'DISCUSSIONS', 108, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(70, 'HEADER-1', 113, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(71, 'AVATAR', 102, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(72, 'COVER', 105, 7, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(73, 'HEADER', 113, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(74, 'TITLE', 128, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(75, 'PERMALINK', 121, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(76, 'DESCRIPTION', 107, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(77, 'TYPE', 129, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_TYPE_DESC', 1, '', '', 1, 0, 1, '', 4, 0, 1, 1, 0, 0, 0),
(78, 'URL', 130, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(79, 'PHOTOS', 123, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PHOTO_DESC', 1, '', '', 1, 1, 0, '', 6, 0, 1, 1, 1, 0, 0),
(80, 'VIDEOS', 131, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_VIDEO_DESC', 1, '', '', 1, 1, 0, '', 7, 0, 1, 1, 1, 0, 0),
(81, 'NEWS', 120, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_NEWS_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(82, 'DISCUSSIONS', 108, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_DISCUSSIONS_DESC', 1, '', '', 1, 1, 0, '', 9, 0, 1, 1, 1, 0, 0),
(83, 'HEADER-1', 113, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(84, 'AVATAR', 102, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 11, 0, 1, 1, 1, 0, 0),
(85, 'COVER', 105, 8, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER', 1, 'COM_EASYSOCIAL_FIELDS_GROUP_COVER_DESC', 1, '', '', 1, 1, 0, '', 12, 0, 1, 1, 1, 0, 0),
(86, 'HEADER', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(87, 'TITLE', 164, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(88, 'PERMALINK', 156, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(89, 'STARTEND', 160, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(90, 'ALLDAY', 133, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY_DESC', 1, '', '', 1, 1, 1, '', 4, 0, 1, 1, 1, 0, 0),
(91, 'RECURRING', 158, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(92, 'DESCRIPTION', 141, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 6, 0, 1, 1, 1, 0, 0),
(93, 'TYPE', 165, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE_DESC', 1, '', '', 1, 0, 1, '', 7, 0, 1, 1, 0, 0, 0),
(94, 'URL', 167, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(95, 'HEADER-1', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION_DESC', 1, '', '', 1, 1, 1, '', 9, 0, 1, 1, 0, 0, 0),
(96, 'CONFIGALLOWMAYBE', 137, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(97, 'CONFIGNOTGOINGGUEST', 138, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST_DESC', 1, '', '', 1, 1, 1, '', 11, 0, 1, 1, 0, 0, 0),
(98, 'GUESTLIMIT', 146, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT_DESC', 1, '', '', 1, 1, 1, '', 12, 0, 1, 1, 0, 0, 0),
(99, 'HEADER-2', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES_DESC', 1, '', '', 1, 1, 1, '', 13, 0, 1, 1, 0, 0, 0),
(100, 'PHOTOS', 157, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO_DESC', 1, '1', '', 1, 1, 0, '', 14, 0, 1, 1, 1, 0, 0),
(101, 'VIDEOS', 168, 9, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS', 1, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS_DESC', 1, '1', '', 1, 1, 0, '', 15, 0, 1, 1, 1, 0, 0),
(102, 'NEWS', 154, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS_DESC', 1, '1', '', 1, 1, 0, '', 16, 0, 1, 1, 1, 0, 0),
(103, 'DISCUSSIONS', 142, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS_DESC', 1, '1', '', 1, 1, 0, '', 17, 0, 1, 1, 1, 0, 0),
(104, 'HEADER-3', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS_DESC', 1, '', '', 1, 1, 1, '', 18, 0, 1, 1, 0, 0, 0),
(105, 'ADDRESS', 132, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS_DESC', 1, '', '', 1, 1, 1, '', 19, 0, 1, 1, 1, 0, 0),
(106, 'HEADER-4', 147, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 20, 0, 1, 1, 0, 0, 0),
(107, 'AVATAR', 134, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 21, 0, 1, 1, 1, 0, 0),
(108, 'COVER', 139, 9, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER_DESC', 1, '', '', 1, 1, 0, '', 22, 0, 1, 1, 1, 0, 0),
(109, 'HEADER', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ACCOUNT_INFO_DESC', 1, '', '', 1, 1, 1, '', 0, 0, 1, 1, 0, 0, 0),
(110, 'TITLE', 164, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TITLE_DESC', 1, '', '', 1, 1, 1, '', 1, 0, 1, 1, 1, 0, 0),
(111, 'PERMALINK', 156, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERMALINK_DESC', 1, '', '', 1, 1, 1, '', 2, 0, 1, 1, 1, 0, 0),
(112, 'STARTEND', 160, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TIME_DESC', 1, '', '', 1, 1, 1, '', 3, 0, 1, 1, 1, 0, 0),
(113, 'ALLDAY', 133, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ALLDAY_DESC', 1, '', '', 1, 1, 1, '', 4, 0, 1, 1, 1, 0, 0),
(114, 'RECURRING', 158, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_RECURRING_DESC', 1, '', '', 1, 1, 0, '', 5, 0, 1, 1, 1, 0, 0),
(115, 'DESCRIPTION', 141, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DESCRIPTION_DESC', 1, '', '', 1, 1, 1, '', 6, 0, 1, 1, 1, 0, 0),
(116, 'TYPE', 165, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_TYPE_DESC', 1, '', '', 1, 0, 1, '', 7, 0, 1, 1, 0, 0, 0),
(117, 'URL', 167, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_WEBSITE_DESC', 1, '', '', 1, 1, 0, '', 8, 0, 1, 1, 1, 0, 0),
(118, 'HEADER-1', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIGURATION_DESC', 1, '', '', 1, 1, 1, '', 9, 0, 1, 1, 0, 0, 0),
(119, 'CONFIGALLOWMAYBE', 137, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_ALLOW_MAYBE_DESC', 1, '', '', 1, 1, 1, '', 10, 0, 1, 1, 0, 0, 0),
(120, 'CONFIGNOTGOINGGUEST', 138, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_NOT_GOING_GUEST_DESC', 1, '', '', 1, 1, 1, '', 11, 0, 1, 1, 0, 0, 0),
(121, 'GUESTLIMIT', 146, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_CONFIG_GUEST_LIMIT_DESC', 1, '', '', 1, 1, 1, '', 12, 0, 1, 1, 0, 0, 0),
(122, 'HEADER-2', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_FEATURES_DESC', 1, '', '', 1, 1, 1, '', 13, 0, 1, 1, 0, 0, 0),
(123, 'PHOTOS', 157, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PHOTO_DESC', 1, '1', '', 1, 1, 0, '', 14, 0, 1, 1, 1, 0, 0),
(124, 'VIDEOS', 168, 10, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS', 1, 'COM_EASYSOCIAL_EVENT_FIELDS_ALLOW_VIDEOS_DESC', 1, '1', '', 1, 1, 0, '', 15, 0, 1, 1, 1, 0, 0),
(125, 'NEWS', 154, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_NEWS_DESC', 1, '1', '', 1, 1, 0, '', 16, 0, 1, 1, 1, 0, 0),
(126, 'DISCUSSIONS', 142, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DISCUSSIONS_DESC', 1, '1', '', 1, 1, 0, '', 17, 0, 1, 1, 1, 0, 0),
(127, 'HEADER-3', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_DETAILS_DESC', 1, '', '', 1, 1, 1, '', 18, 0, 1, 1, 0, 0, 0),
(128, 'ADDRESS', 132, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_ADDRESS_DESC', 1, '', '', 1, 1, 1, '', 19, 0, 1, 1, 1, 0, 0),
(129, 'HEADER-4', 147, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_PERSONALIZATION_DESC', 1, '', '', 1, 1, 1, '', 20, 0, 1, 1, 0, 0, 0),
(130, 'AVATAR', 134, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_AVATAR_DESC', 1, '', '', 1, 1, 0, '', 21, 0, 1, 1, 1, 0, 0),
(131, 'COVER', 139, 10, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER', 1, 'COM_EASYSOCIAL_FIELDS_EVENT_COVER_DESC', 1, '', '', 1, 1, 0, '', 22, 0, 1, 1, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_data`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `datakey` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `params` text NOT NULL,
  `raw` text,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`,`uid`),
  KEY `node_id` (`uid`),
  KEY `idx_uid_type` (`uid`,`type`),
  KEY `idx_type_raw` (`type`(25),`raw`(255)),
  KEY `idx_type_key_raw` (`type`(25),`datakey`(50),`raw`(255)),
  FULLTEXT KEY `fields_data_raw` (`raw`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `j_social_fields_data`
--

INSERT INTO `j_social_fields_data` (`id`, `field_id`, `uid`, `type`, `datakey`, `data`, `params`, `raw`) VALUES
(1, 2, 953, 'user', 'first', 'Manager', '', 'Manager'),
(2, 2, 953, 'user', 'middle', '', '', ''),
(3, 2, 953, 'user', 'last', '', '', ''),
(4, 2, 953, 'user', 'name', 'Manager', '', 'Manager'),
(5, 2, 951, 'user', 'first', 'Super', '', 'Super'),
(6, 2, 951, 'user', 'middle', '', '', ''),
(7, 2, 951, 'user', 'last', 'User', '', 'User'),
(8, 2, 951, 'user', 'name', 'Super User', '', 'Super User'),
(9, 2, 952, 'user', 'first', 'User', '', 'User'),
(10, 2, 952, 'user', 'middle', '', '', ''),
(11, 2, 952, 'user', 'last', '', '', ''),
(12, 2, 952, 'user', 'name', 'User', '', 'User');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_options`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `ordering` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_parents` (`parent_id`,`key`),
  KEY `idx_parentid` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_position`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `position` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_rules`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `match_text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_fields_steps`
--

CREATE TABLE IF NOT EXISTS `j_social_fields_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `sequence` int(11) NOT NULL,
  `visible_registration` tinyint(3) NOT NULL,
  `visible_edit` tinyint(3) NOT NULL,
  `visible_display` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`uid`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `j_social_fields_steps`
--

INSERT INTO `j_social_fields_steps` (`id`, `uid`, `type`, `title`, `description`, `state`, `created`, `sequence`, `visible_registration`, `visible_edit`, `visible_display`) VALUES
(1, 1, 'profiles', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_BASIC_INFORMATION', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_BASIC_INFORMATION_DESC', 1, '2016-02-18 02:06:15', 1, 1, 1, 1),
(2, 1, 'profiles', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_EDUCATION', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_EDUCATION_DESC', 1, '2016-02-18 02:06:15', 2, 1, 1, 1),
(3, 1, 'profiles', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_APPEARANCE', 'COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_STEP_APPEARANCE_DESC', 1, '2016-02-18 02:06:15', 3, 1, 1, 0),
(4, 1, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 02:06:15', 1, 1, 1, 1),
(5, 2, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 02:06:16', 1, 1, 1, 1),
(6, 3, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 02:06:16', 1, 1, 1, 1),
(7, 4, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 02:06:16', 1, 1, 1, 1),
(8, 5, 'clusters', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO', 'COM_EASYSOCIAL_FIELDS_GROUP_INFO_DESC', 1, '2016-02-18 02:06:16', 1, 1, 1, 1),
(9, 6, 'clusters', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO_DESC', 1, '2016-02-18 02:06:17', 1, 1, 1, 1),
(10, 7, 'clusters', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO', 'COM_EASYSOCIAL_FIELDS_EVENT_INFO_DESC', 1, '2016-02-18 02:06:17', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_files`
--

CREATE TABLE IF NOT EXISTS `j_social_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `hits` int(11) NOT NULL,
  `hash` text NOT NULL,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `size` text NOT NULL,
  `mime` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `collection_id` (`collection_id`),
  KEY `idx_storage_cron` (`storage`,`created`),
  KEY `idx_created` (`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_files_collections`
--

CREATE TABLE IF NOT EXISTS `j_social_files_collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'This is the person that creates the item.',
  `title` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_friends`
--

CREATE TABLE IF NOT EXISTS `j_social_friends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `actor_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `message` text NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_friends_actor` (`actor_id`),
  KEY `idx_friends_target` (`target_id`),
  KEY `idx_friends_actor_state` (`actor_id`,`state`),
  KEY `idx_friends_target_state` (`target_id`,`state`),
  KEY `idx_actor_target` (`actor_id`,`target_id`,`state`),
  KEY `idx_target_actor` (`target_id`,`actor_id`,`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_friends_invitations`
--

CREATE TABLE IF NOT EXISTS `j_social_friends_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` text NOT NULL,
  `created` datetime NOT NULL,
  `message` text NOT NULL,
  `registered_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_indexer`
--

CREATE TABLE IF NOT EXISTS `j_social_indexer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `utype` varchar(64) DEFAULT NULL,
  `component` varchar(64) DEFAULT NULL,
  `title` text NOT NULL,
  `content` longtext NOT NULL,
  `link` text,
  `last_update` datetime NOT NULL,
  `ucreator` bigint(20) unsigned DEFAULT '0',
  `image` text,
  PRIMARY KEY (`id`),
  KEY `social_source` (`uid`,`utype`,`component`),
  FULLTEXT KEY `social_indexer_snapshot` (`title`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_languages`
--

CREATE TABLE IF NOT EXISTS `j_social_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `updated` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `translator` varchar(255) NOT NULL,
  `progress` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_likes`
--

CREATE TABLE IF NOT EXISTS `j_social_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `stream_id` bigint(20) DEFAULT '0',
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `social_likes_uid` (`uid`),
  KEY `social_likes_contenttype` (`type`),
  KEY `social_likes_createdby` (`created_by`),
  KEY `social_likes_content_type` (`type`,`uid`),
  KEY `social_likes_content_type_by` (`type`,`uid`,`created_by`),
  KEY `idx_stream_id` (`stream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_links`
--

CREATE TABLE IF NOT EXISTS `j_social_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_links_images`
--

CREATE TABLE IF NOT EXISTS `j_social_links_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_url` text NOT NULL,
  `internal_url` text NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `idx_storage_cron` (`storage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_lists`
--

CREATE TABLE IF NOT EXISTS `j_social_lists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `alias` text NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `default` tinyint(3) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_lists_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_lists_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `list_id` bigint(20) NOT NULL,
  `target_id` bigint(20) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target_id` (`target_id`),
  KEY `idx_target_list_type` (`target_id`,`list_id`,`target_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_locations`
--

CREATE TABLE IF NOT EXISTS `j_social_locations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `type` text NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created` datetime NOT NULL,
  `short_address` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_logger`
--

CREATE TABLE IF NOT EXISTS `j_social_logger` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `line` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_mailer`
--

CREATE TABLE IF NOT EXISTS `j_social_mailer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_name` text NOT NULL,
  `sender_email` text NOT NULL,
  `replyto_email` text NOT NULL,
  `recipient_name` text NOT NULL,
  `recipient_email` text NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `template` text NOT NULL,
  `html` tinyint(4) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `response` text NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  `priority` tinyint(4) NOT NULL COMMENT '1 - Low , 2 - Medium , 3 - High , 4 - Highest',
  `language` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_migrators`
--

CREATE TABLE IF NOT EXISTS `j_social_migrators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `oid` bigint(20) unsigned NOT NULL,
  `element` varchar(100) NOT NULL,
  `component` varchar(100) NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `component_content` (`component`,`oid`,`element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Store migrated content id and map with easysocial item id.' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_moods`
--

CREATE TABLE IF NOT EXISTS `j_social_moods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key of the row',
  `namespace` varchar(255) NOT NULL COMMENT 'Determines if this item is tied to a specific item',
  `namespace_uid` int(11) NOT NULL,
  `icon` varchar(255) NOT NULL COMMENT 'Contains the css class for the emoticon',
  `verb` varchar(255) NOT NULL COMMENT 'Feeling, Watching, Eating etc',
  `subject` text NOT NULL COMMENT 'Happy, Sad, Angry etc',
  `custom` tinyint(3) NOT NULL COMMENT 'Determines if the user supplied a custom text',
  `text` text NOT NULL COMMENT 'If there is a custom text, based on the custom column, this text will be used.',
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_notes`
--

CREATE TABLE IF NOT EXISTS `j_social_notes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `title` text NOT NULL,
  `alias` text NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_notifications`
--

CREATE TABLE IF NOT EXISTS `j_social_notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `context_ids` text NOT NULL,
  `context_type` varchar(255) NOT NULL,
  `cmd` text NOT NULL,
  `app_id` bigint(20) DEFAULT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `image` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '0',
  `actor_id` int(11) NOT NULL,
  `actor_type` varchar(255) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `params` text NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `node_id` (`uid`,`created`),
  KEY `idx_target_state` (`target_id`,`target_type`,`state`),
  KEY `idx_target_created` (`target_id`,`target_type`,`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_oauth`
--

CREATE TABLE IF NOT EXISTS `j_social_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_id` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `client` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `secret` text NOT NULL,
  `created` datetime NOT NULL,
  `expires` varchar(255) NOT NULL,
  `pull` tinyint(3) NOT NULL,
  `push` tinyint(3) NOT NULL,
  `params` text NOT NULL,
  `last_pulled` datetime NOT NULL,
  `last_pushed` datetime NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pull` (`pull`,`push`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_oauth_history`
--

CREATE TABLE IF NOT EXISTS `j_social_oauth_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_id` int(11) NOT NULL,
  `remote_id` int(11) NOT NULL,
  `remote_type` varchar(255) NOT NULL,
  `local_id` int(11) NOT NULL,
  `local_type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_photos`
--

CREATE TABLE IF NOT EXISTS `j_social_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `caption` text NOT NULL,
  `created` datetime NOT NULL,
  `assigned_date` datetime NOT NULL,
  `ordering` tinyint(3) NOT NULL,
  `featured` tinyint(3) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  `total_size` bigint(20) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_photos_user_photos` (`state`,`uid`,`type`,`ordering`),
  KEY `idx_albums` (`state`,`album_id`,`ordering`),
  KEY `idx_storage_cron` (`state`,`storage`,`created`),
  KEY `idx_created` (`created`),
  KEY `idx_state_created` (`state`,`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_photos_meta`
--

CREATE TABLE IF NOT EXISTS `j_social_photos_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `group` varchar(255) NOT NULL,
  `property` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `photo_id` (`photo_id`),
  KEY `group` (`group`),
  KEY `idx_sql1` (`photo_id`,`group`(64),`property`),
  KEY `idx_sql2` (`photo_id`,`group`(64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_photos_tag`
--

CREATE TABLE IF NOT EXISTS `j_social_photos_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `label` text NOT NULL,
  `top` varchar(255) NOT NULL,
  `left` varchar(255) NOT NULL,
  `width` varchar(255) NOT NULL,
  `height` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_points`
--

CREATE TABLE IF NOT EXISTS `j_social_points` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `command` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'The title of the points',
  `description` text NOT NULL,
  `alias` varchar(255) NOT NULL COMMENT 'The permalink that links to the points.',
  `created` datetime NOT NULL COMMENT 'Creation datetime of the points.',
  `threshold` int(11) DEFAULT NULL COMMENT 'Optional value if app needs to give points based on certain actions multiple times.',
  `interval` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0 - every time , 1 - once , 2 - twice - n times',
  `points` int(11) NOT NULL DEFAULT '0' COMMENT 'The amount of points to be given.',
  `state` tinyint(3) NOT NULL COMMENT 'The state of the points. 0 - unpublished, 1 - published ',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `command_id` (`command`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=93 ;

--
-- Dumping data for table `j_social_points`
--

INSERT INTO `j_social_points` (`id`, `command`, `extension`, `title`, `description`, `alias`, `created`, `threshold`, `interval`, `points`, `state`, `params`) VALUES
(1, 'create.article', 'com_content', 'PLG_APP_USER_ARTICLE_CREATE_ARTICLE_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_CREATE_ARTICLE_POINTS_DESC', 'create-article', '2016-02-18 02:05:54', NULL, 0, 5, 1, ''),
(2, 'delete.article', 'com_content', 'PLG_APP_USER_ARTICLE_DELETED_ARTICLE_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_DELETED_ARTICLE_POINTS_DESC', 'deleted-article', '2016-02-18 02:05:54', NULL, 0, -5, 1, ''),
(3, 'read.article', 'com_content', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_POINTS_DESC', 'read-article', '2016-02-18 02:05:54', NULL, 0, 1, 1, ''),
(4, 'author.read.article', 'com_content', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_BY_USER_POINTS_TITLE', 'PLG_APP_USER_ARTICLE_READ_ARTICLE_BY_USER_POINTS_DESC', 'article-read-user', '2016-02-18 02:05:54', NULL, 0, 1, 1, ''),
(5, 'apps.install', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_INSTALL_APPLICATIONS', 'COM_EASYSOCIAL_POINTS_INSTALL_APPLICATIONS_DESC', 'install-apps', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(6, 'apps.uninstall', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNINSTALL_APPLICATIONS', 'COM_EASYSOCIAL_POINTS_UNINSTALL_APPLICATIONS_DESC', 'uninstall-apps', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(7, 'badges.achieve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_BADGES_ACHIEVE', 'COM_EASYSOCIAL_POINTS_BADGES_ACHIEVE_DESC', 'badges-achieve', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(8, 'conversation.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION_DESC', 'conversation-starter', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(9, 'conversation.create.group', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION_GROUP', 'COM_EASYSOCIAL_POINTS_START_CONVERSATION_GROUP_DESC', 'conversation-group-starter', '2016-02-18 02:06:13', NULL, 0, 10, 1, ''),
(10, 'conversation.reply', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REPLY_CONVERSATION', 'COM_EASYSOCIAL_POINTS_REPLY_CONVERSATION_DESC', 'conversation-reply', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(11, 'conversation.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_INVITE_TO_CONVERSATION', 'COM_EASYSOCIAL_POINTS_INVITE_TO_CONVERSATION_DESC', 'invite-user-conversation', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(12, 'conversation.read', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_READ_CONVERSATION', 'COM_EASYSOCIAL_POINTS_READ_CONVERSATION_DESC', 'conversation.read', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(13, 'events.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_CREATE_DESC', 'events-create', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(14, 'events.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_UPDATE', 'COM_EASYSOCIAL_POINTS_EVENTS_UPDATE_DESC', 'events-update', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(15, 'events.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_REMOVED', 'COM_EASYSOCIAL_POINTS_EVENTS_REMOVED_DESC', 'events-remove', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(16, 'events.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_INVITE', 'COM_EASYSOCIAL_POINTS_EVENTS_INVITE_DESC', 'events-invite', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(17, 'events.going', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_GOING', 'COM_EASYSOCIAL_POINTS_EVENTS_GOING_DESC', 'events-going', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(18, 'events.notgoing', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_NOTGOING', 'COM_EASYSOCIAL_POINTS_EVENTS_NOTGOING_DESC', 'events-notgoing', '2016-02-18 02:06:13', NULL, 0, -2, 1, ''),
(19, 'events.discussion.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_CREATE_DESC', 'events-discussion-create', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(20, 'events.discussion.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_DELETE_DESC', 'events-discussion-delete', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(21, 'events.discussion.answer', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_ANSWER', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_ANSWER_DESC', 'events-discussion-answer', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(22, 'events.discussion.reply', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_REPLY', 'COM_EASYSOCIAL_POINTS_EVENTS_DISCUSSION_REPLY_DESC', 'events-discussion-reply', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(23, 'events.news.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_CREATE_DESC', 'events-news-create', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(24, 'events.news.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_NEWS_DELETE_DESC', 'events-news-delete', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(25, 'events.milestone.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_CREATE_DESC', 'events-milestone-create', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(26, 'events.milestone.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_MILESTONE_DELETE_DESC', 'events-milestone-delete', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(27, 'events.task.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_CREATE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_CREATE_DESC', 'events-task-create', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(28, 'events.task.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_DELETE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_DELETE_DESC', 'events-task-delete', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(29, 'events.task.resolve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_RESOLVE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_RESOLVE_DESC', 'events-task-resolve', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(30, 'events.task.unresolve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_UNRESOLVE', 'COM_EASYSOCIAL_POINTS_EVENTS_TASK_UNRESOLVE_DESC', 'events-task-unresolve', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(31, 'files.upload', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FILES_UPLOAD', 'COM_EASYSOCIAL_POINTS_FILES_UPLOAD_DESC', 'file-upload', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(32, 'files.download', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FILES_DOWNLOAD', 'COM_EASYSOCIAL_POINTS_FILES_DOWNLOAD_DESC', 'file-download', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(33, 'friends.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_ADD', 'COM_EASYSOCIAL_POINTS_FRIENDS_ADD_DESC', 'friends-add', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(34, 'friends.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_REMOVE', 'COM_EASYSOCIAL_POINTS_FRIENDS_REMOVE_DESC', 'friends-remove', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(35, 'friends.approve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_APPROVE', 'COM_EASYSOCIAL_POINTS_FRIENDS_APPROVE_DESC', 'friends-approve', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(36, 'friends.list.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_CREATE_FRIEND_LIST', 'COM_EASYSOCIAL_POINTS_CREATE_FRIEND_LIST_DESC', 'friends-list-create', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(37, 'friends.list.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_FRIEND_LIST', 'COM_EASYSOCIAL_POINTS_REMOVE_FRIEND_LIST_DESC', 'friends-list-delete', '2016-02-18 02:06:13', NULL, 0, -2, 1, ''),
(38, 'friends.list.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_ASSIGN_FRIEND_TO_LIST', 'COM_EASYSOCIAL_POINTS_ASSIGN_FRIEND_TO_LIST_DESC', 'friends-list-add', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(39, 'friends.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE_DESC', 'friends-invite', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(40, 'friends.registered', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE_REGISTERED', 'COM_EASYSOCIAL_POINTS_FRIENDS_INVITE_REGISTERED_DESC', 'friends-invite-registered', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(41, 'groups.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_CREATE_GROUP', 'COM_EASYSOCIAL_POINTS_CREATE_GROUP_DESC', 'create-group', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(42, 'groups.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVED_GROUP', 'COM_EASYSOCIAL_POINTS_REMOVED_GROUP_DESC', 'removed-group', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(43, 'groups.join', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_JOIN_GROUP', 'COM_EASYSOCIAL_POINTS_JOIN_GROUP_DESC', 'join-group', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(44, 'groups.leave', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_LEAVE_GROUP', 'COM_EASYSOCIAL_POINTS_LEAVE_GROUP_DESC', 'leave-group', '2016-02-18 02:06:13', NULL, 0, -2, 1, ''),
(45, 'groups.invite', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_INVITE_FRIENDS', 'COM_EASYSOCIAL_POINTS_GROUP_INVITE_FRIENDS_DESC', 'invite-friends-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(46, 'groups.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPDATE_GROUP', 'COM_EASYSOCIAL_POINTS_UPDATE_GROUP_DESC', 'updated-group', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(47, 'groups.discussion.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_DISCUSSION_DESC', 'create-discussion-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(48, 'groups.discussion.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_DISCUSSION_DESC', 'delete-discussion-group', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(49, 'groups.discussion.reply', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_REPLY_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_REPLY_DISCUSSION_DESC', 'reply-discussion-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(50, 'groups.discussion.answer', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_ANSWER_DISCUSSION', 'COM_EASYSOCIAL_POINTS_GROUP_ANSWER_DISCUSSION_DESC', 'answer-discussion-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(51, 'groups.news.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_NEWS', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_NEWS_DESC', 'create-news-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(52, 'groups.news.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_NEWS', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_NEWS_DESC', 'delete-news-group', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(53, 'groups.milestone.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_MILESTONE', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_MILESTONE_DESC', 'create-milestone-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(54, 'groups.milestone.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_MILESTONE', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_MILESTONE_DESC', 'delete-milestone-group', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(55, 'groups.task.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_TASK', 'COM_EASYSOCIAL_POINTS_GROUP_CREATE_TASK_DESC', 'create-task-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(56, 'groups.task.resolve', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_RESOLVE_TASK', 'COM_EASYSOCIAL_POINTS_GROUP_RESOLVE_TASK_DESC', 'resolve-task-group', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(57, 'groups.task.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_TASK', 'COM_EASYSOCIAL_POINTS_GROUP_DELETE_TASK_DESC', 'delete-task-group', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(58, 'photos.upload', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPLOAD_PHOTO', 'COM_EASYSOCIAL_POINTS_UPLOAD_PHOTO_DESC', 'upload-photo', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(59, 'photos.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO_DESC', 'remove-photo', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(60, 'photos.albums.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_CREATE_PHOTO_ALBUM', 'COM_EASYSOCIAL_POINTS_CREATE_PHOTO_ALBUM_DESC', 'create-photo-album', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(61, 'photos.albums.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO_ALBUM', 'COM_EASYSOCIAL_POINTS_REMOVE_PHOTO_ALBUM_DESC', 'remove-photo-album', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(62, 'photos.tag', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_TAG_ON_PHOTO', 'COM_EASYSOCIAL_POINTS_TAG_ON_PHOTO_DESC', 'tag-photo', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(63, 'photos.untag', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_TAG_ON_PHOTO', 'COM_EASYSOCIAL_POINTS_REMOVE_TAG_ON_PHOTO_DESC', 'remove-tag-photo', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(64, 'photos.like', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_LIKE_PHOTO', 'COM_EASYSOCIAL_POINTS_LIKE_PHOTO_DESC', 'like-photo', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(65, 'photos.unlike', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNLIKE_PHOTO', 'COM_EASYSOCIAL_POINTS_UNLIKE_PHOTO_DESC', 'unlike-photo', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(66, 'photos.comment.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_PHOTO', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_PHOTO_DESC', 'comment-photo', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(67, 'photos.comment.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_PHOTO', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_PHOTO_DESC', 'comment-photo-removed', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(68, 'polls.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_ADD', 'COM_EASYSOCIAL_POINTS_POLLS_ADD_DESC', 'add-polls', '2016-02-18 02:06:13', NULL, 0, 3, 1, ''),
(69, 'polls.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_REMOVE', 'COM_EASYSOCIAL_POINTS_POLLS_REMOVE_DESC', 'remove-polls', '2016-02-18 02:06:13', NULL, 0, -3, 1, ''),
(70, 'polls.vote', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_VOTE', 'COM_EASYSOCIAL_POINTS_POLLS_VOTE_DESC', 'vote-polls', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(71, 'polls.unvote', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POLLS_UNVOTE', 'COM_EASYSOCIAL_POINTS_POLLS_UNVOTE_DESC', 'unvote-polls', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(72, 'post.like', 'com_easysocial', 'Like Posts', 'Earn points when someone likes your posts.', 'like-posts', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(73, 'post.unlike', 'com_easysocial', 'Unlike Posts', 'Demote points when someone unlike your posts.', 'unlike-posts', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(74, 'privacy.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_PRIVACY_UPDATE', 'COM_EASYSOCIAL_POINTS_PRIVACY_UPDATE_DESC', 'privacy-update', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(75, 'profile.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE_DESC', 'profile-update', '2016-02-18 02:06:13', NULL, 0, 15, 1, ''),
(76, 'profile.avatar.update', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE_AVATAR', 'COM_EASYSOCIAL_POINTS_UPDATE_PROFILE_AVATAR_DESC', 'profile-avatar-update', '2016-02-18 02:06:13', NULL, 1, 5, 1, ''),
(77, 'profile.follow', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_FOLLOW_USER', 'COM_EASYSOCIAL_POINTS_FOLLOW_USER_DESC', 'profile-follow', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(78, 'profile.unfollow', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNFOLLOW_USER', 'COM_EASYSOCIAL_POINTS_UNFOLLOW_USER_DESC', 'profile-unfollow', '2016-02-18 02:06:13', NULL, 0, -2, 1, ''),
(79, 'profile.followed', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_BEING_FOLLOWED', 'COM_EASYSOCIAL_POINTS_BEING_FOLLOWED_DESC', 'profile-followed', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(80, 'profile.unfollowed', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNFOLLOWED_BY_USER', 'COM_EASYSOCIAL_POINTS_UNFOLLOWED_BY_USER_DESC', 'profile-unfollowed', '2016-02-18 02:06:13', NULL, 0, -2, 1, ''),
(81, 'reports.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REPORTS_ADD', 'COM_EASYSOCIAL_POINTS_REPORTS_ADD_DESC', 'reports-create', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(82, 'reports.delete', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REPORTS_REMOVED', 'COM_EASYSOCIAL_POINTS_REPORTS_REMOVED_DESC', 'reports-removed', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(83, 'story.create', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_POST_NEW_UPDATE', 'COM_EASYSOCIAL_POINTS_POST_NEW_UPDATE_DESC', 'story-create', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(84, 'user.registration', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_USER_REGISTER', 'COM_EASYSOCIAL_POINTS_USER_REGISTER_DESC', 'registration', '2016-02-18 02:06:13', NULL, 0, 20, 1, ''),
(85, 'video.upload', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UPLOAD_VIDEO', 'COM_EASYSOCIAL_POINTS_UPLOAD_VIDEO_DESC', 'upload-video', '2016-02-18 02:06:13', NULL, 0, 5, 1, ''),
(86, 'video.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_REMOVE_VIDEO', 'COM_EASYSOCIAL_POINTS_REMOVE_VIDEO_DESC', 'remove-video', '2016-02-18 02:06:13', NULL, 0, -5, 1, ''),
(87, 'video.like', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_LIKE_VIDEO', 'COM_EASYSOCIAL_POINTS_LIKE_VIDEO_DESC', 'like-video', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(88, 'video.unlike', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_UNLIKE_VIDEO', 'COM_EASYSOCIAL_POINTS_UNLIKE_VIDEO_DESC', 'unlike-video', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(89, 'video.comment.add', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_VIDEO', 'COM_EASYSOCIAL_POINTS_COMMENT_ON_VIDEO_DESC', 'comment-video', '2016-02-18 02:06:13', NULL, 0, 1, 1, ''),
(90, 'video.comment.remove', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_VIDEO', 'COM_EASYSOCIAL_POINTS_COMMENT_REMOVE_FROM_VIDEO_DESC', 'comment-video-removed', '2016-02-18 02:06:13', NULL, 0, -1, 1, ''),
(91, 'video.featured', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_VIDEO_FEATURED', 'COM_EASYSOCIAL_POINTS_VIDEO_FEATURED_DESC', 'video-featured', '2016-02-18 02:06:13', NULL, 0, 2, 1, ''),
(92, 'video.unfeatured', 'com_easysocial', 'COM_EASYSOCIAL_POINTS_VIDEO_UNFEATURED', 'COM_EASYSOCIAL_POINTS_VIDEO_UNFEATURED_DESC', 'video-unfeatured', '2016-02-18 02:06:13', NULL, 0, -2, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_points_history`
--

CREATE TABLE IF NOT EXISTS `j_social_points_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Primary key for this table',
  `points_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL COMMENT 'The target user id',
  `points` int(11) NOT NULL COMMENT 'The number of points',
  `created` datetime NOT NULL COMMENT 'The date time value when the user earned the point.',
  `state` tinyint(3) NOT NULL COMMENT 'The publish state',
  `message` text NOT NULL COMMENT 'Any custom message set for this points assignment',
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `points_id` (`points_id`),
  KEY `idx_userid` (`user_id`),
  KEY `user_points` (`user_id`,`points`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_polls`
--

CREATE TABLE IF NOT EXISTS `j_social_polls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `title` text NOT NULL,
  `multiple` tinyint(1) DEFAULT '0',
  `locked` tinyint(1) DEFAULT '0',
  `cluster_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiry_date` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_element_id` (`element`,`uid`),
  KEY `idx_clusterid` (`cluster_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_polls_items`
--

CREATE TABLE IF NOT EXISTS `j_social_polls_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint(20) unsigned NOT NULL,
  `value` text NOT NULL,
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pollid` (`poll_id`),
  KEY `idx_polls` (`poll_id`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_polls_users`
--

CREATE TABLE IF NOT EXISTS `j_social_polls_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint(20) unsigned NOT NULL,
  `poll_itemid` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pollid` (`poll_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_pollitem` (`poll_itemid`),
  KEY `idx_poll_user` (`poll_id`,`user_id`),
  KEY `idx_poll_item_user` (`poll_id`,`poll_itemid`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL COMMENT 'object type e.g. photos, friends, albums, profile and etc',
  `rule` varchar(64) NOT NULL COMMENT 'rule type e.g. view_friends, view, search, comment, tag and etc',
  `value` int(11) DEFAULT '0',
  `options` text,
  `description` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `core` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type_rule` (`type`,`rule`),
  KEY `type_rule_privacy` (`type`,`rule`,`value`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `j_social_privacy`
--

INSERT INTO `j_social_privacy` (`id`, `type`, `rule`, `value`, `options`, `description`, `state`, `core`) VALUES
(1, 'apps', 'calendar', 0, '{"options":["public","member","friend","only_me","custom"]}', 'APPS_USER_CALENDAR_PRIVACY_FIELD_DESC', 1, 0),
(2, 'field', 'address', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_ADDRESS_PRIVACY_FIELD_DESC', 1, 0),
(3, 'field', 'birthday', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_BIRTHDAY_PRIVACY_FIELD_DESC', 1, 0),
(4, 'field', 'boolean', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_BOOLEAN_PRIVACY_FIELD_DESC', 1, 0),
(5, 'field', 'checkbox', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_CHECKBOX_PRIVACY_FIELD_DESC', 1, 0),
(6, 'field', 'country', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_COUNTRY_PRIVACY_FIELD_DESC', 1, 0),
(7, 'field', 'currency', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_CURRENCY_PRIVACY_FIELD_DESC', 1, 0),
(8, 'field', 'datetime', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_DATETIME_PRIVACY_FIELD_DESC', 1, 0),
(9, 'field', 'dropdown', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_DROPDOWN_PRIVACY_FIELD_DESC', 1, 0),
(10, 'field', 'email', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_EMAIL_PRIVACY_FIELD_DESC', 1, 0),
(11, 'field', 'file', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_FILE_PRIVACY_FIELD_DESC', 1, 0),
(12, 'field', 'gender', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_GENDER_PRIVACY_FIELD_DESC', 1, 0),
(13, 'field', 'headline', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_HEADLINE_PRIVACY_FIELD_DESC', 1, 0),
(14, 'field', 'joomla_email', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_JOOMLA_EMAIL_PRIVACY_FIELD_DESC', 1, 0),
(15, 'field', 'joomla_timezone', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_JOOMLA_TIMEZONE_PRIVACY_FIELD_DESC', 1, 0),
(16, 'field', 'multidropdown', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_MULTIDROPDOWN_PRIVACY_FIELD_DESC', 1, 0),
(17, 'field', 'multilist', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_MULTILIST_PRIVACY_FIELD_DESC', 1, 0),
(18, 'field', 'multitextbox', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_MULTITEXTBOX_PRIVACY_FIELD_DESC', 1, 0),
(19, 'field', 'relationship', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_RELATIONSHIP_PRIVACY_FIELD_DESC', 1, 0),
(20, 'field', 'textarea', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_TEXTAREA_PRIVACY_FIELD_DESC', 1, 0),
(21, 'field', 'textbox', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_TEXTBOX_PRIVACY_FIELD_DESC', 1, 0),
(22, 'field', 'url', 0, '{"options":["public","member","friend","only_me"]}', 'FIELDS_USER_URL_PRIVACY_FIELD_DESC', 1, 0),
(23, 'achievements', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_ACHIEVEMENTS_VIEW', 1, 1),
(24, 'albums', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_ALBUMS_VIEW', 1, 1),
(25, 'core', 'view', 0, '{"options":["public","member","friend","only_me"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_CORE_VIEW', 1, 1),
(26, 'followers', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_FOLLOWERS_VIEW', 1, 1),
(27, 'friends', 'view', 0, '{"options":["public","member","friends_of_friend","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_FRIENDS_VIEW', 1, 1),
(28, 'friends', 'request', 10, '{"options":["public","member","friends_of_friend"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_FRIENDS_REQUEST', 1, 1),
(29, 'photos', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_VIEW', 1, 1),
(30, 'photos', 'tagme', 30, '{"options":["friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_TAGME', 1, 1),
(31, 'photos', 'tag', 30, '{"options":["friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_TAG', 1, 1),
(32, 'polls', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_POLLS_VIEW', 1, 1),
(33, 'polls', 'vote', 10, '{"options":["member","friend","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_POLLS_VOTE', 1, 1),
(34, 'profiles', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_VIEW', 1, 1),
(35, 'profiles', 'search', 0, '{"options":["public","member","friends_of_friend","friend","only_me"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_SEARCH', 1, 1),
(36, 'profiles', 'post.status', 10, '{"options":["public","member","friend","only_me"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_POST_STATUS', 1, 1),
(37, 'profiles', 'post.message', 10, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PROFILES_POST_MESSAGE', 1, 1),
(38, 'story', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_STORY_VIEW', 1, 1),
(39, 'story', 'post.comment', 10, '{"options":["member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_STORY_POST_COMMENT', 1, 1),
(40, 'videos', 'view', 0, '{"options":["public","member","friend","only_me","custom"]}', 'COM_EASYSOCIAL_PRIVACY_DESC_PHOTOS_VIEW', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy_customize`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy_customize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'id from user or profile or item',
  `utype` varchar(64) NOT NULL COMMENT 'user or profile or item',
  `user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_type` (`uid`,`utype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy_items`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `privacy_id` int(11) NOT NULL COMMENT 'key to social_privacy.id',
  `user_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'object id e.g streamid, activityid and etc',
  `type` varchar(64) NOT NULL COMMENT 'object type e.g. stream, activity and etc',
  `value` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `privacy_id` (`privacy_id`),
  KEY `user_privacy_item` (`user_id`,`uid`,`type`),
  KEY `idx_uid_type` (`uid`,`type`),
  KEY `idx_user_type` (`user_id`,`type`),
  KEY `idx_value_user` (`value`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_privacy_map`
--

CREATE TABLE IF NOT EXISTS `j_social_privacy_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `privacy_id` int(11) NOT NULL COMMENT 'key to social_privacy.id',
  `uid` int(11) NOT NULL COMMENT 'userid or profileid',
  `utype` varchar(64) NOT NULL COMMENT 'user or profile',
  `value` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `privacy_id` (`privacy_id`),
  KEY `uid_type` (`uid`,`utype`),
  KEY `uid_type_value` (`uid`,`utype`,`value`),
  KEY `idx_privacy_uid_type` (`privacy_id`,`uid`,`utype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_profiles`
--

CREATE TABLE IF NOT EXISTS `j_social_profiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `gid` text NOT NULL,
  `default` tinyint(4) NOT NULL,
  `default_avatar` int(11) DEFAULT NULL COMMENT 'If this field contains an id, it''s from the default avatar, otherwise use system''s default avatar.',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `state` tinyint(4) NOT NULL,
  `params` text NOT NULL,
  `registration` tinyint(4) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `community_access` tinyint(3) NOT NULL DEFAULT '1',
  `apps` text NOT NULL,
  `site_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `profile_esad` (`community_access`),
  KEY `idx_profile_access` (`id`,`community_access`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_social_profiles`
--

INSERT INTO `j_social_profiles` (`id`, `title`, `alias`, `description`, `gid`, `default`, `default_avatar`, `created`, `modified`, `state`, `params`, `registration`, `ordering`, `community_access`, `apps`, `site_id`) VALUES
(1, 'Registered Users', 'registered-users', 'This is the default profile that was created in the site.', '["2"]', 1, 0, '2016-02-18 02:06:15', '2016-02-18 02:06:15', 1, '{"delete_account":0,"theme":"","registration":"approvals"}', 1, 1, 1, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_profiles_maps`
--

CREATE TABLE IF NOT EXISTS `j_social_profiles_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_profile_users` (`profile_id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `j_social_profiles_maps`
--

INSERT INTO `j_social_profiles_maps` (`id`, `profile_id`, `user_id`, `state`, `created`) VALUES
(1, 1, 953, 1, '2016-02-18 02:06:49'),
(2, 1, 951, 1, '2016-02-18 02:06:49'),
(3, 1, 952, 1, '2016-02-18 02:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_regions`
--

CREATE TABLE IF NOT EXISTS `j_social_regions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(64) NOT NULL,
  `parent_uid` bigint(20) NOT NULL,
  `parent_type` varchar(255) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `site_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_registrations`
--

CREATE TABLE IF NOT EXISTS `j_social_registrations` (
  `session_id` varchar(200) NOT NULL,
  `profile_id` bigint(20) NOT NULL,
  `created` datetime NOT NULL,
  `values` text NOT NULL,
  `step` bigint(20) NOT NULL,
  `step_access` text NOT NULL,
  `errors` text NOT NULL,
  UNIQUE KEY `session_id` (`session_id`),
  KEY `profile_id` (`profile_id`),
  KEY `step` (`step`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_relationship_status`
--

CREATE TABLE IF NOT EXISTS `j_social_relationship_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor` bigint(20) NOT NULL,
  `target` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `relation_type` (`type`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_reports`
--

CREATE TABLE IF NOT EXISTS `j_social_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `extension` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_rss`
--

CREATE TABLE IF NOT EXISTS `j_social_rss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `url` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`),
  KEY `uid` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_search_filter`
--

CREATE TABLE IF NOT EXISTS `j_social_search_filter` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `filter` text NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sitewide` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_searchfilter_element_id` (`element`,`uid`),
  KEY `idx_searchfilter_owner` (`element`,`uid`,`created_by`),
  KEY `idx_searchfilter_alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_shares`
--

CREATE TABLE IF NOT EXISTS `j_social_shares` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `element` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shares_element` (`uid`,`element`),
  KEY `shares_element_user` (`uid`,`element`,`user_id`),
  KEY `shares_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_step_sessions`
--

CREATE TABLE IF NOT EXISTS `j_social_step_sessions` (
  `session_id` varchar(200) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `values` text NOT NULL,
  `step` bigint(20) NOT NULL,
  `step_access` text NOT NULL,
  `errors` text NOT NULL,
  UNIQUE KEY `session_id` (`session_id`),
  KEY `profile_id` (`uid`),
  KEY `step` (`step`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_storage_log`
--

CREATE TABLE IF NOT EXISTS `j_social_storage_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` int(11) NOT NULL,
  `object_type` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream`
--

CREATE TABLE IF NOT EXISTS `j_social_stream` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint(20) unsigned NOT NULL,
  `alias` varchar(255) DEFAULT '',
  `actor_type` varchar(64) DEFAULT 'user',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edited` datetime DEFAULT '0000-00-00 00:00:00',
  `title` text,
  `content` text,
  `context_type` varchar(64) DEFAULT '',
  `verb` varchar(64) DEFAULT '',
  `stream_type` varchar(15) DEFAULT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `target_id` bigint(20) NOT NULL,
  `location_id` int(11) NOT NULL,
  `mood_id` int(11) NOT NULL,
  `with` text NOT NULL,
  `ispublic` tinyint(3) NOT NULL DEFAULT '0',
  `cluster_id` int(11) DEFAULT '0',
  `cluster_type` varchar(64) DEFAULT NULL,
  `cluster_access` tinyint(3) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `privacy_id` int(11) DEFAULT NULL,
  `access` int(11) NOT NULL DEFAULT '0',
  `custom_access` text,
  `last_action` varchar(255) DEFAULT NULL,
  `last_userid` bigint(20) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `stream_actor` (`actor_id`),
  KEY `stream_created` (`created`),
  KEY `stream_modified` (`modified`),
  KEY `stream_alias` (`alias`),
  KEY `stream_source` (`actor_type`),
  KEY `idx_stream_context_type` (`context_type`),
  KEY `idx_stream_target` (`target_id`),
  KEY `idx_actor_modified` (`actor_id`,`modified`),
  KEY `idx_target_context_modified` (`target_id`,`context_type`,`modified`),
  KEY `idx_sitewide_modified` (`sitewide`,`modified`),
  KEY `idx_ispublic` (`ispublic`,`modified`),
  KEY `idx_clusterid` (`cluster_id`),
  KEY `idx_cluster_items` (`cluster_id`,`cluster_type`,`modified`),
  KEY `idx_cluster_access` (`cluster_id`,`cluster_access`),
  KEY `idx_access` (`access`),
  KEY `idx_custom_access` (`access`,`custom_access`(255)),
  KEY `idx_stream_total_cluster` (`cluster_id`,`cluster_access`,`context_type`,`id`,`actor_id`),
  KEY `idx_stream_total_user` (`cluster_id`,`access`,`actor_id`,`context_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_social_stream`
--

INSERT INTO `j_social_stream` (`id`, `actor_id`, `alias`, `actor_type`, `created`, `modified`, `edited`, `title`, `content`, `context_type`, `verb`, `stream_type`, `sitewide`, `target_id`, `location_id`, `mood_id`, `with`, `ispublic`, `cluster_id`, `cluster_type`, `cluster_access`, `params`, `state`, `privacy_id`, `access`, `custom_access`, `last_action`, `last_userid`) VALUES
(1, 951, '', 'user', '2016-02-18 02:11:03', '2016-02-18 02:11:03', '0000-00-00 00:00:00', NULL, NULL, 'users', 'login', NULL, 0, 0, 0, 0, '', 0, 0, NULL, 0, NULL, 1, 25, 0, '', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_assets`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_filter`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_filter` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `utype` varchar(255) DEFAULT 'user',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `streamfilter_uidtype` (`uid`,`utype`),
  KEY `streamfilter_alias` (`alias`),
  KEY `streamfilter_cluster_user` (`uid`,`utype`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_filter_item`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_filter_item` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filter_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `filteritem_fid` (`filter_id`),
  KEY `filteritem_type` (`type`),
  KEY `filteritem_fidtype` (`filter_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_hide`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_hide` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `context` varchar(255) DEFAULT NULL,
  `actor_id` bigint(20) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `stream_hide_user` (`user_id`),
  KEY `stream_hide_uid` (`uid`),
  KEY `stream_hide_actorid` (`actor_id`),
  KEY `stream_hide_user_uid` (`user_id`,`uid`),
  KEY `idx_stream_hide_context` (`context`,`user_id`,`uid`,`actor_id`),
  KEY `idx_stream_hide_actor` (`actor_id`,`user_id`,`uid`,`context`),
  KEY `idx_stream_hide_uid` (`uid`,`user_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_history`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_history` (
  `id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned NOT NULL,
  `alias` varchar(255) DEFAULT '',
  `actor_type` varchar(64) DEFAULT 'user',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edited` datetime DEFAULT '0000-00-00 00:00:00',
  `title` text,
  `content` text,
  `context_type` varchar(64) DEFAULT '',
  `verb` varchar(64) DEFAULT '',
  `stream_type` varchar(15) DEFAULT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `target_id` bigint(20) NOT NULL,
  `location_id` int(11) NOT NULL,
  `mood_id` int(11) NOT NULL,
  `with` text NOT NULL,
  `ispublic` tinyint(3) NOT NULL DEFAULT '0',
  `cluster_id` int(11) DEFAULT '0',
  `cluster_type` varchar(64) DEFAULT NULL,
  `cluster_access` tinyint(3) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `privacy_id` int(11) DEFAULT NULL,
  `access` int(11) NOT NULL DEFAULT '0',
  `custom_access` text,
  `last_action` varchar(255) DEFAULT NULL,
  `last_userid` bigint(20) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `stream_history_created` (`created`),
  KEY `stream_history_modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_item`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_item` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint(20) unsigned NOT NULL,
  `actor_type` varchar(255) DEFAULT 'people',
  `context_type` varchar(64) DEFAULT '',
  `context_id` bigint(20) unsigned DEFAULT '0',
  `verb` varchar(64) DEFAULT '',
  `target_id` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` bigint(20) unsigned NOT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `activity_actor` (`actor_id`),
  KEY `activity_created` (`created`),
  KEY `activity_context` (`context_type`),
  KEY `activity_context_id` (`context_id`),
  KEY `idx_context_verb` (`context_type`,`verb`),
  KEY `idx_uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_social_stream_item`
--

INSERT INTO `j_social_stream_item` (`id`, `actor_id`, `actor_type`, `context_type`, `context_id`, `verb`, `target_id`, `created`, `uid`, `sitewide`, `params`, `state`) VALUES
(1, 951, 'user', 'users', 951, 'login', 0, '2016-02-18 02:11:03', 1, 0, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_item_history`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_item_history` (
  `id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned NOT NULL,
  `actor_type` varchar(255) DEFAULT 'people',
  `context_type` varchar(64) DEFAULT '',
  `context_id` bigint(20) unsigned DEFAULT '0',
  `verb` varchar(64) DEFAULT '',
  `target_id` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` bigint(20) unsigned NOT NULL,
  `sitewide` tinyint(1) DEFAULT '0',
  `params` text,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_history_uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_sticky`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_sticky` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` bigint(20) unsigned NOT NULL,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_streamid` (`stream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_stream_tags`
--

CREATE TABLE IF NOT EXISTS `j_social_stream_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `utype` varchar(255) DEFAULT 'user',
  `with` tinyint(3) unsigned DEFAULT '0',
  `offset` int(11) DEFAULT '0',
  `length` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `streamtags_streamid` (`stream_id`),
  KEY `streamtags_uidtype` (`uid`,`utype`),
  KEY `streamtags_uidoffset` (`stream_id`,`offset`),
  KEY `streamtags_title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_subscriptions`
--

CREATE TABLE IF NOT EXISTS `j_social_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'object id e.g userid, groupid, streamid and etc',
  `type` varchar(64) NOT NULL COMMENT 'subscription type e.g. user, group, stream and etc',
  `user_id` int(11) DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_type` (`uid`,`type`),
  KEY `uid_type_user` (`uid`,`type`,`user_id`),
  KEY `uid_type_email` (`uid`,`type`),
  KEY `idx_uid` (`uid`),
  KEY `idx_type_userid` (`type`,`user_id`),
  KEY `idx_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tags`
--

CREATE TABLE IF NOT EXISTS `j_social_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_type` varchar(255) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `creator_type` varchar(255) NOT NULL,
  `offset` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_targets` (`target_id`,`target_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tasks`
--

CREATE TABLE IF NOT EXISTS `j_social_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `milestone_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `due` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`),
  KEY `milestone_id` (`milestone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tasks_milestones`
--

CREATE TABLE IF NOT EXISTS `j_social_tasks_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `due` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_themes`
--

CREATE TABLE IF NOT EXISTS `j_social_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `element` (`element`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_social_themes`
--

INSERT INTO `j_social_themes` (`id`, `element`, `params`) VALUES
(1, 'jf_connecto_es', '{"jf_es_GoogleWebFont":"1","jf_es_GoogleFontSheet":"","jf_es_GoogleFontTags":"body div#fd.es,body div#fd.es.es-main .es-toolbar .fd-navbar-search .search-query,.jf_es_noty_dialog,.jf_es_noty_dialog h1,.jf_es_noty_dialog h2,.jf_es_noty_dialog h3,.jf_es_noty_dialog h4,.jf_es_noty_dialog h5,.jf_es_noty_dialog h6,.cd-headline,body div#fd.es .es-profile-header-title,body div#fd h1,body div#fd h2,body div#fd h3,body div#fd h4,body div#fd h5,body div#fd h6,body div#fd .h1,body div#fd .h2,body div#fd .h3,body div#fd .h4,body div#fd .h5,body div#fd .h6","jf_es_GoogleFontFace":"","jf_es_UnlimColors":"0","jf_es_UC_MainColor":"#37afcd","jf_es_UC_ToolbarTopGR":"#37afcd","jf_es_UC_ToolbarBottomGR":"#37afcd","jf_es_UC_ButtonRed":"#dd0000","jf_es_UC_ButtonGreen":"#00b856","jf_es_Noty":"1","jf_es_NotyText_p":"","jf_es_NotyText_h5":"","jf_es_NotyText_h2":"","jf_es_NotySocial":"1","jf_es_NotySocial_Google":"+JoomForest","jf_es_NotySocial_FB":"JoomForest","jf_es_NotySocial_Tw":"JoomForest","jf_es_Animtxt":"1","jf_es_Animtxt_effect":"rotate-1","jf_es_Animtxt_delay":"5000","jf_es_Animtxt_1":"","jf_es_Animtxt_2":"","jf_es_Animtxt_3":"","jf_es_Animtxt_4":"","jf_es_Animtxt_5":"","jf_es_Animtxt_6":"","jf_es_Animtxt_7":"","jf_es_Animtxt_8":"","jf_es_Animtxt_9":"","jf_es_Animtxt_10":"","jf_es_Slider":"1","jf_es_Slider_prepend":"#rt-mainbody-surround > .rt-container","jf_es_Slider_h":"500px","jf_es_Slider_speed":"1000","jf_es_Slider_delay":"7000","jf_es_Slider_html_h1":"","jf_es_Slider_html_h3":"","jf_es_Slider_img_1":"","jf_es_Slider_img_2":"","jf_es_Slider_img_3":"","jf_es_Slider_img_4":"","jf_es_Slider_img_5":"","jf_es_Slider_img_6":"","jf_es_Slider_img_7":"","jf_es_Slider_img_8":"","jf_es_Slider_maskColor":"#000","jf_es_Slider_maskOpacy":"1","jf_es_CustomCSS":"","stream_icon":"1","stream_datestyle":"elapsed","stream_dateformat_format":"Y-m-d H:i","dashboard_login_guests":"1","dashboard_feeds_everyone":"1","dashboard_feeds_friendlists":"1","dashboard_feeds_groups":"1","56c5b161a4a01":"1","dashboard_groups_total":"0","dashboard_feeds_events":"1","56c5b161a612b":"1","dashboard_events_total":"0","dashboard_show_apps":"1","dashboard_feeds_apps":"1","registration_profile_headers":"1","registration_profile_avatar":"1","registration_profile_desc":"1","registration_profile_type":"1","registration_profile_users":"1","registration_progress":"1","registration_profile_selected":"1","toolbar":"1","toolbar_guests":"1","toolbar_dashboard":"1","toolbar_search":"1","toolbar_login":"1","toolbar_account":"1","show_browse_users":"1","show_advanced_search":"1","conversation_limit":"20","conversation_sorting":"created","conversation_ordering":"desc","messages_limit":"20","profile_cover":"1","profile_points":"1","profile_type":"1","profile_badges":"1","profile_age":"1","profile_gender":"1","profile_address":"1","profile_website":"1","profile_report":"1","profile_lastlogin":"1","profile_joindate":"1","profile_feeds_apps":"1","apps_sorting":"1","userslimit":"10","users_lastlogin":"1","users_joindate":"1","friendslimit":"20","followersLimit":"20","badgeslimit":"10","achieverslimit":"50","pointslimit":"10","groups_limit":"20","groups_category_header":"1","groups_description":"1","groups_feeds_apps":"1","56c5b161c1a1a":"1","groups_feeds_apps_total":"0","events_limit":"20","events_category_header":"1","events_description":"1","events_feeds_apps":"1","56c5b161c4f8a":"1","events_feeds_apps_total":"0","events_seatsleft":"1","search_toolbarlimit":"5","search_limit":"10","activeTab":""}');

-- --------------------------------------------------------

--
-- Table structure for table `j_social_tmp`
--

CREATE TABLE IF NOT EXISTS `j_social_tmp` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `key` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `expired` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `node_id` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_uploader`
--

CREATE TABLE IF NOT EXISTS `j_social_uploader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `name` text NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_users`
--

CREATE TABLE IF NOT EXISTS `j_social_users` (
  `user_id` bigint(20) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `params` text NOT NULL,
  `connections` int(11) NOT NULL,
  `permalink` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'joomla',
  `auth` varchar(255) NOT NULL,
  `completed_fields` int(11) NOT NULL DEFAULT '0',
  `reminder_sent` tinyint(1) DEFAULT '0',
  `require_reset` tinyint(1) DEFAULT '0',
  `block_date` datetime DEFAULT '0000-00-00 00:00:00',
  `block_period` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `state` (`state`),
  KEY `alias` (`alias`),
  KEY `connections` (`connections`),
  KEY `permalink` (`permalink`),
  KEY `idx_types` (`user_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_social_users`
--

INSERT INTO `j_social_users` (`user_id`, `alias`, `state`, `params`, `connections`, `permalink`, `type`, `auth`, `completed_fields`, `reminder_sent`, `require_reset`, `block_date`, `block_period`) VALUES
(951, '', 1, '', 0, '', 'joomla', '', 0, 0, 0, '0000-00-00 00:00:00', 0),
(952, '', 1, '', 0, '', 'joomla', '', 0, 0, 0, '0000-00-00 00:00:00', 0),
(953, '', 1, '', 0, '', 'joomla', '', 0, 0, 0, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_videos`
--

CREATE TABLE IF NOT EXISTS `j_social_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key for this table',
  `title` varchar(255) NOT NULL COMMENT 'Title of the video',
  `description` text NOT NULL COMMENT 'The description of the video',
  `user_id` int(11) NOT NULL COMMENT 'The user id that created this video',
  `uid` int(11) NOT NULL COMMENT 'This video may belong to another node other than the user.',
  `type` varchar(255) NOT NULL COMMENT 'This video may belong to another node other than the user.',
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `featured` tinyint(3) NOT NULL,
  `category_id` int(11) NOT NULL,
  `hits` int(11) NOT NULL COMMENT 'Total hits received for this video',
  `duration` varchar(255) NOT NULL COMMENT 'Duration of the video',
  `size` int(11) NOT NULL COMMENT 'The file size of the video',
  `params` text NOT NULL COMMENT 'Store video params',
  `storage` varchar(255) NOT NULL COMMENT 'Storage for videos',
  `path` text NOT NULL,
  `original` text NOT NULL,
  `file_title` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `thumbnail` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`,`user_id`,`state`,`featured`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_social_videos_categories`
--

CREATE TABLE IF NOT EXISTS `j_social_videos_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `default` tinyint(3) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL COMMENT 'The user id that created this category',
  `created` datetime NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `j_social_videos_categories`
--

INSERT INTO `j_social_videos_categories` (`id`, `title`, `alias`, `description`, `state`, `default`, `user_id`, `created`, `ordering`) VALUES
(1, 'General', 'general', 'General videos', 1, 1, 951, '2016-02-18 02:06:18', 0),
(2, 'Music', 'music', 'Music videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(3, 'Sports', 'sports', 'Sports videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(4, 'News', 'news', 'News videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(5, 'Gaming', 'gaming', 'Gaming videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(6, 'Movies', 'movies', 'Movies videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(7, 'Documentary', 'documentary', 'Documentary videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(8, 'Fashion', 'fashion', 'Fashion videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(9, 'Travel', 'travel', 'Travel videos', 1, 0, 951, '2016-02-18 02:06:18', 0),
(10, 'Technology', 'technology', 'Technology videos', 1, 0, 951, '2016-02-18 02:06:18', 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_social_videos_categories_access`
--

CREATE TABLE IF NOT EXISTS `j_social_videos_categories_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`profile_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_tags`
--

CREATE TABLE IF NOT EXISTS `j_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `tag_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_tags`
--

INSERT INTO `j_tags` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `created_by_alias`, `modified_user_id`, `modified_time`, `images`, `urls`, `hits`, `language`, `version`, `publish_up`, `publish_down`) VALUES
(1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 42, '2011-01-01 00:00:01', '', 0, '0000-00-00 00:00:00', '', '', 0, '*', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `j_template_styles`
--

CREATE TABLE IF NOT EXISTS `j_template_styles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(50) NOT NULL DEFAULT '',
  `client_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `home` char(7) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_template` (`template`),
  KEY `idx_home` (`home`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `j_template_styles`
--

INSERT INTO `j_template_styles` (`id`, `template`, `client_id`, `home`, `title`, `params`) VALUES
(4, 'beez3', 0, '0', 'Beez3 - Default', '{"wrapperSmall":53,"wrapperLarge":72,"logo":"images\\/joomla_black.png","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","bootstrap":"","templatecolor":"personal","headerImage":"","backgroundcolor":"#eeeeee"}'),
(5, 'hathor', 1, '0', 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}'),
(7, 'protostar', 0, '0', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'),
(8, 'isis', 1, '1', 'isis - Default', '{"templateColor":"","logoFile":""}'),
(10, 'jf_connecto', 0, '1', 'JF Connecto - Default', '{"presets":"Blue","master":"true","current_id":"true","template_full_name":"jf_connecto","grid_system":"12","template_prefix":"jf_connecto-","cookie_time":"31536000","override_set":"2.5","name":"Preset1","copy_lang_files_if_diff":"1","viewswitcher-priority":"1","logo-priority":"2","copyright-priority":"3","styledeclaration-priority":"4","fontsizer-priority":"5","date-priority":"7","totop-priority":"8","systemmessages-priority":"9","morearticles-priority":"12","smartload-priority":"13","pagesuffix-priority":"14","resetsettings-priority":"15","analytics-priority":"16","dropdownmenu-priority":"18","jstools-priority":"21","moduleoverlays-priority":"22","rtl-priority":"23","splitmenu-priority":"24","webfonts-priority":"27","styledeclaration-enabled":"1","date":{"enabled":"0","position":"top-d","clientside":"0","formats":"%A, %B %d, %Y"},"fontsizer":{"enabled":"0","position":"feature-b"},"branding":{"enabled":"1","position":"copyright-a"},"copyright":{"enabled":"1","position":"copyright-a","text":"The Joomla!\\u2122 name is used under a limited license from Open Source Matters in the United States and other countries.<br>JoomForest.com is not affiliated with or endorsed by Open Source Matters or the Joomla! Project.<br>Copyright \\u00a9 2011-2015 JoomForest.com. All Rights Reserved.","layout":"3,3,3,3","showall":"0","showmax":"6"},"systemmessages":{"enabled":"1","position":"showcase-a"},"resetsettings":{"enabled":"0","position":"copyright-d","text":"Reset Settings"},"analytics":{"enabled":"0","code":"","position":"analytics"},"menu":{"enabled":"1","type":"dropdownmenu","dropdownmenu":{"theme":"gantry-dropdown","limit_levels":"0","startLevel":"0","showAllChildren":"1","class_sfx":"top","cache":"0","menutype":"mainmenu","position":"header-b","responsive-menu":"selectbox","enable-current-id":"0","module_cache":"1"},"splitmenu":{"mainmenu-limit_levels":"1","mainmenu-startLevel":"0","mainmenu-endLevel":"0","mainmenu-class_sfx":"top","submenu-limit_levels":"1","submenu-startLevel":"1","submenu-endLevel":"9","cache":"0","menutype":"mainmenu","theme":"gantry-splitmenu","mainmenu-position":"header-b","submenu-position":"sidebar-a","submenu-title":"1","submenu-class_sfx":"","submenu-module_sfx":"","responsive-menu":"panel","roknavmenu_dropdown_enable-current-id":"0","module_cache":"1"}},"top":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"header":{"layout":"4,8","showall":"0","showmax":"6"},"showcase":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"feature":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"utility":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"maintop":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"mainbodyPosition":"6,3,3","mainbottom":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"extension":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"bottom":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"footer":{"layout":"3,3,3,3","showall":"0","showmax":"6"},"layout-mode":"responsive","component-enabled":"0","mainbody-enabled":"1","rtl-enabled":"1","pagesuffix-enabled":"0","selectivizr-enabled":"0","less":{"compression":"1","compilewait":"2","debugheader":"0"},"jf_jquery_easing":"1","jf_preloader":{"enabled":"1","position":"jf-preloader","jf_preloader_bg":"#ffffff","jf_preloader_type":"css3","image":"","jf_preloader_color":"#37AFCD"},"jf_scrolltop":{"enabled":"1","position":"jf-scrolltop"},"jf_stickyheader":{"enabled":"1","jf_stickyheader_Style":"light","jf_stickyheader_Color":"#ffffff"},"jf_prettyphoto":{"enabled":"1","jf_pp_theme":"pp_default","jf_pp_bgopacity":"0.9","jf_pp_slidespeed":"5000","jf_pp_share":"on"},"jf_animate":{"enabled":"1","jf_animate_all":"0","jf_animate_all_sheet":"\\/\\/cdnjs.cloudflare.com\\/ajax\\/libs\\/animate.css\\/3.2.0\\/animate.min.css","jf_animate_custom":"1","jf_animate_1":"0","jf_animate_1_type":"bounce","jf_animate_1_delay":"","jf_animate_1_tags":"","jf_animate_2":"0","jf_animate_2_type":"flash","jf_animate_2_delay":"","jf_animate_2_tags":"","jf_animate_3":"0","jf_animate_3_type":"pulse","jf_animate_3_delay":"","jf_animate_3_tags":"","jf_animate_4":"0","jf_animate_4_type":"rubberBand","jf_animate_4_delay":"","jf_animate_4_tags":"","jf_animate_5":"0","jf_animate_5_type":"shake","jf_animate_5_delay":"","jf_animate_5_tags":"","jf_animate_6":"0","jf_animate_6_type":"swing","jf_animate_6_delay":"","jf_animate_6_tags":"","jf_animate_7":"0","jf_animate_7_type":"tada","jf_animate_7_delay":"","jf_animate_7_tags":""},"jf_canvastext":{"enabled":"0","jf_canvastext_words":"\\"Animated\\",\\"Canvas\\",\\"Text\\"","jf_canvastext_height":"170","jf_canvastext_padding":"margin:100px 0","jf_canvastext_fontsize":"100","jf_canvastext_fontfamily":"\\\\\\"Open Sans\\\\\\", sans-serif","jf_canvastext_color":"#37AFCD"},"logo":{"enabled":"1","position":"header-a","type":"gantry","custom":{"image":""}},"jf_font":{"enabled":"1","jf_font_tags":"html,body","jf_font_family":"Helvetica,Arial,Sans-Serif"},"jf_webfont":{"enabled":"1","jf_webfont_stylesheet":"\\/\\/fonts.googleapis.com\\/css?family=Open+Sans:400,600,700","jf_webfont_tags":"h1,h2,h3,h4,h5,h6,.jf_typo_title,.jf_typo_code_toggle .trigger,.jf_typo_dropcap,.jf_typo_button,#jf_pricing_table,.default-tipsy-inner,.item-page .tags a,.component-content .pagenav li a,.readon,.readmore,button.validate,#member-profile a,#member-registration a,.formelm-buttons button,.btn-primary,.component-content .pagination,.category-list,select,.component-content #searchForm .inputbox,.component-content .search fieldset legend,label,.component-content .searchintro,.component-content .search-results .result-title,.component-content .search-results .result-category .small,.btn,.component-content .login .control-group input,.component-content .login+div,.component-content #users-profile-core legend,.component-content #users-profile-custom legend,.component-content .profile-edit legend,.component-content .registration legend,.component-content .profile-edit,.component-content .registration,.component-content .remind,.component-content .reset,.component-content .tag-category table.category,.rt-error-content,#rt-offline-body,#rt-offline-body input,#rt-breadcrumbs .breadcrumb a,#rt-breadcrumbs .breadcrumb span,#rt-main ul.menu li a,#login-form,.module-content .search,.gf-menu .item,.gf-menu .item.icon [class^=''icon-''],.gf-menu .item.icon [class*= '' icon-''],.gf-menu .item.icon [class^=''fa-''],.gf-menu .item.icon [class*= '' fa-''],.component-content .contact,#jf_styleswitcher,.jf_typo_accord .trigger,.jf_typo_toggle .trigger,#jf_login,.tooltip,.jf_image_block,#rt-footer ul.menu li a,#rt-footer ul.menu li span,#rt-footer-surround #rt-copyright .rt-block","jf_webfont_family":"''Open Sans'',sans-serif"},"jf_styleswitcher":{"enabled":"1","position":"jf-styleswitcher","jf_styleswitcher_1_color":"#37AFCD","jf_styleswitcher_1":"?presets=Blue","jf_styleswitcher_2_color":"#7A7AC3","jf_styleswitcher_2":"?presets=SlateBlue","jf_styleswitcher_3_color":"#27AE60","jf_styleswitcher_3":"?presets=Green","jf_styleswitcher_4_color":"#F1C40F","jf_styleswitcher_4":"?presets=Yellow","jf_styleswitcher_5_color":"#7F8C8D","jf_styleswitcher_5":"?presets=Grey","jf_styleswitcher_6_color":"#999999","jf_styleswitcher_6":"","jf_styleswitcher_7_color":"#999999","jf_styleswitcher_7":"","jf_styleswitcher_8_color":"#999999","jf_styleswitcher_8":"","jf_styleswitcher_9_color":"#999999","jf_styleswitcher_9":"","jf_styleswitcher_10_color":"#999999","jf_styleswitcher_10":""},"jf_colors_bg":"#ffffff","jf_colors_header":"#37AFCD","jf_colors_slideshow":"#37AFCD","jf_colors_breadcrumb":"#F8F8F8","jf_colors_breadcrumb_border":"#eeeeee","jf_colors_footer_bg":"#ffffff","jf_colors_footer_text":"#555555","jf_colors_footer_link":"#37AFCD","jf_colors_main":"#37AFCD","jf_eb_colors_override":"0","jf_eb_UC_MainColor":"#37AFCD","jf_eb_UC_ToolbarTopGR":"#37AFCD","jf_eb_UC_ToolbarBottomGR":"#37AFCD","jf_es_colors_override":"0","jf_es_UC_MainColor":"#37AFCD","jf_es_UC_ToolbarTopGR":"#37AFCD","jf_es_UC_ToolbarBottomGR":"#37AFCD","jf_es_UC_ButtonRed":"#dd0000","jf_es_UC_ButtonGreen":"#00B856","jf_cb_colors_override":"0","jf_cb_UC_MainColor":"#37AFCD","jf_cb_UC_MenubarColor":"#37AFCD","jf_cb_UC_TemplateBody":"#ffffff","jf_typo_01_core":"1","jf_fontawesome":{"enabled":"1","jf_fontawesome_cdn":"\\/\\/netdna.bootstrapcdn.com\\/font-awesome\\/4.2.0\\/css\\/font-awesome.min.css"},"jf_typo_02_accordions":"1","jf_typo_03_toggles":"1","jf_typo_04_pricing_tables":"1","jf_typo_05_image_video_frames":"1","jf_typo_06_social_icons":"1","jf_typo_bs_tooltips_31":"1","jf_typo_bootstrap":"0"}');

-- --------------------------------------------------------

--
-- Table structure for table `j_ucm_base`
--

CREATE TABLE IF NOT EXISTS `j_ucm_base` (
  `ucm_id` int(10) unsigned NOT NULL,
  `ucm_item_id` int(10) NOT NULL,
  `ucm_type_id` int(11) NOT NULL,
  `ucm_language_id` int(11) NOT NULL,
  PRIMARY KEY (`ucm_id`),
  KEY `idx_ucm_item_id` (`ucm_item_id`),
  KEY `idx_ucm_type_id` (`ucm_type_id`),
  KEY `idx_ucm_language_id` (`ucm_language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `j_ucm_content`
--

CREATE TABLE IF NOT EXISTS `j_ucm_content` (
  `core_content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `core_type_alias` varchar(255) NOT NULL DEFAULT '' COMMENT 'FK to the content types table',
  `core_title` varchar(255) NOT NULL,
  `core_alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `core_body` mediumtext NOT NULL,
  `core_state` tinyint(1) NOT NULL DEFAULT '0',
  `core_checked_out_time` varchar(255) NOT NULL DEFAULT '',
  `core_checked_out_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `core_access` int(10) unsigned NOT NULL DEFAULT '0',
  `core_params` text NOT NULL,
  `core_featured` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `core_metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `core_created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `core_created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `core_created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `core_modified_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Most recent user that modified',
  `core_modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `core_language` char(7) NOT NULL,
  `core_publish_up` datetime NOT NULL,
  `core_publish_down` datetime NOT NULL,
  `core_content_item_id` int(10) unsigned DEFAULT NULL COMMENT 'ID from the individual type table',
  `asset_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the j_assets table.',
  `core_images` text NOT NULL,
  `core_urls` text NOT NULL,
  `core_hits` int(10) unsigned NOT NULL DEFAULT '0',
  `core_version` int(10) unsigned NOT NULL DEFAULT '1',
  `core_ordering` int(11) NOT NULL DEFAULT '0',
  `core_metakey` text NOT NULL,
  `core_metadesc` text NOT NULL,
  `core_catid` int(10) unsigned NOT NULL DEFAULT '0',
  `core_xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `core_type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`core_content_id`),
  KEY `tag_idx` (`core_state`,`core_access`),
  KEY `idx_access` (`core_access`),
  KEY `idx_alias` (`core_alias`),
  KEY `idx_language` (`core_language`),
  KEY `idx_title` (`core_title`),
  KEY `idx_modified_time` (`core_modified_time`),
  KEY `idx_created_time` (`core_created_time`),
  KEY `idx_content_type` (`core_type_alias`),
  KEY `idx_core_modified_user_id` (`core_modified_user_id`),
  KEY `idx_core_checked_out_user_id` (`core_checked_out_user_id`),
  KEY `idx_core_created_user_id` (`core_created_user_id`),
  KEY `idx_core_type_id` (`core_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains core content data in name spaced fields' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_ucm_history`
--

CREATE TABLE IF NOT EXISTS `j_ucm_history` (
  `version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ucm_item_id` int(10) unsigned NOT NULL,
  `ucm_type_id` int(10) unsigned NOT NULL,
  `version_note` varchar(255) NOT NULL DEFAULT '' COMMENT 'Optional version name',
  `save_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `editor_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `character_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of characters in this version.',
  `sha1_hash` varchar(50) NOT NULL DEFAULT '' COMMENT 'SHA1 hash of the version_data column.',
  `version_data` mediumtext NOT NULL COMMENT 'json-encoded string of version data',
  `keep_forever` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=auto delete; 1=keep',
  PRIMARY KEY (`version_id`),
  KEY `idx_ucm_item_id` (`ucm_type_id`,`ucm_item_id`),
  KEY `idx_save_date` (`save_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_update_sites`
--

CREATE TABLE IF NOT EXISTS `j_update_sites` (
  `update_site_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `location` text NOT NULL,
  `enabled` int(11) DEFAULT '0',
  `last_check_timestamp` bigint(20) DEFAULT '0',
  `extra_query` varchar(1000) DEFAULT '',
  PRIMARY KEY (`update_site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Update Sites' AUTO_INCREMENT=7 ;

--
-- Dumping data for table `j_update_sites`
--

INSERT INTO `j_update_sites` (`update_site_id`, `name`, `type`, `location`, `enabled`, `last_check_timestamp`, `extra_query`) VALUES
(1, 'Joomla! Core', 'collection', 'http://update.joomla.org/core/list.xml', 1, 1455919395, ''),
(2, 'Joomla! Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', 1, 1455919395, ''),
(3, 'Accredited Joomla! Translations', 'collection', 'http://update.joomla.org/language/translationlist_3.xml', 1, 0, ''),
(4, 'Joomla! Update Component Update Site', 'extension', 'http://update.joomla.org/core/extensions/com_joomlaupdate.xml', 1, 0, ''),
(5, 'Gantry Framework Update Site', 'extension', 'http://www.gantry-framework.org/updates/joomla16/gantry.xml', 1, 0, ''),
(6, 'RocketTheme Update Directory', 'collection', 'http://updates.rockettheme.com/joomla/updates.xml', 1, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `j_update_sites_extensions`
--

CREATE TABLE IF NOT EXISTS `j_update_sites_extensions` (
  `update_site_id` int(11) NOT NULL DEFAULT '0',
  `extension_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`update_site_id`,`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Links extensions to update sites';

--
-- Dumping data for table `j_update_sites_extensions`
--

INSERT INTO `j_update_sites_extensions` (`update_site_id`, `extension_id`) VALUES
(1, 700),
(2, 700),
(3, 600),
(4, 28),
(5, 744),
(6, 749);

-- --------------------------------------------------------

--
-- Table structure for table `j_updates`
--

CREATE TABLE IF NOT EXISTS `j_updates` (
  `update_id` int(11) NOT NULL AUTO_INCREMENT,
  `update_site_id` int(11) DEFAULT '0',
  `extension_id` int(11) DEFAULT '0',
  `name` varchar(100) DEFAULT '',
  `description` text NOT NULL,
  `element` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `folder` varchar(20) DEFAULT '',
  `client_id` tinyint(3) DEFAULT '0',
  `version` varchar(32) DEFAULT '',
  `data` text NOT NULL,
  `detailsurl` text NOT NULL,
  `infourl` text NOT NULL,
  `extra_query` varchar(1000) DEFAULT '',
  PRIMARY KEY (`update_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Available Updates' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `j_updates`
--

INSERT INTO `j_updates` (`update_id`, `update_site_id`, `extension_id`, `name`, `description`, `element`, `type`, `folder`, `client_id`, `version`, `data`, `detailsurl`, `infourl`, `extra_query`) VALUES
(1, 1, 700, 'Joomla', '', 'joomla', 'file', '', 0, '3.4.8', '', 'http://update.joomla.org/core/sts/extension_sts.xml', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `j_user_keys`
--

CREATE TABLE IF NOT EXISTS `j_user_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `series` varchar(255) NOT NULL,
  `invalid` tinyint(4) NOT NULL,
  `time` varchar(200) NOT NULL,
  `uastring` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `series` (`series`),
  UNIQUE KEY `series_2` (`series`),
  UNIQUE KEY `series_3` (`series`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_user_notes`
--

CREATE TABLE IF NOT EXISTS `j_user_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `review_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_category_id` (`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `j_user_profiles`
--

CREATE TABLE IF NOT EXISTS `j_user_profiles` (
  `user_id` int(11) NOT NULL,
  `profile_key` varchar(100) NOT NULL,
  `profile_value` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_user_id_profile_key` (`user_id`,`profile_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Simple user profile storage table';

-- --------------------------------------------------------

--
-- Table structure for table `j_user_usergroup_map`
--

CREATE TABLE IF NOT EXISTS `j_user_usergroup_map` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to j_users.id',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to j_usergroups.id',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j_user_usergroup_map`
--

INSERT INTO `j_user_usergroup_map` (`user_id`, `group_id`) VALUES
(951, 8),
(952, 2),
(953, 6);

-- --------------------------------------------------------

--
-- Table structure for table `j_usergroups`
--

CREATE TABLE IF NOT EXISTS `j_usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference Id',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_usergroup_parent_title_lookup` (`parent_id`,`title`),
  KEY `idx_usergroup_title_lookup` (`title`),
  KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  KEY `idx_usergroup_nested_set_lookup` (`lft`,`rgt`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `j_usergroups`
--

INSERT INTO `j_usergroups` (`id`, `parent_id`, `lft`, `rgt`, `title`) VALUES
(1, 0, 1, 18, 'Public'),
(2, 1, 8, 15, 'Registered'),
(3, 2, 9, 14, 'Author'),
(4, 3, 10, 13, 'Editor'),
(5, 4, 11, 12, 'Publisher'),
(6, 1, 4, 7, 'Manager'),
(7, 6, 5, 6, 'Administrator'),
(8, 1, 16, 17, 'Super Users'),
(9, 1, 2, 3, 'Guest');

-- --------------------------------------------------------

--
-- Table structure for table `j_users`
--

CREATE TABLE IF NOT EXISTS `j_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `block` tinyint(4) NOT NULL DEFAULT '0',
  `sendEmail` tinyint(4) DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `lastResetTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date of last password reset',
  `resetCount` int(11) NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime',
  `otpKey` varchar(1000) NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys',
  `otep` varchar(1000) NOT NULL DEFAULT '' COMMENT 'One time emergency passwords',
  `requireReset` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Require user to reset password on next login',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_block` (`block`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=954 ;

--
-- Dumping data for table `j_users`
--

INSERT INTO `j_users` (`id`, `name`, `username`, `email`, `password`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`, `lastResetTime`, `resetCount`, `otpKey`, `otep`, `requireReset`) VALUES
(951, 'Super User', 'admin', 'admin@example.com', '$2y$10$s2fm.DtMFEr35KGX0zNywOIeSh779ESMOFLd3kZW6AvVFkLFzLDNO', 0, 1, '2013-07-24 09:07:43', '2016-02-19 22:03:12', '0', '{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}', '0000-00-00 00:00:00', 0, '', '', 0),
(952, 'User', 'user', 'user@example.com', '931d334de664be1135bed97fd9bb7b62:ZzvicSTnh9dr1Ln36G3MgkC9WSa9J4PW', 0, 0, '2013-07-24 09:23:03', '0000-00-00 00:00:00', '', '{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}', '0000-00-00 00:00:00', 0, '', '', 0),
(953, 'Manager', 'manager', 'manager@example.com', 'e0f025cc620a663e172c8b25911e5c4e:44wqdHQWhDPcrRg5koGsWJ9Zlhr9WC5x', 0, 0, '2013-07-24 10:53:59', '0000-00-00 00:00:00', '', '{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}', '0000-00-00 00:00:00', 0, '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `j_viewlevels`
--

CREATE TABLE IF NOT EXISTS `j_viewlevels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `title` varchar(100) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_assetgroup_title_lookup` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `j_viewlevels`
--

INSERT INTO `j_viewlevels` (`id`, `title`, `ordering`, `rules`) VALUES
(1, 'Public', 0, '[1]'),
(2, 'Registered', 2, '[6,2,8]'),
(3, 'Special', 3, '[6,3,8]'),
(5, 'Guest', 1, '[9]'),
(6, 'Super Users', 4, '[8]');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
