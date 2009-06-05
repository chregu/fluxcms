<?php

class bx_metadatas_text_filebrowser extends bx_metadata {

    protected $size = 45;
    protected $maxLength = 0;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function setSize($size) {
        $this->size = $size;
    }

    public function setMaxLength($maxLength) {
        $this->maxLength = $maxLength;
    }
    
    public function serializeToDOM() {
        $dom = new domDocument();
        $textField = $dom->createElement('metadata');
        $textField->setAttribute('type', 'fileupload');
        $textField->setAttribute('size', $this->size);
        if ($this->maxLength > 0) {
            $textField->setAttribute('maxLength', $this->maxLength);
        }

        return $textField;
    }

    public function isChangeable() {
        return TRUE;
    }
    
}

?>
