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
// | Author: Alain Petignat <alain@flux-cms.org>                          |
// +----------------------------------------------------------------------+
//
// $Id: index.php 4336 2005-05-26 09:20:14Z chregu $	



include_once("../../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../../..");

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);

if (!$permObj->isAllowed('/',array('admin'))) {
    print "Access denied";
    die();
}
$tablePrefix = $conf->getTablePrefix();

// $tablePrefix = "mytest8_";

echo "$tablePrefix";
echo "starting install newsletter";

print "<pre/>";
$db = $GLOBALS['POOL']->dbwrite;


$queries[] = "CREATE TABLE `".$tablePrefix."mail_queue` (
  `id` bigint(20) NOT NULL default '0',
  `create_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `time_to_send` datetime NOT NULL default '0000-00-00 00:00:00',
  `sent_time` datetime default NULL,
  `id_user` bigint(20) NOT NULL default '0',
  `ip` varchar(20) NOT NULL default 'unknown',
  `sender` varchar(50) NOT NULL default '',
  `recipient` text NOT NULL,
  `headers` text NOT NULL,
  `body` longtext NOT NULL,
  `try_sent` tinyint(4) NOT NULL default '0',
  `delete_after_send` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `time_to_send` (`time_to_send`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."mail_queue_seq` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_drafts` (
  `from` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `htmlfile` varchar(50) NOT NULL,
  `textfile` varchar(50) NOT NULL,
  `attachment` varchar(150) NOT NULL,
  `sent` timestamp NOT NULL default '0000-00-00 00:00:00',
  `prepared` timestamp NOT NULL default '0000-00-00 00:00:00',
  `ID` int(10) unsigned NOT NULL auto_increment,
  `class` varchar(60) NOT NULL,
  `mailserver` int(10) unsigned NOT NULL,
  `group` varchar(255) NOT NULL default '0', 
  `embed` tinyint(4) NOT NULL,
  `baseurl` varchar(100) NOT NULL,
  `colluri` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_drafts2groups` (
  `fk_draft` int(10) unsigned NOT NULL,
  `fk_group` int(10) unsigned NOT NULL,
  `ID` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`ID`),
  KEY `fk_group` (`fk_group`),
  KEY `fk_draft` (`fk_draft`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_feeds` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `url` varchar(250) NOT NULL,
  `lastdate` varchar(20) NOT NULL default '00000000000000',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_from` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `sender` varchar(100) NOT NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `from` (`sender`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_groups` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `public` tinyint(4) NOT NULL default '1',
  `optin` tinyint(4) NOT NULL default '0',
  `test` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_mailservers` (
  `host` varchar(100) NOT NULL,
  `port` varchar(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `descr` varchar(100) NOT NULL,
  `ID` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_users` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `firstname` varchar(100) default NULL,
  `lastname` varchar(100) default NULL,
  `email` varchar(100) NOT NULL,
  `gender` tinyint(4) NOT NULL default '0',
  `activation` int(10) unsigned NOT NULL default '0',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `status` tinyint(4) NOT NULL default '1',
  `bounced` smallint(6) NOT NULL default '0',
  `lastevent` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_users2groups` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `fk_user` int(10) unsigned NOT NULL,
  `fk_group` int(10) unsigned NOT NULL,
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `fk_group` (`fk_group`,`fk_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;";

$queries[] = "CREATE TABLE `".$tablePrefix."newsletter_cache` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_user` int(10) unsigned NOT NULL,
  `fk_draft` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


foreach($queries as $query){
    $res = $db->query($query);
    if ($db->isError($res)) {
        "installation failed, please report to milo@flux-cms.org";    
         printError($res);
    }
}

echo "<h1>Success ;)</h1>";
echo "<p>Newsletter-Plugin-Tables successfully created. Now you can create the newsletter collection with the following .configxml (adjust the activation properties according to your environment):</p>";

printConfigXML();

echo "<p>Now create the following collections inside of newsletter: archive, archive/YYYY (e.g 2006) and drafts. The archive path must be visible in order to view newsletters sent directly from the website. Also make sure, you have newsfeeds.xsl and htmlimage.xsl in your themes-folder. Defaults can be found in 3-cols.</p>";

// Add resources

bx_resourcemanager::setProperty("/newsletter/index.de.xhtml", "parent-uri", "/newsletter/");
bx_resourcemanager::setProperty("/newsletter/index.de.xhtml", "display-name", "Index");
bx_resourcemanager::setProperty("/newsletter/index.de.xhtml", "display-order", "0");
bx_resourcemanager::setProperty("/newsletter/index.de.xhtml", "mimetype", "text/html");
bx_resourcemanager::setProperty("/newsletter/index.de.xhtml", "output-mimetype", "text/html");

bx_resourcemanager::setProperty("/newsletter/subscribe.de.xhtml", "parent-uri", "/newsletter/");
bx_resourcemanager::setProperty("/newsletter/subscribe.de.xhtml", "display-name", "Subscribe");
bx_resourcemanager::setProperty("/newsletter/subscribe.de.xhtml", "display-order", "1");
bx_resourcemanager::setProperty("/newsletter/subscribe.de.xhtml", "mimetype", "text/html");
bx_resourcemanager::setProperty("/newsletter/subscribe.de.xhtml", "output-mimetype", "text/html");

bx_resourcemanager::setProperty("/newsletter/unsubscribe.de.xhtml", "parent-uri", "/newsletter/");
bx_resourcemanager::setProperty("/newsletter/unsubscribe.de.xhtml", "display-name", "Unsubscribe");
bx_resourcemanager::setProperty("/newsletter/unsubscribe.de.xhtml", "display-order", "2");
bx_resourcemanager::setProperty("/newsletter/unsubscribe.de.xhtml", "mimetype", "text/html");
bx_resourcemanager::setProperty("/newsletter/unsubscribe.de.xhtml", "output-mimetype", "text/html");

bx_resourcemanager::setProperty("/newsletter/activation-txt.de.xhtml", "parent-uri", "/newsletter/");
bx_resourcemanager::setProperty("/newsletter/activation-txt.de.xhtml", "display-name", "Activation");
bx_resourcemanager::setProperty("/newsletter/activation-txt.de.xhtml", "display-order", "0");
bx_resourcemanager::setProperty("/newsletter/activation-txt.de.xhtml", "mimetype", "text/html");
bx_resourcemanager::setProperty("/newsletter/activation-txt.de.xhtml", "output-mimetype", "text/html");

/**
 * just prints the configxml used for linkplugin.
 * */
function printConfigXML() {
$configxml = '<bxcms xmlns ="http://bitflux.org/config">
<plugins>

<parameter name ="xslt" type="pipeline" value ="static.xsl"/>

<plugin type ="newsletter">
  <parameter name="sendclass" value="newsmailer"/>
  <parameter name="activation-server" value="Bitflux"/>
  <parameter name="activation-from" value="milo@bitflux.ch"/>
  <parameter name="activation-subject" value="Newsletter Aktivierung"/>
  <parameter name="activation-text" value="activation-txt.de.xhtml"/>
</plugin>

<plugin type ="navitree"></plugin>

<plugin type ="xhtml"></plugin>
</plugins>

</bxcms>';

	print '<pre>'.htmlentities($configxml).'</pre>';

}

// additional functions:
function printError($res) {
    if ($GLOBALS['POOL']->db->isError($res)) {
        print $res->message ."\n";
        print $res->userinfo ."\n";
        die();
    }
}



// include_once(BX_LIBS_DIR."/tools/dbupdate/update.php");

