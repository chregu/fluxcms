<?php
/** 
 * This examples allows everything, except the section /aboutus/ is only allowed to be accessed
 *  for logged in users
 */  

 class bx_permm_perm_simpleexample {

     function __construct() {
        
    }
    
    public function isEditable()
    {
    	return false;	
    }
    
    function isAllowed($uri, $actions, $userid) {
        if (in_array('admin',$actions)) {
          //if not logged in, not allowed to be admin...
          if (!$userid) {
              return false;
          }
          return true;
        }
        if (strpos($uri, '/aboutus/') === 0) {
                if ($userid) {
                    return true;
                }
                return false;
          
            
        }
        return true;   
        
    }
    
    

}



?>