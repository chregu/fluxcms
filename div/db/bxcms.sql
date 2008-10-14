-- MySQL dump 10.11
--
-- Host: localhost    Database: fluxcms
-- ------------------------------------------------------
-- Server version	5.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `##bxcms_##_sequences_seq`
--

DROP TABLE IF EXISTS `##bxcms_##_sequences_seq`;
CREATE TABLE `##bxcms_##_sequences_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##_sequences_seq`
--

LOCK TABLES `##bxcms_##_sequences_seq` WRITE;
/*!40000 ALTER TABLE `##bxcms_##_sequences_seq` DISABLE KEYS */;
INSERT INTO `##bxcms_##_sequences_seq` (`sequence`) VALUES (13);
/*!40000 ALTER TABLE `##bxcms_##_sequences_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##blogcategories`
--

DROP TABLE IF EXISTS `##bxcms_##blogcategories`;
CREATE TABLE `##bxcms_##blogcategories` (
  `id` int(4) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `uri` varchar(50) NOT NULL default '',
  `l` int(11) NOT NULL default '0',
  `r` int(11) NOT NULL default '0',
  `fulluri` varchar(255) NOT NULL default '',
  `parentid` int(11) NOT NULL default '1',
  `fullname` varchar(255) NOT NULL default '',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `blog_id` int(11) NOT NULL default '1',
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `l` (`l`),
  KEY `r` (`r`),
  KEY `fulluri` (`fulluri`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##blogcategories`
--

LOCK TABLES `##bxcms_##blogcategories` WRITE;
/*!40000 ALTER TABLE `##bxcms_##blogcategories` DISABLE KEYS */;
INSERT INTO `##bxcms_##blogcategories` (`id`, `name`, `uri`, `l`, `r`, `fulluri`, `parentid`, `fullname`, `changed`, `blog_id`, `status`) VALUES (1,'All','root',1,6,'root',0,'root','2005-04-08 13:07:35',1,1);
INSERT INTO `##bxcms_##blogcategories` (`id`, `name`, `uri`, `l`, `r`, `fulluri`, `parentid`, `fullname`, `changed`, `blog_id`, `status`) VALUES (3,'Moblog Pictures','moblog',4,5,'moblog',1,'Moblog Pictures','2005-04-08 13:07:35',1,1);
INSERT INTO `##bxcms_##blogcategories` (`id`, `name`, `uri`, `l`, `r`, `fulluri`, `parentid`, `fullname`, `changed`, `blog_id`, `status`) VALUES (7,'General','general',2,3,'general',1,'General','2005-04-08 13:07:35',1,1);
/*!40000 ALTER TABLE `##bxcms_##blogcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##blogcomments`
--

DROP TABLE IF EXISTS `##bxcms_##blogcomments`;
CREATE TABLE `##bxcms_##blogcomments` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `comment_posts_id` int(11) NOT NULL default '0',
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL default '',
  `comment_author_url` varchar(100) NOT NULL default '',
  `comment_author_ip` varchar(100) NOT NULL default '',
  `comment_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment_type` varchar(20) NOT NULL default '',
  `comment_status` tinyint(4) NOT NULL default '1',
  `comment_rejectreason` text,
  `comment_hash` varchar(33) default NULL,
  `comment_notification` tinyint(4) default '0',
  `comment_notification_hash` varchar(32) default '',
  `openid` tinyint(4) NOT NULL default '0',
  `comment_username` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `comment_posts_id` (`comment_posts_id`),
  KEY `comment_status` (`comment_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##blogcomments`
--

LOCK TABLES `##bxcms_##blogcomments` WRITE;
/*!40000 ALTER TABLE `##bxcms_##blogcomments` DISABLE KEYS */;
/*!40000 ALTER TABLE `##bxcms_##blogcomments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##bloglinks`
--

DROP TABLE IF EXISTS `##bxcms_##bloglinks`;
CREATE TABLE `##bxcms_##bloglinks` (
  `id` int(11) NOT NULL auto_increment,
  `text` varchar(200) NOT NULL default '',
  `link` varchar(200) NOT NULL default '',
  `rss_link` varchar(200) default '',
  `rel` varchar(200) default '',
  `bloglinkscategories` int(11) default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `rang` int(11) default '0',
  `description` text,
  `blog_id` int(11) NOT NULL default '1',
  `date` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `bloglinkscategories` (`bloglinkscategories`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##bloglinks`
--

LOCK TABLES `##bxcms_##bloglinks` WRITE;
/*!40000 ALTER TABLE `##bxcms_##bloglinks` DISABLE KEYS */;
INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `blog_id`, `date`) VALUES (5,'Freeflux.net','http://freeflux.net','','',4,'2005-04-08 13:05:55',1,NULL,1,NULL);
INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `blog_id`, `date`) VALUES (6,'Liip AG','http://www.liip.ch/','','',4,'2005-04-08 13:06:09',2,NULL,1,NULL);
INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `blog_id`, `date`) VALUES (7,'netzwirt.ch','http://www.netzwirt.ch/','','',4,'2005-04-08 15:06:10',3,NULL,1,NULL);
INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `blog_id`, `date`) VALUES (8,'monorom.com','http://www.monorom.com/','','',4,'2005-04-08 15:06:10',4,NULL,1,NULL);
/*!40000 ALTER TABLE `##bxcms_##bloglinks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##bloglinkscategories`
--

DROP TABLE IF EXISTS `##bxcms_##bloglinkscategories`;
CREATE TABLE `##bxcms_##bloglinkscategories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `blog_id` int(11) NOT NULL default '1',
  `rang` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##bloglinkscategories`
--

LOCK TABLES `##bxcms_##bloglinkscategories` WRITE;
/*!40000 ALTER TABLE `##bxcms_##bloglinkscategories` DISABLE KEYS */;
INSERT INTO `##bxcms_##bloglinkscategories` (`id`, `name`, `changed`, `blog_id`, `rang`) VALUES (4,'Supported by','2005-04-08 14:05:38',1,1);
/*!40000 ALTER TABLE `##bxcms_##bloglinkscategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##blogposts`
--

DROP TABLE IF EXISTS `##bxcms_##blogposts`;
CREATE TABLE `##bxcms_##blogposts` (
  `id` int(10) NOT NULL auto_increment,
  `post_author` varchar(40) NOT NULL default 'unknown',
  `post_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `post_expires` datetime NOT NULL default '0000-00-00 00:00:00',
  `post_content` longtext NOT NULL,
  `post_content_extended` longtext NOT NULL,
  `post_content_summary` text NOT NULL,
  `post_title` text NOT NULL,
  `post_uri` varchar(255) NOT NULL default '',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `blog_id` int(11) NOT NULL default '1',
  `post_comment_mode` tinyint(4) NOT NULL default '99',
  `post_status` tinyint(4) NOT NULL default '1',
  `post_lang` varchar(2) default NULL,
  `post_info` text,
  `post_guid_version` tinyint(4) NOT NULL default '2',
  `post_author_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `post_uri` (`post_uri`),
  KEY `post_author` (`post_author`),
  KEY `post_status` (`post_status`),
  KEY `blog_id` (`blog_id`),
  FULLTEXT KEY `post_content` (`post_content`,`post_title`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##blogposts`
--

LOCK TABLES `##bxcms_##blogposts` WRITE;
/*!40000 ALTER TABLE `##bxcms_##blogposts` DISABLE KEYS */;
INSERT INTO `##bxcms_##blogposts` (`id`, `post_author`, `post_date`, `post_expires`, `post_content`, `post_content_extended`, `post_content_summary`, `post_title`, `post_uri`, `changed`, `blog_id`, `post_comment_mode`, `post_status`, `post_lang`, `post_info`, `post_guid_version`, `post_author_id`) VALUES (8,'test',date_sub(now(), INTERVAL 1 DAY),'0000-00-00 00:00:00','<p>Welcome to Flux CMS and its blog plugin.</p>\n\n<p>You can edit and posts in the admin section, if you click on the blog collection on the left side.</p>\n<p>Links and Categories can be managed via the Quicklinks dropdown on the top-right in the admin.</p>\n<p>If you have any questions, look at the <a href=\"http://docs.flux-cms.org/en/user/blog/\">blog documentation</a>,  ask on the <a href=\"http://forum.freeflux.net/\">Forum</a> or on our <a href=\"http://wiki.flux-cms.org/Support\">Mailinglist</a>.\n\n</p><p>But now, have fun ;) </p>','','','Your first Post','your-first-post',now(),1,99,1,NULL,NULL,1,0);
/*!40000 ALTER TABLE `##bxcms_##blogposts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##blogposts2categories`
--

DROP TABLE IF EXISTS `##bxcms_##blogposts2categories`;
CREATE TABLE `##bxcms_##blogposts2categories` (
  `id` int(11) NOT NULL auto_increment,
  `blogposts_id` int(11) NOT NULL default '0',
  `blogcategories_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `blogposts_id` (`blogposts_id`),
  KEY `blogcategories_id` (`blogcategories_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##blogposts2categories`
--

LOCK TABLES `##bxcms_##blogposts2categories` WRITE;
/*!40000 ALTER TABLE `##bxcms_##blogposts2categories` DISABLE KEYS */;
INSERT INTO `##bxcms_##blogposts2categories` (`id`, `blogposts_id`, `blogcategories_id`) VALUES (9,8,7);
/*!40000 ALTER TABLE `##bxcms_##blogposts2categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##comments`
--

DROP TABLE IF EXISTS `##bxcms_##comments`;
CREATE TABLE `##bxcms_##comments` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `comment_posts_id` int(11) NOT NULL default '0',
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL default '',
  `comment_author_url` varchar(100) NOT NULL default '',
  `comment_author_ip` varchar(100) NOT NULL default '',
  `comment_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment_type` varchar(20) NOT NULL default '',
  `comment_status` tinyint(4) NOT NULL default '1',
  `comment_rejectreason` text,
  `comment_hash` varchar(33) default NULL,
  `comment_notification` tinyint(4) default '0',
  `comment_notification_hash` varchar(32) default '',
  `openid` tinyint(4) NOT NULL default '0',
  `comment_username` varchar(100) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `comment_posts_id` (`comment_posts_id`),
  KEY `comment_status` (`comment_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##comments`
--

LOCK TABLES `##bxcms_##comments` WRITE;
/*!40000 ALTER TABLE `##bxcms_##comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `##bxcms_##comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##history_diff`
--

DROP TABLE IF EXISTS `##bxcms_##history_diff`;
CREATE TABLE `##bxcms_##history_diff` (
  `diff_id` bigint(20) NOT NULL auto_increment,
  `diff_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `diff_path` varchar(255) NOT NULL default '',
  `diff_value` text NOT NULL,
  PRIMARY KEY  (`diff_id`),
  KEY `diff_path` (`diff_path`),
  KEY `diff_timestamp` (`diff_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##history_diff`
--

LOCK TABLES `##bxcms_##history_diff` WRITE;
/*!40000 ALTER TABLE `##bxcms_##history_diff` DISABLE KEYS */;
/*!40000 ALTER TABLE `##bxcms_##history_diff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##locks`
--

DROP TABLE IF EXISTS `##bxcms_##locks`;
CREATE TABLE `##bxcms_##locks` (
  `token` varchar(100) NOT NULL default '',
  `path` varchar(200) NOT NULL default '',
  `expires` int(11) NOT NULL default '0',
  `owner` varchar(200) default NULL,
  `recursive` int(11) default '0',
  `writelock` int(11) default '0',
  `exclusivelock` int(11) NOT NULL default '0',
  PRIMARY KEY  (`token`),
  KEY `path` (`path`),
  KEY `expires` (`expires`),
  KEY `path_token` (`path`,`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##locks`
--

LOCK TABLES `##bxcms_##locks` WRITE;
/*!40000 ALTER TABLE `##bxcms_##locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `##bxcms_##locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##openid_profiles`
--

DROP TABLE IF EXISTS `##bxcms_##openid_profiles`;
CREATE TABLE `##bxcms_##openid_profiles` (
  `id` int(11) NOT NULL auto_increment,
  `persona` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `postal` varchar(10) NOT NULL,
  `gender` varchar(2) NOT NULL,
  `country` varchar(255) NOT NULL,
  `timezone` varchar(255) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `standard` varchar(2) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##openid_profiles`
--

LOCK TABLES `##bxcms_##openid_profiles` WRITE;
/*!40000 ALTER TABLE `##bxcms_##openid_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `##bxcms_##openid_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##openid_uri`
--

DROP TABLE IF EXISTS `##bxcms_##openid_uri`;
CREATE TABLE `##bxcms_##openid_uri` (
  `id` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `uri` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##openid_uri`
--

LOCK TABLES `##bxcms_##openid_uri` WRITE;
/*!40000 ALTER TABLE `##bxcms_##openid_uri` DISABLE KEYS */;
/*!40000 ALTER TABLE `##bxcms_##openid_uri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##options`
--

DROP TABLE IF EXISTS `##bxcms_##options`;
CREATE TABLE `##bxcms_##options` (
  `name` varchar(100) NOT NULL default '',
  `value` text,
  `isarray` tinyint(4) NOT NULL default '0',
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##options`
--

LOCK TABLES `##bxcms_##options` WRITE;
/*!40000 ALTER TABLE `##bxcms_##options` DISABLE KEYS */;
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('sitename','',0,1);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('blogname','',0,2);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('blogdescription','',0,3);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('outputLanguages','',1,4);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('image_allowed_sizes','',1,5);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('defaultLanguage','',0,6);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('sitedescription','',0,7);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('lastdbversion','10852',0,8);
/*!40000 ALTER TABLE `##bxcms_##options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##properties`
--

DROP TABLE IF EXISTS `##bxcms_##properties`;
CREATE TABLE `##bxcms_##properties` (
  `path` varchar(255) NOT NULL default '',
  `name` varchar(36) NOT NULL default '',
  `ns` varchar(40) NOT NULL default 'DAV:',
  `value` text,
  `value_date` datetime default NULL,
  `value_int` int(11) default NULL,
  UNIQUE KEY `prim` (`path`,`name`,`ns`),
  KEY `path` (`path`),
  KEY `name-ns` (`name`,`ns`),
  FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##properties`
--

LOCK TABLES `##bxcms_##properties` WRITE;
/*!40000 ALTER TABLE `##bxcms_##properties` DISABLE KEYS */;
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/index.de.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/index.de.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/index.de.xhtml','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.de.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.de.xhtml','parent-uri','bx:','/contact/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.de.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.en.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.en.xhtml','parent-uri','bx:','/contact/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.en.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/','display-name','bx:de','Kontakt',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/','display-name','bx:en','Contact',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/','display-order','bx:','40',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/index.en.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/index.en.xhtml','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/index.en.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.de.xhtml','display-name','bx:','Kontakt',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/index.en.xhtml','display-name','bx:','Contact',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/files/','display-name','bx:de','Files',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/files/','display-name','bx:en','Files',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/files/','display-order','bx:','0',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/files/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/files/','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/files/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/themes/','display-name','bx:de','Files',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/themes/','display-name','bx:en','Files',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/themes/','display-order','bx:','0',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/themes/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/themes/','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/themes/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/blog/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/blog/','output-mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/blog/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/blog/','display-name','bx:de','Blog',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/blog/','display-name','bx:en','Blog',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/blog/','display-order','bx:','10',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','output-mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','display-name','bx:de','lang',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','display-order','bx:','0',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','output-mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','display-name','bx:en','Gallery',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','display-order','bx:','20',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.en.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.en.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.en.xhtml','parent-uri','bx:','/contact/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.en.xhtml','display-name','bx:','thanks',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.en.xhtml','display-order','bx:','0',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.de.xhtml','display-name','bx:','thanks',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.de.xhtml','display-order','bx:','0',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.de.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.de.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/thanks.de.xhtml','parent-uri','bx:','/contact/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','display-name','bx:de','Bilder',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/','output-mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/','display-name','bx:en','About me',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/','display-order','bx:','30',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.en.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.en.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.en.xhtml','parent-uri','bx:','/about/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.en.xhtml','display-name','bx:','index',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.en.xhtml','display-order','bx:','99',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/','display-name','bx:de','&#220;ber mich',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.de.xhtml','display-name','bx:','index',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.de.xhtml','display-order','bx:','0',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.de.xhtml','mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.de.xhtml','output-mimetype','bx:','text/html',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/about/index.de.xhtml','parent-uri','bx:','/about/',NULL,NULL);
/*!40000 ALTER TABLE `##bxcms_##properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##properties2tags`
--

DROP TABLE IF EXISTS `##bxcms_##properties2tags`;
CREATE TABLE `##bxcms_##properties2tags` (
  `id` int(11) NOT NULL auto_increment,
  `path` varchar(255) NOT NULL default '',
  `tag_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `path` (`path`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##properties2tags`
--

LOCK TABLES `##bxcms_##properties2tags` WRITE;
/*!40000 ALTER TABLE `##bxcms_##properties2tags` DISABLE KEYS */;
INSERT INTO `##bxcms_##properties2tags` (`id`, `path`, `tag_id`) VALUES (12,'/blog/title-6fe0a6.html',11);
/*!40000 ALTER TABLE `##bxcms_##properties2tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##sidebar`
--

DROP TABLE IF EXISTS `##bxcms_##sidebar`;
CREATE TABLE `##bxcms_##sidebar` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `content` text NOT NULL,
  `sidebar` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `isxml` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##sidebar`
--

LOCK TABLES `##bxcms_##sidebar` WRITE;
/*!40000 ALTER TABLE `##bxcms_##sidebar` DISABLE KEYS */;
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (1,'links','<bloglinks/>',2,1,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (2,'buttons','<buttons/>',2,3,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (3,'html','<h3 class=\"blog\">More HTML ideas here</h3>\n',0,0,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (4,'html2','<h3 class=\"blog\">\nPlace your content here\n</h3>',0,1,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (5,'livesearch','<livesearch/>',2,0,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (6,'del.icio.us','<delicious link=\"tag/freeflux/\"/>',0,2,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (7,'login','<login/>',2,4,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (8,'archive','<archive/>',2,2,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (9,'categories','<categories/>',1,0,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (10,'latest_comments','<h3 class=\"blog\">Latest Comments</h3>\n<latest_comments/>',0,0,'2006-06-24 23:18:55',1);
INSERT INTO `##bxcms_##sidebar` (`id`, `name`, `content`, `sidebar`, `position`, `changed`, `isxml`) VALUES (11,'externalFeed','<externalFeed \n   title=\"Flux CMS DevBlog\" \n   rss=\"http://devblog.flux-cms.org/rss.xml\" \n   url=\"http://devblog.flux-cms.org/\"\n/>',0,3,'2007-03-16 12:44:51',1);
/*!40000 ALTER TABLE `##bxcms_##sidebar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##tags`
--

DROP TABLE IF EXISTS `##bxcms_##tags`;
CREATE TABLE `##bxcms_##tags` (
  `id` int(11) NOT NULL auto_increment,
  `tag` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##tags`
--

LOCK TABLES `##bxcms_##tags` WRITE;
/*!40000 ALTER TABLE `##bxcms_##tags` DISABLE KEYS */;
INSERT INTO `##bxcms_##tags` (`id`, `tag`) VALUES (11,'');
/*!40000 ALTER TABLE `##bxcms_##tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##userauthservices`
--

DROP TABLE IF EXISTS `##bxcms_##userauthservices`;
CREATE TABLE `##bxcms_##userauthservices` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `service` varchar(50) NOT NULL default '',
  `account` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##userauthservices`
--

LOCK TABLES `##bxcms_##userauthservices` WRITE;
/*!40000 ALTER TABLE `##bxcms_##userauthservices` DISABLE KEYS */;
/*!40000 ALTER TABLE `##bxcms_##userauthservices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `##bxcms_##users`
--

DROP TABLE IF EXISTS `##bxcms_##users`;
CREATE TABLE `##bxcms_##users` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `user_login` varchar(80) default NULL,
  `user_pass` varchar(150) default NULL,
  `user_email` varchar(100) NOT NULL default '',
  `user_fullname` varchar(100) NOT NULL default '',
  `user_gupi` varchar(16) default NULL,
  `user_gid` int(11) NOT NULL default '1',
  `user_tmphash` varchar(32) NOT NULL default '',
  `user_adminlang` varchar(5) default '',
  `plazes_username` varchar(40) default NULL,
  `plazes_password` varchar(32) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `user_login` (`user_login`),
  KEY `user_pass` (`user_pass`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `##bxcms_##users`
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2008-10-14 11:41:41
