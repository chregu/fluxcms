<?php

class bx_filters_formwizard_methods_mailman {

    static $table = 'newsletter';
    
    public static function subscribe($email, $morefields = array()) {
        if(isset(self::$table)) {
            
            $fields['email'] = $email;
            
            if(!empty($morefields)) {
                $fields = array_merge($fields, $morefields);
            }
            
            $query = "insert into ".self::$table." ".self::array2sql($fields);

            $res = $GLOBALS['POOL']->db->query($query);
            if(!MDB2::isError($res)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public static function unsubscribe($email) {
        if(isset(self::$table)) {
            $query = "delete from ".self::$table." where email = ";
            $query .= $GLOBALS['POOL']->db->quote($email);
            
            $res = $GLOBALS['POOL']->db->query($query);
            if(!MDB2::isError($res)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public static function array2sql($input) {
        $fields = array_keys($input);
        $values = array_values($input);
        
        foreach($values as $i => $val) {
            $values[$i] = $GLOBALS['POOL']->db->quote($val);
        }
        
        $sql = '('.implode(',', $fields).') values ('.implode(',', $values).')';
        return $sql;
    }

}

?>
