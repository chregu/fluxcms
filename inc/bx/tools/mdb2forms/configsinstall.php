<?php

$dir = new DirectoryIterator(".");

foreach ($dir  as $file) {
    
    $name = $file->getFileName();
    if (preg_match("#config\.([a-z_]+)\.xml#",$name,$matches)) {
        
        if (!file_exists($matches[1]."/config.xml")) {
        mkdir( $matches[1]);
        
        rename($name,$matches[1]."/config.xml");
        file_put_contents($matches[1]."/index.php",'<?php
include ("../form.php");
?>');        
}
        print "\n";
          
    }
    
}