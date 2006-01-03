<?php
/**
* @package bx_interfaces
*/
interface bxIplugin {
    
    /**
    * Plugins are singleton classes, so we need the getInstance method
    */
    
    /* static public function getInstance($mode);*/
    
    
    public function getResourceById($path, $id); 

    public function getIdByRequest($path, $name = NULL, $ext  = NULL);
    
    public function getContentUriById($path, $id);
    
    
}

?>
