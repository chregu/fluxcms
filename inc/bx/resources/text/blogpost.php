<?php

class bx_resources_text_blogpost extends bx_resource {
    
    protected $fulluri = "";
    protected $mimetype = "text/blogpost";
    protected $id = "";
    
    protected $localName = "";
    protected $lang = "";
    protected $newpost = false;
    
    public function __construct($id, $new = false) {
        
        $this->mimetype = "text/html";
        $this->fulluri = $id;
        $this->props['fileuri'] = BX_DATA_DIR.$id;
        $this->id = $id;
        
        if ($new === true) {
            $this->newpost = true;
        }
        
    }
        
    public function getContentUri() {
        
        return BX_DATA_DIR.$this->fulluri;
      
    }
    
    public function getContentUriSample() {
        
        if (isset($_REQUEST['template'])) {
            
            $theme = $GLOBALS['POOL']->config->getConfProperty('theme');
            $template = sprintf("%sthemes/%s/templates/%s", BX_PROJECT_DIR, $theme, $_REQUEST['template']);
            if (file_exists($template)) {
                return $template;
            }
            
        }
        
        return BX_LIBS_DIR.'doctypes/default.xhtml';
        
    }
    
    public function create() {
        $this->init();
    }
    
    protected function init() {
        bx_log::log('html::init');
        $this->setProperty("mimetype","text/html");
        $this->setProperty("output-mimetype","text/html");
        $this->setProperty("parent-uri",bx_collections::getCollectionUri($this->id));
        $this->setProperty("display-name",$this->getFileName());
        $this->setProperty("display-order",99);
        
        //parent::init();
    }
    
     public function getDisplayName() {
        $dn = $this->getProperty("display-name");
        if ($dn) {
            return $dn;
        }
        return $this->getFileName();
           
     }
     
     protected function parseName() {
         if (!$this->localName) {
         $p = bx_collections::getCollectionUriAndFileParts($this->getID());
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
     
     
     public function getOutputMimeType() {
         return "text/html";
     }
     
    public function getMimeType() {
         return "text/html";
     }
     
     public function getEditors() {
         if (popoon_classes_browser::isMozilla()) {
             return array("bxe","kupu","oneform");
         }
         else if (popoon_classes_browser::isMSIEWin()) {
             return array("kupu","oneform");
         } 
         return array("oneform");
     }
     
     public function getLanguage() {
          $this->parseName();
         return $this->lang;  
     }
     
     
     public function getFileName() {
         $this->parseName();
         return $this->localName;  
         
     }
     
     
     public function addResource($name, $parentUri, $options=array()) {
        
         $location = sprintf("%sadmin/blog/", BX_WEBROOT);
         header("Location: $location");
         exit(0);
     
     }
     
     
     
     
}
