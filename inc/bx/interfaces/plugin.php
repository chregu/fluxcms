<?php
/**
* @package bx_interfaces
*/
interface bxIplugin {
    
    /**
    * Plugins are singleton classes, so we need the getInstance method
    */
    
    /* static public function getInstance($mode);*/
		
	 /**
		 * Returns resource id from request parts
		 * Resource ids are plugin-dependant (in "plugin space")
		 *
		 * @param path collection path
		 * @param name filenamr
		 * @param ext requested extension
		 * @return plugin-denendant resource id 
		 */
    public function getIdByRequest($path, $name = NULL, $ext  = NULL);
    
		/**
		 * Returns resource object identified by id.
		 * 
		 * @param path collection path
		 * @param id resource id in plugin space
		 * @return resource object or null
		 */
    public function getResourceById($path, $id); 

		/**
		 * Transforms resource id into url of content.
		 * 
		 * @param path collection path
		 * @param id resource id in plugin space
		 * @returns uri of any supported (by streams?) kind pointing to content
		 */
    public function getContentUriById($path, $id);
    
}

?>
