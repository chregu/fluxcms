<?php

class bx_resources_simplecollection extends bx_resource {
    
    protected $fulluri = "";
    protected $mimetype = "httpd/unix-directory";
    protected $id = "";
    
    public function __construct($id, $new = false) {
        $this->id = $id;
        $this->props['mimetype'] = "httpd/unix-directory";
        if ($new) {
           $this->init();
        }
    }
    
   public function getProperty($name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        if ($name == "output-mimetype") {
            return $this->mimetype;
        }
    }
    
    
    public function getDisplayName() {
        return trim($this->id,'/');
    }
    
    public function getLocalName() {
        return ".".$this->id;   
    }
    
    public function getLastModified() {
        return time();
    }
    
    public function getOutputChildren($name) {
        
        // FIXME: hardcoded. bad. 
        $p = bx_plugins_address::getInstance();
        
        return $p->getOutputChildren("",$this->id);
    }
}