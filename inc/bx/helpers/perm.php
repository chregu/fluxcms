<?php

class bx_helpers_perm {
    static $accessHash = null;
    
    static function getUsername() {
        $perm = bx_permm::getInstance();
        return $perm->getUsername();
    }
    
    static function isAdmin() {
        $perm = bx_permm::getInstance();
        return $perm->isAllowed('/',array('admin'));
    }
    
    static function getAccessHash() {
        
        if (!self::$accessHash) {
            $db = $GLOBALS['POOL']->db;
            $px = $GLOBALS['POOL']->config->getTablePrefix();
        
            $query = "select value from ".$px."options where name='accesshash'";
            self::$accessHash = $db->queryOne($query);
            
            if (!self::$accessHash) {
                $query = "delete from ".$px."options where name = 'accesshash'";
                bx_helpers_debug::webdump($query);
                $GLOBALS['POOL']->dbwrite->query($query);
                
                $id = $db->nextId($px."_sequences");
                
                
                $h = md5(time() . rand(0,1000000000) . $GLOBALS['POOL']->config->magicKey.$id);
                
                $query = "insert into ".$px."options (id,name,value) values($id,'accesshash','$h')";
                self::$accessHash = $h;
                $GLOBALS['POOL']->dbwrite->query($query);
            }
        }
        return self::$accessHash;
        
    }
    static function updateAccessHash() {
        
            $db = $GLOBALS['POOL']->db;
            $px = $GLOBALS['POOL']->config->getTablePrefix();
        
            $id = $db->nextId($px."_sequences");
            $h = md5(time() . rand(0,1000000000) . $GLOBALS['POOL']->config->magicKey.$id);
                
            $query = "update ".$px."options set value ='$h' where name = 'accesshash'";
            $GLOBALS['POOL']->dbwrite->query($query);
            
        return $h;
        
    }
        
}

?>
