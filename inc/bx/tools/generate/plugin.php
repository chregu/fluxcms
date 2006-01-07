<?php
require_once("inc/bx/init.php");
bx_init::start('conf/config.xml', "");

define('PHP_CLASSPATH', getcwd(). "/install/phing/classes/" . PATH_SEPARATOR . getcwd()."/inc/". PATH_SEPARATOR . get_include_path());
ini_set('include_path', PHP_CLASSPATH);
require_once 'phing/Phing.php';
require_once 'inc/bx/tools/generate/db2forms.php';


/* Setup Phing environment */
Phing::startup();

 

$generateDir =  getcwd()."/inc/bx/tools/generate/";
Phing::setProperty("BxRootDir",getcwd()."/");
Phing::setProperty("theme",$GLOBALS['POOL']->config->theme);
$args = array("-buildfile", $generateDir."builds/plugin.xml");
/* fire main application */

Phing::fire($args);