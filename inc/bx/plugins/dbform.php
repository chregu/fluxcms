<?php

class bx_plugins_dbform extends bx_plugin implements bxIplugin {
    
    private $res = array();
    
    static private $instance = array();
    static private $idMapper = null;
    
    /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_dbform::$instance[$mode])) {
            bx_plugins_dbform::$instance[$mode] = new bx_plugins_dbform($mode);
        } 
        return bx_plugins_dbform::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    }
    
    /** resource methods **/
    
    public function resourceExists($path, $name, $ext) {
        if ($this->getResourceId($path,$name,$ext)) {
            return true;
        }
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
    
    
    /**
    * gets the resource object associated to an id
    *
    * this is the preferred method of doing things ;)
    *
    * @param string $id the id of the resource
    * @return object resource
    */
    
    protected function getResourceById($id) {
        return null;
    }
    /**
    * gets the resource object associated to a request triple
    *
    * @param string $path the collection uri path
    * @param string $name the filename part
    * @param string $ext the extension
    * @param string $new if the resource doesn't exist, should it return an appropriate resource object?
    * @return object resource
    */
    
    public function getResourceByRequest($path, $name, $ext, $new = false) {
        return null;
    }

    public function getMimeTypes() {
        return array("text/html","text/wiki");
    }
        

    
    /** output functions **/
    
    
    /**
    * gets the output content of a request
    *
    * @param string $path the collection uri path
    * @param string $name the filename part
    * @param string $ext the extension
    * @return object resource
    */
    
    public function getContent($path, $name, $ext) {
        $st2xml = new popoon_components_generators_structure2xml();
        $st2xml->db = $GLOBALS['POOL']->db;
        
        return $st2xml->showPage($this->getParameter("structure"));
    }
    
    public function getChildren($uri) {
        return array();
    }
    
    protected function getChildrenByMimeType($uri, $mimetype) {
        return array();
    }
    
    /** input methods **/
    

    
 
    /** pipeline methods **/
    
    public function handlePost($path, $name, $ext, $data) {
    }
    
    public function getEditorsByRequest($path, $name, $ext) {
        return array("dbform");
    }
}
?>
