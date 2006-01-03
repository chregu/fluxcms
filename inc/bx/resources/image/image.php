<?php

class bx_resources_image_image extends bx_resource {

    protected $fulluri = "";
    protected $mimetype = "image/image";
    protected $id = "";
    public $mock = false;
    
    public function __construct($id, $mock = false) {
        
        $p = bx_collections::getFileParts($id);
        
        $this->mimetype = $this->getMimeTypeByExtension($p['ext']);
        $this->fulluri = $id;
        $this->props['fileuri'] = BX_DATA_DIR.$id;
        $this->id = $id;
       
        if ($mock === true) {
           $this->mock = true;
        }
    }
        
    public function getContentUri($mock = false) {
        
        if ($mock) {
            return null;
        }
        return BX_DATA_DIR.$this->fulluri;
    }
    

    
    protected function init() {
        $this->setProperty("mimetype", $this->mimetype);
        $this->setProperty("output-mimetype", $this->mimetype);
        $this->setProperty("parent-uri",bx_collections::getCollectionUri($this->id));
        //parent::init();
    }
    
    public function getDisplayName() {
        $p = bx_collections::getCollectionUriAndFileParts($this->getID());
        return $p['name'];  
    }
     
    public function getOutputMimeType() {
        return $this->mimetype;
    }
     
    public function getEditors() {
        return array("image");   
    }
     
    // FIXME::extension to mime-type mapping shouldn't be here
    protected function getMimeTypeByExtension($ext) {
        switch($ext) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            break;
            case 'png':
                return 'image/png';
            break;
            case 'gif':
                return 'image/gif';
            break;
            default:
                return '';
        }
    }
}
