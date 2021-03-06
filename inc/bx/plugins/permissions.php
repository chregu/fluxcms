<?php

/**
 * matrix permission system plugin
 */
class bx_plugins_permissions extends bx_plugin implements bxIplugin {

	static public $instance = array ();

	public static function getInstance ( $mode ) {
		if (! isset ( self::$instance [ $mode ])) {
		self::$instance[$mode] = new bx_plugins_permissions ( $mode );
		}
		return self::$instance [ $mode ];
	}

	public function __construct ( $mode  = "output" ) {
		$this -> mode = $mode ;
	}
	
	/**
	 * list of available permissions
	 */
    public function getPermissionList() {
    	return array(	"permissions-back-manage", 
    					"admin_dbforms2-back-users",
    					"admin_dbforms2-back-perm_groups",
    					"permissions-back-admin",
    					"permissions-back-siteoptions",
    					"permissions-back-themes");	
    }

	public function getEditorsById($path, $id) {
        return array("permissions");
       	
    }
    
    public function getMimeTypes() {
        return array("text/html");
    }
	
	public function isRealResource ( $path , $id) {
		return true ;
	}
	
	public function adminResourceExists($path, $id, $ext=null, $sample = false) {
		if($ext == 'xhtml') {
			return false;
		} 
        return true;
    }

	public function getOverviewSections($path) {
        $sections = array();
        $dom = new bx_domdocs_overview();
        
        $dom->setTitle("Permissions", "Permissions");
        $dom->setPath($path);
        $dom->setIcon("gallery");
        
        // fluxcms is currently using another dbforms for the user management, but this one
        // supports group assignment
        $perm = bx_permm::getInstance();
        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-users'))) {
	        $dom->addLink("Edit Users","../admin/dbforms2/users/");
        }    
        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-perm_groups'))) {
	        $dom->addLink("Edit Groups","../admin/dbforms2/perm_groups/");
        }   
        
        return $dom;
    }
}
?>
