<?php

class bx_permm_perm_true {
    function __construct() {
        
    }
    
    function isAllowed($uri, $actions,$userId) {
        if (in_array('admin',$actions)) {
            if ($userId) {
                if (isset($_SESSION['_authsession']['data']['user_gid']) && (int) $_SESSION['_authsession']['data']['user_gid'] == 2) {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else if (in_array('isuser',$actions) && !$userId) {
            return false;
        }
        return true;
    }
}



?>