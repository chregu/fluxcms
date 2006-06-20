<?php

class bx_permm_perm_true {
    function __construct() {
        
    }
    
    public function isEditable()
    {
    	return false;	
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
        } 
        $ishashed = in_array('ishashed', $actions);
        $isuser = in_array('isuser',$actions);
        if ($ishashed || $isuser) {
                    if ($ishashed ) {
                        $ishashed = false;
                        if (!empty($_GET['ah']) && $_GET['ah'] == bx_helpers_perm::getAccessHash()) {
                            $ishashed = true;
                            $_SESSION['fluxcms']['ah'] = $_GET['ah'];
                        } else if (!empty($_SESSION['fluxcms']['ah']) && $_SESSION['fluxcms']['ah'] == bx_helpers_perm::getAccessHash()) {
                            $ishashed = true;
                        } 
                    }
                    if ($isuser && !$userId) {
                        $isuser = false;
                    }
                    return ($ishashed || $isuser);   
        }
        
        return true;
    }
}



?>