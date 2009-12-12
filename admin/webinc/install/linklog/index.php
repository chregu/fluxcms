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
echo "starting install linklog";

print "<pre/>";
$db = $GLOBALS['POOL']->dbwrite;

// links
$queries[] = "CREATE TABLE `".$tablePrefix."linklog_links` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(40) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `status` smallint(2) NOT NULL default '0',
  `timeadded` varchar(12) default NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

// tags
$queries[] = "CREATE TABLE `".$tablePrefix."linklog_tags` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) character set latin1 NOT NULL default '',
  `fulluri` varchar(255) character set latin1 default NULL,
  PRIMARY KEY  (`id`),
  KEY `SRLR` (`id`),
  KEY `node_id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

// links2tags
$queries[] = "CREATE TABLE `".$tablePrefix."linklog_links2tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `linkid` int(10) unsigned NOT NULL default '0',
  `tagid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `lid` (`linkid`,`tagid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = 'ALTER TABLE '.$tablePrefix.'linklog_links ADD UNIQUE (url)';


foreach($queries as $query){
    $res = $db->query($query);
    if ($db->isError($res)) {
        "installation failed, please report to alain@flux-cms.org";    
         printError($res);
    }
}

echo "<h1>Success ;)</h1>";
echo "<p>Linklog-Plugin-Tables successfully created. Now you can create a collection with the following .configxml:</p>";

printConfigXML();

echo "<p>Make sure, you have the correspondig linklog.xsl in your themes-folder. Default can be found in 2-cols and 3-cols.</p>";


/**
 * just prints the configxml used for linkplugin.
 * */
function printConfigXML() {
$configxml = '<bxcms xmlns="http://bitflux.org/config">
    <plugins inGetChildren="false">
        <extension type="xml"/>
        <file preg="#plugin=#"/>
        <plugin type="linklog">
        </plugin>
    </plugins>

    <plugins>
        <parameter name="xslt" type="pipeline" value="linklog.xsl"/>
         <extension type="html"/>
         <plugin type="linklog">
            <!-- provide your del.icio.us-username here -->
            <parameter name="deliciousname" value="" />
         </plugin>
         <plugin type="navitree"></plugin>
    </plugins>

    <plugins inGetChildren="false">
        <extension type="xml"/>
        <file preg="#rss$#"/>
        <parameter name="output-mimetype" type="pipeline" value="text/xml"/>
        <parameter type="pipeline" name="xslt" value="../standard/plugins/linklog/linklog2rss.xsl"/>
        <plugin type="linklog">
            <parameter name="mode" value="rss"/>
        </plugin>
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

