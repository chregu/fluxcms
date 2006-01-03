<?php

class bx_dbforms2_fields_text_area_small extends bx_dbforms2_fields_text_area {
    
    protected function getXMLAttributes() {
        return array(
            'cols' => 80,
            'rows' => 3
        );
    }
    
}

?>
