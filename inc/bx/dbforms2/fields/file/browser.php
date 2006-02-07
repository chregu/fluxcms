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
    }
    
    
}

?>