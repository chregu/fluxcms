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
echo "starting install matrix permission system";

print "<pre/>";
$db = $GLOBALS['POOL']->dbwrite;


$queries[] = "CREATE TABLE `".$tablePrefix."groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."perms` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_group` int(10) unsigned NOT NULL,
  `plugin` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `uri` varchar(100) NOT NULL,
  `inherit` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `plugin` (`plugin`),
  KEY `action` (`action`),
  KEY `uri` (`uri`,`inherit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries[] = "CREATE TABLE `".$tablePrefix."users2groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_user` int(10) unsigned NOT NULL,
  `fk_group` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `fk_user` (`fk_user`,`fk_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


foreach($queries as $query){
    $res = $db->query($query);
    if ($db->isError($res)) {
        "installation failed, please report to milo@flux-cms.org";    
         printError($res);
    }
}

echo "<h1>Success ;)</h1>";
echo "<p>Matrix-Plugin-Tables successfully created. Now you can create the permissions collection with the following .configxml:</p>";

printConfigXML();

echo "<p>Create two groups called 'anonymous' and 'authenticated'. This allows you to give permissions based on the users role.</p>";


/**
 * just prints the configxml used for linkplugin.
 * */
function printConfigXML() {
$configxml = '<bxcms xmlns ="http://bitflux.org/config">
<plugins>

<parameter name ="xslt" type="pipeline" value ="static.xsl"/>

<plugin type ="permissions">
</plugin>

</plugins>

</bxcms> ';

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

