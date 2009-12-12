<?php

class bx_plugins_admin_assets extends bx_plugins_navitree implements bxIplugin {
    
    static private $instance = null;
    protected $editor  = array();
    protected $res = array();
    
    public static function getInstance($mode) {
        if (!bx_plugins_admin_assets::$instance) {
            bx_plugins_admin_assets::$instance = new bx_plugins_admin_assets();
        } 
        return bx_plugins_admin_assets::$instance;
    }
    

    protected function getFullPath($path, $name, $ext) {
        // strip admin part of url - sort of weak
        $path = str_replace('admin/images/', '', $path);
        return $path.$name.'.'.$ext;
    }
    
    public function getContent($path, $name, $ext) {
        
          
        
        $fullPath = $this->getFullPath($path,$name,$ext);
        
        
         $tree = new bx_tree($fullPath, $this->mode,"/admin/images");
         
      
        $tree->setMimeTypes(array("image/jpg","text/html"));
        $tree->setElements(array("src","uri","preview"));
        return $tree->getXml();
    }
		
		/** bx_plugin::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
				$params = array();
				$params['pipelineName'] = 'xml';
				return $params;
		}
    
    public function getDataUri($path,$name,$ext) {
        return FALSE;
    }
    
    
    
    
}
?>
