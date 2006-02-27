<?php

class bx_editors_dbform extends bx_editor implements bxIeditor {    
    
    
    public function __construct($id = NULL) {
        /*header("Location: /admin/form/?id=$id");
        die();*/
       /* bx_helpers_debug::dump_backtrace();
        bx_helpers_debug::webdump($id);*/
    }   
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'dbform');
    }
    
    public function getDisplayName() {
        return "DB Formular";
    }
    
        
    public function handlePOST($path, $id, $data) {
     
        $parts = bx_collections::getCollectionAndFileParts($id,"admin");
       $parts['coll']->handlePostById($parts['rawname'],$data,"FullXML");
        
            
    }
    
}

?>
