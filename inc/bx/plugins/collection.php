<?php

class bx_plugins_collection extends bx_plugin {
     
    static private $instance = array();
    static private $idMapper = null;
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_collection::$instance[$mode])) {
            bx_plugins_collection::$instance[$mode] = new bx_plugins_collection($mode);
        } 
        return bx_plugins_collection::$instance[$mode];
    }   
           
    public function getContent($path, $name, $ext) {
        return FALSE;
    }
   
    public function resourceExists($path, $name, $ext) {
        return FALSE;
    }

    /**
    * gets the unique id of a resource associated to a request triple
    *
    * @param string $path the collection uri path
    * @param string $name the filename part
    * @param string $ext the extension
    * @return string id
    */ 
    protected function getResourceId ($path, $name, $ext) {
        if (!isset($this->idMapper[$path.$name])) {
            $this->idMapper[$path.$name] = bx_resourcemanager::getResourceId($path,$name,$ext);
        }
        return $this->idMapper[$path.$name];
    }
    
    public function getResourceByRequest($path, $name, $ext) {
        return new bx_resources_simplecollection($this->getResourceId($path, $name, $ext));
    }

}

?>
