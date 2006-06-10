<?php

include_once("inc/bx/init.php");
bx_init::start('conf/config.xml', '');

//FIXME replace with PHP code...
$files = `cd data && find . -name "*.xhtml" -print`;


$files = explode("\n",trim($files));
foreach ($files as $file) {
    
    $file = substr($file,2);
    //print $file ."\n";
    
    print "index : ".$file ."\n";
     bx_metaindex::callIndexerFromFilename(BX_DATA_DIR.$file,"/".$file);
    
}

