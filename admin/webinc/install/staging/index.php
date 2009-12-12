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

$staging = $GLOBALS['POOL']->config->staging;

bx_helpers_debug::webdump($staging);

if (!$staging) {
    
    die("You don't have staging enabled in your config.xml file");
}

$live = array_keys($staging);;
$live = $live[0];

$hostsdir = preg_replace("#".$live."/*#","",BX_OPEN_BASEDIR);

if (!file_exists($hostsdir)) {
    if (!mkdir($hostsdir)) {
        die("Could not mkdir $hostsdir");
    }    
} else if (!is_writeable($hostsdir)) {
    
    die("$hostsdir is not writable.");
}

if (!file_exists(BX_OPEN_BASEDIR)) {
    if (!mkdir(BX_OPEN_BASEDIR)) {
        die("Could not mkdir ". BX_OPEN_BASEDIR);
    }    
} else if (!is_writeable(BX_OPEN_BASEDIR)) {
    
    die(BX_OPEN_BASEDIR ." is not writable.");
}


rename(BX_PROJECT_DIR."files/",BX_OPEN_BASEDIR."files/");
rename(BX_PROJECT_DIR."themes/",BX_OPEN_BASEDIR."themes/");
rename(BX_PROJECT_DIR."data/",BX_OPEN_BASEDIR."data/");

$prfold = $GLOBALS['POOL']->config->getTablePrefix(false);
$prfnew = $GLOBALS['POOL']->config->getTablePrefix();

bx_helpers_debug::webdump($prf);

 $db = $GLOBALS['POOL']->db;
foreach ($db->queryCol("show tables ") as $tbl) {
    if (strpos($tbl,$prfold) === 0 && !(strpos($tbl,$prfnew) === 0)) {
        $newtable = str_replace($prfold,$prfnew,$tbl);
        $query = "show create table $tbl";
        $row = $db->queryRow($query);
        
        printError($row);
        $create = $row[1];
        
        
        $query = 'DROP TABLE IF EXISTS `'.$newtable.'`' ;
        printError($db->query($query));
        
        $create = str_replace('CREATE TABLE `'.$tbl.'`','CREATE TABLE `'.$newtable.'`',$create);
        printError($db->query($create));
        
        $query = 'INSERT INTO `'.$newtable.'` SELECT * FROM `'.$tbl.'`';
        printError($db->query($query));

        $query = 'DROP TABLE `'.$tbl.'`';
        printError($db->query($query));        
    }
}



die("done");




// additional functions:
function printError($res) {
    if ($GLOBALS['POOL']->db->isError($res)) {
        print $res->message ."\n";
        print $res->userinfo ."\n";
        die();
    }
}



// include_once(BX_LIBS_DIR."/tools/dbupdate/update.php");

