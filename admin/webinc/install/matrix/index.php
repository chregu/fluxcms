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

// only a logged in admin may install a plugin
if (!$permObj->isAllowed('/',array('admin'))) {
    print "Access denied";
    die();
}
$tablePrefix = $conf->getTablePrefix();

echo "<h1>starting install matrix permission system</h1>";

print "<pre/>";
$db = $GLOBALS['POOL']->dbwrite;


$queries["creating groups table"] = "CREATE TABLE `".$tablePrefix."groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY name (name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$queries["creating basic groups annonymous and authenticated"] = "
    INSERT INTO {$tablePrefix}groups (id, name) VALUES
    (1, 'anonymous'),
    (2, 'authenticated');
    ";

$queries["creating permissions table"] = "CREATE TABLE `".$tablePrefix."perms` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_group` int(10) unsigned NOT NULL,
  `plugin` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `uri` varchar(100) NOT NULL,
  # `inherit` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `plugin` (`plugin`),
  KEY `action` (`action`),
  KEY `uri` (`uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

// grant authenticated users basic rights
$plugin_objects[] = new bx_plugins_permissions();
$plugin_objects[] = new bx_plugins_collection();
$plugin_objects[] = new bx_plugins_xhtml();
$values = '';
foreach ($plugin_objects as $plugin_object) {
    foreach ($plugin_object->getPermissionList() as $permission) {
        $uri = '/';
        list($plugin, $level, $perm) = explode('-', $permission);
        if ($plugin == "admin_dbforms2") {
            $uri = "/dbforms2/";
        }
        $values .= "(2, '$plugin', '$permission', '$uri'),\n";
    }
}
$values .= "(1, 'collection', 'collection-front-read_navi', '/'),
(1, 'collection', 'collection-front-read', '/'),
    ";
$values = trim($values, " ,\n");
$queries["creating basic permissions for annonymous and authenticated"] = "
    INSERT INTO {$tablePrefix}perms 
    (
        `fk_group`,
        `plugin`,
        `action`,
        `uri`
    )
    VALUES
    $values
    ";

$queries["creating user to groups relation table"] = "CREATE TABLE `".$tablePrefix."users2groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_user` int(10) unsigned NOT NULL,
  `fk_group` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `fk_user` (`fk_user`,`fk_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

echo "<ul>";
foreach ($queries as $label => $query) {
    $res = $db->query($query);
    if ($db->isError($res)) {
        "installation failed, please report to milo@flux-cms.org";    
         printError($res);
    } else {
        echo "<li>" . $label . "</li>";
    }
}
echo "</ul>";

echo "<p>Matrix-Plugin-Tables <b style='color: red;'>successfully</b> created.</p>";

$id       = "/";
$name     = "permissions";
$name_url = bx_helpers_string::makeUri($name);
$collection = bx_plugins_admin_collection::getInstance('admin');

if ($collection->makeCollection($id.$name_url, $name)) {

    echo "<p>The /permissions/ collection was <b style='color: red;'>successfully</b> created.</p>";

    $file    = BX_DATA_DIR . $id . $name . "/" . ".configxml";
    $success = file_put_contents($file,bx_helpers_string::utf2entities(getConfigXml()));

    if ($success) {
        echo "<p>The /permissions/ collection's was <b style='color: red;'>successfully</b> created with the following content:</p>";
    } else {
        echo "<p>Now add the following .configxm to the /permissions/ collection:</p>";
    }

} else {
    echo "<p>Now <b style='color: red;'>you</b> can <b style='color: red;'>create the permissions collection</b> with the following .configxml:</p>";
}
printConfigXML();

echo "<h2>Success ;)</h2>";

echo <<<EOT
<h1>Finishing Up</h1>
<p>To activate matrix <b style='color: red;'>you need to</b> <b>uncomment</b> the permm section in your conf/config.xml file <b>and fill in</b> 'matrix' as type for the <tt>permModule</tt>. It should look as follows:</p>
EOT;
echo "<pre>" . htmlentities(getFinishUp()) . "</pre>";

/**
 * just prints the configxml used for linkplugin.
 * */
function printConfigXML() {
	print '<pre>'.htmlentities(getConfigXml()).'</pre>';
}

function getConfigXml()
{
    $configxml = '<bxcms xmlns ="http://bitflux.org/config">
    <plugins>
        <parameter name ="xslt" type="pipeline" value ="static.xsl"/>
        <plugin type ="permissions">
        </plugin>
    </plugins>
</bxcms> ';
    return $configxml;
}

// additional functions:
function printError($res) {
    if ($GLOBALS['POOL']->db->isError($res)) {
        print $res->message ."\n";
        print $res->userinfo ."\n";
        die();
    }
}

function getFinishUp()
{
    return <<<EOT
        <permm type="permm">
            <authModule>
                <type>pearauth</type>
                <auth_table>users</auth_table>
                <auth_prependTablePrefix>true</auth_prependTablePrefix>
                <auth_usernamecol>user_login</auth_usernamecol>
                <auth_passwordcol>user_pass</auth_passwordcol>
                <auth_dbfields></auth_dbfields>
                <auth_overwriteDbfields>false</auth_overwriteDbfields>
                <cryptType>md5</cryptType>
                <dsn copy="auth_dsn"/>
            </authModule>
            <permModule>
                <type>matrix</type>
            </permModule>
        </permm>
EOT;
}

ob_end_flush();
// include_once(BX_LIBS_DIR."/tools/dbupdate/update.php");
