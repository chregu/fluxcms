<?php

class bx_versioning {
    
    private $repodirs = array('data','files');
    
    private function __construct() {}
    
    public static function versioning($driver='', $opts=array()) {
        switch($driver) {
            default:
                return bx_versioning_svn::getInstance($opts);
            break;
        }
    }

}

?>
