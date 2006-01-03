<?php

class bx_plugins_tree extends bx_plugin {
     
     static private $instance = array();
     
     public static function getInstance($mode) {
         if (!isset(bx_plugins_tree::$instance[$mode])) {
            bx_plugins_tree::$instance[$mode] = new bx_plugins_tree($mode);
        } 
        return bx_plugins_tree::$instance[$mode];
    }   
           
   public function getContent($path, $name, $ext) {
       
      $coll = bx_collections::getCollection($path, $this->mode);
      $dom = new domDocument();
      $dom->loadXML("<tree/>");
      
      foreach( $coll->getChildren($name) as $element => $entry) {
          
          switch ($entry->getProperty("output-mimetype")) {
              case "text/html":
              case "httpd/unix-directory":
                    
                    $el = $dom->createElement("item");
                    $te = $dom->createTextNode($entry->getDisplayName());
                    $el->setAttribute('mimetype',$entry->getProperty("output-mimetype")); 
                    $el->setAttribute('uri',$entry->getLocalName()); 
                    $el->appendChild($te);
                    $dom->documentElement->appendChild($el);
          
          }
      }
      
      return $dom;
   }
   
   
   public function getContentNode($path, $name, $ext) {
       
       
   }
   
   public function resourceExists($path, $name, $ext) {
       return false;
   }
   
}

?>
