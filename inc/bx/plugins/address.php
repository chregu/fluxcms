<?php

class bx_plugins_address extends bx_plugin {
    
    
    static private $instance = null;
    
    protected function __construct() {
        $this->db = $GLOBALS['POOL']->db;
    }
    
    public static function getInstance() {
        
        if (!self::$instance) {
            self::$instance = new bx_plugins_address();
        } 
        return self::$instance;
    }   
    public function getOutputContent($path, $name, $ext) {
        
        $dom = new domDocument();
        $dom->loadXML($this->getResource($name)->getContent());
        return $dom;
    }
    
    
    public function getOutputChildren($coll, $name = "") {
        if (substr($name,-1) == "/") {
            $uri = $name;
        } else {
            $uri = bx_collections::sanitizeUrl(dirname($name));
        }
        $children = array();
        
        $res = $this->db->query("select * from address_address where parenturi = '$uri'");
        
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $children[] = new bx_resources_text_address($uri.str_replace(" ","_",$row['name']));
        }
        
        $res = $this->db->query("select * from address_collection where parenturi = '$uri'");
        
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            
            $children[] = new bx_resources_simplecollection($row['uri']);
        }
        return $children;
    }
    
    
    public function getInputContent($path, $name, $ext) {}
    
    public function resourceExists($path, $name, $ext) {
        $res = $this->getResource($name);
        if ($res) {
            return true;
        }
        
    }
    
    public function getResource( $name) {
        
        if (!isset($this->res[$name])) {
                $r = new bx_resources_text_address($name);
                if (!$r->isValid) {
                    $this->res[$name] = null;
                } else {
                    $this->res[$name] = $r;
                }
        }
        return $this->res[$name];
    }  
    
    public function getInputResourceByRequest($path, $name, $ext) {
        if ($ext == "collection") {
            return new bx_resources_simplecollection(bx_collections::sanitizeUrl($name));
        } 
        return new bx_resources_text_address($path.str_replace(" ","_",$name));
    }
}

?>
