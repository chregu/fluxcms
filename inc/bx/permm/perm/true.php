<?php

 class bx_permm_perm_true {

     function __construct() {
       
    }
    
    function isAllowed($uri, $action) {
        /*if (strpos($uri, '/aboutus/') === 0) {
            if (isset($userid)) { 
                return true;
            } else {
                return false;
            }
            
        }*/
        return true;   
        
    }
    
    

}



?>