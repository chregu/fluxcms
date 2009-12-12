<?php

include_once("inc/bx/init.php");
bx_init::start('conf/config.xml', '');

//FIXME replace with PHP code...
$files = `cd data && find . -name "*.xhtml" -print`;
$prefix = $GLOBALS['POOL']->config->getTablePrefix();
$db = $GLOBALS['POOL']->db;
$files = $db->queryAll("select path from ".$prefix."properties where path like '%xhtml' group by path"); 

foreach ($files as $file) {
    $file = $file[0];
    //$file = substr($file,2);
    //print $file ."\n";
    
    if (file_exists('data'.$file)) {
    print "index : ".$file ."\n";
     bx_metaindex::callIndexerFromFilename('data'.$file,$file);
    }
    
}

