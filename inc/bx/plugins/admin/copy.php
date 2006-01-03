<?php

class bx_plugins_admin_copy extends bx_plugins_admin implements bxIplugin {

    static private $instance = null;
    
    private function __construct() {
    }
    
    public static function getInstance($mode) {
        
        if (self::$instance === NULL) {
            self::$instance = new bx_plugins_admin_copy($mode);
        }
        
        return self::$instance;
    
    }
    
    public function getContentById($path, $id) {
        $to = $this->getParameter($path,"to");
        $move = $this->getParameter($path,"move");
        $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
        $dom = new domDocument();
        $response = $dom->createElement('response');
        if ($move) {
            $success = $parts['coll']->moveResourceById($parts['rawname'],$to);
            $msg = "$id moved to $to!";
           /* $response->setAttribute("updateTree","$id");
            $response->setAttribute("updateTree2","$to");*/
        } else {
            $success = $parts['coll']->copyResourceById($parts['rawname'],$to);
            $msg = "$id copied to $to!";
            /*$response->setAttribute("updateTree","$id");
            $response->setAttribute("updateTree2","$to");*/
        }
        
        
        if ($success) {
            $response->setAttribute("status","ok");
            $response->appendChild($dom->createTextNode($msg));
        } else {
            $response->setAttribute("status","failed");
            $response->appendChild($dom->createTextNode('failed'));
        }
        $dom->appendChild($response);
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
}


?>
