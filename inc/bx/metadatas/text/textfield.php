<?php

class bx_metadatas_text_textfield extends bx_metadata {

    protected $size = 45;
    protected $maxLength = 60;
    
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
        $textField->setAttribute('type', 'textfield');
        $textField->setAttribute('size', $this->size);
        $textField->setAttribute('maxLength', $this->maxLength);

        return $textField;
    }

    public function isChangeable() {
        return TRUE;
    }
    
}

?>
