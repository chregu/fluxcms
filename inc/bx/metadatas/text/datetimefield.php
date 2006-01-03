<?php

class bx_metadatas_text_datetimefield extends bx_metadata {

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
        $textField->setAttribute('type', 'datetime');

        return $textField;
    }

    public function isChangeable() {
        return TRUE;
    }

    public function getPropertyValueFromPOSTValue($value,$res) {
        $v = bx_helpers_date::normalizeDate($value); 
        
        return $v;
    }
}

?>
