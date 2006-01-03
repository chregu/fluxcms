<?php

class bx_resources_application_dbform extends bx_resource {
    
    protected $fulluri = "";
    protected $mimetype = "application/dbform";
    protected $id = "";
    
    protected $localName = "";
    protected $lang = "";
    
    public function __construct($id, $new = false) {
        
        $this->mimetype = "application/dbform";
        $this->fulluri = $id;
        $this->props['fileuri'] = BX_DATA_DIR.$id;
        $this->id = $id;
        if ($new === true) {
          $this->mock = true;
        }
    }
        
    public function getContentUri() {
        return BX_DATA_DIR.$this->fulluri;
      
    }
    
    public function getContentUriSample() {
        return BX_LIBS_DIR.'doctypes/dreispalt.xhtml';
    }
    
    public function create() {
        $this->init();
    }
    
    protected function init() {
        $this->setProperty("mimetype","application/dbform");
        $this->setProperty("output-mimetype","application/dbform");
        $this->setProperty("parent-uri",bx_collections::getCollectionUri($this->id));
        //parent::init();
    }
    
     public function getDisplayName() {
         
        return $this->displayName;
     }
     
     protected function parseName() {
         if (!$this->localName) {
             
         $p = bx_collections::getFileParts($this->id);
         if (preg_match("#(.*)\.([a-z]{2})$#",$p['name'],$matches)) {
             $this->localName = $matches[1];
             $this->lang = $matches[2];
         }
         else {
            $this->localName = $p['name'];
            $this->lang = "";
         }
         }
     
    }
     
    public function getDisplayOrder() {
        return $this->displayOrder;
    }
     public function getOutputMimeType() {
         return "text/html";
     }
     
      public function getMimeType() {
         return "application/dbform";
     }
     
     public function getEditors() {
        return array("dbform");   
     }
     
     public function getLanguage() {
          $this->parseName();
         return $this->lang;  
     }
     
     public function getFileName() {
        $this->parseName();
        return $this->localName;  
     }
     
     public function getInternalDBId() {
         $this->getFileName();
         $query = "select id from ".$this->table . " where " .$this->webdavId . " = '".bx_helpers_string::utf2entities($this->getFileName())."'";
         if ($this->langField) {
             $query ."and $this->langField = '".$this->getLanguage()."'";
         }
        
         $dbres = $GLOBALS['POOL']->db->query($query);
         if (!MDB2::isError($dbres)) {
             $res = $dbres->fetchRow();
            return $res[0];
         }
        return null;         
     }
     
     public function getLastModified() {
         return null;
     }
}
