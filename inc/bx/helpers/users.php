<?php

class bx_helpers_users {
    protected static $fullnames = array();
    
    public function getFullnameByUsername($username) {
        
        if (count(self::$fullnames) == 0) {
            if ($f = $GLOBALS['POOL']->cache->get("helpers_users_fullnames")) {
                self::$fullnames = $f;
            }
        }
        
        if (!isset(self::$fullnames[$username])) {
            $db = $GLOBALS['POOL']->db;    
            $query = "select user_fullname from ".$GLOBALS['POOL']->config->getTablePrefix()."users where user_login = ".$db->quote($username);
            $row = $db->queryOne($query);
            if ($row) {
                self::$fullnames[$username] = $row;
            } else {
                self::$fullnames[$username] = "";
            }
            $GLOBALS['POOL']->cache->set("helpers_users_fullnames",self::$fullnames,0,'table_users');
            
        }
        
        return self::$fullnames[$username];
        
        
        
    }
    
}

?>
