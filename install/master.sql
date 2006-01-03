-- phpMyAdmin SQL Dump
-- version 2.6.0-pl1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jun 25, 2005 at 08:07 PM
-- Server version: 4.1.9
-- PHP Version: 5.0.5-dev
-- 
-- Database: `freeflux_master`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `master`
-- 

CREATE TABLE `master` (
  `id` int(11) NOT NULL auto_increment,
  `host` varchar(255) default NULL,
  `db` varchar(50) default 'bitflux_free',
  `prefix` varchar(50) default NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `email` varchar(255) NOT NULL default '',
  `hostsdir` varchar(100) NOT NULL default '',
  `user` varchar(100) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastlogin` datetime NOT NULL default '0000-00-00 00:00:00',
  `logincount` int(11) NOT NULL default '0',
  `quota_soft` int(11) NOT NULL default '20',
  `quota_hard` int(11) NOT NULL default '30',
  `quota_current` float NOT NULL default '0',
  `ads` tinyint(4) NOT NULL default '1',
  `installed` datetime NOT NULL default '0000-00-00 00:00:00',
  `webalizer` tinyint(4) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`host`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
        
