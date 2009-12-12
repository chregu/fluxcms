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



$db = $GLOBALS['POOL']->db;

$sql = "SELECT distinct(path) FROM `{$tablePrefix}properties` where name = 'output-mimetype' AND  value = 'httpd/unix-directory'";

$res = $db->queryAll($sql, null, MDB2_FETCHMODE_ASSOC);
if (MDB2::isError($res)) {
    throw new PopoonDBException($res);
}

$used = array();

foreach($res as $row) {
    $path = $row['path'];
    
    echo $path;
    
    $coll = bx_collections::getCollection($path);
    
    $id = $coll->getProperty('unique-id');
    if(!$id) {
        $id = bx_helpers_sql::nextSequence();
        $coll->setProperty('unique-id', $id);
        echo " Generating uid -> ($id)";
    }
    else if(isset($used[$id])) {
        echo "<br /><span style=\"color:red;\">Duplicate id !</span><br />";
        $old = $id;
        $id = bx_helpers_sql::nextSequence();
        $coll->setProperty('unique-id', $id);
        echo " Correcting uid ($old -> $id)";
    }
    else {      
        echo " has uid $id ";
    }
    $used[$id] = $id;
    echo "<br>";
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

