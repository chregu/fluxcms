<?php

class bx_helpers_users {
    protected static $fullnames = array();
    
    public function getFullnameByUsername($username) {
        
        
        if (!isset(self::$fullnames[$username])) {
            $db = $GLOBALS['POOL']->db;    
            $query = "select user_fullname from ".$GLOBALS['POOL']->config->getTablePrefix()."users where user_login = ".$db->quote($username);
            $row = $db->queryOne($query);
            if ($row) {
                self::$fullnames[$username] = $row;
            } else {
                self::$fullnames[$username] = null;
            }
            
        }
        
        return self::$fullnames[$username];
        
        
        
    }
    
}

?>
