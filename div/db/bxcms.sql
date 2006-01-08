-- MySQL dump 10.9
--
-- Host: localhost    Database: bxcms
-- ------------------------------------------------------
-- Server version	4.1.12-max
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,MYSQL40' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `##bxcms_##_sequences_seq`
--

DROP TABLE IF EXISTS `##bxcms_##_sequences_seq`;
CREATE TABLE `##bxcms_##_sequences_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
);

--
-- Dumping data for table `##bxcms_##_sequences_seq`
--

INSERT INTO `##bxcms_##_sequences_seq` (`sequence`) VALUES (9);

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
  `changed` timestamp NOT NULL,
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `l` (`l`),
  KEY `r` (`r`),
  KEY `fulluri` (`fulluri`)
);

--
-- Dumping data for table `##bxcms_##blogcategories`
--

INSERT INTO `##bxcms_##blogcategories` (`id`, `name`, `uri`, `l`, `r`, `fulluri`, `parentid`, `fullname`, `changed`, `status`) VALUES (1,'All','root',1,6,'root',0,'root','2005-04-08 15:07:35',1);
INSERT INTO `##bxcms_##blogcategories` (`id`, `name`, `uri`, `l`, `r`, `fulluri`, `parentid`, `fullname`, `changed`, `status`) VALUES (3,'Moblog Pictures','moblog',4,5,'moblog',1,'Moblog Pictures','2005-04-08 15:07:35',1);
INSERT INTO `##bxcms_##blogcategories` (`id`, `name`, `uri`, `l`, `r`, `fulluri`, `parentid`, `fullname`, `changed`, `status`) VALUES (7,'General','general',2,3,'general',1,'General','2005-04-08 15:07:35',1);

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
  `changed` timestamp NOT NULL,
  `comment_type` varchar(20) NOT NULL default '',
  `comment_status` tinyint(4) NOT NULL default '1',
  `comment_rejectreason` text,
  `comment_hash` varchar(33) default NULL,
  `comment_notification` tinyint(4) default '0',
  `comment_notification_hash` varchar(32) default '',
  PRIMARY KEY  (`id`),
  KEY `comment_posts_id` (`comment_posts_id`)
);

--
-- Dumping data for table `##bxcms_##blogcomments`
--


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
  `changed` timestamp NULL default NULL,
  `rang` int(11) default '0',
  `description` text,
  `date` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `bloglinkscategories` (`bloglinkscategories`)
);

--
-- Dumping data for table `##bxcms_##bloglinks`
--

INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `date`) VALUES (5,'Freeflux.net','http://freeflux.net','','',4,'2005-04-08 15:05:55',1,NULL,NULL);
INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `date`) VALUES (6,'Bitflux GmbH','http://www.bitflux.ch/','','',4,'2005-04-08 15:06:09',2,NULL,NULL);
INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `date`) VALUES (7,'netzwirt.ch','http://www.netzwirt.ch/','','',4,'2005-04-08 17:06:10',3,NULL,NULL);
INSERT INTO `##bxcms_##bloglinks` (`id`, `text`, `link`, `rss_link`, `rel`, `bloglinkscategories`, `changed`, `rang`, `description`, `date`) VALUES (8,'monorom.com','http://www.monorom.com/','','',4,'2005-04-08 17:06:10',4,NULL,NULL);

--
-- Table structure for table `##bxcms_##bloglinkscategories`
--

DROP TABLE IF EXISTS `##bxcms_##bloglinkscategories`;
CREATE TABLE `##bxcms_##bloglinkscategories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `changed` timestamp NOT NULL,
  `rang` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

--
-- Dumping data for table `##bxcms_##bloglinkscategories`
--

INSERT INTO `##bxcms_##bloglinkscategories` (`id`, `name`, `changed`, `rang`) VALUES (4,'Supported by','2005-04-08 16:05:38',1);

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
  `changed` timestamp NOT NULL,
  `post_comment_mode` tinyint(4) NOT NULL default '99',
  `post_status` tinyint(4) NOT NULL default '1',
  `post_lang` char(2) default NULL,
  `post_info` text,
  `post_guid_version` tinyint(4) NOT NULL default '2',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `post_uri` (`post_uri`),
  KEY `post_author` (`post_author`),
  FULLTEXT KEY `post_content` (`post_content`,`post_title`)
);

--
-- Dumping data for table `##bxcms_##blogposts`
--

INSERT INTO `##bxcms_##blogposts` (`id`, `post_author`, `post_date`, `post_content`, `post_content_extended`, `post_content_summary`, `post_title`, `post_uri`, `changed`, `post_comment_mode`, `post_status`, `post_lang`, `post_info`, `post_guid_version`) VALUES (8,'test','2006-01-03 16:10:00','<p>Welcome to Flux CMS and its blog plugin.</p>\n\n<p>You can edit and posts in the admin section, if you click on the blog collection on the left side.</p>\n<p>Links and Categories can be managed via the Quicklinks dropdown on the top-right in the admin.</p>\n<p>If you have any questions, look at the <a href=\"http://docs.bitflux.org/en/user/blog/\">blog documentation</a>,  ask on the <a href=\"http://forum.freeflux.net/\">Forum</a> or on our <a href=\"http://wiki.bitflux.org/Support\">Mailinglist</a>.\n\n</p><p>But now, have fun ;) </p>','','','Your first Post','your-first-post','2005-12-29 08:34:06',99,1,NULL,NULL,1);

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
);

--
-- Dumping data for table `##bxcms_##blogposts2categories`
--

INSERT INTO `##bxcms_##blogposts2categories` (`id`, `blogposts_id`, `blogcategories_id`) VALUES (9,8,7);

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
);

--
-- Dumping data for table `##bxcms_##locks`
--


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
);

--
-- Dumping data for table `##bxcms_##options`
--

INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('sitename','',0,1);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('blogname','',0,2);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('blogdescription','',0,3);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('outputLanguages','',1,4);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('image_allowed_sizes','',1,5);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('defaultLanguage','',0,6);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('sitedescription','',0,7);
INSERT INTO `##bxcms_##options` (`name`, `value`, `isarray`, `id`) VALUES ('lastdbversion','6286',0,8);

--
-- Table structure for table `##bxcms_##properties`
--

DROP TABLE IF EXISTS `##bxcms_##properties`;
CREATE TABLE `##bxcms_##properties` (
  `path` varchar(255) NOT NULL default '',
  `name` varchar(120) NOT NULL default '',
  `ns` varchar(120) NOT NULL default 'DAV:',
  `value` text,
  `value_date` datetime default NULL,
  `value_int` int(11) default NULL,
  UNIQUE KEY `prim` (`path`,`name`,`ns`),
  KEY `path` (`path`),
  KEY `name-ns` (`name`,`ns`),
  FULLTEXT KEY `value` (`value`)
);

--
-- Dumping data for table `##bxcms_##properties`
--

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
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/contact/','display-order','bx:','10',NULL,NULL);
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
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/blog/','display-order','bx:','1',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','output-mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','display-name','bx:de','lang',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/lang/','display-order','bx:','0',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','output-mimetype','bx:','httpd/unix-directory',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','parent-uri','bx:','/',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','display-name','bx:en','Gallery',NULL,NULL);
INSERT INTO `##bxcms_##properties` (`path`, `name`, `ns`, `value`, `value_date`, `value_int`) VALUES ('/gallery/','display-order','bx:','2',NULL,NULL);
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
);

--
-- Dumping data for table `##bxcms_##properties2tags`
--


--
-- Table structure for table `##bxcms_##tags`
--

DROP TABLE IF EXISTS `##bxcms_##tags`;
CREATE TABLE `##bxcms_##tags` (
  `id` int(11) NOT NULL auto_increment,
  `tag` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`)
);

--
-- Dumping data for table `##bxcms_##tags`
--


--
-- Table structure for table `##bxcms_##users`
--

DROP TABLE IF EXISTS `##bxcms_##users`;
CREATE TABLE `##bxcms_##users` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `user_login` varchar(80) default NULL,
  `user_pass` varchar(32) default NULL,
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
);

--
-- Dumping data for table `##bxcms_##users`
--


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

