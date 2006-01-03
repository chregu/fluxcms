<?php

class bx_metadatas_text_readonly extends bx_metadata {

    public function __construct() {
        parent::__construct();
    }
    
    public function serializeToDOM() {
        $dom = new domDocument();
        
        $textField = $dom->createElement('metadata');
        $textField->setAttribute('type', 'readonly');

        return $textField;
    }

    public function isChangeable() {
        return FALSE;
    }
    
}

?>
