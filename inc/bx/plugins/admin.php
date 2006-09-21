<?php
/**
 * class bx_plugins_admin
 * @package bx_plugins
 * @subpackage admin
 * @todo write, what this plugin is used for ;)
 * */
abstract class bx_plugins_admin extends bx_component {
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
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

		public function getPipelineParametersById($path, $id) { return array(); }
    public function getJavaScriptSources() {
	return array();
	}
    
    public function getOverviewSections($path,$mainOverview) {
        return false;
    }
}


?>
