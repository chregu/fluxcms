<?php

class bx_plugins_admin_addresource extends bx_plugins_admin implements bxIplugin  {
    
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!bx_plugins_admin_addresource::$instance) {
            bx_plugins_admin_addresource::$instance = new bx_plugins_admin_addresource($mode);
        } 
        
        return bx_plugins_admin_addresource::$instance;
    }
    

   /* protected function getFullPath($path, $name, $ext) {
        return $path.$name;
    }*/
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        
        if ($ext) {
            return $name.".$ext";    
        } else if ($name == '') {
            return '/';
        } else {
            return $name;
        }
        
    }
    
    public function getContentById($path, $id) {
        $coll =  bx_collections::getCollection($id,"output");
        $resourceType = $this->getResourceType();
        
        if (($plugin = $coll->getPluginByResourceType($resourceType)) !== NULL ) {
             
            if ($xml = $plugin->getAddResourceParams($resourceType,$coll->uri)) {
                return $xml;
            } else {
                
                $pr = $plugin->addResource('', $id, array('type' => $resourceType));
                
            }
        }
        
        return null;
    }
    
    
    /* FIXME:: this should be cleaned up. arguments are $path,$id,$data,$mode */
    public function handlePost($path, $name, $ext, $data=null) {
        if ($data == NULL) {
            $data = $_REQUEST['bx']['plugins']['admin_addresource'];
        }
        
        $name = (strpos($name, '/') === 0) ? $name : "/$name";
        
        $coll = bx_collections::getCollection($name, "output");
        if ($coll instanceof bx_collection) {
            
            if (($plugin = $coll->getPluginByResourceType($this->getResourceType())) !== NULL) {
                $plugin->addResource(bx_helpers_string::makeUri($data['name']), $name, $data, $this->getResourceType());
            }
            
        }
    }
    
    protected function getResourceType() {
        return (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) ? $_REQUEST['type']:NULL;
    }

    protected function getParentCollection($path) {
        $parent = dirname($path);
        if ($parent != "/"){
            $parent .= '/';
        }
        
        return bx_collections::getCollection($parent,"output");
    }

    protected function getAction() {
        return !empty($_GET['action']) ? $_GET['action'] : FALSE;
    }
    
    public function resourceExists($path, $name, $ext) {
        return TRUE;
    }
    
    public function getDataUri($path, $name, $ext) {
        return FALSE;
    }

    public function getEditorsByRequest($path, $name, $ext) {
        return array();   
    }

    public function getStylesheetNameById($path = NULL, $id = NULL) {
        return 'addresource.xsl';
    }
    
   public function getPipelineName($path = NULL, $name = NULL, $ext = NULL) {
        return "standard";
    }
    
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return TRUE;
    }
    
}
?>
