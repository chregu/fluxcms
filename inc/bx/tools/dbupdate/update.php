<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Bitflux GmbH                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+
//
// $Id$





print "<pre/>";
$db = $GLOBALS['POOL']->dbwrite;

// check for last version
$res = $db->query("select value from  ".$tablePrefix."options where name = 'lastdbversion'");


if ($db->isError($res)) {
    // if no such table, create it
    if ($res->code == -18) {
        $lastVersion = 3779;
        
    } else {
        printError($res);
    }
} else {
    $lastVersion = $res->fetchOne(0,0);
    if (!$lastVersion) {
        print "no lastdbversion found in option table, try to get it from _state \n";
        $lastVersion = $db->queryOne("select value from  ".$tablePrefix."_state where name = 'lastdbversion'");
        if ($db->isError($lastVersion) || !$lastVersion) {
            $lastVersion = 3779;
        }
        doQuery("insert into ".$tablePrefix."options (name, value) VALUES ('lastdbversion', $lastVersion)");
    }
    
}
/** add user_gid into users table **/
if ($lastVersion < 3780) {
  addCol('users','user_gid','int',"'1'");  
  addCol('bloglinks','rel','varchar(40)',"''");
  updateLastVersion(3780);
}

if ($lastVersion < 3839) {

    $res = doQuery("CREATE TABLE  `".$tablePrefix."options` (
    `id` int(11) NOT NULL auto_increment,
    `name` varchar(100) NOT NULL default '',
    `value` varchar(200) NOT NULL default '',
    `isarray` tinyint(4) NOT NULL default '0',
    PRIMARY KEY  (`id`)
    ) TYPE=MyISAM;",false);
    if (MDB2::isError($res)) {
        if ($res->code == -5) {
            print $tablePrefix."options already exists, moving forward \n";
            
        } else {
            printError($res);
        }
    }
    doQuery("insert into ".$tablePrefix."options (name, value) VALUES ('lastdbversion', $lastVersion)");
    updateLastVersion(3839);
        
    
}

if ($lastVersion < 3991) {
     addCol('users','user_tmphash','varchar(32)',"''");  
     updateLastVersion(3991);
}


if ($lastVersion < 4210) {
    addCol('properties','value_date','DATETIME');
    addCol('properties','value_int','INT');
    doQuery("ALTER TABLE `{tablePrefix}properties` ADD FULLTEXT (
`value`
);",false);
 updateLastVersion(4210);
}

if ($lastVersion < 4218) {
    
    $res = doQueryTable("CREATE TABLE `".$tablePrefix."properties2tags` (
    `id` int(11) NOT NULL auto_increment,
    `path` varchar(255) NOT NULL default '',
    `tag_id` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`),
    KEY `path` (`path`),
    KEY `tag_id` (`tag_id`)
    )","properties2tags");
    
    $res = doQueryTable("CREATE TABLE `".$tablePrefix."tags` (
    `id` int(11) NOT NULL auto_increment,
    `tag` varchar(255) NOT NULL default '',
    PRIMARY KEY  (`id`),
    KEY `tag` (`tag`)
    )","tags");
    updateLastVersion(4218);
}

if ($lastVersion < 4219) {
    doQuery("ALTER TABLE `{tablePrefix}properties` ADD INDEX  `name-ns` ( `name` , `ns` ) ",false);
    updateLastVersion(4219);
}

if ($lastVersion < 4233) {
    addCol('bloglinks','description','TEXT');
    updateLastVersion(4233);
}

if ($lastVersion < 4265) {
    addCol('bloglinks','date','DATETIME');
    updateLastVersion(4265);
}


if ($lastVersion < 4284) {
    addCol('blogposts','post_content_extended','TEXT NOT NULL');
    addCol('blogposts','post_content_summary','TEXT NOT NULL');
    updateLastVersion(4284);
}


if ($lastVersion < 4328) {
    doQuery("ALTER TABLE `{tablePrefix}blogposts` DROP `post_keywords`",false);
    updateLastVersion(4328);
}

if ($lastVersion < 4534) {
    addCol('blogcomments','comment_rejectreason','TEXT');
    updateLastVersion(4534);  
}

if ($lastVersion < 4841) {
    doQuery("ALTER TABLE `{tablePrefix}options` CHANGE `value` `value` TEXT NULL ",false);
    updateLastVersion(4841);  
}


if ($lastVersion < 5084) {
    addCol('blogposts','post_lang','CHAR(2)');
    updateLastVersion(5084);  
}


if ($lastVersion < 5163) {
 doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `rel` `rel` VARCHAR( 200 )  NULL",false); 
 doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `bloglinkscategories` `bloglinkscategories` INT( 11 ) NULL DEFAULT '0'",false);
 doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `rang` `rang` INT( 11 ) DEFAULT '0'",false);
  updateLastVersion(5163);  
}
if ($lastVersion < 5266) {
    addCol('blogcomments','comment_hash','VARCHAR( 33 ) NULL');
    updateLastVersion(5266);
}

if ($lastVersion < 5282) {
    addCol('blogposts','post_info', 'TEXT NULL');
    updateLastVersion(5282);
}

if ($lastVersion < 5283) {
    addCol('users','plazes_username', 'VARCHAR(40) NULL');
    addCol('users','plazes_password', 'VARCHAR(32) NULL');
    updateLastVersion(5283);
}

if ($lastVersion < 5349) {
    doQuery("ALTER TABLE `{tablePrefix}blogposts` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT",false); 
    updateLastVersion(5349);
}

if ($lastVersion < 5390) {
    addCol('users','user_adminlang', 'VARCHAR(5) NULL');
    updateLastVersion(5390);
}

if ($lastVersion < 5522) {
    doQuery("update  `{tablePrefix}blogposts` set post_comment_mode = 99 where post_comment_mode = ". $GLOBALS['POOL']->config->blogDefaultPostCommentMode,false); 
    updateLastVersion(5522);
}

if ($lastVersion < 5643) {
    addCol('blogcomments','comment_notification', "tinyint(4) NOT NULL default '0'");
    addCol('blogcomments','comment_notification_hash', "varchar(32) NULL");
    updateLastVersion(5643);
}


if ($lastVersion < 5673) {
    doQuery("ALTER TABLE `{tablePrefix}blogcomments` CHANGE  `comment_notification` `comment_notification` TINYINT( 4 ) DEFAULT '0'",false);
    updateLastVersion(5673);
}

if ($lastVersion < 5718) {
    doQuery("update {tablePrefix}blogposts set post_status = 1 where post_status = 0", false);
    updateLastVersion(5718);
}

if ($lastVersion < 5812) {
    addCol('bloglinks','rss_link', "varchar(200) NULL");
    updateLastVersion(5812);
}
  
  
if ($lastVersion < 5849) {
  doQuery("ALTER TABLE `{tablePrefix}blogposts` DROP `post_category`, DROP `post_karma`;", false);
  addCol('blogposts','post_guid_version',"TINYINT DEFAULT '2' NOT NULL");
  doQuery("update `{tablePrefix}blogposts` set post_guid_version = 1, changed = changed");
  updateLastVersion(5849);
}

if ($lastVersion < 6247) {
    addCol('blogposts','post_expires',"DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    updateLastVersion(6286);
}

if ($lastVersion < 6286) {
    doQuery("ALTER TABLE `{tablePrefix}blogposts` CHANGE `post_expires` `post_expires` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",false);
    updateLastVersion(6286);
}

if ($lastVersion < 6328) {

    doQuery("ALTER TABLE `{tablePrefix}properties` CHANGE `name` `name` VARCHAR( 36 ) NOT NULL",false);
    doQuery("ALTER TABLE `{tablePrefix}properties` CHANGE `ns` `ns` VARCHAR( 40 ) DEFAULT 'DAV:' NOT NULL",false);
    updateLastVersion(6328);
}

if ($lastVersion < 6469) {
    doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `changed` `changed` TIMESTAMP NOT NULL",false);
     updateLastVersion(6469);
}

if ($lastVersion < 6478) {
    addCol('blogposts','blog_id',"INT NOT NULL DEFAULT 1");
    updateLastVersion(6478);
}

if ($lastVersion < 6504) {
    addCol('bloglinks','blog_id',"INT NOT NULL DEFAULT 1");
    addCol('bloglinkscategories','blog_id',"INT NOT NULL DEFAULT 1");
    updateLastVersion(6504);
}

if ($lastVersion < 6520) {
    addCol('blogcategories','blog_id',"INT NOT NULL DEFAULT 1");
    updateLastVersion(6520);
}


if ($lastVersion < 6595) {
    $res = doQueryTable("
    CREATE TABLE `".$tablePrefix."openid_uri` (
      `id` int(11) NOT NULL auto_increment,
      `date` date NOT NULL default '0000-00-00',
      `uri` varchar(255) NOT NULL default '',
      PRIMARY KEY  (`id`)
      )",'openid_uri');
      addCol("blogcomments","openid"," TINYINT( 4 ) DEFAULT '0' NOT NULL");
      updateLastVersion(6595);
}



// delete config files
@unlink(BX_TEMP_DIR."/config.inc.php");
@unlink(BX_TEMP_DIR."/config.inc.php.post");

print "DB up-to-date \n";


/** FUNCTIONS **/

function doQueryTable($query,$tableName) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $res = doQuery($query,false);
    if ($GLOBALS['POOL']->db->isError($res)) {
        if ($res->code == -5) {
            print $tablePrefix.$tableName ." already exists, moving forward \n";
            
        } else {
            printError($res);
        }
    }
    
}

function doQuery($query,$fatal=true) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $db = $GLOBALS['POOL']->db;
    $query = str_replace("{tablePrefix}",  $tablePrefix,$query);
    print "Do " . $query ."\n\n";
    $res = $db->query($query);
    if ($fatal) {
        printError($res);
    } else {
        return $res;
    }
}

function printError($res) {
    if ($GLOBALS['POOL']->db->isError($res)) {
        print $res->message ."\n";
        print $res->userinfo ."\n";
        die();
    }
}

function updateLastVersion($version) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $query = "update  ".$tablePrefix."options set value = $version where name = 'lastdbversion'";
    
    doQuery($query);
}

function addCol($table,$name,$type,$default = null) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $query = "ALTER TABLE `".$tablePrefix."$table` ADD `$name` $type";
    if ($default) {
        $query .= " DEFAULT $default";
    }
    $res = doQuery($query, false);
    if ($GLOBALS['POOL']->db->isError($res)) {
        if ($res->code == -1) {
            print "  '$name' already exists in '".$tablePrefix."$table' (non fatal) \n\n";
        } else {
            printError($res);
        }
    }
}

