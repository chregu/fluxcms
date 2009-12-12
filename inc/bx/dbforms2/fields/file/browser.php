<?php
/**
*
* @package bx_dbforms2
*/
/* example

<dbform:field name="pdf" uploaddir="/files/pdfs" type="file" descr="PDF Document"/>
        
*/

class bx_dbforms2_fields_file_browser extends bx_dbforms2_field {
    
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'file_browser';
        $this->XMLName = 'input';
        $this->attributes['isImage'] = FALSE;
    }

    protected function getXMLAttributes() {
        return array('isImage' => $this->attributes['isImage'] === TRUE ? '1' : '0');
    }
    
    public function getConfigAttributes() {
        $ret =  parent::getConfigAttributes();
        $ret['isImage'] = 'boolean';
        return $ret;
    }
    
    
}

?>