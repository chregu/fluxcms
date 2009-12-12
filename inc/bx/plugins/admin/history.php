<?php
class bx_plugins_admin_history extends bx_component implements bxIplugin {
	
	private static $instance;
	
	public static function getInstance($mode) {
		if (!self::$instance) {
			self::$instance = new bx_plugins_admin_history($mode);
		}
		
		return self::$instance;
	}
	
    public function adminResourceExists($path, $id, $ext=null) {
        return $this; 
    }
    
    
    public function stripRoot() {
        return false;
    }
      
	
		 /**
		 * Returns resource id from request parts
		 * Resource ids are plugin-dependant (in "plugin space")
		 *
		 * @param path collection path
		 * @param name filenamr
		 * @param ext requested extension
		 * @return plugin-denendant resource id 
		 */
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
    	
    }
    
		/**
		 * Returns resource object identified by id.
		 * 
		 * @param path collection path
		 * @param id resource id in plugin space
		 * @return resource object or null
		 */
    public function getResourceById($path, $id) {
    	
    }

		/**
		 * Transforms resource id into url of content.
		 * 
		 * @param path collection path
		 * @param id resource id in plugin space
		 * @returns uri of any supported (by streams?) kind pointing to content
		 */
    public function getContentUriById($path, $id) {
    	
    }
    

    
    public function getContentById ($path, $id) {
    	$vconfig = $GLOBALS['POOL']->config->getConfProperty('versioning');
        if ($vconfig && !empty($vconfig)) {
    		$v = bx_versioning::versioning($vconfig);
    		if(!$v instanceof bx_versioning_interface) {
                throw new Exception("Unable to load versioning support (no driver available).");
    		}
			return $v->getListById($id);
        }
    }
}