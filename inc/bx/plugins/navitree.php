<?php

class bx_plugins_navitree extends bx_plugin {
     
     static private $instance = array();
     
     public static function getInstance($mode) {
         if (!isset(bx_plugins_navitree::$instance[$mode])) {
            bx_plugins_navitree::$instance[$mode] = new bx_plugins_navitree($mode);
        } 
        return bx_plugins_navitree::$instance[$mode];
    }   
           
           
    public function getIdByRequest ($path, $name = NULL, $ext = NULL) {
        return "navi.$name.$ext";
    }
    
   public function getContentById($path, $id) {
       
      // get up path
      $tree = new bx_tree($path, $this->mode);
      $tree->setRecursive(true);
      if ($lo = $this->getParameter($path,"levelOpen")) {
          $tree->setLevelOpen($lo);
      }
      
      if ($lo = $this->getParameter($path,"levelOpenStart")) {
          $tree->setLevelOpenStart($lo);
      }
      
      $tree->setMimeTypes(array("text/html"));
      //$tree->setProperties(array("navi"));
      return $tree->getXml();
      
   }
   
   public function resourceExists($path, $name, $ext) {
       return false;
   }

}

?>
