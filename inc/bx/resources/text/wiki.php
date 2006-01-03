<?php

class bx_resources_text_wiki extends bx_resource {

    protected $fulluri = "";
    protected $mimetype = "text/wiki";
    protected $id = "";
    
    public function __construct($id, $new = false ) {
        
        $this->fulluri = $id;
        $this->id = $id;
        $this->props['fileuri'] = BX_DATA_DIR.$id;
        if ($new) {
           $this->init();
        }
    }

    public function getInputContentUri() {
        return BX_DATA_DIR.$this->fulluri;
    }
    
    
    public function getContentUri() {
        bx_global::registerStream("wiki");
        return "wiki://".BX_DATA_DIR.$this->fulluri;
    }

    protected function init() {
        $this->setProperty("mimetype",$this->mimetype);
        $this->setProperty("output-mimetype","text/html");
        $this->setProperty("parent-uri",bx_collections::getCollectionUri($this->id));
    }
    
    public function getEditors() {
         
        return array("oneform");   
     }
     
     public function getOutputMimeType() {
         return "text/html";
     }
     
    
}