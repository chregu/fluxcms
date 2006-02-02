<?php

class bx_metadatas_text_textarea extends bx_metadata {

    protected $rows = 6;
    protected $cols = 60;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function serializeToDOM() {
        $dom = new domDocument();
        
        $textField = $dom->createElement('metadata');
        $textField->setAttribute('type', 'textarea');
        $textField->setAttribute('rows', $this->rows);
        $textField->setAttribute('cols', $this->cols);

        return $textField;
    }

    public function isChangeable() {
        return TRUE;
    }
    
}

?>