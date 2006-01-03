<?php
/**
 * class bx_plugins_admin
 * @package bx_plugins
 * @subpackage admin
 * @todo write, what this plugin is used for ;)
 * */
abstract class bx_plugins_admin extends bx_component {
    
    
    public function getStylesheetNameById($path = NULL, $id = NULL) {
        return null;
    }
    
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    
    
    public function getPipelineName($path = NULL, $name = NULL, $ext = NULL) {
        return "standard";   
    }
    
    
    public function stripRoot() {
        return false;
    }
    
    public function addResource($name, $parentUri, $options=array(), $resourceType = null) {
        return false;
    }
    
    public function getResourceById($path, $id) {}
    public function getContentUriById($path, $id) {}
    
    public function getResourceByType($type, $path, $id) {}
    
}


?>
