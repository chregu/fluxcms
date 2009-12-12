<?php

class bx_helpers_dbforms2 {
    
    /**
     *  Completely deletes all contents of the structure2xml data cache.
     *
     *  @param string $queryType Type of the query executed
     *  @param object $form dbforms2 form object calling this function
     *  @access public
     */
    
    static function deleteStructureCache($queryType, $form) {
        if($queryType == 'update' || $queryType == 'delete') {
            // clean structure2xml cache
            bx_helpers_file::rmdir(BX_TEMP_DIR.'st2xml_data/');
        }
    }
    
    
}