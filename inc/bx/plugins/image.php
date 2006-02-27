<?php

class bx_plugins_image extends bx_plugin {
    
    
    static private $instance = array();
    static private $idMapper = null;
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_image::$instance[$mode])) {
            bx_plugins_image::$instance[$mode] = new bx_plugins_image($mode);
        } 
        return bx_plugins_image::$instance[$mode];
    }
    
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
       
        if (!isset($this->idMapper[$path.$name])) {
             if (file_exists(BX_DATA_DIR.$path.$name.".".$ext)) {
                 $this->idMapper[$path.$name] = $name.".".$ext;
             }
        }
        
        return $this->idMapper[$path.$name];
    }
    
		public function getPipelineParametersById($path = NULL, $id = NULL) {
      // FIXME, we need another resource reader, it doesn't work if request != id
			return array('pipelineName'=>'resourceReader');
		}
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    public function getResourceById($path,$id, $mock = false) {
        $id = $path.$id;
        if (!isset($this->res[$id])) {
            
            if (file_exists(BX_DATA_DIR.$id)) {
                $this->res[$id] = new bx_resources_image_image($id);
            } else  if ($mock) {
                $this->res[$id] = new bx_resources_image_image($id, $mock);
            } else {
                $this->res[$id] = null;
            }
        }
        return $this->res[$id];
    }
    
       public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        switch (strtolower($ext)) {
            case "jpg":
            case "jpeg":
            case "gif":
            case "png":
                $res = $this->getResourceById($path, $id.".".$ext,$sample); 
                if ($res) {
                    return $this;
                }
            default:
                return null;
        }
    }
    
}

?>
