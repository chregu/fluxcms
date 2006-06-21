<?php

/**
 * The permissions matrix class autorizes actions based on the perms table
 */
class bx_permm_perm_matrix {

    function __construct() {

    }
    
    /**
     * If true a permissions link is created in each collection's overview
     */
    public function isEditable()
    {
    	return true;	
    }

	/**
	 * Check if the requested actions may be performed by the user
	 * 
	 * @param uri requested uri (most be a collection)
	 * @param actions array of actions
	 * @param userId reference to tabel users
	 * @return true if all actions may be performed
	 */
    public static function isAllowed($uri, $actions, $userId) {

    	//bx_helpers_debug::webdump($uri . ' ' . implode(',',$actions) . ' ' . $userId);  
    	//file_put_contents("debug.txt", $uri . ' ' . implode(',',$actions) . ' ' . $userId . "\n", FILE_APPEND);
    	//bx_helpers_debug::dump_backtrace();

    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
   
   		// make sure the first char is a slash
   		if($uri{0} != '/') {
   			$uri = '/'.$uri;
   		}
   		
   		// predefined roles
		if($userId) {
			$roles = "'anonymous', 'authenticated'";	
		} else {
			$roles = "'anonymous'";	
		}
    	
    	$cache = false;
    	
    	foreach($actions as $action) {
    		
    		$localUri = $uri;
    		
    		// copies simple perm behaviour
    		if($action == "read" or $action == "read_navi") {
    			
    			// real simple caching for frontend read requests
    			if(isset($_SESSION["perms"][$uri.'-'.$action.'-'.$userId])) {  
    				return $_SESSION["perms"][$uri.'-'.$action.'-'.$userId];
    			} else { 
    				$cache = $uri.'-'.$action.'-'.$userId;
    				$_SESSION["perms"][$cache] = false;
    			}
  
				$localUri = substr($uri, 0, strrpos($uri, '/')+1);
				$action = "collection-front-".$action;
				
				// the collection based approach doesn't work well with blog's virtual folders
				if(strpos($localUri, "/blog/") === 0) {
					$localUri = "/blog/";
				}
			}
			// admin and edit permissions are global
    		else if($action == "admin" or $action == "edit") {
    			$localUri = '/permissions/';
    			$action = "permissions-back-".$action;
    		}
    		else if($action == "isuser") {
    			if(!$userId) {
    				return false;
    			}
    			continue;
    		}
    		else if($action == "ishashed") {
	            if (!empty($_GET['ah']) && $_GET['ah'] == bx_helpers_perm::getAccessHash()) {
	                $_SESSION['fluxcms']['ah'] = $_GET['ah'];
	               continue;
	            } else if (!empty($_SESSION['fluxcms']['ah']) && $_SESSION['fluxcms']['ah'] == bx_helpers_perm::getAccessHash()) {
	                continue;
	            } else {
	                return false;
	            }
        	}
        	
        	list($plugin, $level, $name) = explode("-", $action);

    		// forward permission request if inherited
    		$query = "	SELECT p.inherit 
						FROM {$prefix}perms p 
						WHERE p.plugin='{$plugin}' 
						AND p.inherit!='' 
						AND p.uri='{$localUri}'";
        	$inherit = $GLOBALS['POOL']->db->queryOne($query);

        	if($inherit !== null) {
    			if(bx_permm_perm_matrix::isAllowed($inherit, array($action), $userId) == false) {
    				return false;	        		
    			}
        	}
        	
        	else {	
	    		// get the permission associated with this request
	    		$query = "	SELECT p.id 
							FROM {$prefix}perms p 
							JOIN {$prefix}users2groups u2g ON u2g.fk_group=p.fk_group 
							WHERE p.plugin='{$plugin}' 
							AND p.action='{$action}' 
							AND p.uri='{$localUri}' 
							AND u2g.fk_user='{$userId}'";
							
	        	$perms = $GLOBALS['POOL']->db->queryOne($query);
	    		
	    		
	    		if($perms === null) {
	    			// no permission found, try again with the predefined roles
	    			// TODO: merge the two of them into one query
				   	$query = "	SELECT p.id 
					FROM {$prefix}perms p 
					JOIN {$prefix}groups g ON g.id=p.fk_group 
					WHERE p.plugin='{$plugin}' 	
					AND p.action='{$action}' 
					AND p.uri='{$localUri}' 
					AND g.name IN ({$roles})";
	    			
	    			$perms = $GLOBALS['POOL']->db->queryOne($query);
	    			
	    			if($perms === null) {
			    		// deny by default
			    		
			    		//file_put_contents("debug.txt", $localUri . ' ' . implode(',',$actions) . ' ' . $userId);
			    		//bx_helpers_debug::webdump($uri . ' ' . implode(',',$actions) . ' ' . $userId);  
			    		return false;
			    		
	    			}
	    		}        
        	}
        	
        	if($cache !== false) {
        		// read/navi permission granted, cache it
    			$_SESSION["perms"][$cache] = true;
    			$cache = false;
    		}
    	}
    	
    	return true;
    }
}
?>
