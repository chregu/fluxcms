<?php

class bx_resources_simple extends bx_resource {
    
    protected $fulluri = "";
    protected $mimetype = "";
    protected $id = "";
    
    public function __construct($id, $mock = false) {
        $this->mimetype = "text/html";
        $this->fulluri = $id;
        $this->id = $id;
        if ($mock === true) {
           $this->mock = true;
        }
        $this->props['fileuri'] = $id;
    }
    
    protected function init() {
    }
    
 
    
}  
  