<?php

Class bx_plugins_admin_navi extends bx_plugins_admin implements bxIplugin {

    static private $instance = null;
    
    protected $mode=null;
    
    private function __construct($mode) {
       $this->mode = $mode;
    }
    
    
    public static function getInstance($mode) {
        
        if (self::$instance === null) {
            self::$instance = new bx_plugins_admin_navi($mode);
        }
        
        return self::$instance;
        
    }
    
    
    public function getContentById($path, $id) {
        
        /* not of much use yet ;) */
        $dom = new domDocument();
        $pathnode = $dom->createElement('id');
        $pathnode->appendChild($dom->createTextNode($id));
        $dom->appendChild($pathnode);
        return $dom;
        
    }
    
    
    public function getResourceById($path,$id) {
        return false;
    }
    
    
    public function getIdByRequest($path, $name=NULL, $ext=NULL) {
        return "/$name.$ext";
    }
    
    
    public function getContentUriById($path, $id, $sample = false) {
        
        $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
        return $parts['coll']->getContentUriById($parts['rawname'],$sample);   
         
    }
    
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        
       
        return true;
    }
    
    
}

?>
