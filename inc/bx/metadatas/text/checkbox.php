<?php

class bx_metadatas_text_checkbox extends bx_metadata {

    
    public function __construct() {
        parent::__construct();
    }
    
    
    public function serializeToDOM() {
        $dom = new domDocument();
        $textField = $dom->createElement('metadata');
        $textField->setAttribute('type', 'checkbox');
        

        return $textField;
    }

    public function isChangeable() {
        return TRUE;
    }
    
}

?>
