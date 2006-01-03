<?php
/** 
 * This examples allows everything, except the section /aboutus/ is only allowed to be accessed
 *  for logged in users
 */  

 class bx_permm_perm_simple {
     protected $perms = array();
     function __construct() {
     
        
    }
    
    function isAllowed($uri, $actions, $userid) {
        // FIXME: this code needs to be rewritten, it's getting messy. (there are 
        // too many "return false;"
        // The more actions there are, the bigger the chaos well be. 
        
        if (in_array('edit', $actions)) {
          //if not logged in, not allowed to be admin...
          if (!$userid) {
              return false;
          }
          // users whose gid has the 9th or the 1st bit set are allowed to edit
          if ((int)$_SESSION['_authsession']['data']['user_gid'] & 257) {
              return true;
          } else {
              return false;
          }
        }

        if (in_array('admin', $actions)) {
          //if not logged in, not allowed to be admin...
          if (!$userid) {
              return false;
          }
          // only users whose gid has the 1st bit set are allowed to be admins
          if ((int) $_SESSION['_authsession']['data']['user_gid'] & 1) {
              return true;
          } else {
              return false;
          }
        }
        
        foreach ($actions as $action) {
            if (!isset($this->perms[$uri])) {
                $this->perms[$uri] = array();
            }
            
            if (!isset($this->perms[$uri][$action])) {
                $this->perms[$uri][$action] = bx_resourcemanager::getFirstProperty($uri,$action);
                // if we get null back, no restriction was applied, everyone is allowed to access that
                if ($this->perms[$uri][$action] === null) {
                    $this->perms[$uri][$action] = 2;
                    return true;
                }
            }
            // 2 == everyone ;)
            if ((int) $this->perms[$uri][$action] & 2) {
                return true;
            }
            if ($userid && ((int) $_SESSION['_authsession']['data']['user_gid'] & (int) $this->perms[$uri][$action])) {
                return true;
            }
        }
        return false;
    }
}



?>